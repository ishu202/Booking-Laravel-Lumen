<?php

namespace R7\Booking\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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


    public function get_basic_userinfo($table_name, $user_id): array
    {
        return DB::select("SELECT * FROM {$table_name} WHERE id = '{$user_id}'");
    }

    public function get_web_user_info_where($user_type_id, $user_id)
    {
        self::query()->join('tblpinfo',function ($join) use ($user_type_id,$user_id){
            $join->on('tblusers.p_id', '=' ,'tblpinfo.id')
                ->where('tblpinfo.u_id', '=', $user_id)
                ->where('tblpinfo.type_id', '=', $user_type_id);
        })->get([
            'tblusers.id',
            'tblusers.username',
            'tblusers.password',
            'tblpinfo.f_name',
            'tblpinfo.l_name',
            'tblpinfo.email',
            'tblpinfo.phone',
            'tblpinfo.dob',
            'tblpinfo.address',
            'tblpinfo.zip',
            'tblpinfo.state_id',
            'tblpinfo.city_id',
            'tblpinfo.country_id',
            'tblpinfo.type_id'
        ]);
    }

    public function display_contact_info(): array
    {
        return DB::select("SELECT * FROM tblcontact WHERE tblcontact.id = 1");
    }

    public function fetch_state()
    {
        return Tblstate::all();
    }

    public function user_count(): int
    {
        return self::query()->count('id');
    }
}
