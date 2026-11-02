<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Validation des dépôts au point de collecte grâce au QR code du citoyen.
class DepositCheckinController extends Controller
{
    // Page scanner (caméra, photo du QR ou saisie manuelle du code)
    public function scan()
    {
        $pendingCount = Deposit::where('status', Deposit::STATUS_EN_ATTENTE)->count();

        return view('admin.deposits.scan', compact('pendingCount'));
    }

    // Saisie manuelle : accepte le code seul ("TC-ABCD1234") ou l'URL complète lue dans le QR
    public function lookup(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:255']]);

        $code = $this->normalizeCode($request->string('code'));

        if (! Deposit::where('code', $code)->exists()) {
            return redirect()
                ->route('admin.deposits.scan')
                ->withInput()
                ->with('error', "Aucun dépôt ne correspond au code « {$code} ».");
        }

        return redirect()->route('admin.deposits.checkin', $code);
    }

    // Fiche du dépôt scanné, avec le formulaire de validation
    public function show(string $code)
    {
        $deposit = Deposit::with(['user', 'depositPoint', 'checkedInBy'])
            ->where('code', $this->normalizeCode($code))
            ->first();

        if (! $deposit) {
            return redirect()
                ->route('admin.deposits.scan')
                ->with('error', 'QR code inconnu ou dépôt supprimé.');
        }

        return view('admin.deposits.checkin', compact('deposit'));
    }

    // Accepter (→ Déposé) ou refuser (→ Rejeté) le dépôt, avec le poids réellement pesé
    public function confirm(Request $request, string $code)
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['accept', 'reject'])],
            'weight_kg' => ['required', 'numeric', 'min:0.1', 'max:500'],
            'state' => ['required', Rule::in(Deposit::states())],
        ]);

        $admin = $request->user();

        // Verrou pour éviter qu'un même QR soit validé deux fois (double scan, deux agents...)
        // Retourne [dépôt, traité maintenant ?]
        [$deposit, $processed] = DB::transaction(function () use ($code, $data, $admin) {
            $deposit = Deposit::where('code', $this->normalizeCode($code))->lockForUpdate()->first();

            if (! $deposit || ! $deposit->isPending()) {
                return [$deposit, false];
            }

            $accepted = $data['decision'] === 'accept';

            $deposit->forceFill([
                'status' => $accepted ? Deposit::STATUS_DEPOSE : Deposit::STATUS_REJETE,
                'weight_kg' => $data['weight_kg'],
                'state' => $data['state'],
                'checked_in_at' => now(),
                'checked_in_by' => $admin->id,
            ])->save();

            User::logActivity(
                $deposit->user,
                $accepted ? 'Dépôt validé' : 'Dépôt refusé',
                "Dépôt {$deposit->code} ({$deposit->weight_kg} kg) ".($accepted ? 'validé' : 'refusé').' par QR code.',
                $admin,
                ['deposit_id' => $deposit->id, 'deposit_point_id' => $deposit->deposit_point_id],
            );

            return [$deposit, true];
        });

        if (! $deposit) {
            return redirect()->route('admin.deposits.scan')->with('error', 'QR code inconnu ou dépôt supprimé.');
        }

        if (! $processed) {
            return redirect()
                ->route('admin.deposits.checkin', $deposit->code)
                ->with('error', 'Ce dépôt a déjà été traité.');
        }

        return redirect()
            ->route('admin.deposits.scan')
            ->with('success', $deposit->status === Deposit::STATUS_DEPOSE
                ? "Dépôt {$deposit->code} validé ({$deposit->weight_kg} kg)."
                : "Dépôt {$deposit->code} refusé.");
    }

    private function normalizeCode(string $value): string
    {
        $value = trim($value);

        // Le QR contient une URL du type .../admin/deposits/checkin/TC-XXXXXXXX
        if (str_contains($value, '/')) {
            $value = basename(parse_url($value, PHP_URL_PATH) ?? $value);
        }

        return strtoupper($value);
    }
}
