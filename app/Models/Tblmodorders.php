<?php


namespace R7\Booking\Models;

use R7\Booking\Models\Abstracts\BookingAbstract;
use R7\Booking\Models\Interfaces\UpdateOrderInterface;

class Tblmodorders extends BookingAbstract implements UpdateOrderInterface
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable('r7.booking.tables.mod_booking');
    }

    public function get_all_mod_orders(string $order_id)
    {
        return self::query()->where(['order_id',$order_id])->get()->toArray();
    }

    public function update_booking_data(array $booking_data)
    {
        return self::query()->create($booking_data);
    }
}
