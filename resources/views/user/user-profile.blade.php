@extends('layouts.app')

@section('content')
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="order-add bg-platinum dashboard-space">
            <div class="order-add-box bg-white">
                <div class="order-add-box__form">
                    <div class="row mgb-32">
                        <div class="col-md-8 ">
                            <div class="form-box">
                                <h4 class="box-header mb-3">{{ __('messages.profile_view.my_profile') }}</h4>

                                <div class="d-flex justify-content-between w-100">
                                    <div class="left max-w-424 w-100 me-3">
                                        <div class="group">
                                            <label for="companyName"
                                                   class="d-block mb-2 dashboard-label"> {{ __('messages.profile_view.company_name') }}
                                                <span class="text-danger require"></span></label>
                                            <input type="text"
                                                   id="companyName"
                                                   class="dashboard-input w-100 @error('company_name') is-invalid @enderror"
                                                   name="company_name"
                                                   value="{{ $company->name ?? '' }}"
                                                   required
                                                   @if($company->owner_id != $user->id) readonly @endif
                                                   autocomplete="company_name"
                                                   autofocus>
                                            @error('company_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="group">
                                            <label for="userName"
                                                   class="d-block mb-2 dashboard-label">{{ __('messages.profile_view.name') }}</label>
                                            <input type="text"
                                                   id="userName"
                                                   class="dashboard-input w-100 @error('user_name') is-invalid @enderror"
                                                   name="user_name"
                                                   value="{{ $user->name ?? '' }}">
                                            @error('user_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="group">
                                            <label for="address"
                                                   class="d-block mb-2 dashboard-label">{{ __('messages.profile_view.address') }}</label>
                                            <input type="text"
                                                   id="address"
                                                   class="dashboard-input w-100 @error('address') is-invalid @enderror" name="address"
                                                   value="{{ $profile->address ?? '' }}">
                                            @error('address')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="group">
                                            <label for="city"
                                                   class="d-block mb-2 dashboard-label">{{ __('messages.profile_view.city') }}</label>
                                            <input type="text"
                                                   id="city"
                                                   class="dashboard-input w-100 @error('city') is-invalid @enderror"
                                                   name="city"
                                                   value="{{ $profile->city ?? '' }}">
                                            @error('city')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="group">
                                            <label for="state"
                                                   class="d-block mb-2 dashboard-label">{{ __('messages.profile_view.state') }}</label>
                                            <input type="text"
                                                   id="state"
                                                   class="dashboard-input w-100 @error('city') is-invalid @enderror"
                                                   name="state"
                                                   value="{{ $profile->state ?? '' }}">
                                            @error('state')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="right max-w-424 w-100">
                                        <div class="group">
                                            <label for="zipCode"
                                                   class="d-block mb-2 dashboard-label">{{ __('messages.profile_view.zip') }}</label>
                                            <input type="text"
                                                   id="zipCode"
                                                   class="dashboard-input w-100 @error('zip_code') is-invalid @enderror"
                                                   name="zip_code"
                                                   value="{{ $profile->zip_code ?? '' }}">
                                            @error('zip_code')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="group">
                                            <label for="phone"
                                                   class="d-block mb-2 dashboard-label">{{ __('messages.profile_view.phone') }}</label>
                                            <input type="text"
                                                   id="phone"
                                                   class="dashboard-input w-100 @error('phone') is-invalid @enderror" name="phone"
                                                   value="{{ $profile->phone ?? '' }}">
                                            @error('phone')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="group">
                                            <label for=""
                                                   class="d-block mb-2 dashboard-label">{{ __('messages.profile_view.email') }}</label>
                                            <input type="text" class="dashboard-input w-100" name="email"
                                                   value="{{ $user->email }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="add-client__bottom d-flex justify-content-end  p-3">
                    <button type="submit" class="button button-primary"> {{ __('messages.save') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
