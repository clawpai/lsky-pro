<?php

namespace App\Rules;

use App\Facades\MailService;
use App\Facades\SmsService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidVerifyCode implements ValidationRule
{
    public function __construct(
        public string  $event,
        public ?string $identity,
    )
    {
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var($this->identity, FILTER_VALIDATE_EMAIL)) {
            if (!MailService::verifyCode($this->event, $this->identity, $value)) {
                $fail(__('Invalid verification code.'));
            }
        } else {
            if (!SmsService::verifyCode($this->event, $this->identity, $value)) {
                $fail(__('Invalid verification code.'));
            }
        }
    }
}
