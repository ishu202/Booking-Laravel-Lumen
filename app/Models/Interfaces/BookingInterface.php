<?php

declare(strict_types=1);

namespace R7\Booking\Models\Interfaces;

interface BookingInterface{

    public function check_rental_exists($booking_table_id);

    //alias fun getPaymentOptions
    public function get_payment_options(string $order_id);

    public function display_orders_with_user_info($from,$to);

    public function get_rental_status_types();

    public function booking_count();

    public function store_booking_data( array $booking );

}
