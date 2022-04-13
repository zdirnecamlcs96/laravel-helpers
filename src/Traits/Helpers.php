<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Illuminate\Foundation\Validation\ValidatesRequests;

trait Helpers {

    use ValidatesRequests;

    use Authentication, Env, FCM, FileSystem, Locale, Logging, SMS, Requests, Validation, Date, Str, Others, Sessions, Datatable {
        Logging::__normalLog insteadOf FCM, SMS;
        Env::__isDebug insteadOf Requests;
        ValidatesRequests::getValidationFactory insteadOf Validation;
        Validation::validate insteadOf ValidatesRequests;
    }

}