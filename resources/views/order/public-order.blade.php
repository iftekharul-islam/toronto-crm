@extends('layouts.app')
@section('content')
<div class="user-registration bg-login-box">
    <div class=" max-w-1576 mx-auto">
        <div class="user-registration-logo d-flex align-items-center mgb-48">
            <img src="{{ asset('img/boston-black.png') }}" alt="logo" class="img-fluid">
            <div class="mgl-20">
                <h4 class="fs-34 fw-bold mgb-12">Boston Appraisal Services</h4>
                <a href="/" class="text-light-black mb-0">www.bostonappraisal.com</a>
            </div>
        </div>
        <h4 class="text-light-black fs-20 mb-3 text-600">{{ $order->system_order_no }} require some files</h4>
        @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
        @endif
        <form id="public-order-form" action="{{ route('order.file.upload', request()->route('id') ) }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="user-registration__box bg-white br-8">
                <div class="p-4">
                    <p class="fs-20 text-600 text-light-black mb-3">Upload Order Files </p>
                </div>
                <div class="col-md-4 p-4">
                    <div class="position-relative group">
                        <select class="login-input w-100 h-40" name="file_type">
                            <option value="">Select a type</option>
                            @foreach($order_types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <span class="icon-arrow-down bottom-arrow-icon text-gray"></span>
                    </div>
                </div>
                <input type="hidden" name="public">
                <div class="col-md-4 mb-4 p-4">
                    <input class="form-control" type="file" name="files[]" multiple>
                </div>
                <div class="py-3 bg-platinum d-flex justify-content-end px-3 box-footer">
                    <button type="submit" class="submit button button-primary px-5">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@push('js')
<script type="text/javascript">
    $(function () {
        $("#public-order-form").validate({
            rules: {
                file_type: {
                    required: true,
                    normalizer: function (value) {
                        return value.trim();
                    }
                },
                "files[]": {
                    required: true,
                }
            },
            messages: {
                file_type: {
                    required: "Please select a file type"
                },
                "files[]": {
                    required: "Please select one or more files"
                }
            },
            submitHandler: function (form) {
                $('.submit').attr('disabled','disabled')
                form.submit()
            }
        });
    });
</script>
@endpush
