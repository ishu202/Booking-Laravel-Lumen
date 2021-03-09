<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\StateInterface;

class Tblstate extends Model implements StateInterface
{
    public function __construct(array $attributes = [])
    {
        $this->setTable(config('r7.booking.tables.state'));
        parent::__construct($attributes);

    }

    public function fetch_state()
    {
        return self::all();
    }
}
