@extends('merchantPayment.layouts.app')

@section('content')
<div class="section-payment">
    <div class="transaction-details-module">
        <div class="total-amount"> 
            <h2>{{ __('Transaction Details') }}</h2>
            <div class="d-flex justify-content-between mb-10">
                <p>{{ __('You are sending') }}</p>
                <p>{{ __('Medium') }}</p>
            </div>
            <div class="d-flex justify-content-between">
                <div>
                    <h3>
                        @php
                            $totalAmount = isset($totalAmount) ? $totalAmount : 0;
                        @endphp
                        {{ moneyFormat(optional($merchant->currency)->code, formatNumber($totalAmount, optional($merchant->currency)->id)) }}
                        <br>
                        
                    </h3>
                    @if ($feeBearer == 'User')
                        <p><small>{{ __('Transaction charge : :x', ['x' => moneyFormat(optional($merchant->currency)->code, formatNumber($totalFee, optional($merchant->currency)->id))]) }}</small></p>
                    @endif
                </div>
                <div class="amount-logo">
                    <img  src="{{ asset('public/dist/images/gateways/payments/stripe.png') }}" alt="logo" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="amount-desc"><p>{{ __('The recipient passed a special verification and confirmed his trustworthiness.') }}</p></div>
        <form action="" method="post" id="stripePaymentForm">
            @csrf
            <input name="order_no" value="{{ isset($paymentInfo['order_no']) ? $paymentInfo['order_no'] : '' }}" type="hidden">
            <input name="item_name" value="{{ isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : '' }}" type="hidden">
            <input name="merchant" value="{{ isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : '' }}" type="hidden" id="merchant">
            <input name="merchant_uuid" value="{{ isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : '' }}" type="hidden">
            <input name="currency" value="{{ $merchant?->currency?->code}}" type="hidden" id="currency">
            <input name="amount" class="form-control" value="{{ isset($paymentInfo['amount']) ? $paymentInfo['amount'] : '' }}" type="hidden">
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-3 xs-mb">
                        <label class="form-label" for="cardNumber">{{ __('Card Number') }}</label>
                        <div id="card-number"></div>
                        <input type="text" class="form-control input-form-control" name="cardNumber" maxlength="19" id="cardNumber" onkeypress="return isNumber(event)" placeholder="4242 - 4242 - 4242 - 4242" required data-value-missing="{{ __('This field is required.') }}">
                        <div id="card-errors" class="error"></div>
                    </div>
                </div> 
            </div>
            <div class="row sm-gx-10">
                <div class="col-md-4 col-6">
                    <div class="select2-extends">
                    <label for="month">{{ __('Month') }}</label>
                    <select class="select2 sl_common_bx" data-minimum-results-for-search="Infinity" name="month" id="month" data-value-missing="{{ __('This field is required.') }}">
                        {!! getStripeMonths() !!}
                    </select>
                    </div> 
                </div>
                <div class="col-md-4 col-6">
                    <div class="form-group mb-3 xs-mb custom-input">
                    <label class="form-label" for="year">{{ __('Year') }}</label>
                    <input type="text" class="form-control input-form-control" name="year" id="year" maxlength="2" onkeypress="return isNumber(event)" required data-value-missing="{{ __('This field is required.') }}">
                    </div>
                </div>
                <div class="col-md-4  col-12">
                    <div class="form-group mb-3 mt-3p xs-mb-btn custom-input">
                        <label class="form-label d-flex justify-content-between align-items-center" for="cvc"><span>{{ __('CVC') }}</span>
                            <a class="pointer" data-bs-toggle="tooltip" data-bs-placement="left" title="{{ __('CVC is the final three digits of the number printed on the signature strip on the back of your debit or credit card.') }}">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8" cy="8" r="8" fill="#C6C5D7"></circle>
                                <path d="M8.12335 5.11196C7.78479 5.05389 7.4366 5.11751 7.14046 5.29156C6.84431 5.46561 6.61932 5.73885 6.50533 6.06289C6.37204 6.44179 5.95683 6.6409 5.57793 6.50761C5.19903 6.37432 4.99992 5.95911 5.13321 5.58021C5.36119 4.93213 5.81117 4.38565 6.40347 4.03755C6.99576 3.68945 7.69214 3.56221 8.36926 3.67835C9.04638 3.7945 9.66054 4.14654 10.103 4.67211C10.5453 5.19759 10.7875 5.86263 10.7865 6.54949C10.7862 7.66247 9.96104 8.39747 9.37178 8.79031C9.05495 9.00153 8.74331 9.15683 8.51373 9.25887C8.39791 9.31034 8.30038 9.3494 8.23011 9.37618C8.19491 9.38959 8.16637 9.39998 8.1456 9.40736L8.12034 9.41621L8.11229 9.41896L8.10946 9.41991L8.10834 9.42029C8.10834 9.42029 8.10743 9.42059 7.87761 8.73113L8.10743 9.42059C7.72638 9.54761 7.31451 9.34167 7.1875 8.96062C7.06052 8.5797 7.26628 8.16798 7.64709 8.04082L7.65831 8.03687C7.6695 8.03289 7.68783 8.02624 7.71229 8.01692C7.76133 7.99824 7.83426 7.96912 7.92299 7.92969C8.1025 7.8499 8.33631 7.73248 8.56494 7.58006C9.06647 7.24571 9.332 6.89001 9.332 6.54882L9.332 6.54774C9.33251 6.20423 9.21143 5.87163 8.99021 5.60884C8.769 5.34605 8.46191 5.17003 8.12335 5.11196Z" fill="white"></path>
                                <path d="M7.20836 11.6397C7.20836 11.2381 7.53397 10.9125 7.93563 10.9125H7.9429C8.34457 10.9125 8.67018 11.2381 8.67018 11.6397C8.67018 12.0414 8.34457 12.367 7.9429 12.367H7.93563C7.53397 12.367 7.20836 12.0414 7.20836 11.6397Z" fill="white"></path>
                            </svg>
                            </a>
                        </label>
                        <input type="text" class="form-control input-form-control" name="cvc" id="cvc" maxlength="4" onkeypress="return isNumber(event)" required data-value-missing="{{ __('This field is required.') }}">
                        <div id="card-cvc"></div>
                    </div>
                </div>
                <div class="col-md-12">
                    <p class="text-danger" id="stripeError"></p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="d-grid mt-3p">
                        <button type="submit" class="btn btn-lg btn-primary" type="submit" id="stripeSubmitBtn">
                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                <span class="visually-hidden"></span>
                            </div>
                            <span id="stripeSubmitBtnText" class="px-1">{{ __('Confirm Payment') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
  </div>
@endsection

@section('js')
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}"></script>
    <script>
        'use strict';
        let amount = "{{ $totalAmount }}";
        let token = "{{ csrf_token() }}";
        let stripeSubmitBtnText = "{{ __('Confirming...') }}"; 
    </script>

    <script src="{{ asset('public/frontend/customs/js/merchant-payments/stripe.min.js') }}"></script>
@endsection