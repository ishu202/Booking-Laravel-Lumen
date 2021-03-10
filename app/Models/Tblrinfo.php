<?php
declare(strict_types=1);

namespace R7\Booking\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use R7\Booking\Models\Abstracts\BookingAbstract;
use R7\Booking\Models\Interfaces\BookingInterface;

class Tblrinfo extends BookingAbstract implements BookingInterface
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.booking'));
    }

    public function check_rental_exists($booking_table_id)
    {
        return self::query()->find($booking_table_id);
    }

    public function get_payment_options(string $order_id)
    {
        return self::query()->where(['order_id',$order_id])->get();
    }

    public function display_orders_with_user_info($from = null, $to = null): array
    {
        return self::create_query($from,$to,null);
    }

    public function get_rental_status_types()
    {
        $result =  DB::select("SELECT * FROM `tblrentalStatus`");
        if(!empty($result)){
            return $result;
        }else{
            return "";
        }
    }

    public function booking_count(): int
    {
        return self::query()->count('id');
    }
}
