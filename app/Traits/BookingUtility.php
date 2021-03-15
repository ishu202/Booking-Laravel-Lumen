<?php
declare(strict_types=1);

namespace R7\Booking\Traits;

trait BookingUtility
{
    public static function prepare_stripe_response( $refundResponse, $type, $orderid ): array
    {
        $transaction = array(
            'response' => $refundResponse,
            'status'   => 1,
            'order_id' => $orderid,
            'type'     => $type
        );

        return $transaction;
    }

    public static function format_txn_id_from_db($data): array {
        for ( $i = 0; $i < count( $data ); $i ++ ) {
            $data[ $i ]["response"] = json_decode( $data[ $i ]["response"], true );
        }
        return self::prepare_charge_id_for_view( $data );
    }

    public static function prepare_charge_id_for_view($actual_txn_id): array {
        $charge = array();
        for ( $i = 0; $i < count( $actual_txn_id ); $i ++ ) {
            if ( preg_match( '#^in#i', $actual_txn_id[ $i ]["response"]['id'] ) === 1 ) {
                $charge[ $i ] = $actual_txn_id[ $i ]["response"]['charge'];
            } else if ( preg_match( '#^re#i', $actual_txn_id[ $i ]["response"]['id'] ) === 1 ) {
                continue;
            } else {
                $charge[ $i ] = $actual_txn_id[ $i ]["response"]['id'];
            }
        }

        return array_values( $charge );
    }

    public static function format_charge_ids_from_refund_ids($actual_refund_response): array {
        for ( $i = 0; $i < count( $actual_refund_response ); $i ++ ) {
            $actual_refund_response[ $i ]["response"] = json_decode( $actual_refund_response[ $i ]["response"], true );
        }

        return self::prepare_charge_id_from_refund_ids( $actual_refund_response );
    }

    public static function format_refund_item_array($cart_item) {
        if(count($cart_item)){
            $products = count($cart_item)/10;
            $temp_count = 0;
            $cart_array = array();
            for($i=0;$i < $products;$i++){
                for($j=0;$j < 10;$j++){
                    $cart_array[$i][$j] = $cart_item[$temp_count + $j];
                    if(count($cart_array[$i]) % 10 == 0){
                        $temp_count += 10;
                    }
                }
            }
            return $cart_array;
        }else{
            return [];
        }
    }

    public static function prepare_charge_id_from_refund_ids( $actual_txn_id ): array
    {
        $charge = array();
        for ( $i = 0; $i < count( $actual_txn_id ); $i ++ ) {
            $charge[ $i ] = array_key_exists('charge' , $actual_txn_id[ $i ]["response"])
                ? $actual_txn_id[ $i ]["response"]['charge']
                : $actual_txn_id[ $i ]["response"]['id'];
        }

        return $charge;
    }

    public static function format_cart_array(array $cart_item ): array
    {
        if(count($cart_item)){
            $products   = count( $cart_item ) / 7;
            $temp_count = 0;
            $cart_array = array();

            for ( $i = 0; $i < $products; $i ++ ) {
                for ( $j = 0; $j < 7; $j ++ ) {
                    $cart_array[ $i ][ $j ] = $cart_item[ $temp_count + $j ];
                    if ( count( $cart_array[ $i ] ) % 7 == 0 ) {
                        $temp_count += 7;
                    }
                }
            }

            return $cart_array;
        }else{
            return [];
        }
    }

