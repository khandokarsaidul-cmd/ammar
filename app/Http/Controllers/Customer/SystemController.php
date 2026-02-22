<?php

namespace App\Http\Controllers\Customer;

use App\Services\FacebookConversionService;
use App\Models\User;
use App\Models\Product;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\CartShipping;
use App\Traits\CommonTrait;
use App\Utils\CartManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    use CommonTrait;

    public function setPaymentMethod($name): JsonResponse
    {
        if (auth('customer')->check() || session()->has('mobile_app_payment_customer_id')) {
            session()->put('payment_method', $name);
            return response()->json(['status' => 1]);
        }
        return response()->json(['status' => 0]);
    }

    public function setShippingMethod(Request $request): JsonResponse
    {
        if ($request['cart_group_id'] == 'all_cart_group') {
            foreach (CartManager::get_cart_group_ids() as $groupId) {
                $request['cart_group_id'] = $groupId;
                self::insertIntoCartShipping($request);
            }
        } else {
            self::insertIntoCartShipping($request);
        }
        return response()->json(['status' => 1]);
    }

    public static function insertIntoCartShipping($request): void
    {
        $shipping = CartShipping::where(['cart_group_id' => $request['cart_group_id']])->first();
        if (isset($shipping) == false) {
            $shipping = new CartShipping();
        }
        $shipping['cart_group_id'] = $request['cart_group_id'];
        $shipping['shipping_method_id'] = $request['id'];
        $shipping['shipping_cost'] = ShippingMethod::find($request['id'])->cost;
        $shipping->save();
    }

    /*
     * default theme
     * @return json
     */
    public function getChooseShippingAddress(Request $request): JsonResponse
    {
       
        $physical_product = $request['physical_product'];
        $shipping = [];
        $billing = [];

        parse_str($request['shipping'], $shipping);
        parse_str($request['billing'], $billing);
        $is_guest = !auth('customer')->check();

        if (isset($shipping['save_address']) && $shipping['save_address'] == 'on') {

            if ($shipping['contact_person_name'] == null || $shipping['address'] == null || ($is_guest && $shipping['email'] == null)) {
                return response()->json([
                    'errors' => translate('Fill_all_required_fields_of_shipping_address')
                ], 403);
            }
            elseif (!self::delivery_country_exist_check($shipping['country'])) {
                return response()->json([
                    'errors' => translate('Delivery_unavailable_in_this_country.')
                ], 403);
            }
         

            $address_id = DB::table('shipping_addresses')->insertGetId([
                'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id'):0)),
                'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1:0),
                'contact_person_name' => $shipping['contact_person_name'],
                'address_type' => $shipping['address_type'],
                'address' => $shipping['address'],
                'city' => $shipping['city'],
                // 'zip' => $shipping['zip'],
                'country' => $shipping['country'],
                'phone' => $shipping['phone'],
                'email' => auth('customer')->check() ? null : $shipping['email'],
                'latitude' => $shipping['latitude'],
                'longitude' => $shipping['longitude'],
                'is_billing' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }
        else if (isset($shipping['shipping_method_id']) && $shipping['shipping_method_id'] == 0) {

            if ($shipping['contact_person_name'] == null || $shipping['address'] == null || $shipping['city'] == null || $shipping['country'] == null || ($is_guest && $shipping['email'] == null)) {
                return response()->json([
                    'errors' => translate('Fill_all_required_fields_of_shipping/billing_address')
                ], 403);
            }
            elseif (!self::delivery_country_exist_check($shipping['country'])) {
                return response()->json([
                    'errors' => translate('Delivery_unavailable_in_this_country')
                ], 403);
            }
         

            $address_id = DB::table('shipping_addresses')->insertGetId([
                'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id'):0)),
                'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1:0),
                'contact_person_name' => $shipping['contact_person_name'],
                'address_type' => $shipping['address_type'],
                'address' => $shipping['address'],
                'city' => $shipping['city'],
                'country' => $shipping['country'],
                'phone' => $shipping['phone'],
                'email' => auth('customer')->check() ? null : $shipping['email'],
                'latitude' => $shipping['latitude'],
                'longitude' => $shipping['longitude'],
                'is_billing' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        else {
            if (isset($shipping['shipping_method_id'])) {
                $address = ShippingAddress::find($shipping['shipping_method_id']);
                if (!$address->country) {
                    return response()->json([
                        'errors' => 'Please update country for this shipping address'
                    ], 403);
                }
                elseif (!self::delivery_country_exist_check($address->country)) {
                    return response()->json([
                        'errors' => translate('Delivery_unavailable_in_this_country')
                    ], 403);
                }
              
                $address_id = $shipping['shipping_method_id'];
            }else{
                $address_id =  0;
            }
        }

        if ($request->billing_addresss_same_shipping == 'false') {
            if (isset($billing['save_address_billing']) && $billing['save_address_billing'] == 'on') {

                if ($billing['billing_contact_person_name'] == null || $billing['billing_address'] == null || $billing['billing_city'] == null || $billing['billing_country'] == null || ($is_guest && $billing['billing_contact_email'] == null)) {
                    return response()->json([
                        'errors' => translate('Fill_all_required_fields_of_billing_address')
                    ], 403);
                }
                elseif (!self::delivery_country_exist_check($billing['billing_country'])) {
                    return response()->json([
                        'errors' => translate('Delivery_unavailable_in_this_country')
                    ], 403);
                }
              

                $billing_address_id = DB::table('shipping_addresses')->insertGetId([
                    'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id'):0)),
                    'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1:0),
                    'contact_person_name' => $billing['billing_contact_person_name'],
                    'address_type' => $billing['billing_address_type'],
                    'address' => $billing['billing_address'],
                    'city' => $billing['billing_city'],
                    'country' => $billing['billing_country'],
                    'phone' => $billing['billing_phone'],
                    'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
                    'latitude' => $billing['billing_latitude'],
                    'longitude' => $billing['billing_longitude'],
                    'is_billing' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);


            }
            elseif ($billing['billing_method_id'] == 0) {

                if ($billing['billing_contact_person_name'] == null || $billing['billing_address'] == null || $billing['billing_city'] == null || $billing['billing_country'] == null || ($is_guest && $billing['billing_contact_email'] == null)) {
                    return response()->json([
                        'errors' => translate('Fill_all_required_fields_of_billing_address')
                    ], 403);
                }
                elseif (!self::delivery_country_exist_check($billing['billing_country'])) {
                    return response()->json([
                        'errors' => translate('Delivery_unavailable_in_this_country')
                    ], 403);
                }
            

                $billing_address_id = DB::table('shipping_addresses')->insertGetId([
                    'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id'):0)),
                    'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1:0),
                    'contact_person_name' => $billing['billing_contact_person_name'],
                    'address_type' => $billing['billing_address_type'],
                    'address' => $billing['billing_address'],
                    'city' => $billing['billing_city'],
                    'country' => $billing['billing_country'],
                    'phone' => $billing['billing_phone'],
                    'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
                    'latitude' => $billing['billing_latitude'],
                    'longitude' => $billing['billing_longitude'],
                    'is_billing' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            else {
                $address = ShippingAddress::find($billing['billing_method_id']);
                if ($physical_product == 'yes') {
                    if (!$address->country) {
                        return response()->json([
                            'errors' => translate('Update country for this billing address')
                        ], 403);
                    }
                    elseif (!self::delivery_country_exist_check($address->country)) {
                        return response()->json([
                            'errors' => translate('Delivery_unavailable_in_this_country')
                        ], 403);
                    }
                
                }
                $billing_address_id = $billing['billing_method_id'];
            }
        }
        else {
            $billing_address_id = $address_id;
        }

        session()->put('address_id', $address_id);
        session()->put('billing_address_id', $billing_address_id);

        return response()->json([], 200);
    }

    /*
     * Except Default Theme
     * @return json
     */
    public function getChooseShippingAddressOther(Request $request): JsonResponse
    {
        $shipping = [];
        $billing = [];
        parse_str($request['shipping'], $shipping);
        parse_str($request['billing'], $billing);
        
        if (isset($shipping['phone'])) {
            $shippingPhoneValue = preg_replace('/[^0-9]/', '', $shipping['phone']);
            $shippingPhoneLength = strlen($shippingPhoneValue);
            if ($shippingPhoneLength < 11) {
                return response()->json([
                    'errors' => 'The phone number must be at least 11 characters'
                ], 403);
            }
           
            if (strlen($shipping['phone']) > 11) {
      $mdig = substr($shipping['phone'], -11);
      $shipping['phone'] = '+88'.$mdig;
      }else{
      $shipping['phone'] = '+88'.$shipping['phone'];
      }
           
        }

        if ($request['billing_addresss_same_shipping'] == 'false' && isset($billing['billing_phone'])) {
            $billingPhoneValue = preg_replace('/[^0-9]/', '', $billing['billing_phone']);
            $billingPhoneLength = strlen($billingPhoneValue);
            if ($billingPhoneLength < 11) {
                return response()->json([
                    'errors' => translate('The phone number must be at least 11 characters')
                ], 403);
            }

            if ($billingPhoneLength > 20) {
                return response()->json([
                    'errors' => translate('The_phone_number_may_not_be_greater_than_20_characters')
                ], 403);
            }
            $billing['billing_phone'] = '+88'.$billing['billing_phone'];
        }
        
        $cart = \App\Utils\CartManager::get_cart(type: 'checked');
        if($cart->count() > 0){
        $subTotal = 0;
        $totalTax = 0;
        $totalDiscountOnProduct = 0;
        $coupon_dis = 0;
        $orderWiseShippingDiscount = 0;
        $getShippingCost=\App\Utils\CartManager::get_shipping_cost(type: 'checked');
        $getShippingCostSavedForFreeDelivery=\App\Utils\CartManager::get_shipping_cost_saved_for_free_delivery(type: 'checked');
            foreach($cart as $key => $cartItem){
                $product_ids[] = $cartItem->product_id;
                $fbProduct = Product::find($cartItem->product_id);
                $product_name[] = $fbProduct?->name;
                $subTotal+=$cartItem['price']*$cartItem['quantity'];
                $totalTax+=$cartItem['tax_model']=='exclude' ? ($cartItem['tax']*$cartItem['quantity']):0;
                $totalDiscountOnProduct+=$cartItem['discount']*$cartItem['quantity'];
            }
            if(session()->missing('coupon_type') || session('coupon_type') !='free_delivery'){
                $totalShippingCost=$getShippingCost - $getShippingCostSavedForFreeDelivery;
            }else{
                $totalShippingCost=$getShippingCost;
            }
$total = $subTotal+$totalTax+$totalShippingCost-$coupon_dis-$totalDiscountOnProduct-$orderWiseShippingDiscount;
$facebookService = new FacebookConversionService();
    $userData = [
        'em' => hash('sha256', $shipping['contact_person_name']),
        'ph' => hash('sha256', $shipping['phone']),
        'fn' =>hash('sha256', 'Guest First Name'),
                    'ln' =>hash('sha256', 'Guest Last Name'),
                        'ct' => hash('sha256', 'Dhaka'),
                        'cn' => hash('sha256', 'Bangladesh'),
                        'zp' => hash('sha256', 1212),
                        'ge' => hash('sha256', 'M'),
                         'db' => hash('sha256', '200-01-15')
    ];

    $customData = [
        'content_ids' => $product_ids,
        'content_type' => 'product',
        'value' => $total,
        'currency' => 'BDT',
    ];
    $customData = [
                'content_ids' => $product_ids, 
                'content_name' => $product_name, 
                'content_type' => 'product', 
                'value' => $total,
                'currency' => 'BDT', 
                'num_items' => count($product_ids), 
                'payment_method' => 'N/A', 
                'shipping' => $totalShippingCost,
                'tax' => 0 
            ];

    $response = $facebookService->sendEvent('InitiateCheckout', $userData, $customData);
    if ($response['events_received'] ?? 0 > 0) {
                \Log::info('Facebook Conversion API: InitiateCheckout Event successfully sent.', $response);
            } else {
                \Log::error('Facebook Conversion API: InitiateCheckout Failed to send event.', $response);
            }
        }

        $physicalProduct = $request['physical_product'];
        $zipRestrictStatus = getWebConfig(name: 'delivery_zip_code_area_restriction');
        $countryRestrictStatus = getWebConfig(name: 'delivery_country_restriction');
        $billingInputByCustomer = getWebConfig(name: 'billing_input_by_customer');
        $isGuestCustomer = !auth('customer')->check();

        // Shipping start
        $addressId = $shipping['shipping_method_id'] ?? 0;

        if (isset($shipping['shipping_method_id'])) {
            if ($shipping['contact_person_name'] == null || $shipping['address'] == null || $shipping['phone'] == null || ($isGuestCustomer && $shipping['email'] == null)) {
                return response()->json([
                    'errors' => translate('Fill_all_required_fields_of_shipping_address')
                ], 403);
            } elseif (!self::delivery_country_exist_check($shipping['country'])) {
                return response()->json([
                    'errors' => translate('Delivery_unavailable_in_this_country.')
                ], 403);
            }
          
        }

        if (isset($shipping['save_address']) && $shipping['save_address'] == 'on') {
            $dis = DB::table('districts')->where('id', $shipping['district'])->value('name');
            $than = DB::table('thanas')->where('id', $shipping['thana'])->first();
        $faddress = $shipping['address'] . ', ' . $than->tname . ', ' . $dis;
            
            $addressId = ShippingAddress::insertGetId([
                'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
                'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                'contact_person_name' => $shipping['contact_person_name'],
                'address_type' => $shipping['address_type'],
                'address' => $faddress,
                'city' => $shipping['city'],
                'country' => $shipping['country'],
                'phone' => $shipping['phone'],
                'latitude' => $shipping['latitude'],
                'longitude' => $shipping['longitude'],
                'district' => $shipping['district'],
                'area' => $than->id,
                'email' => auth('customer')->check() ? null : $shipping['email'],
                'is_billing' => 0,
            ]);

        } elseif (isset($shipping['update_address']) && $shipping['update_address'] == 'on') {
            $dis = DB::table('districts')->where('id', $shipping['district'])->value('name');
            $than = DB::table('thanas')->where('id', $shipping['thana'])->first();
        $faddress = $shipping['address'] . ', ' . $than->tname . ', ' . $dis;
            
            $getShipping = ShippingAddress::find($addressId);
            $getShipping->contact_person_name = $shipping['contact_person_name'];
            $getShipping->address_type = $shipping['address_type'];
            $getShipping->address = $faddress;
            $getShipping->city = $shipping['city'];
            $getShipping->country = $shipping['country'];
            $getShipping->phone = $shipping['phone'];
            $getShipping->latitude = $shipping['latitude'];
            $getShipping->longitude = $shipping['longitude'];
            $getShipping->district = $shipping['district'];
            $getShipping->area = $than->id;
            $getShipping->save();

        } elseif (isset($shipping['shipping_method_id']) && !isset($shipping['update_address']) && !isset($shipping['save_address'])) {
        
        if ($isGuestCustomer) {
            $faddress = $shipping['address'];
        } else {
    
        $dis = DB::table('districts')->where('id', $shipping['district'])->value('name');
        $faddress = $shipping['address'] . ', ' . $shipping['thana'] . ', ' . $dis;
        }
        
      
        
            $addressId = ShippingAddress::insertGetId([
                'customer_id' => auth('customer')->check() ? 0 : ((session()->has('guest_id') ? session('guest_id') : 0)),
                'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                'contact_person_name' => $shipping['contact_person_name'],
                'address_type' => $shipping['address_type'],
                'address' => $faddress,
                'city' => $shipping['city'],
                'country' => $shipping['country'],
                'phone' => $shipping['phone'],
                'email' => auth('customer')->check() ? null : $shipping['email'],
                'latitude' => $shipping['latitude'] ?? '',
                'longitude' => $shipping['longitude'] ?? '',
                'is_billing' => 0,
            ]);
        }
        // Shipping End

        // Billing Start
        $billingAddressId = $addressId ?? 0;
        if ($request['billing_addresss_same_shipping'] == 'false' && isset($billing['billing_method_id']) && $billingInputByCustomer) {
            $billingAddressId = $billing['billing_method_id'];


            if ($billing['billing_contact_person_name'] == null || !isset($billing['billing_address']) || $billing['billing_address'] == null || $billing['billing_phone'] == null || ($isGuestCustomer && $billing['billing_contact_email'] == null)) {
                return response()->json([
                    'errors' => translate('Fill_all_required_fields_of_billing_address')
                ], 403);
            } elseif (!self::delivery_country_exist_check($billing['billing_country'])) {
                return response()->json([
                    'errors' => translate('Delivery_unavailable_in_this_country')
                ], 403);
            }
         

            if (isset($billing['save_address_billing']) && $billing['save_address_billing'] == 'on') {
                
                $billingAddressId = ShippingAddress::insertGetId([
                    'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
                    'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                    'contact_person_name' => $billing['billing_contact_person_name'],
                    'address_type' => $billing['billing_address_type'],
                    'address' => $billing['billing_address'],
                    'city' => $billing['billing_city'],
                    'country' => $billing['billing_country'],
                    'phone' => $billing['billing_phone'],
                    'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
                    'latitude' => $billing['billing_latitude'] ?? '',
                    'longitude' => $billing['billing_longitude'] ?? '',
                    'is_billing' => 1,
                ]);
            } elseif (isset($billing['update_billing_address']) && $billing['update_billing_address'] == 'on') {
                $getBilling = ShippingAddress::find($billingAddressId);
                $getBilling->contact_person_name = $billing['billing_contact_person_name'];
                $getBilling->address_type = $billing['billing_address_type'];
                $getBilling->address = $billing['billing_address'];
                $getBilling->city = $billing['billing_city'];
                $getBilling->country = $billing['billing_country'];
                $getBilling->phone = $billing['billing_phone'];
                $getBilling->latitude = $billing['billing_latitude'];
                $getBilling->longitude = $billing['billing_longitude'];
                $getBilling->save();
            } elseif (!isset($billing['update_billing_address']) && !isset($billing['save_address_billing'])) {
                $billingAddressId = ShippingAddress::insertGetId([
                    'customer_id' => auth('customer')->check() ? 0 : ((session()->has('guest_id') ? session('guest_id') : 0)),
                    'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                    'contact_person_name' => $billing['billing_contact_person_name'],
                    'address_type' => $billing['billing_address_type'],
                    'address' => $billing['billing_address'],
                    'city' => $billing['billing_city'],
                    'country' => $billing['billing_country'],
                    'phone' => $billing['billing_phone'],
                    'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
                    'latitude' => $billing['billing_latitude'] ?? '',
                    'longitude' => $billing['billing_longitude'] ?? '',
                    'is_billing' => 1,
                ]);
            }
        } elseif ($request['billing_addresss_same_shipping'] == 'false' && !isset($billing['billing_method_id']) && $physicalProduct != 'yes') {
            return response()->json([
                'errors' => translate('Fill_all_required_fields_of_billing_address')
            ], 403);
        }

        session()->put('address_id', $addressId);
        session()->put('billing_address_id', $billingAddressId);

        if ($request['is_check_create_account'] && $isGuestCustomer) {
            if (empty($request['customer_password']) || empty($request['customer_confirm_password'])) {
                return response()->json([
                    'errors' => translate('The_password_or_confirm_password_can_not_be_empty')
                ], 403);
            }
            if ($request['customer_password'] != $request['customer_confirm_password']) {
                return response()->json([
                    'errors' => translate('The_password_and_confirm_password_must_match')
                ], 403);
            }
            if (strlen($request['customer_password']) < 7 || strlen($request['customer_confirm_password']) < 7) {
                return response()->json([
                    'errors' => translate('The_password_must_be_at_least_8_characters')
                ], 403);
            }
            if ($request['shipping']) {
                $newCustomerAddress = [
                    'name' => $shipping['contact_person_name'],
                    'email' => $shipping['email'],
                    'phone' => $shipping['phone'],
                    'password' => $request['customer_password'],
                ];
            } else {
                $newCustomerAddress = [
                    'name' => $billing['billing_contact_person_name'],
                    'email' => $billing['billing_contact_email'],
                    'phone' => $billing['billing_phone'],
                    'password' => $request['customer_password'],
                ];
            }

            if (User::where(['email' => $newCustomerAddress['email']])->orWhere(['phone' => $newCustomerAddress['phone']])->first()) {
                return response()->json(['errors' => translate('Already_registered')], 403);
            }else{
                $newCustomerRegister = self::getRegisterNewCustomer(request: $request, address: $newCustomerAddress);
                session()->put('newCustomerRegister', $newCustomerRegister);
            }
        } else {
            session()->forget('newCustomerRegister');
            session()->forget('newRegisterCustomerInfo');
        }

        return response()->json([], 200);
    }

    function getRegisterNewCustomer($request, $address): array
    {
        return [
            'name' => $address['name'],
            'f_name' => $address['name'],
            'l_name' => '',
            'email' => $address['email'],
            'phone' => $address['phone'],
            'is_active' => 1,
            'password' => $address['password'],
            'referral_code' => Helpers::generate_referer_code(),
            'shipping_id' => session('address_id'),
            'billing_id' => session('billing_address_id'),
        ];
    }

}
