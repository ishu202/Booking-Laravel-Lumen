<?php
declare(strict_types=1);

namespace R7\Booking\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tblrinfo extends Model
{
    protected $fillable = [
        'order_id',
        'tool_id',
        'units',
        'user_id',
        'guest_id',
        'date_from',
        'date_to',
        'message',
        'pick_time',
        'drop_time',
        'total_amount',
        'payment_status',
        'payment_ids',
        'payment_type',
        'status',
        'order_status'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function getData($order_id): array
    {
        $base_query = file_get_contents( base_path('vendor/booking/Models/get_current_booking_state.sql'));
        $query = self::create_query($base_query,Carbon::now()->subYears(5),Carbon::now()->addYears(5));
        return DB::select(($query),[$order_id]);
    }

    private static function create_query($base_sql, $from, $to ): string
    {
        $dateTemplate 	= "STR_TO_DATE( '%1\$s', '%%Y-%%m-%%d' )";
        $fieldTemplate 	= "STR_TO_DATE( original_mod_split.`%1\$s`, '%%Y-%%m-%%d' )";
        $filterTemplate = "( ( ( %1\$s <= %3\$s ) AND ( %3\$s <= %2\$s ) ) OR ( ( %1\$s <= %4\$s ) AND ( %4\$s <= %2\$s ) ) ) AND";

        return sprintf(
            $base_sql,
            ( $from && $to ) ? sprintf( $filterTemplate, sprintf( $dateTemplate, $from ), sprintf( $dateTemplate, $to ), sprintf( $fieldTemplate, 'date_from' ), sprintf( $fieldTemplate, 'date_to' ) ) : ''
        );
    }
    
}