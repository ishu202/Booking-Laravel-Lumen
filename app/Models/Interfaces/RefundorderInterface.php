<?php
declare(strict_types=1);

namespace R7\Booking\Models\Interfaces;

interface RefundorderInterface{
    public static function get_all_refunds($order_id);


}
