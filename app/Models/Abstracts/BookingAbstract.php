<?php


namespace R7\Booking\Models\Abstracts;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BookingAbstract extends Model
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

    protected static function create_query($from = null, $to = null, ...$args): array
    {
        $base_sql = file_get_contents(
            base_path('vendor/r7booking/ishu8957/app/Models/get_current_booking_state.sql'
            ));
        $args = array_reduce($args,function ($memo,$arg){
            if ($arg){
                $memo .= " {$arg} AND ";
            }
            return $memo;
        },"");


        $dateTemplate 	= "STR_TO_DATE( '%1\$s', '%%Y-%%m-%%d' )";
        $fieldTemplate 	= "STR_TO_DATE( original_mod_split.`%1\$s`, '%%Y-%%m-%%d' )";
        $filterTemplate = "( ( ( %1\$s <= %3\$s )
                          AND ( %3\$s <= %2\$s ) )
                          OR ( ( %1\$s <= %4\$s )
                          AND ( %4\$s <= %2\$s ) ) )
                          AND {$args}";



        $query = sprintf(
            $base_sql,
            ( $from && $to ) ?
                sprintf( $filterTemplate,
                    sprintf( $dateTemplate, $from ),
                    sprintf( $dateTemplate, $to ),
                    sprintf( $fieldTemplate, 'date_from' ),
                    sprintf( $fieldTemplate, 'date_to' ) )
                :
                '');
        dd($from,$to);

        return DB::select($query);
    }
}
