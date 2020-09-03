<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    // protected $except = [
    //     '/*/webhook',
    // ];
    public function __construct(Application $app, Encrypter $encrypter) {
        parent::__construct($app, $encrypter);
        $this->except = [
          env("TELEGRAM_BOT_TOKEN") . '/webhook'
        ];
    }
}
