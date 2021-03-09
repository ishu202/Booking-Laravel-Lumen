<?php


namespace R7\Booking\Models\Interfaces;


interface ItemInterface
{
    public function display_product_info();

    public function get_sellable_tool();

    public function get_rentable_tool();

    public function item_count();


}
