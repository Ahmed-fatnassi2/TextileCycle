<?php

namespace App\Http\Requests;

use App\Models\RepairRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRepairRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = ['item_description' => 'required|string|min:5|max:1000', 'problem_type' => ['required', Rule::in(RepairRequest::problemTypes())], 'workshop_id' => 'required|exists:workshops,id', 'photos' => 'nullable|array|max:3', 'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096'];
        if ($this->routeIs('admin.*')) { $rules += ['user_id' => 'required|exists:users,id', 'cost' => 'required|numeric|min:0|max:100000', 'estimated_cost' => 'nullable|numeric|min:0|max:100000', 'estimated_completion_date' => 'nullable|date|after_or_equal:today', 'status' => ['required', Rule::in(RepairRequest::statuses())], 'after_photos' => 'nullable|array|max:3', 'after_photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096']; }
        return $rules;
    }

    public function messages(): array
    {
        return ['item_description.required' => 'La description du vêtement est obligatoire.', 'item_description.string' => 'La description doit être du texte.', 'item_description.min' => 'La description doit contenir au moins 5 caractères.', 'item_description.max' => 'La description ne peut pas dépasser 1000 caractères.', 'problem_type.required' => 'Le type de problème est obligatoire.', 'problem_type.in' => 'Le type de problème sélectionné est invalide.', 'workshop_id.required' => "Vous devez sélectionner un atelier.", 'workshop_id.exists' => "L'atelier sélectionné est invalide.", 'user_id.required' => 'Le citoyen est obligatoire.', 'user_id.exists' => 'Le citoyen sélectionné est invalide.', 'cost.required' => 'Le coût est obligatoire.', 'cost.numeric' => 'Le coût doit être un nombre.', 'cost.min' => 'Le coût ne peut pas être négatif.', 'cost.max' => 'Le coût est trop élevé.', 'estimated_cost.numeric' => 'Le devis doit être un nombre.', 'estimated_cost.min' => 'Le devis ne peut pas être négatif.', 'estimated_completion_date.date' => 'La date estimée est invalide.', 'estimated_completion_date.after_or_equal' => 'La date estimée ne peut pas être passée.', 'status.required' => 'Le statut est obligatoire.', 'status.in' => 'Le statut sélectionné est invalide.', 'photos.array' => 'Les photos sont invalides.', 'photos.max' => 'Vous pouvez envoyer jusqu’à 3 photos.', 'photos.*.image' => 'Chaque fichier doit être une image.', 'photos.*.mimes' => 'Les photos doivent être au format JPG, PNG ou WebP.', 'photos.*.max' => 'Chaque photo ne peut pas dépasser 4 Mo.', 'after_photos.array' => 'Les photos après réparation sont invalides.', 'after_photos.max' => 'Vous pouvez envoyer jusqu’à 3 photos après réparation.', 'after_photos.*.image' => 'Chaque fichier doit être une image.', 'after_photos.*.mimes' => 'Les photos doivent être au format JPG, PNG ou WebP.', 'after_photos.*.max' => 'Chaque photo ne peut pas dépasser 4 Mo.'];
    }

    public function attributes(): array { return ['user_id' => 'citoyen', 'item_description' => 'description du vêtement', 'problem_type' => 'type de problème', 'workshop_id' => 'atelier', 'cost' => 'coût final', 'estimated_cost' => 'devis estimé', 'estimated_completion_date' => 'date estimée', 'status' => 'statut', 'photos' => 'photos', 'after_photos' => 'photos après réparation']; }
}
