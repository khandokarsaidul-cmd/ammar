@extends('layouts.back-end.app')
@section('title', translate('order_List'))

@section('content')
    <div class="content container-fluid">
        <div>
            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                <h2 class="h1 mb-0">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/all-orders.png')}}" class="mb-1 mr-1" alt="">
                    <span class="page-header-title">
                        Draft Orders
                    </span>
                   
                </h2>
                <span class="badge badge-soft-dark radius-50 fz-14"></span>
            </div>
           
            <div class="card mt-3">
                <div class="card-body">
                    <div class="px-3 py-4 light-bg">
                       
                    </div>
                    <div class="table-responsive datatable-custom">
                        <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('order_ID')}}</th>
                                    <th class="text-capitalize">Customer Name & Phone</th>
                                    <th class="text-capitalize">Customer Address</th>
                                    <th class="text-capitalize">Products</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach($orders as $key=>$order)
                            
                           

                                <tr >
                                    <td class="">
                                        {{$orders->firstItem()+$key}}
                                    </td>
                                    <td >{{$order->customer_id}}
                                        <br/>
                                        <div>{{date('d M Y',strtotime($order->created_at))}},</div>
                                        <div>{{ date("h:i A",strtotime($order->created_at)) }}</div>
                                    </td>
                                    <td>
                                        {{$order->cname}}
                                        <br/><b>{{$order->phone}}</b>
                                    </td>
                                   
                                    <td>{{$order->caddress}} </td>
                                    <td>
                                        @php
                                        $all=DB::table('carts')->where('customer_id',$order->customer_id)->pluck('product_id')->toArray();
                                        $products=DB::table('products')->whereIn('id',$all)->get();
                                        @endphp
                                        @foreach($products as $key=>$product)
                                        <span>{{ $key+1}} . <a href="{{ route('product',$product->slug) }}" target="_blank">{{$product->name}}</a></br>
                                        </span></br>
                                        
                                         @endforeach
                                    </td>
                                   
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a class="btn btn-outline--danger square-btn btn-sm mr-1" title="Delete"
                                                href="{{ route('admin.orders.draftdelete',[$order->customer_id])}}">
                                                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/delete.png')}}" class="svg" alt="">
                                            </a>
                                           
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="d-flex justify-content-lg-end">
                            {!! $orders->links() !!}
                        </div>
                    </div>
                    @if(count($orders) == 0)
                        @include('layouts.back-end._empty-state',['text'=>'no_order_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
            <div class="js-nav-scroller hs-nav-scroller-horizontal d-none">
                <span class="hs-nav-scroller-arrow-prev d-none">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-left"></i>
                    </a>
                </span>

                <span class="hs-nav-scroller-arrow-next d-none">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-right"></i>
                    </a>
                </span>
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">{{translate('order_list')}}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <span id="message-date-range-text" data-text="{{ translate("invalid_date_range") }}"></span>
    <span id="js-data-example-ajax-url" data-url="{{ route('admin.orders.customers') }}"></span>
@endsection

@push('script_2')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/order.js')}}"></script>
@endpush
