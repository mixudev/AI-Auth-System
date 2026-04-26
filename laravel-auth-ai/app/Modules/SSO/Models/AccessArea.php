<?php

namespace App\Modules\SSO\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AccessArea extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_access_area');
    }
}
