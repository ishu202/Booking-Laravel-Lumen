<?php


namespace R7\Booking\Models\Interfaces;


interface GuestInterface
{
    public function insert_guest_record_and_return_id(array $user_info);
}