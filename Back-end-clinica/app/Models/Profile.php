<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    protected $table = 'auth_profiles';

    protected $fillable = [
        'nome'
    ];

    /**
     * Relacionamento com usuários
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'profile_id');
    }
}
