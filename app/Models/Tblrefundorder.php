<?php


namespace R7\Booking\Models;

use R7\Booking\Models\Abstracts\BookingAbstract;
use R7\Booking\Models\Interfaces\RefundOrderInterface;

class Tblrefundorder extends BookingAbstract implements RefundOrderInterface
{
    protected $fillable = [
        'order_id',
        'tool_id',
        'units',
        'user_id',
        'guest_id',
        'date_from',
        'date_to',
        'message',
        'pick_time',
        'drop_time',
        'total_amount',
        'payment_status',
        'payment_ids',
        'payment_type',
        'refundFromTable',
        'refundIdFromTable',
        'status',
        'order_status'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
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
