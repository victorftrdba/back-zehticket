<?php

namespace App\Http\Requests\Event;

use App\Helpers\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BuyTicketEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_type' => ['required', 'integer'],
            'card_number' => ['required_if:payment_type,' . Constants::CARTAO_CREDITO, 'string'],
            'card_name' => ['required_if:payment_type,' . Constants::CARTAO_CREDITO, 'string'],
            'card_cvv' => ['required_if:payment_type,' . Constants::CARTAO_CREDITO, 'string'],
            'card_expiration_month' => ['required_if:payment_type,' . Constants::CARTAO_CREDITO, 'string'],
            'card_expiration_year' => ['required_if:payment_type,' . Constants::CARTAO_CREDITO, 'string'],
            'installments' => ['required_if:payment_type,' . Constants::CARTAO_CREDITO, 'numeric', 'between:1,12'],
            'cpf' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'string'],
            'address' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'array'],
            'address.country' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'string'],
            'address.street' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'string'],
            'address.street_number' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'string'],
            'address.state' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'string'],
            'address.city' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'string'],
            'address.neighborhood' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'string'],
            'address.zipcode' => ['required_unless:payment_type,'.Constants::BOLETO.','.Constants::PIX, 'string'],
            'tickets' => 'required|array',
            'tickets.*.id' => 'required|integer',
            'tickets.*.quantity' => 'required|integer',
            'tickets.*.description' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_type.required' => 'O tipo de pagamento é obrigatório.',
            'payment_type.integer' => 'O tipo de pagamento deve ser um dado do tipo inteiro.',
            'card_number.required_if' => 'Se o tipo de pagamento for cartão de crédito, é obrigatório enviar o número do cartão.',
            'card_name.required_if' => 'Se o tipo de pagamento for cartão de crédito, é obrigatório enviar o nome no cartão.',
            'card_cvv.required_if' => 'Se o tipo de pagamento for cartão de crédito, é obrigatório enviar o CVV do cartão.',
            'card_expiration_month.required_if' => 'Se o tipo de pagamento for cartão de crédito, é obrigatório enviar o mês de expiração do cartão.',
            'card_expiration_year.required_if' => 'Se o tipo de pagamento for cartão de crédito, é obrigatório enviar o ano de expiração do cartão.',
            'installments.required_if' => 'Se o tipo de pagamento for cartão de crédito, é obrigatório enviar a quantidade de parcelas.',
            'cpf.required_unless' => 'O CPF é obrigatório para realizar compras.',
            'address.required_unless' => 'O endereço é obrigatório.',
            'address.zipcode.required_unless' => 'É obrigatório inserir um CEP válido para preencher as colunas de endereço.',
            'tickets' => 'É obrigatório informar os ingressos que serão comprados.',
        ];
    }
}
