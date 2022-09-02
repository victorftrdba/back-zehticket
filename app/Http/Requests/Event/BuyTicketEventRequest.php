<?php

namespace App\Http\Requests\Event;

use App\Helpers\Constants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

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
            'payment_type' => ['required', 'integer'],
            'card_number' => ['required_if:payment_type,'.Constants::CARTAO_CREDITO, 'integer'],
            'card_name' => ['required_if:payment_type,'.Constants::CARTAO_CREDITO,  'string'],
            'card_cvc' => ['required_if:payment_type,'.Constants::CARTAO_CREDITO, 'integer'],
            'card_expiration_month' => ['required_if:payment_type,'.Constants::CARTAO_CREDITO, 'integer'],
            'card_expiration_year' => ['required_if:payment_type,'.Constants::CARTAO_CREDITO, 'integer'],
            'tickets' => 'required|array',
            'tickets.*.id' => 'required|integer',
            'tickets.*.amount' => 'required|integer',
            'tickets.*.quantity' => 'required|integer',
            'tickets.*.value' => 'required',
            'tickets.*.description' => 'required',
        ];
    }
}
