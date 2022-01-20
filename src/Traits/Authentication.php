<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Authentication
{

    static function __getGuard()
    {

        $domain = Request::getHttpHost();

        $guard = Auth::getDefaultDriver();

        switch ($domain) {
            case config('app.admin_url'):
                $guard = "admin";
                break;
            default:
                $guard = config('auth.defaults.guard');
                break;
        }

        return $guard;
    }

    /**
     * __currentUser
     *
     * @param  mixed $guard
     * @return null|\App\Models\User
     */
    function __currentUser($guard = null)
    {
        $guard = $guard ?: $this->__getGuard();
        return request()->user() ?? auth($guard)->user();
    }
}