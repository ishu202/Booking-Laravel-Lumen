<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\TransactionInterface;

class Transaction extends Model implements TransactionInterface
{
    const CREATED_AT = "created_at";
    const UPDATED_AT = NULL;

    protected $fillable = [
        'response',
        'type',
        'status',
        'order_id'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.transaction'));
    }

    public function get_payment_ids_where($order_id)
    {
        return self::query()->where(['order_id' => $order_id])
            ->orderBy('created_at')->get();
    }

    public function insert_transaction_cash(array $response)
    {
        $response['type'] = ($response['type'] == 1)? 1 : 2 ;
        return self::query()->create($response);
    }

    public function return_inserted_transaction(): int
    {
        return self::query()->orderByDesc('created_at')
            ->limit(1)->get('id')->toArray()[0]['id'];
    }

    public function get_txn_id_from_refund($order_id)
    {
        self::query()->where([
            'order_id' => $order_id,
            'type' => 2
        ])->orderBy('created_at')->get()->toArray();
    }
}
