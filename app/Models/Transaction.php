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
        return self::query()->where(['order_id' => $order_id])->get();
    }

    public function insert_transaction_cash(array $response)
    {
        // TODO: Implement insert_transaction_cash() method.
    }

    public function return_inserted_transaction()
    {
        // TODO: Implement return_inserted_transaction() method.
    }

    public function get_txn_id_from_refund($order_id)
    {
        self::query()->where([
            'order_id' => $order_id,
            'type' => 2
        ])->orderBy('created_at')->get()->toArray();
    }
}
