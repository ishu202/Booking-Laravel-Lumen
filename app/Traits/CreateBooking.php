<?php


namespace R7\Booking\Traits;


use Illuminate\Http\Request;

trait CreateBooking
{
    use BookingUtility;
    use Gateway;

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
        }

        return false;
    }

    public function update_guest_order_cash( $guest, $user, $orderid, $totalAmount, $items, $payment_type, $transaction_type, $message ): bool
    {
        if ( app('r7.booking.transaction')->insert_transaction_cash( self::prepare_cash_response( $orderid, $totalAmount, $transaction_type, $message ) ) ) {
            $booking = self::create_cash_booking_array( $guest, $user, $orderid, $items, $payment_type, $message );
            if ( app('r7.booking.tblrinfo')->store_booking_data( $booking ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function create_guest_user_in_database_and_stripe ($type_id,$user_info) {
        if ($type_id == 4){
            $guest_id = app('r7.booking.tblguest')->insert_guest_record_and_return_id($user_info);
            $this->create_stripe_user($user_info);

            if (count($this->get_error()) == 0){
                $this->response['user'];
                app('r7.booking.tblstripeCustomers')->insert_customer_id_user(
                    $user_info['type_id'],
                    $guest_id,
                    $this->response['user']['id']
                );
            }

            return $guest_id;
        }
        return null;
    }

    /**
     * @param $stripe_token
     * @param $guest_id
     */
    public function attach_payment_method_to_guest_user($stripe_token, $guest_id): void
    {
        $this->create_default_source_card_user(
            app('r7.booking.tblstripeCustomers')->getCustomerToken($guest_id, 4),
            $stripe_token
        );
    }

    public function create_online_booking($data, $stripe_token)
    {
        $guest_id = $this->create_guest_user_in_database_and_stripe(4,$data->user_info);
        $attach_payment_method_to_guest_user = $this->attach_payment_method_to_guest_user($stripe_token, $guest_id);
        if (count($this->get_error()) == 0){
            if ($this->update_guest_order_cash(
                $data->user_info,
                NULL,
                $data->order_id,
                $data->total_amount,
                $data->line_items,
                $data->payment_type,
                $data->transaction_type,
                NULL
            )){
                return true;
            }
        }
        return false;
    }

}
