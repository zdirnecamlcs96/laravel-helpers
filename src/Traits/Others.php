<?php

namespace Zdirnecamlcs96\Helpers\Traits;
use Illuminate\Support\Facades\DB;

trait Others {

    function __calculateDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        return ( 3959 * acos( cos( deg2rad($latitude1) ) * cos( deg2rad( $latitude2 ) ) * cos( deg2rad( $longitude2 ) - deg2rad($longitude1) ) + sin( deg2rad($latitude1) ) * sin( deg2rad( $latitude2 ) ) ) );
    }

    function __nearbyLocation($target, $latitude, $longitude, $distance = 25, $query = [], bool $return_as_query = false)
    {
        $results = $target::select(
            DB::raw('*, ( 6367 * acos( cos( radians( ? ) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( ? ) ) + sin( radians( ? ) ) * sin( radians( latitude ) ) ) ) AS distance'))
            ->having('distance', '<', $distance)
            ->setBindings([$latitude, $longitude, $latitude])
            ->orderBy('distance');

        foreach ($query as $key => $value) {
            $results->where($value[0], $value[1], $value[2]);
        }

        return $return_as_query ? $results : $results->get();
    }

    function __isEmpty($value, $default = null)
    {

        if(is_array($value)) {
            return sizeof($value) > 0 ? $value : $default;
        }

        return empty($value) ? $default : $value;
    }

    function __generateUniqueSlug($name, $className)
    {
        $name = mb_ereg_replace('/', '', $name);
        $slug = mb_strtolower(trim(mb_ereg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $unique = false;
        $counter = 0;
        while (!$unique) {
            $checkSlug = $className::whereSlug($slug)->first();
            if(empty($checkSlug)){
                $unique = true;
            }else{
                $uniqueness = "";

                if ($counter > 0) {
                    $uniqueness = "-$counter";
                }

                $slug = mb_strtolower(trim(mb_ereg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')) . $uniqueness;
            }
            $counter++;
        }

        return $slug;
    }

}