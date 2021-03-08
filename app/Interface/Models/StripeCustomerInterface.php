<?php
declare(strict_types=1);

namespace R7\Booking\Interface\Models;

interface StripeCustomerInterface{
    
    public static function insert_customer_id_guest($customer_id);
    
    public static function getCustomerToken($user_id);

    public static function insert_customer_id_user($user_type,$user_id,$customer_id);

    public static function insert_payment_id_for_user($id, $payment_id);

    public static function insert_payment_id_for_guest($id, $guest_id, $payment_id);

    public static function update_guest_user_stripe($customer_id,$user_id);
    
}
