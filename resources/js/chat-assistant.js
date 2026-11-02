// Chatbot "assistant de dépôt". La conversation est gardée dans sessionStorage
// (survit aux changements de page, effacée à la fermeture de l'onglet) et envoyée à chaque message.

const root = document.querySelector('[data-assistant]');

if (root) {
    const STORAGE_KEY = 'textilecycle.assistant';
    const MAX_HISTORY = 12; // doit correspondre à la validation côté serveur
    const WELCOME = 'Bonjour ! Dites-moi où vous êtes (ou partagez votre position) et combien de vêtements vous avez : je vous indique le meilleur point de collecte.';

    const panel = root.querySelector('[data-assistant-panel]');
    const list = root.querySelector('[data-assistant-messages]');
    const form = root.querySelector('[data-assistant-form]');
    const input = form.querySelector('input');
    const sendButton = form.querySelector('button');
    const suggestions = root.querySelector('[data-assistant-suggestions]');
    const locateButton = root.querySelector('[data-assistant-locate]');
    const locationStatus = root.querySelector('[data-assistant-location-status]');

    const state = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || 'null') ?? { open: false, messages: [], position: null };
    const save = () => sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));

    // ---------- Affichage ----------

    const bubble = (role, text) => {
        const div = document.createElement('div');
        div.className = role === 'user'
            ? 'ml-8 rounded-2xl rounded-br-sm bg-emerald-600 px-3 py-2 text-white whitespace-pre-line'
            : 'mr-8 rounded-2xl rounded-bl-sm bg-white px-3 py-2 text-slate-800 shadow-sm whitespace-pre-line';
        div.textContent = text; // textContent : la réponse de l'IA n'est jamais interprétée comme du HTML
        list.appendChild(div);
        return div;
    };

    const pointCards = (points) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'mr-8 space-y-2';

        points.forEach((point, index) => {
            const card = document.createElement('div');
            card.className = `rounded-xl border bg-white p-3 ${index === 0 ? 'border-emerald-500' : 'border-slate-200'}`;

            const title = document.createElement('p');
            title.className = 'font-semibold text-slate-900';
            title.textContent = `${index === 0 ? '⭐ ' : ''}${point.name}`;

            const details = document.createElement('p');
            details.className = 'text-xs text-slate-600';
            const parts = [point.city];
            if (point.distance_km !== null) parts.push(`${String(point.distance_km).replace('.', ',')} km`);
            parts.push(`rempli à ${point.fill_percent} %${point.nearly_full ? ' (presque plein)' : ''}`);
            details.textContent = parts.join(' · ');

            const actions = document.createElement('div');
            actions.className = 'mt-2 flex gap-3 text-xs font-semibold';
            const link = (href, label, external = false) => {
                const a = document.createElement('a');
                a.href = href;
                a.textContent = label;
                a.className = 'text-emerald-700 hover:underline';
                if (external) { a.target = '_blank'; a.rel = 'noopener'; }
                actions.appendChild(a);
            };
            link(point.deposit_url, 'Déposer ici →');
            if (point.directions_url) link(point.directions_url, 'Itinéraire', true);

            card.append(title, details, actions);
            wrapper.appendChild(card);
        });

        list.appendChild(wrapper);
    };

    const render = () => {
        list.innerHTML = '';
        bubble('assistant', WELCOME);
        state.messages.forEach((m) => {
            bubble(m.role, m.content);
            if (m.points?.length) pointCards(m.points);
        });
        suggestions.hidden = state.messages.length > 0;
        locationStatus.textContent = state.position ? ' ✓ position partagée' : '';
        list.scrollTop = list.scrollHeight;
    };

    const setOpen = (open) => {
        state.open = open;
        panel.hidden = !open;
        save();
        if (open) {
            render();
            input.focus();
        }
    };

    // ---------- Envoi ----------

    const send = async (text) => {
        text = text.trim();
        if (!text) return;

        state.messages.push({ role: 'user', content: text });
        save();
        render();
        input.value = '';
        sendButton.disabled = true;
        const typing = bubble('assistant', '…');
        list.scrollTop = list.scrollHeight;

        try {
            const { data } = await window.axios.post(root.dataset.endpoint, {
                // Seuls role/content partent au serveur (pas les cartes de points)
                messages: state.messages.slice(-MAX_HISTORY).map(({ role, content }) => ({ role, content })),
                ...(state.position ?? {}),
            });
            state.messages.push({ role: 'assistant', content: data.reply || '…', points: data.points ?? [] });
        } catch (error) {
            const status = error.response?.status;
            const message = status === 429
                ? 'Vous envoyez beaucoup de messages. Patientez une minute avant de réessayer.'
                : error.response?.data?.message ?? 'Une erreur est survenue. Réessayez dans un instant.';
            // L'erreur est affichée mais pas renvoyée au modèle : on retire la question pour pouvoir la reposer
            typing.remove();
            state.messages.pop();
            save();
            render();
            bubble('assistant', `⚠️ ${message}`);
            input.value = text;
            sendButton.disabled = false;
            return;
        }

        save();
        render();
        sendButton.disabled = false;
        input.focus();
    };

    // ---------- Événements ----------

    root.querySelector('[data-assistant-toggle]').addEventListener('click', () => setOpen(panel.hidden));
    root.querySelector('[data-assistant-close]').addEventListener('click', () => setOpen(false));
    root.querySelector('[data-assistant-reset]').addEventListener('click', () => {
        state.messages = [];
        save();
        render();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        send(input.value);
    });

    suggestions.querySelectorAll('button').forEach((button) => {
        button.addEventListener('click', () => send(button.textContent));
    });

    locateButton.addEventListener('click', () => {
        if (!window.isSecureContext || !navigator.geolocation) {
            locationStatus.textContent = ' La localisation nécessite HTTPS : indiquez plutôt votre ville.';
            return;
        }
        locationStatus.textContent = ' localisation…';
        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                state.position = { latitude: Number(coords.latitude.toFixed(5)), longitude: Number(coords.longitude.toFixed(5)) };
                save();
                locationStatus.textContent = ' ✓ position partagée';
            },
            () => { locationStatus.textContent = ' Position refusée : indiquez votre ville.'; },
            { timeout: 10000 },
        );
    });

    if (state.open) setOpen(true);
}
