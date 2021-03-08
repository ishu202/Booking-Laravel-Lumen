<?php

declare(strict_types=1);

namespace R7\Booking\Models\Interfaces;

interface BookingInterface{

    public static function getPaymentOptions($order_id);

}
