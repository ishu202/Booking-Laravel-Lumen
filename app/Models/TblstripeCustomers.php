<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;

class TblstripeCustomers extends Model
{
    const CREATED_AT = "creationDate";
    const UPDATED_AT = "updationDate";

    protected $fillable = ['customerId','paymentMethodId','user_id','user_type'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.stripe_customer'));
        $this->setTable(config('r7.booking.tables.stripe_customer_table'));
    }



}
