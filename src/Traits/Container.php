<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Illuminate\Container\Container as BaseContainer;
use RuntimeException;

class Container
{
    /**
     * Get the available container instance.
     *
     * @param  string|null  $abstract
     * @param  array  $parameters
     * @return mixed|\Illuminate\Contracts\Foundation\Application
     */
    private static function __app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return BaseContainer::getInstance();
        }

        return BaseContainer::getInstance()->make($abstract, $parameters);
    }

    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Config\Repository
     */
    public static function __config($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::__app('config');
        }

        if (is_array($key)) {
            return self::__app('config')->set($key);
        }

        return self::__app('config')->get($key, $default);
    }

    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return \Illuminate\Http\Request|string|array|null
     */
    public static function __request($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::__app('request');
        }

        if (is_array($key)) {
            return self::__app('request')->only($key);
        }

        $value = self::__app('request')->__get($key);

        return is_null($value) ? value($default) : $value;
    }

    /**
     * Translate the given message.
     *
     * @param  string|null  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return \Illuminate\Contracts\Translation\Translator|string|array|null
     */
    public static function __translate($key = null, $replace = [], $locale = null)
    {
        if (is_null($key)) {
            return self::__app('translator');
        }

        return self::__app('translator')->get($key, $replace, $locale);
    }

    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Session\Store|\Illuminate\Session\SessionManager
     */
    public static function __session($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::__app('session');
        }

        if (is_array($key)) {
            return self::__app('session')->put($key);
        }

        return self::__app('session')->get($key, $default);
    }

    /**
     * Get the CSRF token value.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function __csrfToken()
    {
        $session = self::__app('session');

        if (isset($session)) {
            return $session->token();
        }

        throw new RuntimeException('Application session store not set.');
    }

}