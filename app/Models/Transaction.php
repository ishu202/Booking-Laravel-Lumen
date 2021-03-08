<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\TransactionInterface;

class Transaction extends Model implements TransactionInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.transaction'));
    }

    public function get_payment_ids_where($order_id)
    {
        return $this->where('order_id',$order_id)->get();
    }
}
