<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\BrandInterface;

class Tblbrand extends Model implements BrandInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.brand'));
    }

    public function brand_count()
    {
        // TODO: Implement brand_count() method.
    }
}
