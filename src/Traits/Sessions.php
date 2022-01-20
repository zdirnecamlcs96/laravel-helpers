<?php

namespace Zdirnecamlcs96\Helpers\Traits;

trait Sessions
{
    public function __refreshCsrfToken()
    {
        session()->regenerate();
        return csrf_token();

    }
}