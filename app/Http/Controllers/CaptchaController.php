<?php

namespace App\Http\Controllers;

use App\Services\CaptchaService;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    public function __invoke(CaptchaService $captcha): Response
    {
        $code = $captcha->generate();

        return $captcha->render($code);
    }
}
