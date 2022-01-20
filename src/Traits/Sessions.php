<?php

namespace Zdirnecamlcs96\Helpers\Traits;

trait Sessions
{
    public function __refreshCsrfToken()
    {
        Container::__session()->regenerate();
        return Container::__csrfToken();
    }
}