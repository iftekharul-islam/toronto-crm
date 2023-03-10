@extends('layouts.app')

@section('content')
    <div class="clients bg-platinum dashboard-space">
        <div class="clients-box user-box clients-box-main bg-white">
            <div class="clients-top d-flex flex-wrap justify-content-between">
                <p class="mb-0 text-light-black fs-20 text-600">Loan Types</p>
                <a href="{{ route('loan-types.create') }}" class="button button-primary">Add loan type</a>
            </div>
            <div class="clients-table user-table appraiser-type-table mt-4">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">Loan Type</th>
                        <th scope="col">Is FHA</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($loan_types ?? [] as $loan_type)
                        <tr>
                            <td>{{ $loan_type->name }}</td>
                            <td>{{ $loan_type->is_fha ? 'Yes' : 'No' }}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-end">
                                    @if($is_owner || in_array('update.loantype', $user_permissions))
                                        <a href="{{ route('loan-types.edit', $loan_type->id) }}" class="me-3 text-light-black cursor-pointer action-icon">
                                        <span class="icon-edit"><span class="path1"></span><span
                                                    class="path2"></span></span></a>
                                    @endif
                                    @if($is_owner || in_array('delete.loantype', $user_permissions))
                                        <a class="cursor-pointer text-light-black action-icon" data-id="{{ $loan_type->id }}"
                                           data-action="{{ route('loan-types.destroy',$loan_type->id) }}"
                                           onclick="deleteConfirmation({{$loan_type->id}})"> <span
                                                    class="icon-trash"><span class="path1"></span><span
                                                        class="path2"></span><span class="path3"></span><span
                                                        class="path4"></span></span></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if ($loan_types->hasPages())
                <div class="pagination-wrapper">
                    {{ $loan_types->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection

@section('js')
    <script>
        /**
         * User delete id.
         *
         * @param id
         */
        function deleteConfirmation(id) {
            let url = '{{ route("loan-types.destroy", ":id") }}';
            url = url.replace(':id', id);
            swal({
                title: "Delete?",
                text: "Please ensure and then confirm!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                reverseButtons: !0
            }).then(function (e) {

                if (e.value === true) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: {_token: CSRF_TOKEN},
                        dataType: 'JSON',
                        success: function (results) {

                            if (results.success === true) {
                                swal("Done!", results.message, "success").then(function () {
                                        location.reload();
                                    }
                                );
                            } else {
                                swal("Error!", results.message, "error").then(function () {
                                        location.reload();
                                    }
                                );
                            }
                        },
                        error: function (results) {
                            swal("Error!", results.responseJSON.message, "error").then(function () {
                                    location.reload();
                                }
                            );
                        },
                    });

                } else {
                    e.dismiss;
                }

            }, function (dismiss) {
                return false;
            })
        }
    </script>
@endsection
