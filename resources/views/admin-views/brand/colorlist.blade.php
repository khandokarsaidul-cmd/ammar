@extends('layouts.back-end.app')

@section('title', translate('brand_List'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 d-flex gap-2">
                
                Color List
            </h2>
        </div>
        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="row g-2 flex-grow-1">
                            
                            <div class="col-12">
    <form action="{{route('admin.color.store')}}" method="POST">
         @csrf 
        <div class="p-4 border rounded bg-light shadow-sm"> {{-- Add styling wrapper here --}}
            <div class="row">
                {{-- Search by Name --}}
                <div class="col-md-6 mb-3">
                    <div class="input-group input-group-custom input-group-merge">
                        <input id="searchName" type="search" name="name" class="form-control"
                            placeholder="Write Color Name Here"
                            value="{{ request('name') }}" required>
                    </div>
                </div>

                {{-- Search by Code --}}
                <div class="col-md-6 mb-3">
                    <div class="input-group input-group-custom input-group-merge">
                        <input id="searchCode" type="search" name="code" class="form-control"
                            placeholder="Color Code With #"
                            value="" required>
                        <button type="submit" class="btn btn-info input-group-text">Add New</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

                            
                            <div class="col-12">
    <form action="{{ url()->current() }}" method="GET">
        <div class="row">
            {{-- Search by Name --}}
            <div class="col-md-6 mb-3">
                <div class="input-group input-group-custom input-group-merge">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="tio-search"></i>
                        </div>
                    </div>
                    <input id="searchName" type="search" name="name" class="form-control"
                        placeholder="Search With Color Name"
                        aria-label="{{ translate('search_by_brand_name') }}"
                        value="{{ request('name') }}">
                    <button type="submit" class="btn btn--primary input-group-text">{{ translate('search') }}</button>
                </div>
            </div>

            {{-- Search by Code --}}
            <div class="col-md-6 mb-3">
                <div class="input-group input-group-custom input-group-merge">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="tio-search"></i>
                        </div>
                    </div>
                    <input id="searchCode" type="search" name="code" class="form-control"
                        placeholder="Search With Color Code"
                        aria-label="{{ translate('search_by_brand_name') }}"
                        value="{{ request('code') }}">
                    <button type="submit" class="btn btn--primary input-group-text">{{ translate('search') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

                           
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                                <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{ translate('SL') }}</th>
                                    <th class="max-width-100px">Color Name</th>
                                    <th class="text-center">Color Code</th>
                                    <th class="text-left">Preview</th>
                                    <th class="text-center"> {{ translate('action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($colors as $key => $color)
                                    <tr>
                                        <td> {{$key+1}}</td>
                                       
                                        <td class="overflow-hidden max-width-100px">
                                            
                                                 {{ Str::limit($color->name,20) }}
                                           
                                        </td>
                                        <td class="text-center">{{$color->code}}</td>
                                        <td class="text-center flex items-center">
                                            
                                                <div style="width: 20px; height: 20px; background-color: {{ $color->code }}; border: 1px solid #ccc;"></div>
                                              
                                                </td>
                                        
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                              
                                                <a href="{{route('admin.color.delete',[$color->id])}}" class="btn btn-outline-info btn-sm square-btn"  title="{{ translate('edit') }}">
                                                 <i class="tio-edit"></i>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="d-flex justify-content-lg-end">
                            {{ $colors->links() }}
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="modal fade" id="select-brand-modal" tabindex="-1" aria-labelledby="toggle-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i
                            class="tio-clear"></i></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0 pb-sm-5">
                    <div class="d-flex flex-column align-items-center text-center gap-2 mb-2">
                        <div
                            class="toggle-modal-img-box d-flex flex-column justify-content-center align-items-center mb-3 position-relative">
                            <img src="{{dynamicAsset('public/assets/back-end/img/icons/info.svg')}}" alt="" width="90"/>
                        </div>
                        <h5 class="modal-title mb-2 brand-title-message"></h5>
                    </div>
                    <form action="{{ route('admin.brand.delete') }}" method="post" class="product-brand-update-form-submit">
                        @csrf
                        <input name="id" hidden="">
                        <div class="gap-2 mb-3">
                            <label class="title-color"
                                   for="exampleFormControlSelect1">{{ translate('select_Category') }}
                                <span class="text-danger">*</span>
                            </label>
                            <select name="brand_id" class="form-control js-select2-custom brand-option" required>

                            </select>
                        </div>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn--primary min-w-120">{{translate('update')}}</button>
                            <button type="button" class="btn btn-danger-light min-w-120"
                                    data-dismiss="modal">{{ translate('cancel') }}</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/products-management.js') }}"></script>
    
    <script>
        $('.delete-color').click(function () {
    let id = $(this).data('id');
    if (confirm('Are you sure Want to Delete Color?')) {
        $.ajax({
            url: '/admin/color/delete/' + id,
            type: 'post',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                // optionally reload or remove the item from the DOM
                location.reload();
            },
            error: function (xhr) {
                alert('Delete failed');
            }
        });
    }
});

    </script>
@endpush
