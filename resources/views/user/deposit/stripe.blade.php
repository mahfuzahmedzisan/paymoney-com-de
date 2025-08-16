@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="depositStripe">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Deposit Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Card Information') }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Check your deposit information before confirmation.') }}</p>

    <form id="stripePaymentForm" method="post">
        @csrf

        <!-- Card Number -->
        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="amount">{{ __('Card Number') }}</label>
            <div id="card-number"></div>
            <input type="text" class="form-control input-form-control apply-bg l-s2" name="cardNumber" maxlength="19" id="cardNumber" onkeypress="return isNumber(event)" required data-value-missing="{{ __('This field is required.') }}">
            <div id="card-errors" class="error"></div>
        </div>

        <!-- Card Details -->
        <div class="row mt-20">
            <div class="col-md-4">
                <div class="label-top param-ref">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Month') }}</label>
                    <select class="select2" data-minimum-results-for-search="Infinity" name="month" id="month">
                        @for ($i = 1; $i <= 12; $i++)
                            @php $value = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="label-top">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Year') }}</label>
                    <input type="text" class="form-control input-form-control apply-bg l-s2" name="year" id="year" maxlength="2" onkeypress="return isNumber(event)" required data-value-missing="{{ __('This field is required.') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="label-top">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15" for="usr">{{ __('CVC') }}</label>
                    <input type="text" class="form-control input-form-control apply-bg l-s2" name="cvc" id="cvc" maxlength="4" onkeypress="return isNumber(event)" required data-value-missing="{{ __('This field is required.') }}">
                    <div id="card-cvc"></div>
                </div>
            </div>
        </div>

        <div class="row mt-20">
            <div class="col-12">
                <p class="error" id="stripeError"></p>
            </div>
        </div>

        <!-- Confirm Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="depositStripeSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span class="px-1" id="depositStripeSubmitBtnText">{{ __('Confirm & Deposit') }}</span>
                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>

        <!-- Back Button -->
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.deposit.create') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn" id="depositConfirmBackBtn">
                {!! svgIcons('left_angle') !!}
                <span class="ms-1 back-btn ns depositConfirmBackBtnText">{{ __('Back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection

@push('js')
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}"></script>
    <script type="text/javascript">
        'use strict';
        var token = $('[name="_token"]').val();
        var stripeMakePaymentUrl = "{{ route('user.deposit.stripe.store') }}";
        var stripeConfirmPaymentUrl = "{{ route('user.deposit.stripe.confirm') }}";
        var stripeSuccessUrl = "{{ route('user.deposit.stripe.success') }}";
        var errorMessage = "{{ __('Failed Stripe payment.') }}";
        var confirmBtnText = "{{ __('Confirming...') }}";
        var paymentIntendId = null;
        var paymentMethodId = null;
    </script>
    <script src="{{ asset('public/user/customs/js/deposit.min.js') }}"></script>
@endpush
