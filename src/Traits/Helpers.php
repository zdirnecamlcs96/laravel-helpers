<?php

namespace Zdirnecamlcs96\Helpers\Traits;

trait Helpers {

    use Authentication, Env, FCM, FileSystem, Locale, Logging, SMS, Requests, Validation, Date, Str, Others, Sessions {
        Logging::__normalLog insteadOf FCM, SMS;
        Env::__isDebug insteadOf Requests;
    }

}