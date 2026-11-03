<?php

namespace App\Http\Controllers;

use App\Models\RepairPhoto;
use App\Models\RepairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RepairPhotoController extends Controller
{
    public function citizen(Request $request, RepairRequest $repairRequest, RepairPhoto $photo): StreamedResponse
    {
        abort_unless($repairRequest->user_id === $request->user()->id, 404);
        return $this->serve($repairRequest, $photo);
    }

    public function workshopOwner(Request $request, RepairRequest $repairRequest, RepairPhoto $photo): StreamedResponse
    {
        abort_unless($request->user()->ownedWorkshops()->whereKey($repairRequest->workshop_id)->exists(), 404);
        return $this->serve($repairRequest, $photo);
    }

    public function admin(RepairRequest $repairRequest, RepairPhoto $photo): StreamedResponse
    {
        return $this->serve($repairRequest, $photo);
    }

    private function serve(RepairRequest $repairRequest, RepairPhoto $photo): StreamedResponse
    {
        abort_unless($photo->repair_request_id === $repairRequest->id, 404);
        abort_unless(Storage::disk('public')->exists($photo->path), 404);

        return Storage::disk('public')->response($photo->path);
    }
}
