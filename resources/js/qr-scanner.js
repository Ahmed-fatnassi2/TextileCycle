import { Html5Qrcode } from 'html5-qrcode';

// Page admin "Scanner un dépôt" : caméra, photo du QR ou saisie manuelle.
// Le QR contient l'URL de validation ; on la transmet à la route lookup qui en extrait le code.
const root = document.querySelector('[data-qr-scanner]');

if (root) {
    const lookupUrl = root.dataset.lookupUrl;
    const status = root.querySelector('[data-scanner-status]');
    const startButton = root.querySelector('[data-scanner-start]');
    const stopButton = root.querySelector('[data-scanner-stop]');
    const fileInput = root.querySelector('[data-scanner-file]');
    const readerId = root.querySelector('[data-scanner-reader]').id;

    const scanner = new Html5Qrcode(readerId);
    let handled = false;

    const setStatus = (text, isError = false) => {
        status.textContent = text;
        status.className = `text-sm ${isError ? 'text-red-600' : 'text-slate-600'}`;
    };

    const goTo = (decodedText) => {
        if (handled) return;
        handled = true;
        setStatus('QR code détecté, ouverture du dépôt…');
        window.location.href = `${lookupUrl}?code=${encodeURIComponent(decodedText)}`;
    };

    if (!window.isSecureContext) {
        startButton.disabled = true;
        startButton.classList.add('opacity-50', 'cursor-not-allowed');
        setStatus('La caméra nécessite HTTPS (ou localhost). Utilisez « Importer une photo » ou la saisie manuelle.', true);
    }

    startButton.addEventListener('click', async () => {
        try {
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                goTo,
                () => {}, // pas de QR sur cette image : on continue
            );
            startButton.classList.add('hidden');
            stopButton.classList.remove('hidden');
            setStatus('Placez le QR code du citoyen devant la caméra.');
        } catch (error) {
            setStatus('Impossible d’accéder à la caméra. Vérifiez les autorisations du navigateur.', true);
        }
    });

    stopButton.addEventListener('click', async () => {
        await scanner.stop().catch(() => {});
        stopButton.classList.add('hidden');
        startButton.classList.remove('hidden');
        setStatus('Caméra arrêtée.');
    });

    // Fonctionne sans caméra ni HTTPS : lecture du QR depuis une photo
    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) return;
        try {
            goTo(await scanner.scanFile(file, false));
        } catch (error) {
            setStatus('Aucun QR code lisible sur cette image.', true);
        } finally {
            fileInput.value = '';
        }
    });
}
