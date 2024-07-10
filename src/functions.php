<?php

use IPay\IPayClient;

if (!function_exists('ipay')) {
    function ipay(): IPayClient
    {
        return IPayClient::create();
    }
}
