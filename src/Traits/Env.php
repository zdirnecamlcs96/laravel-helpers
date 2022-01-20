<?php

namespace Zdirnecamlcs96\Helpers\Traits;

trait Env
{

    private function __isProduction()
    {
        return config('app.env') == "production";
    }

    private function __isDebug()
    {
        return config('app.debug') && !$this->__isProduction();
    }
}