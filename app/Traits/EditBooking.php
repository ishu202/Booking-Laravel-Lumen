<?php
declare(strict_types=1);

namespace R7\Booking\Traits;


trait EditBooking
{
    protected $txn_ids;
    protected $orderid;
    protected $rinfo_table_type = 1;
    protected $modinfo_table_type = 2;
    protected $charge_id_from_refund_id;
    protected $userId;
    protected $user_type;
    protected $store = 1; // instore or cash/card
    protected $online = 2; // online order or card
    protected $debit = 1;
    protected $credit = 2;
    protected $CusStripeId;


    public function ifRentalExists($id): bool {
        if($id !== NULL && app('r7.booking.tblrinfo')->check_rental_exists($id)){
            return true;
        }else{
            return false;
        }
    }

    public function setOrderId() :self
    {
        $this->orderid = $this->editorder['results'][0]['order_id'];
        return $this;
    }

    public function getOrderId() {
        return $this->orderid;
    }

    public function getTxnIds() {
        return $this->txn_ids;
    }

    public function setTxnIds() :self
    {
        $this->txn_ids = app('r7.booking.transactions')->get_payment_ids_where($this->validOrderId($this->orderid));
        return $this;
    }

    public function setRinfoTableType() :self
    {
        $this->editorder['results'][0]['table_type'] = $this->rinfo_table_type;
        return $this;
    }

    public function setModinfoTableType():self
    {
        if(!empty($this->editorder['mod_results'])){
            for($i=0;$i<count($this->editorder['mod_results']);$i++){
                $this->editorder['mod_results'][$i]['table_type'] = $this->modinfo_table_type;
            }
            $this->editorder['results'] = array_merge($this->editorder['results'],$this->editorder['mod_results']);
        }
        return $this;
    }

    public function setPaymentIds() :self
    {
        $this->editorder['payment_ids'] = self::format_txn_id_from_db($this->txn_ids);
        return $this;
    }

    public function setRefundResult() :self
    {
        $this->editorder['refund_result'] = app('r7.booking.tblrefundorder')->get_all_refunds($this->validOrderId($this->orderid));
        return $this;
    }

    public function setModResult() :self
    {
        $this->editorder['mod_results'] = app('r7.booking.tblmodorders')->get_all_mod_orders($this->validOrderId($this->orderid));
        return $this;
    }

    public function setChargeIdFromRefundId() :self {
        $this->charge_id_from_refund_id = app('r7.booking.transactions')->get_txn_id_from_refund($this->validOrderId($this->orderid));
        return $this;
    }

    public function setReferedChargeRefundIds() :self {
        $this->editorder['refered_charge_refund_ids'] = self::format_charge_ids_from_refund_ids($this->charge_id_from_refund_id);
        return $this;
    }

    public function getPaymentOptions() :self {
        $this->editorder['payment_options'] = app('r7.booking.tblrinfo')->get_payment_options($this->validOrderId($this->orderid));
        return $this;
    }

    public function setUserId() :self {
        if ($this->editorder['results'][0]['user_id'] != NULL){
            $this->userId = $this->editorder['results'][0]['user_id'];
            $this->setUserTypeId(2);
        }else{
            $this->userId = $this->editorder['results'][0]['guest_id'];
            $this->setUserTypeId(4);
        }
        return $this;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function ifUserOrGuest($user_type_id): bool {
        if ($user_type_id == 2){
            return true;
        }else{
            return false;
        }
    }

    public function getUserTypeId() {
        return $this->user_type;
    }

    public function setUserTypeId($type) {
        $this->user_type = $type;
    }

    public function getUserAndStripeData() :self {
        $this->editorder['stripeCustomer'] = app('r7.booking.tblstripeCustomers')->getCustomerToken($this->getUserId());
        if ($this->getUserTypeId() == 2){
            $this->editorder['user'] = app('r7.booking.tblusers')->get_basic_userinfo("tblusers",$this->getUserId());
            $this->editorder['user_info'] = app('r7.booking.tblusers')->get_web_user_info_where($this->getUserTypeId(),$this->getUserId());
        }else{
            $this->editorder['user_info'] = app('r7.booking.tblusers')->get_basic_userinfo("tblguest",$this->getUserId());
        }
        $this->setCusStripeId();
        return $this;
    }

    public function ifUserThenDisableInput() :self {
        if($this->getUserTypeId() == 2){
            $this->editorder['disabled_input'] = 'disabled';
        }else{
            $this->editorder['disabled_input'] = 'required';
        }
        return $this;
    }

    public function getTableItemsData() :self {
        $this->editorder += array(
            'product_array' => $this->editorder['results'][0]['tool_id'],
            'pick_date_array' => $this->editorder['results'][0]['date_from'],
            'drop_date_array' => $this->editorder['results'][0]['date_to'],
            'pick_time_array' => $this->editorder['results'][0]['pick_time'],
            'drop_time_array' => $this->editorder['results'][0]['drop_time'],
            'unit' => $this->editorder['results'][0]['units'],
            'total_amount' => $this->editorder['results'][0]['total_amount'],
            'item_info' => app('r7.booking.tbltool')->display_product_info()
        );
        return $this;
    }

    public function validOrderId($data): string
    {
        return $data == "" ? "" : $this->getOrderId();
    }

    public function didProductChanged($data): bool
    {
        return $data == 'productChange';
    }

    public function didUserChange($data): bool
    {
        return $data == 'userChange';
    }

    public function setCusStripeId(){
        $this->CusStripeId = array_key_exists(0,$this->editorder['stripeCustomer'])
            ? $this->editorder['stripeCustomer'][0]['customerId'] : NULL;
    }

    public function getCusStripeId() {
        return $this->CusStripeId;
    }

    public function create_guest_due_order( $guest, $user, $orderid, $items, $payment_type, $transaction_id, $message ): bool
    {
        $booking = self::create_stripe_booking_array( $guest, $user, $orderid, $items, $transaction_id, $payment_type, $message );
        if ( app('r7.booking.tblmodorders')->update_booking_data( $booking ) ) {
            return true;
        } else {
            return false;
        }
    }

    public function update_guest_refund_order_stripe( $guest, $user, $orderid, $items, $payment_type, $transaction_id, $message ): bool
    {
        $booking = self::create_stripe_booking_refund_array( $guest, $user, $orderid, $items, $transaction_id, $payment_type, $message );
        if ( app('r7.booking.tblrefundorder')->update_order_refund_items( $booking ) ) {
            return true;
        } else {
            return false;
        }
    }

    private function update_guest_order_cash( $guest, $user, $orderid, $totalAmount, $items, $payment_type, $transaction_type, $message ) {

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

    public function refundFromCash( $chargeId, $amount, $message, $transaction_type, $orderid ){
        if ( app('r7.booking.transaction')->insert_transaction_cash( self::prepare_cash_response( $orderid, $amount, $transaction_type, $message ), $transaction_type, $orderid ) ) {
            return app('r7.booking.transaction')->return_inserted_transaction();
        } else {
            return false;
        }
    }

    public function update_guest_refund_order_cash($guest, $user, $orderid, $items, $payment_type, $transaction_id, $message){
        $booking = self::create_stripe_booking_refund_array( $guest, $user, $orderid, $items, $transaction_id, $payment_type, $message );

        if ( app('r7.booking.tblrefundorder')->update_order_refund_items( $booking ) ) {
            return true;
        } else {
            return false;
        }
    }

}
