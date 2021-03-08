<?php
declare(strict_types=1);

namespace R7\Booking\Models\Interfaces;

interface TransactionInterface
{
    public function get_payment_ids_where($order_id);

}
