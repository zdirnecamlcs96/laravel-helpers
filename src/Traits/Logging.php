<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as Log;

trait Logging
{

    private function __log($type = "__normalLog")
    {

        $date = $this->__currentTime('Y-m-d');

        $log = null;

        switch ($type) {
            case '__transactionLog':
                $log = new Log($type, [
                    new StreamHandler(storage_path("logs/transactions/transaction-".php_sapi_name()."-$date.log"), Log::INFO)
                ]);
                break;
            case '__cronLog':
                $log = new Log($type, [
                    new StreamHandler(storage_path("logs/cron/cron-".php_sapi_name()."-$date.log"), Log::INFO)
                ]);
                break;
            case '__errorLog':
                $log = new Log($type, [
                    new StreamHandler(storage_path("logs/errors/error-".php_sapi_name()."-$date.log"), Log::ERROR)
                ]);
                break;
            default:
                $log = new Log($type, [
                    new StreamHandler(storage_path("logs/laravel-".php_sapi_name()."-$date.log"), Log::INFO)
                ]);
                break;
        }

        return $log;
    }

    function __transactionLog($message, $fileName = null, $fileLine = null)
    {
        $this->__log('__transactionLog')
            ->info($fileName . "($fileLine): " . $message);
    }

    function __transactionLogData($data, $fileName = null, $fileLine = null)
    {
        $this->__log('__transactionLog')
            ->alert($fileName . "($fileLine):", $data);
    }

    function __errorLog($data)
    {
        $this->__log('__errorLog')
            ->error(PHP_EOL . PHP_EOL  .$data);
    }

    function __cronLog($data)
    {
        $this->__log('__cronLog')
            ->info($data);
    }

    function __normalLog($data)
    {
        $this->__log()
            ->info($data);
    }

    function __activityLog($type, $causedBy, $target_model, array $properties = [], string $action = "")
    {
        activity($type)
            ->causedBy($causedBy)
            ->performedOn($target_model)
            ->withProperties($properties)
            ->log($action);
    }
}