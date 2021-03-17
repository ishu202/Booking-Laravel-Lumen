<?php


namespace R7\Booking\Traits;


trait CreateBooking
{
    use BookingUtility;

    public $order_id;
    public $totalAmount;
    public $products;
    public $customer_info;
    public $payment_type;

    public function __construct(
        $totalAmount,
        $products,
        $customer_info,
        $payment_type
    )
    {
        $this->order_id = app('r7.booking.tblrinfo')->generate_order_id();
        $this->totalAmount = $totalAmount;
        $this->products = self::format_cart_array($products?: []);
        $this->customer_info = $customer_info;
        $this->payment_type = $payment_type;

    }

    public function if_payment_store_or_online($payment_type): bool
    {
        if ($payment_type == 1){
            return true;
        }elseif($payment_type == 2){
            return false;
        }
    }

    public function update_guest_order_cash( $guest, $user, $orderid, $totalAmount, $items, $payment_type, $transaction_type, $message ) {

        if ( app('r7.booking.transaction')->insert_transaction_cash( self::prepare_cash_response( $orderid, $totalAmount, $transaction_type, $message ) ) ) {
            $booking = self::create_cash_booking_array( $guest, $user, $orderid, $items, $payment_type, $message );
            if ( app('r7.booking.tblmodorders')->update_booking_data( $booking ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }



}
