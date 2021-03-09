<?php
declare(strict_types=1);

namespace R7\Booking\Models\Interfaces;

interface UpdateOrderInterface{

    public function get_all_mod_orders(string $order_id);

    //alias
    public function update_booking_data(array $booking_data);

}
