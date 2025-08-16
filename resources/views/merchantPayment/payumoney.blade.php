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
                    <p><small>{{ __('Transaction charge : :x', ['x' => moneyFormat(optional($merchant->currency)->code, formatNumber($totalFee, optional($merchant->currency)->id))]) }}</small></p>
                </div>
                <div class="amount-logo">
                    <img  src="{{ asset('public/dist/images/gateways/payments/payumoney.png') }}" alt="logo" class="img-fluid">
                </div>
            </div>
        </div>
        <form id="payUMoneyPaymentForm" method="post" action="{{ url('payment/payumoney') }}">
            @csrf
            <input name="order_no" value="{{ isset($paymentInfo['order_no']) ? $paymentInfo['order_no'] : '' }}" type="hidden">
            <input name="item_name" value="{{ isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : '' }}" type="hidden">
            <input name="merchant" value="{{ isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : '' }}" type="hidden" id="merchant">
            <input name="merchant_uuid" value="{{ isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : '' }}" type="hidden">
            <input name="currency" value="{{ $merchant?->currency?->code}}" type="hidden" id="currency">
            <input name="amount" class="form-control" value="{{ isset($paymentInfo['amount']) ? $paymentInfo['amount'] : '' }}" type="hidden">
            <input name="total_amount" class="form-control" value="{{ $totalAmount }}" type="hidden">

            <div class="row">
                <div class="col-12">
                    <div class="d-grid mt-3p">
                        <button type="submit" class="btn btn-lg btn-primary" type="submit" id="payUMoneySubmitBtn">
                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                <span class="visually-hidden"></span>
                            </div>
                            <span id="payUMoneySubmitBtnText" class="px-1 text-uppercase">{{ __('Confirm Payment') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
  </div>
@endsection

@section('js')
    <script>
        'use strict';
        let payUMoneySubmitBtnText = "{{ __('Confirming...') }}"; 
    </script>

    <script src="{{ asset('public/frontend/customs/js/merchant-payments/payumoney.min.js') }}"></script>
@endsection