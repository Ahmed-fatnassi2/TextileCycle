{{-- Chatbot "assistant de dépôt" : bouton flottant + panneau de discussion (logique dans resources/js/chat-assistant.js) --}}
<div data-assistant data-endpoint="{{ route('assistant.chat') }}" class="fixed bottom-4 right-4 z-50 flex flex-col items-end gap-3 print:hidden">

    <section data-assistant-panel hidden
             class="flex h-[32rem] max-h-[calc(100vh-6rem)] w-[calc(100vw-2rem)] max-w-sm flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
             aria-label="Assistant de dépôt">
        <header class="flex items-center justify-between bg-emerald-900 px-4 py-3 text-white">
            <div>
                <p class="font-semibold">Assistant de dépôt</p>
                <p class="text-xs text-emerald-100">Je vous aide à choisir le meilleur point de collecte</p>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" data-assistant-reset class="rounded px-2 py-1 text-xs text-emerald-100 hover:bg-white/10" title="Nouvelle conversation">↺</button>
                <button type="button" data-assistant-close class="rounded px-2 py-1 text-lg leading-none hover:bg-white/10" aria-label="Fermer">×</button>
            </div>
        </header>

        <div data-assistant-messages class="flex-1 space-y-3 overflow-y-auto bg-slate-50 p-4 text-sm" aria-live="polite"></div>

        <div data-assistant-suggestions class="flex flex-wrap gap-2 border-t border-slate-100 bg-white px-4 pt-3">
            <button type="button" class="rounded-full border border-emerald-200 px-3 py-1 text-xs text-emerald-800 hover:bg-emerald-50">Où déposer près de moi ?</button>
            <button type="button" class="rounded-full border border-emerald-200 px-3 py-1 text-xs text-emerald-800 hover:bg-emerald-50">J’ai 10 kg de vêtements à Sousse</button>
            <button type="button" class="rounded-full border border-emerald-200 px-3 py-1 text-xs text-emerald-800 hover:bg-emerald-50">Comment se passe un dépôt ?</button>
        </div>

        <div class="bg-white px-4 pt-2">
            <button type="button" data-assistant-locate class="text-xs font-medium text-emerald-700 hover:underline">📍 Partager ma position</button>
            <span data-assistant-location-status class="text-xs text-slate-500"></span>
        </div>

        <form data-assistant-form class="flex gap-2 bg-white p-3">
            <input type="text" name="message" maxlength="1000" autocomplete="off" required
                   placeholder="Ex. : où déposer 5 kg à Tunis ?"
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
            <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">Envoyer</button>
        </form>
    </section>

    <button type="button" data-assistant-toggle
            class="flex items-center gap-2 rounded-full bg-emerald-900 px-5 py-3 font-semibold text-white shadow-lg transition hover:bg-emerald-800">
        <span aria-hidden="true">💬</span> Où déposer ?
    </button>
</div>
