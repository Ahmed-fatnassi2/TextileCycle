// "Idées de produits" (IA) à partir d'une matière, avec pagination dynamique.
// data-mode="stock"   : admin, matière en stock -> produits à fabriquer, bouton "Créer ce produit" (formulaire prérempli)
// data-mode="visitor" : public, matière du visiteur -> idées à faire soi-même + produits du catalogue
//
// Pagination dynamique : 3 idées par page. Les pages déjà chargées s'affichent instantanément ;
// "Suivant" sur la dernière page demande de nouvelles idées à l'IA, en lui envoyant celles déjà
// affichées (paramètre exclude) pour qu'elle n'en repropose aucune.
const panel = document.querySelector('[data-upcycling-ideas]');

if (panel) {
    const PER_PAGE = 3;
    const MAX_IDEAS = 30; // = UpcyclingIdeaGenerator::MAX_EXCLUDED côté serveur

    const mode = panel.dataset.mode;
    const form = panel.querySelector('[data-ideas-form]');
    const runButton = panel.querySelector('[data-ideas-run]');
    const result = panel.querySelector('[data-ideas-result]');
    const runLabel = runButton?.textContent;

    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text; // textContent : le texte de l'IA n'est jamais du HTML
        return node;
    };

    const money = (value) => Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const kg = (value) => Number(value).toLocaleString('fr-FR', { maximumFractionDigits: 2 });
    const difficultyColors = { facile: 'bg-emerald-100 text-emerald-800', moyen: 'bg-amber-100 text-amber-800', 'avancé': 'bg-rose-100 text-rose-800' };

    const errorMessage = (error) => {
        const errors = error.response?.data?.errors;
        if (error.response?.status === 429) return 'Trop de demandes en peu de temps. Patientez une minute.';
        return (errors ? Object.values(errors).flat()[0] : error.response?.data?.message) ?? 'La génération a échoué. Réessayez.';
    };

    const cardHead = (idea) => {
        const head = el('div', 'flex items-start justify-between gap-2');
        head.append(
            el('h3', 'font-semibold text-slate-900', idea.name),
            el('span', `shrink-0 rounded px-2 py-0.5 text-[11px] font-semibold ${difficultyColors[idea.difficulty] ?? ''}`, idea.difficulty),
        );
        return head;
    };

    // ---------- Cartes ----------

    const stockCard = (idea) => {
        const card = el('article', 'flex flex-col rounded-xl border border-violet-200 bg-white p-4');

        const facts = el('dl', 'mt-3 grid grid-cols-2 gap-2 text-xs');
        [
            ['Prix conseillé', `${money(idea.suggested_price)} TND`],
            ['Matière / pièce', `${kg(idea.kg_per_unit)} kg`],
            ['Premier lot', `${idea.suggested_stock} pièces (${kg(idea.kg_used)} kg)`],
            ['Maximum possible', `${idea.units_possible} pièces`],
        ].forEach(([label, value]) => {
            const box = el('div', 'rounded bg-slate-50 p-2');
            box.append(el('dt', 'text-slate-500', label), el('dd', 'font-semibold text-slate-800', value));
            facts.appendChild(box);
        });

        const create = el('a', 'mt-4 inline-block self-start rounded-lg bg-violet-700 px-3 py-2 text-xs font-semibold text-white hover:bg-violet-800', 'Créer ce produit →');
        create.href = idea.create_url;

        card.append(
            cardHead(idea),
            el('p', 'mt-2 text-sm text-slate-700', idea.description),
            facts,
            el('p', 'mt-2 text-xs text-slate-500', `Matière prise dans : ${idea.batch.label}`),
            el('p', 'mt-2 flex-1 text-xs italic text-slate-500', idea.reasoning),
            create,
        );
        return card;
    };

    const visitorCard = (idea) => {
        const card = el('article', 'flex flex-col border border-slate-200 bg-white p-5');
        const steps = el('ol', 'mt-3 list-decimal space-y-1 pl-5 text-sm text-slate-700');
        idea.steps.forEach((step) => steps.appendChild(el('li', '', step)));

        card.append(
            cardHead(idea),
            el('p', 'mt-2 text-sm text-slate-600', idea.description),
            idea.time_needed ? el('p', 'mt-2 text-xs font-semibold text-violet-800', `⏱ ${idea.time_needed}`) : el('span'),
            steps,
        );
        return card;
    };

    // ---------- Pagination dynamique ----------

    /**
     * @param ideas      idées du premier chargement
     * @param buildCard  idée -> élément DOM
     * @param loadMore   (namesAlreadyShown) => Promise<idées supplémentaires>
     */
    const dynamicPager = (ideas, buildCard, loadMore) => {
        const state = { ideas: [...ideas], cards: ideas.map(buildCard), page: 1, exhausted: ideas.length === 0, loading: false };
        const wrapper = el('div');
        const grid = el('div', 'grid gap-4 md:grid-cols-3');
        const nav = el('nav', 'mt-4 flex flex-wrap items-center justify-center gap-2 text-sm');
        const notice = el('p', 'mt-2 text-center text-xs text-slate-500');
        nav.setAttribute('aria-label', 'Pages des idées');
        wrapper.append(grid, nav, notice);

        const pages = () => Math.max(1, Math.ceil(state.cards.length / PER_PAGE));
        const canLoadMore = () => !state.exhausted && state.ideas.length < MAX_IDEAS;

        const button = (label, onClick, { current = false, disabled = false } = {}) => {
            const b = el('button', `min-w-9 rounded-lg border px-3 py-1.5 ${current ? 'border-violet-700 bg-violet-700 font-semibold text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'} disabled:cursor-not-allowed disabled:opacity-40`, label);
            b.type = 'button';
            b.disabled = disabled || state.loading;
            if (current) b.setAttribute('aria-current', 'page');
            b.addEventListener('click', onClick);
            nav.appendChild(b);
        };

        const render = () => {
            const start = (state.page - 1) * PER_PAGE;
            grid.replaceChildren(...state.cards.slice(start, start + PER_PAGE));

            nav.replaceChildren();
            const lastLoadedPage = state.page === pages();
            button('‹ Précédent', () => go(state.page - 1), { disabled: state.page === 1 });
            for (let p = 1; p <= pages(); p++) button(String(p), () => go(p), { current: p === state.page });
            if (canLoadMore()) {
                // Page "à venir" : sera générée par l'IA au clic
                button(state.loading ? '…' : `${pages() + 1} ✨`, () => go(pages() + 1));
            }
            button(lastLoadedPage && canLoadMore() ? 'Plus d’idées ›' : 'Suivant ›', () => go(state.page + 1), {
                disabled: lastLoadedPage && !canLoadMore(),
            });
            nav.appendChild(el('span', 'ml-2 text-xs text-slate-500', `${state.ideas.length} idée(s)`));
        };

        const go = async (page) => {
            if (page < 1 || state.loading) return;

            if (page > pages()) {
                if (!canLoadMore()) return;
                state.loading = true;
                notice.textContent = 'L’IA cherche de nouvelles idées…';
                render();

                try {
                    const more = await loadMore(state.ideas.map((idea) => idea.name));
                    if (more.length === 0) {
                        state.exhausted = true;
                        notice.textContent = 'L’IA n’a plus de nouvelle idée pour cette matière.';
                    } else {
                        state.ideas.push(...more);
                        state.cards.push(...more.map(buildCard));
                        notice.textContent = state.ideas.length >= MAX_IDEAS ? `Maximum de ${MAX_IDEAS} idées atteint pour cette recherche.` : '';
                        page = Math.min(page, pages());
                    }
                } catch (error) {
                    notice.textContent = errorMessage(error);
                    page = state.page;
                } finally {
                    state.loading = false;
                }
            }

            state.page = Math.min(page, pages());
            render();
        };

        render();
        return wrapper;
    };

    // ---------- Rendu des résultats ----------

    const renderStock = (data, request) => {
        const { material, ideas, discarded } = data;
        result.appendChild(el('p', 'mb-4 text-sm text-slate-600',
            `${material.type} en stock : ${kg(material.total_kg)} kg sur ${material.batches} lot(s) · qualité ${material.quality_grades.join(', ')}`));

        if (!ideas.length) {
            result.appendChild(el('p', 'text-sm font-semibold text-slate-700', 'Aucune idée exploitable cette fois-ci. Relancez la génération.'));
            return;
        }

        result.appendChild(dynamicPager(ideas, stockCard, async (exclude) => (await request(exclude)).ideas));

        if (discarded) {
            result.appendChild(el('p', 'mt-3 text-xs text-slate-500', `${discarded} idée(s) écartée(s) : matière insuffisante, prix hors limites ou produit déjà existant.`));
        }
    };

    const renderVisitor = (data, request) => {
        const { is_textile: isTextile, message, ideas, catalog } = data;
        if (message) {
            result.appendChild(el('p', `mb-6 ${isTextile ? 'text-slate-700' : 'border-l-2 border-amber-400 pl-4 text-amber-900'}`, message));
        }
        if (!isTextile) return;

        if (!ideas.length) {
            result.appendChild(el('p', 'text-slate-700', 'Aucune idée exploitable cette fois-ci. Reformulez ou réessayez.'));
        } else {
            result.appendChild(dynamicPager(ideas, visitorCard, async (exclude) => (await request(exclude)).ideas));
        }

        if (catalog.length) {
            result.appendChild(el('h2', 'mt-10 font-display text-2xl text-slate-950', 'Pas le temps ? Nos créations dans la même matière'));
            const list = el('div', 'mt-4 grid gap-4 sm:grid-cols-3');
            catalog.forEach((product) => {
                const link = el('a', 'block border border-slate-200 bg-white p-4 transition hover:border-emerald-800');
                link.href = product.url;
                link.append(
                    el('p', 'text-xs font-semibold uppercase tracking-wide text-slate-500', product.material),
                    el('p', 'mt-1 font-semibold text-slate-900', product.name),
                    el('p', 'mt-2 text-emerald-900', `${money(product.price)} TND`),
                );
                list.appendChild(link);
            });
            result.appendChild(list);
        }
    };

    const run = async () => {
        // Les pages suivantes reprennent la même matière que la recherche de départ
        const payload = Object.fromEntries(new FormData(form));
        const request = async (exclude = []) => (await window.axios.post(panel.dataset.url, { ...payload, exclude })).data;

        runButton.disabled = true;
        runButton.textContent = 'Génération…';
        result.hidden = false;
        result.replaceChildren(el('p', 'text-sm text-slate-500', 'L’IA réfléchit à ce qu’on peut créer avec cette matière…'));

        try {
            const data = await request();
            result.replaceChildren();
            (mode === 'stock' ? renderStock : renderVisitor)(data, request);
        } catch (error) {
            result.replaceChildren(el('p', 'text-sm font-semibold text-rose-700', errorMessage(error)));
        } finally {
            runButton.disabled = false;
            runButton.textContent = runLabel;
        }
    };

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        run();
    });

    // Exemples cliquables (page publique)
    panel.querySelectorAll('[data-ideas-example]').forEach((button) => {
        button.addEventListener('click', () => {
            form.querySelector('input[name="material"]').value = button.textContent;
            run();
        });
    });

    // Matière déjà choisie (lien depuis la fiche d'un lot) : on lance directement
    if (mode === 'stock' && new URLSearchParams(window.location.search).has('material_type') && form) {
        run();
    }
}
