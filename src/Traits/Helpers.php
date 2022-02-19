<?php

namespace App\Helpers;

trait Helpers {

    use Authentication, Env, FCM, FileSystem, Locale, Logging, SMS, Requests, Validation, Date, Str, Others, Sessions, Datatable {
        Logging::__normalLog insteadOf FCM, SMS;
        Env::__isDebug insteadOf Requests;
    }

}