    public static function create_stripe_booking_array( $guest, $user, $orderid, $items, $transaction_id, $payment_type, $message ) {
        $items_array      = array();
        $pick_dates_array = array();
        $drop_dates_array = array();
        $pick_time_array  = array();
        $drop_time_array  = array();
        $units            = array();
        $total_amount     = array();
        $status           = array();
        if ( is_array( $transaction_id ) ) {
            $transaction_ids = implode( ' , ', $transaction_id );
        } else {
            $transaction_ids = $transaction_id;
        }


        for ( $i = 0; $i < count( $items ); $i ++ ) {
            if ( ! empty( $items[ $i ][0] ) ) {
                array_push( $items_array, $items[ $i ][0] );
                array_push( $status, 0 );
            }
            if ( ! empty( $items[ $i ][2] ) ) {
                if ( $items[ $i ][2] != "N/A" ) {
                    array_push( $pick_dates_array, date( 'Y-m-d', strtotime( $items[ $i ][2] ) ) );
                } else {
                    array_push( $pick_dates_array, "N/A" );
                }

            }
            if ( ! empty( $items[ $i ][3] ) ) {
                if ( $items[ $i ][3] != "N/A" ) {
                    array_push( $drop_dates_array, date( 'Y-m-d', strtotime( $items[ $i ][3] ) ) );
                } else {
                    array_push( $drop_dates_array, "N/A" );
                }

            }
            if ( ! empty( $items[ $i ][4] ) ) {
                array_push( $pick_time_array, $items[ $i ][4] );
            }
            if ( ! empty( $items[ $i ][5] ) ) {
                array_push( $drop_time_array, $items[ $i ][5] );
            }
            if ( ! empty( $items[ $i ][1] ) ) {
                array_push( $units, $items[ $i ][1] );
            }
            if ( ! empty( $items[ $i ][6] ) ) {
                array_push( $total_amount, number_format( $items[ $i ][6], 2, '.', '' ) );
            }
        }

        $booking = array(
            'order_id'       => $orderid,
            'tool_id'        => implode( " , ", $items_array ),
            'units'          => implode( " , ", $units ),
            'date_from'      => implode( " , ", $pick_dates_array ),
            'user_id'        => $user,
            'guest_id'       => $guest,
            'date_to'        => implode( " , ", $drop_dates_array ),
            'message'        => $message,
            'pick_time'      => implode( " , ", $pick_time_array ),
            'drop_time'      => implode( " , ", $drop_time_array ),
            'total_amount'   => implode( " , ", $total_amount ),
            'payment_status' => 1,
            'payment_id'     => $transaction_ids,
            'payment_type'   => $payment_type,
            'status'         => implode( " , ", $status )
        );

        return $booking;
    }

    public static function amountFormatter( $amount, $tax ) {
        return number_format($amount + (($tax * $amount)/100),2,'.','');
    }

    public static function create_stripe_booking_refund_array( $guest, $user, $orderid, $items, $transaction_id, $payment_type, $message ) {
        $table_ids_array  = array();
        $table_type_array = array();
        $items_array      = array();
        $pick_dates_array = array();
        $drop_dates_array = array();
        $pick_time_array  = array();
        $drop_time_array  = array();
        $units            = array();
        $total_amount     = array();
        $status           = array();
        if ( is_array( $transaction_id ) ) {
            $transaction_ids = implode( ' , ', $transaction_id );
        } else {
            $transaction_ids = $transaction_id;
        }


        for ( $i = 0; $i < count( $items ); $i ++ ) {
            if ( ! empty( $items[ $i ][0] ) ) {
                array_push( $table_type_array, $items[ $i ][0] );
            }
            if ( ! empty( $items[ $i ][1] ) ) {
                array_push( $table_ids_array, $items[ $i ][1] );
            }
            if ( ! empty( $items[ $i ][3] ) ) {
                array_push( $items_array, $items[ $i ][3] );
            }
            if ( ! empty( $items[ $i ][5] ) ) {
                if ( $items[ $i ][5] != "N/A" ) {
                    array_push( $pick_dates_array, date( 'Y-m-d', strtotime( $items[ $i ][5] ) ) );
                    array_push( $status, 4 );
                } else {
                    array_push( $pick_dates_array, "N/A" );
                    array_push( $status, 5 );
                }

            }
            if ( ! empty( $items[ $i ][6] ) ) {
                if ( $items[ $i ][6] != "N/A" ) {
                    array_push( $drop_dates_array, date( 'Y-m-d', strtotime( $items[ $i ][6] ) ) );
                } else {
                    array_push( $drop_dates_array, "N/A" );
                }

            }
            if ( ! empty( $items[ $i ][7] ) ) {
                array_push( $pick_time_array, $items[ $i ][7] );
            }
            if ( ! empty( $items[ $i ][8] ) ) {
                array_push( $drop_time_array, $items[ $i ][8] );
            }
            if ( ! empty( $items[ $i ][4] ) ) {
                array_push( $units, $items[ $i ][4] );
            }
            if ( ! empty( $items[ $i ][9] ) ) {
                array_push( $total_amount, number_format( $items[ $i ][9], 2, '.', '' ) );
            }
        }

        $booking = array(
            'order_id'          => $orderid,
            'tool_id'           => implode( " , ", $items_array ),
            'units'             => implode( " , ", $units ),
            'date_from'         => implode( " , ", $pick_dates_array ),
            'user_id'           => $user,
            'guest_id'          => $guest,
            'date_to'           => implode( " , ", $drop_dates_array ),
            'message'           => $message,
            'pick_time'         => implode( " , ", $pick_time_array ),
            'drop_time'         => implode( " , ", $drop_time_array ),
            'total_amount'      => implode( " , ", $total_amount ),
            'payment_status'    => 1,
            'payment_id'        => $transaction_ids,
            'payment_type'      => $payment_type,
            'refundFromTable'   => implode( " , ", $table_type_array ),
            'refundIdFromTable' => implode( " , ", $table_ids_array ),
            'status'            => implode( " , ", $status )
        );

        return $booking;
    }

