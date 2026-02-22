@extends('layouts.front-end.app')

@section('title', translate('choose_Payment_Method'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/payment.css') }}">
    <script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
    <script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
    <div class="container pb-5 mb-2 mb-md-4 rtl px-0 px-md-3 text-align-direction">
        <div class="row mx-max-md-0">
            <div class="col-md-12 mb-3 pt-3 px-max-md-0">
                <div class="feature_header px-3 px-md-0">
                    <span>{{ translate('payment_method')}}</span>
                </div>
            </div>
            <section class="col-lg-8 px-max-md-0">
                <div class="checkout_details">
                    <div class="px-3 px-md-0">
                        @php $step = 3; @endphp
                        @include('web-views.partials._checkout-steps',['step'=>$step])
                         
                    
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">

                            <div class="gap-2 mb-4">
                                <div class="d-flex justify-content-between">
                                    <h4 class="mb-2 text-nowrap">ক্যাশ অন ডেলিভারি </h4>
                                    <a href="{{route('checkout-details')}}" class="d-flex align-items-center gap-2 text-primary font-weight-bold text-nowrap">
                                        <i class="tio-back-ui fs-12 text-capitalize"></i>
                                        {{ translate('go_back') }}
                                    </a>
                                </div>
                                <p class="text-capitalize mt-2">পন্য হাতে পেয়ে টাকা পরিশোধ করুন</p>
                            </div>
                            @if($cashOnDeliveryBtnShow && $cash_on_delivery['status'] || $digital_payment['status']==1)
                                <div class="d-flex flex-wrap gap-3 mb-5">
                                    @if($cashOnDeliveryBtnShow && $cash_on_delivery['status'])
                                        <div id="cod-for-cart">
                                            <div class="card cursor-pointer">
                                                <form action="{{route('checkout-complete')}}" method="get" class="needs-validation" id="cash_on_delivery_form">
                                                    <label class="m-0">
                                                        <input type="hidden" name="payment_method" value="cash_on_delivery">
                                                        <span class="btn btn-block click-if-alone d-flex gap-2 align-items-center cursor-pointer">
                                                            <input type="radio" id="cash_on_delivery" class="custom-radio" checked="checked">
                                                            <img width="20" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/money.png') }}" alt="">
                                                            <span class="fs-12">{{ translate('cash_on_Delivery') }}aa</span>
                                                        </span>
                                                    </label>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($digital_payment['status']==1)
                                <div class="d-flex flex-wrap gap-2 align-items-center mb-4 ">
                                    <h5 class="mb-0 text-capitalize">{{ translate('pay_via_online') }}</h5>
                                    <span class="fs-10 text-capitalize mt-1">({{ translate('faster_&_secure_way_to_pay') }})</span>
                                </div>

                                <div class="row gx-4 mb-4">
                                    @foreach ($payment_gateways_list as $payment_gateway)
                                        <div class="col-sm-6">
                                            <form method="post" class="digital_payment card" id="{{($payment_gateway->key_name)}}_form" action="{{ route('customer.web-payment-request') }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ auth('customer')->check() ? auth('customer')->user()->id : session('guest_id') }}">
                                                <input type="hidden" name="customer_id" value="{{ auth('customer')->check() ? auth('customer')->user()->id : session('guest_id') }}">
                                                <input type="hidden" name="payment_method" value="{{ $payment_gateway->key_name }}">
                                                <input type="hidden" name="payment_platform" value="web">

                                                @if ($payment_gateway->mode == 'live' && isset($payment_gateway->live_values['callback_url']))
                                                    <input type="hidden" name="callback" value="{{ $payment_gateway->live_values['callback_url'] }}">
                                                @elseif ($payment_gateway->mode == 'test' && isset($payment_gateway->test_values['callback_url']))
                                                    <input type="hidden" name="callback" value="{{ $payment_gateway->test_values['callback_url'] }}">
                                                @else
                                                    <input type="hidden" name="callback" value="">
                                                @endif

                                                <input type="hidden" name="external_redirect_link" value="{{ route('web-payment-success') }}">
                                                <label class="d-flex align-items-center gap-2 mb-0 form-check py-2 cursor-pointer">
                                                    <input type="radio" id="{{($payment_gateway->key_name)}}" name="online_payment" class="form-check-input custom-radio" value="{{($payment_gateway->key_name)}}">
                                                    <img width="30"
                                                         src="{{dynamicStorage(path: 'storage/app/public/payment_modules/gateway_image')}}/{{ $payment_gateway->additional_data && (json_decode($payment_gateway->additional_data)->gateway_image) != null ? (json_decode($payment_gateway->additional_data)->gateway_image) : ''}}" alt="">
                                                    <span class="text-capitalize form-check-label">
                                                    @if($payment_gateway->additional_data && json_decode($payment_gateway->additional_data)->gateway_title != null)
                                                            {{ json_decode($payment_gateway->additional_data)->gateway_title }}
                                                        @else
                                                            {{ str_replace('_', ' ',$payment_gateway->key_name) }}
                                                        @endif

                                                </span>
                                                </label>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>

                              
                            @endif
                        </div>
                    </div>
                </div>
            </section>
            @include('web-views.partials._order-summary')
        </div>
    </div>



    <span id="route-action-checkout-function" data-route="checkout-payment"></span>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/payment.js') }}"></script>
@endpush
