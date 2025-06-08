<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TopUpController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->only('amount', 'pin', 'payment_method_code');

        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:10000',
            'pin' => 'required|digits:6',
            'payment_method_code' => 'required|in:bni_va,bca_va,bri_va',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->messages(),
            ], 422);
        }

        $pinChecker = pinChecker($request->pin);

        if (!$pinChecker) {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN is incorrect',
            ], 422);
        }

        $transactionType = TransactionType::where('code', 'top_up')->first();
        $paymentMethod = PaymentMethod::where('code', $request->payment_method_code)->first();

        if (!$paymentMethod) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payment method code.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'payment_method_id' => $paymentMethod->id,
                'transaction_type_id' => $transactionType->id,
                'amount' => $request->amount,
                'transaction_code' => strtoupper(Str::random(10)),
                'description' => 'Top Up via ' . $paymentMethod->name,
                'status' => 'pending',
            ]);

            $params = $this->buildMidtransParams([
                'transaction_code' => $transaction->transaction_code,
                'amount' => $transaction->amount,
                'payment_method_code' => $paymentMethod->code,
            ]);

            $midtransResponse = $this->callToMidtrans($params ?? []);

            // call to midtrans
            DB::commit();

            return response()->json($midtransResponse, 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    // This function calls Midtrans API to create a transaction
    private function callToMidtrans(array $params)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$isSanitized = (bool) env('MIDTRANS_IS_SANITIZED');
        \Midtrans\Config::$is3ds =  (bool)env('MIDTRANS_IS_3DS');

        $createTransaction = \Midtrans\Snap::createTransaction($params);

        return [
            'redirect_url' => $createTransaction->redirect_url,
            'token' => $createTransaction->token,
        ];
    }

    // This function builds the parameters for Midtrans API
    private function buildMidtransParams(array $params)
    {
        $transactionDetails = [
            'order_id' => $params['transaction_code'],
            'gross_amount' => $params['amount'],
        ];

        $user = request()->user();
        $splitName = $this->splitName($user->name);

        $customerDetails = [
            'first_name' => $splitName['first_name'],
            'last_name' => $splitName['last_name'],
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        $enabledPayment = [
            $params['payment_method_code'],
        ];

        return [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'enabled_payments' => $enabledPayment,
        ];
    }

    // This function splits the full name into first and last names
    private function splitName($fullName){
        $nameParts = explode(' ', $fullName);
        $lastName = count($nameParts) > 1 ? array_pop($nameParts) : $fullName;
        $firstName = implode(' ', $nameParts);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
    }
}
