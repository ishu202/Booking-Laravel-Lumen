<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\StripeCustomerInterface;

class TblstripeCustomers extends Model implements StripeCustomerInterface
{
    const CREATED_AT = "creationDate";
    const UPDATED_AT = "updationDate";

    protected $fillable = ['customerId','paymentMethodId','user_id','user_type'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.stripe_customer'));
    }


    public function insert_customer_id_guest($customer_id)
    {
        return self::query()->create([
            'customerId' => $customer_id
        ]);
    }

    public function getCustomerToken($user_id)
    {
        return self::query()->where(['user_id' => $user_id])
                    ->get()->toArray();
    }

    public function insert_customer_id_user($user_type, $user_id, $customer_id)
    {
        return self::query()->create([
            'customerId' => $customer_id,
            'user_type' => $user_type,
            'user_id' => $user_id
        ]);
    }

    public function insert_payment_id_for_user($user_id, $payment_id)
    {
        return self::query()->where(['customerid' => $user_id])
            ->update([
                'paymentMethodId' => $payment_id
            ]);

    }

    public function insert_payment_id_for_guest($id, $guest_id, $payment_id)
    {
        return self::query()->where([
            'customerid' => $id
        ])->update([
                'paymentMethodId' => $payment_id,
                'user_id' => $guest_id
            ]);
    }

    public function update_guest_user_stripe($customer_id, $user_id)
    {
        return self::query()->where([
            'customerid' => $customer_id
        ])->update([
            'user_id' => $user_id
        ]);
    }
}
