@extends('layouts.back-end.app')

@section('title', 'Color Update')

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0 align-items-center d-flex gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/brand.png') }}" alt="">
                Update Color
            </h2>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-start">
                        <form action="{{ route('admin.color.update') }}" method="post"
                              enctype="multipart/form-data" class="brand-setup-form">
                            @csrf

                          
                            <div class="">
                                <div class="col-md-12">
                                 
                                        <div >
                                          <input type="text" name="name" value="{{$color->name}}" class="form-control" id="name" >
                                        </div>
                                        <div class="mt-3">
                                          <input type="text" name="code" value="{{$color->code}}" class="form-control" id="code" >
                                        </div>
                                        
                                        <input type="hidden" name="cid" value="{{$color->id}}">
                              
                                </div>
                             
                            </div>

                            
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" id="reset"
                                        class="btn btn-secondary px-4">{{ translate('reset') }}</button>
                                <button type="submit" class="btn btn--primary px-4">{{ translate('update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
  
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/products-management.js') }}"></script>
@endpush
