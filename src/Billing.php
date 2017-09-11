<?php

namespace IZaL\Knet;

interface Billing
{
    /**
     * Perform Payment
     * for more info check (https://www.tap.company/developers/)
     */
    public function requestPayment();

    public function getPaymentURL();

}