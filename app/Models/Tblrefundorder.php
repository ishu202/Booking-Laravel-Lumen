<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;

class Tblrefundorder extends Tblrinfo
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.refund_booking'));
        $this->mergeFillable([
            'refundFromTable',
            'refundIdFromTable'
        ]);
    }


}
