<?php


namespace R7\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\RefundOrderInterface;

class Tblrefundorder extends Model implements RefundOrderInterface
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
        'status',
        'order_status',
        'refundFromTable',
        'refundIdFromTable'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('r7.booking.tables.refund_booking'));

    }


    public function get_all_refunds(string $order_id)
    {
        // TODO: Implement get_all_refunds() method.
    }

    public function update_order_refund_items(array $booking_data)
    {
        // TODO: Implement update_order_refund_items() method.
    }

    public function get_refund_items($order_id)
    {
        // TODO: Implement get_refund_items() method.
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
