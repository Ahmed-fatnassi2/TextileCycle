<?php

namespace App\Http\Controllers;

use App\Actions\NotifyDonationStatusChanged;
use App\Actions\NotifyModuleAdmins;
use App\Models\Donation;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DonationTrackingController extends Controller
{
    public function show(Request $request, string $trackingToken): View
    {
        $donation = Donation::with(['association', 'statusHistory.actor'])
            ->where('tracking_token', $trackingToken)
            ->firstOrFail();

        return view('front.donations.track', compact('donation'));
    }

    public function qr(Request $request, Donation $donation): Response
    {
        $this->authorizeDonation($request, $donation);
        abort_unless($donation->status !== Donation::STATUS_PENDING, 404);

        return $this->qrResponse($donation, false);
    }

    public function downloadQr(Request $request, Donation $donation): Response
    {
        $this->authorizeDonation($request, $donation);
        abort_unless($donation->status !== Donation::STATUS_PENDING, 404);

        return $this->qrResponse($donation, true);
    }

    private function qrResponse(Donation $donation, bool $download): Response
    {
        $baseUrl = rtrim((string) config('donation.qr_base_url'), '/');
        $trackingPath = route('donations.track', $donation->tracking_token, false);
        $trackingUrl = $baseUrl.$trackingPath;
        $svg = Cache::rememberForever('donation-qr:'.hash('sha256', $trackingUrl), function () use ($trackingUrl): string {
            $qrCode = QrCode::create($trackingUrl)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
                ->setSize(320)
                ->setMargin(12);

            return (new SvgWriter)->write($qrCode)->getString();
        });
        $headers = [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, no-store',
        ];

        if ($download) {
            $headers['Content-Disposition'] = 'attachment; filename="'.$donation->trackingReference().'.svg"';
        }

        return response($svg, 200, $headers);
    }

    public function confirmReceived(
        Request $request,
        Donation $donation,
        NotifyDonationStatusChanged $notifyStatus,
        NotifyModuleAdmins $notifyAdmins,
    ): RedirectResponse {
        $this->authorizeDonation($request, $donation);
        abort_unless($donation->status === Donation::STATUS_VALIDATED, 403, 'Seul un don validé peut être confirmé comme reçu.');

        $donation->update(['status' => Donation::STATUS_DELIVERED]);
        $notifyStatus($donation);
        $notifyAdmins(
            'Don marqué comme reçu',
            $donation->association->name,
            $donation->status,
            route('admin.donations.show', $donation),
            $donation->trackingReference(),
        );

        return redirect()
            ->route('donations.track', $donation->tracking_token)
            ->with('success', 'La réception du don a été confirmée.');
    }

    private function authorizeDonation(Request $request, Donation $donation): void
    {
        abort_unless(
            $request->user()->role === 'admin'
                || $donation->association()->where('user_id', $request->user()->id)->exists(),
            403,
        );
    }
}
