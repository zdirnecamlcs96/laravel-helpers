<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Illuminate\Http\Request;

trait Locale
{
    function __trans(Request $request, $key, $replace = [])
    {
        $lang = $request->header("lang");

        return __($key, $replace, $lang);
    }
}