<?php

declare(strict_types=1);

namespace R7\Booking\Interface\Models;

interface BookingInterface{

    public static function getPaymentOptions($order_id);
    
}