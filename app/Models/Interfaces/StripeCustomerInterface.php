<?php
declare(strict_types=1);

namespace R7\Booking\Models\Interfaces;

interface StripeCustomerInterface{

    public function insert_customer_id_guest($customer_id);

    public function getCustomerToken($user_id);

    public function insert_customer_id_user($user_type,$user_id,$customer_id);

    public function insert_payment_id_for_user($user_id, $payment_id);

    public function insert_payment_id_for_guest($id, $guest_id, $payment_id);

    public function update_guest_user_stripe($customer_id,$user_id);

}
