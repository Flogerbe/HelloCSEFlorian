<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /** Créer un nouveau profil (authentifié). */
    public function store(StoreProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('profiles', 'public');
        }

        $profile = Profile::create($data);

        return response()->json([
            'message' => 'Profil créé avec succès.',
            'data'    => $profile,
        ], 201);
    }

    /** Lister les profils actifs (public) — sans le champ statut. */
    public function index(): JsonResponse
    {
        $profiles = Profile::actif()
            ->get()
            ->makeHidden('statut');

        return response()->json([
            'data' => $profiles,
        ]);
    }

    /** Lister tous les profils (authentifié) — champ statut visible. */
    public function indexAdmin(): JsonResponse
    {
        $profiles = Profile::all();

        return response()->json([
            'data' => $profiles,
        ]);
    }

    /** Modifier un profil (authentifié). */
    public function update(UpdateProfileRequest $request, Profile $profile): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($profile->image) {
                Storage::disk('public')->delete($profile->image);
            }
            $data['image'] = $request->file('image')->store('profiles', 'public');
        }

        $profile->update($data);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'data'    => $profile->fresh(),
        ]);
    }

    /** Supprimer un profil (authentifié). */
    public function destroy(Profile $profile): JsonResponse
    {
        if ($profile->image) {
            Storage::disk('public')->delete($profile->image);
        }

        $profile->delete();

        return response()->json([
            'message' => 'Profil supprimé avec succès.',
        ]);
    }
}
