<?php


namespace R7\Booking\Models;

use R7\Booking\Models\Abstracts\BookingAbstract;
use R7\Booking\Models\Interfaces\RefundOrderInterface;

class Tblrefundorder extends BookingAbstract implements RefundOrderInterface
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->mergeFillable([
            'refundFromTable',
            'refundIdFromTable'
        ]);

        $this->setTable(config('r7.booking.tables.refund_booking'));

    }


    public function get_all_refunds(string $order_id)
    {
        self::query()->where(['order_id',$order_id])->get();
    }

    public function update_order_refund_items(array $booking_data)
    {
       self::query()->create($booking_data);
    }
}
