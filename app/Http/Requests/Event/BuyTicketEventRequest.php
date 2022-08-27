<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BuyTicketEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'payment_type' => 'required|integer',
            'card_number' => 'integer',
            'card_name' => 'string',
            'card_cvc' => 'integer',
            'card_expiration_month' => 'integer',
            'card_expiration_year' => 'integer',
            'tickets' => 'required|array',
            'tickets.*.id' => 'required|integer',
            'tickets.*.amount' => 'required|integer',
        ];
    }
}