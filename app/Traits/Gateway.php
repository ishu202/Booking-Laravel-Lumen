<?php
declare(strict_types=1);

namespace R7\Booking\Traits;

use Exception;
use phpDocumentor\Reflection\Types\This;
use R7\Booking\Models\Tbltool;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\StripeClient;

trait Gateway
{
    use BookingUtility;
    use EditBooking;

    private $stripe;
    private $error = [];
    private $success = [];
    private $response = [];


    public function __construct()
    {
        $this->stripe = new StripeClient(config('r7.booking.gateway.stripe_api_secret'));
    }

    /**
     * @return array
     */
    public function get_response(): array {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function set_response( array $response ) {
        array_push($this->response,$response);
    }

    /**
     * @return mixed
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function set_error( $error ) {
        array_push($this->error,$error);
    }

    /**
     * @return mixed
     */
    public function get_success() {
        return $this->success;
    }

    /**
     * @param mixed $success
     */
    public function set_success( $success ) {
        array_push($this->success,$success);
    }

    /**
     * @param $function
     *
     * @return array|mixed
     */
    private function base_request($function){

        try {
            $this->set_response($function());
            $this->set_success(["success"]);
        } catch( CardException $e) {
            $this->set_error([
                $e->getHttpStatus() ,
                $e->getError()->type ,
                $e->getError()->code ,
                $e->getError()->param ,
                $e->getError()->message
            ]);
        } catch ( RateLimitException $e) {
            $this->set_error(
                $e->getMessage()
            );
        } catch ( InvalidRequestException $e) {
            $this->set_error(
                $e->getMessage()
            );
        } catch ( AuthenticationException $e) {
            $this->set_error(
                $e->getMessage()
            );
        } catch ( ApiConnectionException $e) {
            $this->set_error(
                $e->getMessage()
            );
        } catch ( ApiErrorException $e) {
            $this->set_error(
                $e->getMessage()
            );
        } catch (Exception $e) {
            $this->set_error(
                $e->getMessage()
            );
        }

        if (count($this->error)){
            return $this->get_error();
        }else{
            return $this->get_success();
        }

    }

    /**
     * @param $customerId
     * @param $amount
     */
    public function CreateCustomerBalance( $customerId, $amount ) :self
    {
        $this->base_request(function() use ($customerId,$amount) {
            return ['customer_response' => $this->stripe->customers->update(
                $customerId,
                array(
                    'balance' => $amount * 100
                )
            )];
        });
        return $this;
    }

    public function createInvoiceAndCharge( $customerId, $transaction_type, $orderid, $itemData )
    {
        $this->createTaxRateStripe();
        $this->itemGenerator($itemData,$customerId);

        $this->base_request(function () use ($customerId) {
            return [
                'invoice' => $this->stripe->invoices->create([
                    'customer' => $customerId,
                    'default_tax_rates' => $this->response[0]['tax_rates']
                ])->pay()
            ];
        });

        if(count($this->get_error()) > 0){
            return false;
        }else{
            $this->makeTaxRatesInactiveStripe();
            $balanceJson = $this->response[2]['invoice']->jsonSerialize();
            if ( app('r7.booking.transaction')->insert_transaction_cash( self::prepare_stripe_response( json_encode( $balanceJson ), $transaction_type, $orderid ) ) ) {
                return app('r7.booking.transaction')->return_inserted_transaction();
            } else {
                return false;
            }
        }
    }

    private function createTaxRateStripe(){
        $this->base_request(function (){
            $states = app('r7.booking.tblusers')->fetch_state();
            $contact = app('r7.booking.tblusers')->display_contact_info();

            $tax_State = [];

            foreach ($states as $state){
                preg_match("/{$state->State}/",$contact[0]->con_address,$matches);
                if (count($matches) > 0 ){
                    array_push($tax_State,$matches);
                }
            }

            return [
                'tax_rates' => array_map(function($state) {
                    return $this->stripe->taxRates->create([
                        'display_name' => 'TAX',
                        'description' => "TAX {$state[0]}",
                        'jurisdiction' => $state[0],
                        'percentage' => app('r7.booking.tbltaxrate')->get_tax_rate()[0]->taxpercentage,
                        'inclusive' => false,
                    ]);
                },$tax_State)
            ];
        });
    }

    private function makeTaxRatesInactiveStripe(){
        $this->base_request(function (){
            return [
                'inactive_tax_rates' => array_map(function($tax) {
                    return $this->stripe->taxRates->update(
                        $tax->id,
                        ['active' => false]
                    );
                },$this->response[0]['tax_rates'])
            ];
        });
    }

    private function itemGenerator($itemData,$customerId) {

        $this->base_request(function () use ($itemData,$customerId ) {
            return [
                'line_item' => array_reduce($itemData,function($memo,$item) use ($customerId){
                    $tool_name = Tbltool::find($item[0])->t_name;

                    array_push($memo,
                        $this->stripe->invoiceItems->create([
                            "amount"      => $item[6] * 100,
                            "currency"    => "usd",
                            "customer"    => $customerId,
                            "description" => $tool_name
                        ])
                    );
                    return $memo;
                },[])

            ];
        });
    }


    public function refundFromStripe( $chargeId, $amount, $message, $transaction_type, $orderid ) {

        $this->base_request(function () use ($chargeId, $amount,$message,$orderid){
            return [
                'refund' => $this->stripe->refunds->create([
                    'charge' => $chargeId,
                    'amount' => $amount * 100,
                    'metadata' => [
                        'message' => $message,
                        'order_id' => $orderid,
                        'amount' => $amount
                    ]
                ])
            ];
        });

        if(count($this->get_error()) > 0){
            return false;
        }else{
            $refundJson = $this->response[0]['refund']->jsonSerialize();
            if ( app('r7.booking.transaction')->insert_transaction_cash( self::prepare_stripe_response( json_encode( $refundJson ), $transaction_type, $orderid ) ) ) {
                return app('r7.booking.transaction')->return_inserted_transaction();
            } else {
                return false;
            }
        }
    }

    public function refund_transaction_generator($order_id,$tax_percentage,$refund_data,$refundMessage) {
        return array_reduce($refund_data,function($memo,$refund) use ($refundMessage,$tax_percentage, $order_id){

            $refund_amount = self::amountFormatter($refund[9],$tax_percentage);

            $transaction = $this->refundFromStripe(
                $refund[2],
                $refund_amount,
                $refundMessage,
                $this->credit,
                $order_id);

            if($transaction){
                array_push($memo,$transaction);
            }

            return $memo;
        },[]);
    }

    public function create_stripe_user($user_info)
    {
        $this->base_request(function () use ($user_info){
            return [
                "user" => $this->stripe->customers->create([
                "address"=> array(
                    "line1" => $user_info['add1'],
                    "line2" => $user_info['add2'],
                    "city" => $user_info['city'],
                    "postal_code" => $user_info['zip'],
                    "state" => $user_info['state']
                ),
                "description"=> (array_key_exists('type_id', $user_info))? "Online User Booking" : "Online Guest Booking",
                "email"=> $user_info['email'],
                "metadata"=> [
                    'First Name' => $user_info['f_name'],
                    'Last Name' => $user_info['l_name'],
                    'Email' => $user_info['email'],
                    'Phone' => $user_info['phone'],
                    'Address Line 1' => $user_info['add1'],
                    'Address Line 2' => $user_info['add2'],
                    'Country' => $user_info['country'],
                    'State' => $user_info['state'],
                    'Zip' => $user_info['zip']
                ],
                "name"=> $user_info['f_name']." ".$user_info['l_name'],
                "phone"=> $user_info['phone']
            ])
            ];
        });
    }

//    public function attach_source_card_user()
//    {
//        $this->base_request(function (){
//            return [
//                'user_payment_source' => $this->stripe->paymentMethods->attach(
//                    "payment_method",
//                    ['customer' => $this->response['user']['id']]
//                )
//            ];
//        });
//    }
//
//    public function create_payment_method($card)
//    {
//        $this->base_request(function () use ($card){
//            return [
//                'payment_method' => $this->stripe->paymentMethods->create([
//                'type' => 'card',
//                    'card' => [
//                        'number' => $card->number,
//                        'exp_month' => $card->exp_month,
//                        'exp_year' => $card->exp_year,
//                        'cvc' => $card->cvv,
//                    ]
//                ])
//            ];
//        });
//    }

    public function create_default_source_card_user($customer_token,$stripe_token)
    {
        $this->base_request(function () use ($customer_token,$stripe_token){
            return [
                'default_payment' => $this->stripe->customers->createSource(
                    $customer_token,
                    ['source' => $stripe_token]
                )
            ];
        });
    }

    public function check_dublicate_payment_method($customer_token,$cardid,$last4_card)
    {
        $this->base_request(function () use ($customer_token,$cardid,$last4_card){
            $card_source = $this->stripe->customers->retrieveSource(
                $customer_token,
                $cardid
            );


            $customer_info = $card_source->jsonSerialize();
            if($customer_info['last4'] != NULL){
                if($customer_info['last4'] == $last4_card){
                    return ['dublicate' => true];
                }
            }

            return ['dublicate' => false];
        });
    }


}
