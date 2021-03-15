<?php


namespace R7\Booking\Models\Abstracts;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BookingAbstract extends Model
{
    const CREATED_AT = "CreationDate";
    const UPDATED_AT = "UpdationDate";

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

    protected static function create_query($from, $to, $file , ...$args): string
    {
        $base_sql = file_get_contents(
            base_path("vendor/r7booking/ishu8957/app/Models/{$file}"
            ));
        $args = array_reduce($args,function ($memo,$arg){
            if ($arg){
                $memo .= " {$arg} AND ";
            }
            return $memo;
        },"");

        if ($from && $to){
            $dateTemplate 	= "STR_TO_DATE( '%1\$s', '%%Y-%%m-%%d' )";
            $fieldTemplate 	= "STR_TO_DATE( original_mod_split.`%1\$s`, '%%Y-%%m-%%d' )";
            $filterTemplate = "( ( ( %1\$s <= %3\$s )
                          AND ( %3\$s <= %2\$s ) )
                          OR ( ( %1\$s <= %4\$s )
                          AND ( %4\$s <= %2\$s ) ) )
                          AND {$args}";



            return sprintf(
                $base_sql,
                ( $from && $to ) ?
                    sprintf( $filterTemplate,
                        sprintf( $dateTemplate, $from ),
                        sprintf( $dateTemplate, $to ),
                        sprintf( $fieldTemplate, 'date_from' ),
                        sprintf( $fieldTemplate, 'date_to' ) )
                    :
                    '0');
        }

        return $base_sql;

    }

    protected static function base_request($query) :array {
        return DB::select($query);
    }

    protected static function item_generator($id){
        $query = self::create_query(
            null,
            null,
            "get_current_booking_with_response_state.sql",null)."WHERE booking.id = '{$id}'";
        $data = DB::transaction(function () use ($query){
            $pdo = DB::connection()->getPdo();
            $pdo->query('SET group_concat_max_len = 100000000;');
            return self::base_request($query);
        });

        $generated_result = array_reduce($data,function ($memo,$value){
            if ($memo[0]->type_id == 2){
                $memo[0]->disabled_input = "disabled";
            }else{
                $memo[0]->disabled_input = "required";
            }

            if (count(explode(" , ",$value->tool_id)) >= 1){
                $temp = [
                    'tool_id'         => explode(" , ",$value->tool_id),
                    'table_type'      => explode(" , ", $value->table_id),
                    'payment_id'      => explode(" , ",$value->payment_id),
                    't_name'          => explode(" , ",$value->t_name),
                    'real_table_id'   => explode(" , ",$value->real_table_id),
                    'units'           => explode(" , ",$value->units),
                    'date_from'       => explode(" , ",$value->date_from),
                    'date_to'         => explode(" , ",$value->date_to),
                    'pick_time'       => explode(" , ",$value->pick_time),
                    'drop_time'       => explode(" , ",$value->drop_time),
                    'message'         => explode(" , ",$value->message),
                    'is_outgoing'     => explode(" , ",$value->is_outgoing),
                    'is_incoming'     => explode(" , ",$value->is_incoming),
                    'is_past_due'     => explode(" , ",$value->is_past_due),
                    'rental_status'   => explode(" , ",$value->rental_status),
                    'amount'          => explode(" , ",$value->amount),
                    'payment_type'    => explode(" , ",$value->payment_type),
                    'response'        => explode(" , ",$value->response),
                    'creation_date'   => explode(" , ",$value->creation_date),
                    'updation_date'   => explode(" , ",$value->updation_date),
                ];

                $temp1 = function () use ($temp){
                    $tool_data = [];
                    for ($i = 0; $i < count($temp['tool_id']) ; $i++){
                        $keys = array_keys($temp);
                        $values = array_column($temp,$i);
                        array_push($tool_data,array_combine($keys,$values));
                    }
                    return $tool_data;
                };
            }

            if (count($temp1()) >= 1){
                $memo[0]->tool_data = $temp1();
                unset(
                    $memo[0]->tool_id,
                    $memo[0]->t_name,
                    $memo[0]->payment_id,
                    $memo[0]->units,
                    $memo[0]->date_from,
                    $memo[0]->date_to,
                    $memo[0]->pick_time,
                    $memo[0]->drop_time,
                    $memo[0]->is_outgoing,
                    $memo[0]->is_incoming,
                    $memo[0]->is_past_due,
                    $memo[0]->rental_status,
                    $memo[0]->amount,
                    $memo[0]->response,
                    $memo[0]->message,
                    $memo[0]->payment_type,
                    $memo[0]->creation_date,
                    $memo[0]->updation_date,
                    $memo[0]->table_id,
                    $memo[0]->real_table_id
                );
            }

            return $memo;
        },$data);

        return $generated_result;
    }


}
