<?php

namespace Zdirnecamlcs96\Helpers\Traits;

trait Str {

    function __toStr($data)
    {
        if(is_object($data) || is_array($data)){
            $data = json_encode((array) $data, true);
        }
        return $data;
    }

    /**
     * References: https://tenerant.com/blog/laravel-ordinal-helper-function/
     * Append an ordinal indicator to a numeric value.
     *
     * @param  string|int  $value
     * @param  bool  $superscript
     * @return string
     */
    function __numberOrdinal($value, $superscript = false)
    {
        $number = abs($value);

        $indicators = ['th','st','nd','rd','th','th','th','th','th','th'];

        $suffix = $superscript ? '<sup>' . $indicators[$number % 10] . '</sup>' : $indicators[$number % 10];
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            $suffix = $superscript ? '<sup>th</sup>' : 'th';
        }

        return number_format($number) . $suffix;
    }

}