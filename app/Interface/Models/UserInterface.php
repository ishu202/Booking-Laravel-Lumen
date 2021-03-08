<?php
declare(strict_types=1);

namespace R7\Booking\Interface\Models;

interface UserInterface{
    public static function get_basic_userinfo($table_name,$user_id);

    public static function get_web_user_info_where($user_type_id, $user_id);
    
    
}