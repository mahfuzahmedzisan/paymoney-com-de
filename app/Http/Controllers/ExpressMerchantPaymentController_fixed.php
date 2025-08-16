<?php

namespace App\Http\Controllers;

use App\Exceptions\ExpressMerchantPaymentException;
use App\Services\ExpressMerchantPaymentService;
use Session, Auth, Exception;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use App\Models\{
    AppTransactionsInfo,
    Currency
};
use Illuminate\Support\Facades\Log;

class ExpressMerchantPaymentController extends Controller
{
    protected $helper, $service;
    public function __construct()
    {
        $this->helper = new Common();
        $this->service = new ExpressMerchantPaymentService();
    }

    public function verifyClient(Request $request)
    {
        try {
            // Add logging for debugging
            Log::info('PayMoney API: verifyClient called', [
                'client_id' => $request->client_id,
                'has_secret' => !empty($request->client_secret),
                'request_data' => $request->except(['client_secret'])
            ]);

            $app = $this->service->verifyClientCredentials($request->client_id, $request->client_secret);
            $response = $this->service->createAccessToken($app);
            
            Log::info('PayMoney API: verifyClient success', ['response' => $response]);
            return response()->json($response);

        } catch (ExpressMerchantPaymentException $exception) {
            Log::error('PayMoney API: verifyClient ExpressMerchantPaymentException', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            $data = [
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ];
            return response()->json($data, 400);

        } catch(Exception $exception) {
            Log::error('PayMoney API: verifyClient Exception', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            $data = [
                'status'  => 'error',
                'message' => __("Failed to process the request."),
            ];
            return response()->json($data, 500);
        }
    }

    public function storeTransactionInfo(Request $request)
    {
        try {
            // Add comprehensive logging for debugging
            Log::info('PayMoney API: storeTransactionInfo called', [
                'request_data' => $request->except(['client_secret']),
                'headers' => $request->headers->all(),
                'method' => $request->method()
            ]);

            // Validate required parameters
            $validator = \Validator::make($request->all(), [
                'payer' => 'required|string',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|size:3',
            ]);

            if ($validator->fails()) {
                Log::warning('PayMoney API: storeTransactionInfo validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $paymentMethod = $request->payer;
            $amount        = $request->amount;
            $currency      = $request->currency;
            
            // Handle both parameter names: successUrl OR returnUrl
            $successUrl    = $request->successUrl ?? $request->returnUrl;
            $cancelUrl     = $request->cancelUrl;

            // Validate URLs
            if (empty($successUrl)) {
                Log::warning('PayMoney API: Missing success/return URL', [
                    'successUrl' => $request->successUrl,
                    'returnUrl' => $request->returnUrl
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'successUrl or returnUrl is required'
                ], 422);
            }

            if (empty($cancelUrl)) {
                Log::warning('PayMoney API: Missing cancel URL');
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'cancelUrl is required'
                ], 422);
            }

            # check token missing
            $hasHeaderAuthorization = $request->hasHeader('Authorization');
            if (!$hasHeaderAuthorization) {
                Log::warning('PayMoney API: Missing Authorization header');
                
                $res = [
                    'status'  => 'error',
                    'message' => __('Access token is missing'),
                    'data'    => [],
                ];
                return response()->json($res, 401);
            }

            # check token authorization
            $headerAuthorization = $request->header('Authorization');
            Log::info('PayMoney API: Checking token authorization', [
                'has_authorization' => !empty($headerAuthorization)
            ]);
            
            $token = $this->service->checkTokenAuthorization($headerAuthorization);
            Log::info('PayMoney API: Token validated successfully', [
                'app_id' => $token->app_id ?? 'unknown'
            ]);

            # Currency And Amount Validation
            Log::info('PayMoney API: Checking merchant wallet availability', [
                'currency' => $currency,
                'amount' => $amount
            ]);
            
            $this->service->checkMerchantWalletAvailability($token, $currency, $amount);
            Log::info('PayMoney API: Merchant wallet validation passed');

            # Update/Create AppTransactionsInfo and return response
            Log::info('PayMoney API: Creating transaction info', [
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'currency' => $currency
            ]);
            
            $res = $this->service->createAppTransactionsInfo($token->app_id, $paymentMethod, $amount, $currency, $successUrl, $cancelUrl);
            
            Log::info('PayMoney API: Transaction info created successfully', [
                'response' => $res
            ]);
            
            return response()->json($res);
            
        } catch (ExpressMerchantPaymentException $exception) {
            Log::error('PayMoney API: storeTransactionInfo ExpressMerchantPaymentException', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            $data = [
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ];
            return response()->json($data, 400);

        } catch(Exception $exception) {
            Log::error('PayMoney API: storeTransactionInfo Exception', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);
            
            $data = [
                'status'  => 'error',
                'message' => __("Failed to process the request."),
                'debug' => config('app.debug') ? $exception->getMessage() : null
            ];
            return response()->json($data, 500);
        }
    }

    /**
     * [Generat URL]
     * @param  Request $request  [email, password]
     * @return [view]  [redirect to merchant confirm page or redirect back]
     */
    public function generatedUrl(Request $request)
    {
        try {
            Log::info('PayMoney API: generatedUrl called', [
                'grant_id' => $request->grant_id,
                'token' => $request->token,
                'user_authenticated' => auth()->check()
            ]);

            $transInfo = $this->service->getTransactionData($request->grant_id, $request->token);
            $currency = Currency::whereCode($transInfo->currency)->first();
            $feesLimit = $this->service->checkMerchantPaymentFeesLimit($currency->id, Mts, $transInfo->amount, $transInfo->app->merchant->fee);

            $totalAmount = $transInfo?->app?->merchant?->merchant_group?->fee_bearer == 'Merchant' ? $transInfo['amount'] : $transInfo['amount'] + $feesLimit['totalFee'];

            if (!auth()->check()) {

                if ($request->isMethod('POST')) {
                    $credentials = $request->only('email', 'password');

                    if (Auth::attempt($credentials)) {

                        $authCredentials = [
                            'email'    => $request->email,
                            'password' => $request->password,
                        ];

                        Session::put('credentials', $authCredentials);
                        //Abort if logged in user is same as merchant
                        $this->checkMerchantUser($transInfo);

                        $this->checkUserStatus(auth()->user()->status);

                        $this->service->checkUserBalance(auth()->user()->id, $totalAmount, $currency->id);

                        $data = $this->service->checkoutToPaymentConfirmPage($transInfo);
                        $data['fees'] = $feesLimit['totalFee'];
                        $data['transInfo'] = $transInfo;
                        $data['currencyId'] = $currency->id;
                        return view('merchantPayment.confirm', $data);
                        
                    } else {
                        $this->helper->one_time_message('error', __('Unable to login with provided credentials!'));
                        return redirect()->back();
                    }
                } else {
                    $data['fees'] = $feesLimit['totalFee'];
                    $data['transInfo'] = $transInfo;
                    $data['currencyId'] = $currency->id;
                    return view('merchantPayment.login', $data);
                }
            }

            //Abort if logged in user is same as merchant
            $this->checkMerchantUser($transInfo);

            $this->checkUserStatus(auth()->user()->status);

            $this->service->checkUserBalance(auth()->user()->id, $totalAmount, $currency->id);

            $data = $this->service->checkoutToPaymentConfirmPage($transInfo);

            $data['fees'] = $feesLimit['totalFee'];
            $data['currencyId'] = $currency->id;

            return view('merchantPayment.confirm', $data);

        } catch (ExpressMerchantPaymentException $exception) {
            Log::error('PayMoney API: generatedUrl ExpressMerchantPaymentException', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            $data = [
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ];
            return view('merchantPayment.fail', $data);

        } catch(Exception $exception) {
            Log::error('PayMoney API: generatedUrl Exception', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            $data = [
                'status'  => 'error',
                'message' => __("Failed to process the request."),
            ];
            return view('merchantPayment.fail', $data);
        }
    }

    public function confirmPayment()
    {
        try {
            Log::info('PayMoney API: confirmPayment called', [
                'user_authenticated' => auth()->check()
            ]);

            if (!auth()->check()) {
                $getLoggedInCredentials = Session::get('credentials');
    
                if (Auth::attempt($getLoggedInCredentials)) {
                    $successPath = $this->service->storePaymentInformations();
                    return redirect()->to($successPath);
                } 
    
                $this->helper->one_time_message('error', __('Unable to login with provided credentials!'));
                return redirect()->back();
                
            }
    
            $data = $this->service->storePaymentInformations();
            if ($data['status'] == 200) {
                return redirect()->to($data['successPath']);
            }
            Session::forget('transInfo');

        } catch (ExpressMerchantPaymentException $exception) {
            Log::error('PayMoney API: confirmPayment ExpressMerchantPaymentException', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            $data = [
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ];
            return view('merchantPayment.fail', $data);

        } catch(Exception $exception) {
            Log::error('PayMoney API: confirmPayment Exception', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            $data = [
                'status'  => 'error',
                'message' => __("Failed to process the request."),
            ];
            return view('merchantPayment.fail', $data);
        }
        
    }

    public function cancelPayment()
    {
        try {
            Log::info('PayMoney API: cancelPayment called');
            
            $transInfo     = Session::get('transInfo');
            $trans         = AppTransactionsInfo::find($transInfo->id, ['id', 'status', 'cancel_url']);
            $trans->status = 'cancel';
            $trans->save();
            Session::forget('transInfo');
            return redirect()->to($trans->cancel_url);
            
        } catch(Exception $exception) {
            Log::error('PayMoney API: cancelPayment Exception', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            return redirect('/');
        }
    }

    protected function checkUserStatus($status)
    {
        //Check whether user is Suspended
        if ($status == 'Suspended') {
            $data['message'] = __('You are suspended to do any kind of transaction!');
            return view('merchantPayment.user_suspended', $data);
        }

        //Check whether user is inactive
        if ($status == 'Inactive') {
            auth()->logout();
            $this->helper->one_time_message('danger', __('Your account is inactivated. Please try again later!'));
            return redirect('/login');
        }
    }

    protected function checkMerchantUser(object $transInfo)
    {
        if ($transInfo?->app?->merchant?->user?->id == auth()->user()->id) {
            auth()->logout();
            $this->helper->one_time_message('error', __('Merchant cannot make payment to himself!'));
            return redirect()->back();
        } 
    }
}
