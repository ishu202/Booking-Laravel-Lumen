<?php

namespace R7\Booking\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use R7\Booking\Models\Interfaces\AdminInterface;
use R7\Booking\Models\Interfaces\StateInterface;
use R7\Booking\Models\Interfaces\UserInterface;

class User extends Model implements AuthenticatableContract, AuthorizableContract ,
    UserInterface, AdminInterface, StateInterface
{
    use Authenticatable, Authorizable, HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.user'));

    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password', 'code', 'type_id', 'p_id', 'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];


    public function get_basic_userinfo($table_name, $user_id)
    {
        // TODO: Implement get_basic_userinfo() method.
    }

    public function get_web_user_info_where($user_type_id, $user_id)
    {
        // TODO: Implement get_web_user_info_where() method.
    }

    public function display_contact_info()
    {
        // TODO: Implement display_contact_info() method.
    }

    public function fetch_state()
    {
        // TODO: Implement fetch_state() method.
    }

    public function user_count()
    {
        // TODO: Implement user_count() method.
    }
}
