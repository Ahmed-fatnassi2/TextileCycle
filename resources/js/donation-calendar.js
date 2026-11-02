import { Calendar } from '@fullcalendar/core';
import frLocale from '@fullcalendar/core/locales/fr';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

// Calendrier admin des livraisons de dons.
// Glisser-déposer -> PATCH /admin/donations/{id}/reschedule. Le serveur vérifie la capacité :
// en cas de refus, l'événement revient à sa place et le message d'erreur s'affiche.
const element = document.querySelector('[data-donation-calendar]');

if (element) {
    const maxDeliveries = Number(element.dataset.dailyDeliveries);
    const maxItems = Number(element.dataset.dailyItems);
    const flash = document.querySelector('[data-calendar-flash]');
    let flashTimer;

    const showFlash = (message, ok) => {
        flash.textContent = message;
        flash.className = `mb-4 border px-4 py-3 text-sm ${ok
            ? 'border-emerald-300 bg-emerald-50 text-emerald-900'
            : 'border-rose-300 bg-rose-50 text-rose-900'}`;
        flash.hidden = false;
        clearTimeout(flashTimer);
        flashTimer = setTimeout(() => { flash.hidden = true; }, 7000);
    };

    // Affiche "2/3 · 150/300 pcs" dans chaque case et colore les jours complets
    const renderDayLoads = (calendar) => {
        const loads = {};
        calendar.getEvents().forEach((event) => {
            const day = event.startStr.slice(0, 10);
            loads[day] ??= { deliveries: 0, items: 0 };
            loads[day].deliveries += 1;
            loads[day].items += event.extendedProps.quantity;
        });

        element.querySelectorAll('.fc-daygrid-day[data-date]').forEach((cell) => {
            const load = loads[cell.dataset.date];
            let badge = cell.querySelector('[data-day-load]');

            if (!load) {
                badge?.remove();
                cell.classList.remove('bg-rose-50');
                return;
            }

            if (!badge) {
                badge = document.createElement('div');
                badge.dataset.dayLoad = '';
                cell.querySelector('.fc-daygrid-day-top')?.prepend(badge);
            }

            const full = load.deliveries >= maxDeliveries || load.items >= maxItems;
            badge.className = `mr-auto px-1 pt-1 text-[10px] font-semibold ${full ? 'text-rose-700' : 'text-slate-500'}`;
            badge.textContent = `${load.deliveries}/${maxDeliveries} · ${load.items}/${maxItems} pcs`;
            badge.title = full ? 'Jour complet' : 'Livraisons · pièces prévues';
            cell.classList.toggle('bg-rose-50', full);
        });
    };

    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, interactionPlugin, listPlugin],
        locale: frLocale,
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,dayGridWeek,listMonth' },
        height: 'auto',
        firstDay: 1,
        dayMaxEvents: 4,
        editable: true,
        eventStartEditable: true,
        eventDurationEditable: false,
        events: element.dataset.eventsUrl,

        eventDidMount: ({ event, el }) => {
            const p = event.extendedProps;
            el.title = [
                `${p.reference ?? ''} · ${p.association}`,
                `${p.quantity} pièces · ${p.category ?? ''} · ${p.status}`,
                p.city ? `Livraison : ${p.city}` : null,
                event.startEditable === false ? '(livré : non déplaçable)' : null,
            ].filter(Boolean).join('\n');
        },

        eventsSet: () => renderDayLoads(calendar),
        datesSet: () => renderDayLoads(calendar),

        eventDrop: async ({ event, revert }) => {
            try {
                const { data } = await window.axios.patch(event.extendedProps.rescheduleUrl, { date: event.startStr.slice(0, 10) });
                showFlash(data.message, data.emailSent);
                renderDayLoads(calendar);
            } catch (error) {
                revert();
                const errors = error.response?.data?.errors;
                const message = errors ? Object.values(errors).flat()[0] : error.response?.data?.message;
                showFlash(message ?? 'Le déplacement a échoué. Réessayez.', false);
            }
        },
    });

    calendar.render();

    initPlanningAssistant(calendar, showFlash);
}

