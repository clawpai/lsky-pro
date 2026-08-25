<?php

namespace App\Http\Responses\Auth;

use App\Support\R;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetResponse implements PasswordResetResponseContract
{
    /**
     * The response status language key.
     *
     * @var string
     */
    protected string $status;

    /**
     * Create a new response instance.
     *
     * @param string $status
     * @return void
     */
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function toResponse($request): Response
    {
        return R::success(trans($this->status));
    }
}
