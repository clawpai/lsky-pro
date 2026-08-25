<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    public function success(string $message = 'success', $data = []): Response
    {
        return $this->response(true, $message, $data);
    }

    public function response(bool $status, string $message = '', $data = []): Response
    {
        $data = $data ?: new stdClass;
        return response(compact('status', 'message', 'data'));
    }

    public function fail(string $message = 'fail', $data = []): Response
    {
        return $this->response(false, $message, $data);
    }
}
