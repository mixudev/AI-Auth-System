<?php

namespace App\Modules\WaGateway\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWaGatewayConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $supportedProviders = implode(',', array_keys(config('wa_gateway.providers', ['fonnte' => []])));

        return [
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|in:security,auth,info,system',
            'token' => 'required|string',
            'alert_phone_number' => 'required|string',
            'send_on_critical_alert' => 'boolean',
            'is_active' => 'boolean',
            'meta' => 'nullable|array',
            'meta.provider' => 'nullable|string|in:' . $supportedProviders,
        ];
    }
}
