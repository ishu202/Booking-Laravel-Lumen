<?php
declare(strict_types=1);

namespace R7\Booking\Models\Interfaces;

interface RefundOrderInterface{

    public function get_all_refunds(string $order_id);

    public function update_order_refund_items(array $booking_data);

    public function get_refund_items($order_id);


}
