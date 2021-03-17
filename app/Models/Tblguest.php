<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\GuestInterface;

class Tblguest extends Model implements GuestInterface
{
    const CREATED_AT = "CreationDate";
    const UPDATED_AT = "UpdationDate";

    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'phone',
        'address',
        'zip',
        'state_id',
        'city_id',
        'country_id',
        'type_id'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('r7.booking.tables.guest'));
    }

    //TODO: create guest user and return id;

    public function insert_guest_record_and_return_id(array $user_info)
    {
        self::query()->create($user_info);

        return self::query()->orderByDesc('CreationDate')
            ->limit(1)->get('id')->toArray()[0]['id'];

    }


}
