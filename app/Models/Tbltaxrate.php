<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\TaxInterface;

class Tbltaxrate extends Model implements TaxInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.tax'));
    }

    public function get_tax_rate()
    {
        // TODO: Implement get_tax_rate() method.
    }
}
