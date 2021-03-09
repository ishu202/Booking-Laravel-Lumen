<?php
declare(strict_types=1);

namespace R7\Booking\Models\Interfaces;

interface UserInterface{

    public function get_basic_userinfo($table_name,$user_id);

    public function get_web_user_info_where($user_type_id, $user_id);


}
