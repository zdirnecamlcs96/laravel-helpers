<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Illuminate\Notifications\Notification as Notice;
use Illuminate\Support\Facades\Notification as Facades;

trait Notification
{
    function __sendNotification($targets, Notice $notification)
    {
        Facades::send($targets, $notification);
    }
}