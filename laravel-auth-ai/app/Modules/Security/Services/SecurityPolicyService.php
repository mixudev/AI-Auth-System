<?php

namespace App\Modules\Security\Services;

use App\Modules\Settings\Models\Setting;
use Illuminate\Validation\Rules\Password;

class SecurityPolicyService
{
    /**
     * Generate password validation rules based on database settings.
     */
    public static function getPasswordRules(): Password
    {
        $min = (int) Setting::get('password_min_length', 8);
        $rules = Password::min($min);

        if (Setting::get('password_require_symbols', false)) {
            $rules->symbols();
        }

        if (Setting::get('password_require_numbers', false)) {
            $rules->numbers();
        }

        // Kita tetap tambahkan mixed case dan uncompromised secara default 
        // demi keamanan tinggi sesuai permintaan user
        $rules->mixedCase()->uncompromised(3);

        return $rules;
    }
}
