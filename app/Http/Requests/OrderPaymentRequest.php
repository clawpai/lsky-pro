<?php

namespace App\Http\Requests;

use App\PaymentChannel;
use App\PaymentMethod;
use App\PaymentProvider;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderPaymentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', Rule::in(PaymentProvider::cases())],
            'channel' => ['required', Rule::in(PaymentChannel::cases())],
            'method' => ['required', Rule::in(PaymentMethod::cases())],
            'return_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ];
    }

    public function attributes(): array
    {
        return [
            'platform' => '支付平台',
            'channel' => '支付渠道',
            'method' => '支付方法',
            'return_url' => '同步跳转地址',
            'cancel_url' => '取消跳转地址',
        ];
    }
}
