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
            'cpf' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'integer'],
            'address' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'array'],
            'address.country' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'string'],
            'address.street' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'string'],
            'address.street_number' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'string'],
            'address.state' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'string'],
            'address.city' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'string'],
            'address.neighborhood' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'string'],
            'address.zipcode' => ['required_if:payment_type,!=,'.Constants::BOLETO, 'string'],
            'tickets' => 'required|array',
            'tickets.*.id' => 'required|integer',
            'tickets.*.amount' => 'required|integer',
            'tickets.*.quantity' => 'required|integer',
            'tickets.*.value' => 'required',
            'tickets.*.description' => 'required',
        ];
    }
}
