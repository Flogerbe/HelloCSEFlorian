<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'    => ['sometimes', 'string', 'max:100'],
            'prenom' => ['sometimes', 'string', 'max:100'],
            'image'  => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'statut' => ['sometimes', 'string', 'in:inactif,en_attente,actif'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.image'  => 'Le fichier doit être une image.',
            'statut.in'    => 'Le statut doit être : inactif, en_attente ou actif.',
        ];
    }
}