// ---------- Assistant de planning IA ----------
// Affiche les propositions de l'IA ; « Accepter » réutilise l'endpoint du glisser-déposer
// (le serveur revérifie la capacité et envoie l'email à l'association).
function initPlanningAssistant(calendar, showFlash) {
    const panel = document.querySelector('[data-planning-assistant]');
    if (!panel) return;

    const runButton = panel.querySelector('[data-planning-run]');
    const result = panel.querySelector('[data-planning-result]');

    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text; // textContent : le texte de l'IA n'est jamais du HTML
        return node;
    };

    const accept = async (proposal, row) => {
        row.querySelectorAll('button').forEach((b) => { b.disabled = true; });
        try {
            const { data } = await window.axios.patch(proposal.reschedule_url, { date: proposal.to });
            row.classList.add('opacity-60');
            row.querySelector('[data-actions]').replaceChildren(el('span', 'text-xs font-semibold text-emerald-700', data.emailSent ? '✔ Appliqué · email envoyé' : '✔ Appliqué · email non envoyé'));
            calendar.refetchEvents();
            return true;
        } catch (error) {
            const errors = error.response?.data?.errors;
            const message = errors ? Object.values(errors).flat()[0] : error.response?.data?.message;
            row.querySelector('[data-actions]').replaceChildren(el('span', 'text-xs font-semibold text-rose-700', `✖ ${message ?? 'Échec'}`));
            return false;
        }
    };

    const render = (data) => {
        result.replaceChildren();
        result.hidden = false;

        result.appendChild(el('p', 'text-sm text-slate-800', data.summary));

        const facts = [`${data.stats.candidates} livraison(s) analysée(s)`];
        if (data.stats.overdue) facts.push(`${data.stats.overdue} en retard`);
        if (data.discarded) facts.push(`${data.discarded} proposition(s) écartée(s) car elles ne respectaient pas la capacité ou les dates`);
        result.appendChild(el('p', 'mt-1 text-xs text-slate-500', facts.join(' · ')));

        if (!data.proposals.length) {
            result.appendChild(el('p', 'mt-3 text-sm font-semibold text-emerald-800', 'Aucun déplacement nécessaire.'));
            return;
        }

        const list = el('div', 'mt-4 divide-y divide-violet-100 border border-violet-200 bg-white');
        const rows = [];

        data.proposals.forEach((proposal) => {
            const row = el('div', 'flex flex-wrap items-center justify-between gap-3 p-3');
            const info = el('div', 'min-w-0 flex-1');

            const title = el('p', 'text-sm font-semibold text-slate-900', `${proposal.association} · ${proposal.quantity} pièces`);
            if (proposal.overdue) title.appendChild(el('span', 'ml-2 bg-rose-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-rose-800', 'en retard'));
            if (proposal.status === 'Validé') title.appendChild(el('span', 'ml-2 bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-sky-800', 'déjà confirmé'));

            info.append(
                title,
                el('p', 'text-sm text-slate-700', `${proposal.from_label} → ${proposal.to_label}`),
                el('p', 'text-xs text-slate-500', proposal.reason),
            );

            const actions = el('div', 'flex gap-2');
            actions.dataset.actions = '';
            const acceptButton = el('button', 'bg-violet-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-800 disabled:opacity-50', 'Accepter');
            const ignoreButton = el('button', 'border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50', 'Ignorer');
            acceptButton.type = ignoreButton.type = 'button';
            acceptButton.addEventListener('click', () => accept(proposal, row));
            ignoreButton.addEventListener('click', () => {
                row.classList.add('opacity-50');
                actions.replaceChildren(el('span', 'text-xs text-slate-500', 'Ignorée'));
            });
            actions.append(acceptButton, ignoreButton);

            row.append(info, actions);
            list.appendChild(row);
            rows.push({ proposal, row });
        });

        result.appendChild(list);

        // Les propositions sont pensées dans l'ordre (une peut libérer la place pour la suivante)
        const acceptAll = el('button', 'mt-3 bg-violet-700 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-800 disabled:opacity-50', 'Tout accepter');
        acceptAll.type = 'button';
        acceptAll.addEventListener('click', async () => {
            acceptAll.disabled = true;
            let applied = 0;
            for (const { proposal, row } of rows) {
                if (row.querySelector('[data-actions] button')) {
                    applied += (await accept(proposal, row)) ? 1 : 0;
                }
            }
            showFlash(`${applied} déplacement(s) appliqué(s) sur ${rows.length}. Le détail (email, refus) est indiqué sur chaque ligne.`, applied > 0);
        });
        result.appendChild(acceptAll);
    };

    runButton.addEventListener('click', async () => {
        runButton.disabled = true;
        runButton.textContent = 'Analyse en cours…';
        result.hidden = false;
        result.replaceChildren(el('p', 'text-sm text-slate-500', 'L’IA étudie le planning, les capacités et les besoins des associations…'));

        try {
            const { data } = await window.axios.post(panel.dataset.optimizeUrl);
            render(data);
        } catch (error) {
            const message = error.response?.status === 429
                ? 'Trop d’analyses en peu de temps. Patientez une minute.'
                : error.response?.data?.message ?? 'L’analyse a échoué. Réessayez.';
            result.replaceChildren(el('p', 'text-sm font-semibold text-rose-700', message));
        } finally {
            runButton.disabled = false;
            runButton.textContent = 'Relancer l’analyse';
        }
    });
}
