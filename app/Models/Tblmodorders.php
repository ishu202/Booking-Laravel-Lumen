<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\UpdateOrderInterface;

class Tblmodorders extends Model implements UpdateOrderInterface
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
        'order_status'
    ];

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
        // TODO: Implement update_booking_data() method.
    }
}