    public static function array_flatten($array) {
        if (!is_array($array)) {
            return false;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, self::array_flatten($value));
            } else {
                $result = array_merge($result, array($key => $value));
            }
        }
        return $result;
    }

    public static function prepare_cash_response( $orderid, $totalAmount, $type, $message = null ) {
        $date               = new DateTime();
        $transaction_prefix = ( $type === 1) ? 'ch' : 're';
        $response_arr       = array(
            'id'                     => $transaction_prefix . "_" . md5( $date->format( 'Y-m-d H:i:s' ) ),
            'metadata'               => array(
                'order_id' => $orderid,
                'Amount'   => $totalAmount,
                'message'  => $message
            ),
            'created_at'             => $date->format( 'Y-m-d H:i:s' ),
            'payment_method_details' => array(
                'card' => array(
                    'funding' => 'Cash/Card At'
                ),
                'type' => 'Store'
            )
        );

        $transaction = array(
            'response' => json_encode( $response_arr ),
            'status'   => 1,
            'order_id' => $orderid,
            'type'     => $type
        );

        return $transaction;
    }

    private static function create_cash_booking_array( $guest, $user, $orderid, $items, $payment_type, $message ) {
        $items_array      = array();
        $pick_dates_array = array();
        $drop_dates_array = array();
        $pick_time_array  = array();
        $drop_time_array  = array();
        $units            = array();
        $total_amount     = array();
        $status           = array();
        $transaction_id   = app('r7.booking.transaction')->return_inserted_transaction();

        if ( empty( $transaction_id ) ) {
            $transaction_id = "N/A";
        }
        for ( $i = 0; $i < count( $items ); $i ++ ) {
            if ( ! empty( $items[ $i ][0] ) ) {
                array_push( $items_array, $items[ $i ][0] );
                array_push( $status, 0 );
            }
            if ( ! empty( $items[ $i ][2] ) ) {
                if ( $items[ $i ][2] != "N/A" ) {
                    array_push( $pick_dates_array, date( 'Y-m-d', strtotime( $items[ $i ][2] ) ) );
                } else {
                    array_push( $pick_dates_array, "N/A" );
                }

            }
            if ( ! empty( $items[ $i ][3] ) ) {
                if ( $items[ $i ][3] != "N/A" ) {
                    array_push( $drop_dates_array, date( 'Y-m-d', strtotime( $items[ $i ][3] ) ) );
                } else {
                    array_push( $drop_dates_array, "N/A" );
                }

            }
            if ( ! empty( $items[ $i ][4] ) ) {
                array_push( $pick_time_array, $items[ $i ][4] );
            }
            if ( ! empty( $items[ $i ][5] ) ) {
                array_push( $drop_time_array, $items[ $i ][5] );
            }
            if ( ! empty( $items[ $i ][1] ) ) {
                array_push( $units, $items[ $i ][1] );
            }
            if ( ! empty( $items[ $i ][6] ) ) {
                array_push( $total_amount, number_format( $items[ $i ][6], 2, '.', '' ) );
            }
        }

        $booking = array(
            'order_id'       => $orderid,
            'tool_id'        => implode( " , ", $items_array ),
            'units'          => implode( " , ", $units ),
            'date_from'      => implode( " , ", $pick_dates_array ),
            'user_id'        => $user,
            'guest_id'       => $guest,
            'date_to'        => implode( " , ", $drop_dates_array ),
            'message'        => $message,
            'pick_time'      => implode( " , ", $pick_time_array ),
            'drop_time'      => implode( " , ", $drop_time_array ),
            'total_amount'   => implode( " , ", $total_amount ),
            'payment_status' => 1,
            'payment_id'     => $transaction_id,
            'payment_type'   => $payment_type,
            'status'         => implode( " , ", $status )
        );

        return $booking;
    }
    
}
