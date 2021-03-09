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
        // TODO: Implement insert_customer_id_guest() method.
    }

    public function getCustomerToken($user_id)
    {
        // TODO: Implement getCustomerToken() method.
    }

    public function insert_customer_id_user($user_type, $user_id, $customer_id)
    {
        // TODO: Implement insert_customer_id_user() method.
    }

    public function insert_payment_id_for_user($id, $payment_id)
    {
        // TODO: Implement insert_payment_id_for_user() method.
    }

    public function insert_payment_id_for_guest($id, $guest_id, $payment_id)
    {
        // TODO: Implement insert_payment_id_for_guest() method.
    }

    public function update_guest_user_stripe($customer_id, $user_id)
    {
        // TODO: Implement update_guest_user_stripe() method.
    }
}
