<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'image',
        'statut',
    ];

    protected $attributes = [
        'statut' => 'en_attente',
    ];

    protected $casts = [
        'statut' => 'string',
    ];


    /**
     * Scope pour filtrer uniquement les profils actifs.
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }
}
