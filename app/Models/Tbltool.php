<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\ItemInterface;

class Tbltool extends Model implements ItemInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.tbltool'));
    }

    public function display_product_info()
    {
        // TODO: Implement display_product_info() method.
    }

    public function get_sellable_tool()
    {
        // TODO: Implement get_sellable_tool() method.
    }

    public function get_rentable_tool()
    {
        // TODO: Implement get_rentable_tool() method.
    }

    public function item_count()
    {
        // TODO: Implement item_count() method.
    }
}
