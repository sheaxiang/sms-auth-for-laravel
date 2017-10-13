<?php

namespace Sheaxiang\SmsAuth\Facades;

use Illuminate\Support\Facades\Facade;

class SmsAuth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sms.auth';
    }
}
