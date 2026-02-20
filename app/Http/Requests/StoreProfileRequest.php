<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'    => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'statut' => ['sometimes', 'string', 'in:inactif,en_attente,actif'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'    => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'image.image'     => 'Le fichier doit être une image.',
            'statut.in'       => 'Le statut doit être : inactif, en_attente ou actif.',
        ];
    }
}
