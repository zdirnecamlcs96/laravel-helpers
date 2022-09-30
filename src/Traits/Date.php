<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Illuminate\Support\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Str;

trait Date {

    function __createDateFromFormat($format, $dateString)
    {
        return !empty($dateString) ? Carbon::createFromFormat($format, $dateString) : null;
    }

    function __formatDateTime($date, $format = "Y-m-d h:i:s")
    {
        return $date ? Carbon::parse($date)->format($format) : null;
    }

    function __currentTime($format = null)
    {
        return !empty($format) ? Carbon::now()->format($format) : Carbon::now();
    }

    function __timeLeftInSeconds(Carbon $target, $readable = false)
    {
        // http://oldblog.codebyjeff.com/blog/2016/04/time-left-strings-with-carbon-and-laravel-string-helper
        $now = Carbon::now();

        if ($now->diffInSeconds($target) > 0) {
            return $readable ?
                $now->diffInSeconds($target) . Str::plural(' seconds', $now->diffInSeconds($target)) . ' left'
                : $now->diffInSeconds($target);
        }
    }

    function __parseFromSeconds(int $seconds, $lang = "en", string $format = "%H hour(s) %i minute(s) %s second(s)")
    {
        Carbon::setLocale($lang);
        return CarbonInterval::seconds($seconds)->cascade()->format($format);
    }
}