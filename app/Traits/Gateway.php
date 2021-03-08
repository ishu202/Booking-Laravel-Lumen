<?php
declare(strict_types=1);

namespace R7\Booking\Traits;

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

    private $stripe;
    private $error = [];
    private $success = [];
    private $response = [];
    private $order;

    /**
     * Gateway constructor.
     *
     * @param $params
     * @param EditOrder|CreateBooking $order
     */
    public function __construct($params , $order)
    {
        $this->stripe = new StripeClient(config('r7.booking.gateway.stripe_api_secret'));
        $this->order = $order;
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

    public function createInvoiceAndCharge( $customerId, $transaction_type, $orderid, $itemData ): bool
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
            if ( $this->Rntr->insert_transaction_cash( BookingUtiliy::prepare_stripe_response( json_encode( $balanceJson ), $transaction_type, $orderid ) ) ) {
                return $this->transaction->return_inserted_transaction();
            } else {
                return false;
            }
        }
    }

    private function createTaxRateStripe(){
        $this->base_request(function (){
            $states = $this->Rntr->fetch_state();
            $contact = $this->getStoreContactData();
            $tax_State = array_reduce($states , function($memo, $state) use ($contact) {
                preg_match("/{$state->State}/",$contact[0]['con_address'],$matches);

                if (count($matches) > 0 ){
                    array_push($memo,$matches);
                }
                return $memo;
            },[]);
            return [
                'tax_rates' => array_map(function($state) {
                    return $this->stripe->taxRates->create([
                        'display_name' => 'TAX',
                        'description' => "TAX {$state[0]}",
                        'jurisdiction' => $state[0],
                        'percentage' => $this->order->editorder['tax_percentage'][0]['taxpercentage'],
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
                    $tool_name = array_map(function ($item_info) use ($item){
                        preg_match("/{$item_info->id}/", $item[0],$matches);
                        if(count($matches) > 0){
                            array_push($matches, $item_info->t_name);
                        }
                        return $matches;
                    } , $this->order->editorder['tools_rent']);

                    $filtered_tools = BookingUtiliy::array_flatten(array_filter($tool_name, function($val){
                        return count($val) > 0;
                    }));
                    array_push($memo,
                        $this->stripe->invoiceItems->create([
                            "amount"      => $item[6] * 100,
                            "currency"    => "usd",
                            "customer"    => $customerId,
                            "description" => $filtered_tools[1]
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
            if ( $this->Rntr->insert_transaction_cash( BookingUtiliy::prepare_stripe_response( json_encode( $refundJson ), $transaction_type, $orderid ) ) ) {
                return $this->transaction->return_inserted_transaction();
            } else {
                return false;
            }
        }
    }

    public function refund_transaction_generator($refund_data,$refundMessage) {
        return array_reduce($refund_data,function($memo,$refund) use ($refundMessage){

            $refund_amount = BookingUtiliy::amountFormatter($refund[9],$this->order->editorder['tax_percentage'][0]['taxpercentage']);

            $transaction = $this->refundFromStripe(
                $refund[2],
                $refund_amount,
                $refundMessage,
                $this->order->credit,
                $this->order->getOrderId());

            if($transaction){
                array_push($memo,$transaction);
            }

            return $memo;
        },[]);
    }

//	private function productGenerator($itemData) : self{
//
//		$this->base_request(function () use ($itemData){
//
//			return [
//				'products' => array_reduce($this->editorder['tools_rent'],function($memo, $item) use($itemData){
//
//				if(array_search("{$item['id']}",$itemData)){
//
//					array_push($memo,
//						$this->stripe->products->create([
//							'name' => $item['t_name']
//						])
//
//					);
//				}
//				return $memo;
//			},[])
//			];
//		});
//		return $this;
//	}
//
//	private function priceGenerator($products){
//		$this->base_request(function () use ($products ){
//			return [
//				'prices' => array_reduce($products, function($memo, $product) {
//
//					$this->stripe->prices->create([
//						'unit_amount' => 2000,
//						'currency' => 'usd',
//						'recurring' => ['interval' => 'month'],
//						'product' => $product['id']
//					]);
//
//					$product['id']
//
//				return $memo;
//			})
//				];
//			$price = [];
//			$product_ids = array_column($products,'id');
//			for ($i = 0; $i < count($product_ids); $i++){
//				array_push($products['products_array'],[
//					$i => $this->stripe->prices->create([
//						'unit_amount' => 2000,
//						'currency' => 'usd',
//						'recurring' => ['interval' => 'month'],
//						'product' => 'prod_IBhjk7BaWbDFKl',
//					])
//				]);
//			}
//			return $products;
//		});
//	}
}
