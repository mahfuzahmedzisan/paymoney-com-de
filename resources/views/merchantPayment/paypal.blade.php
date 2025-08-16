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
                <div class="d-flex justify-content-between mb-4">
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
                        <img src="{{ asset('public/dist/images/gateways/payments/paypal.png') }}" class="img-fluid">
                    </div>
                </div>
            </div>
            <!--- PAYPAL GATEWAY START-->
            <form class="dis-none" id="Paypal" name="Paypal" method="post" action="{{ url('payment/paypal') }}" accept-charset="UTF-8">
                @csrf
                <input name="order_no" value="{{ isset($paymentInfo['order_no']) ? $paymentInfo['order_no'] : '' }}" type="hidden">
                <input name="item_name" value="{{ isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : '' }}" type="hidden">
                <input name="merchant" value="{{ isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : '' }}" type="hidden">
                <input name="merchant_uuid" value="{{ isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : '' }}" type="hidden">
                <input name="no_shipping" value="1" type="hidden">
                <input name="currency" value="{{ $merchant?->currency?->code }}" type="hidden">
                <input class="form-control" name="amount" value="{{ isset($paymentInfo['amount']) ? $paymentInfo['amount'] : '' }}" type="hidden">
                <div id="paypal-button-container"></div>
            </form>
            <!--- PAYPAL GATEWAY END-->
        </div>
        
    </div>
@endsection

@section('js')
    <script src="https://www.paypal.com/sdk/js?client-id={{ isset($clientId) ? $clientId : '' }}&disable-funding=paylater&currency={{ isset($currencyCode) ? $currencyCode : '' }}"></script>

    <script>
        'use strict';
        var token = "{{ csrf_token() }}";
        var amount = "{{ $totalAmount }}";
    </script>

<script src="{{ asset('public/frontend/customs/js/merchant-payments/paypal.min.js') }}"></script>
@endsection
