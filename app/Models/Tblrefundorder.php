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

    public function display_orders_with_user_info(?string $from, ?string $to)
    {
        // TODO: Implement display_orders_with_user_info() method.
    }

    public function get_rental_status_types()
    {
        // TODO: Implement get_rental_status_types() method.
    }

    public function booking_count()
    {
        // TODO: Implement booking_count() method.
    }
}
