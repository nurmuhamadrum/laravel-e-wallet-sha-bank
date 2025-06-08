<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function update()
    {
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        $notif = new \Midtrans\Notification();

        $transactionStatus = $notif->transaction_status;
        $transactionType = $notif->payment_type;
        $transactionCode = $notif->order_id;
        $fraud = $notif->fraud_status;

        DB::beginTransaction();
        try {
            $status = null;
            if ($transactionStatus == 'capture') {
                if ($fraud == 'challenge') {
                    $status = 'challenge';
                } else if ($fraud == 'accept') {
                    $status = 'success';
                }
            } else if ($transactionStatus == 'settlement') {
                $status = 'success';
            } else if (
                $transactionStatus == 'cancel' ||
                $transactionStatus == 'deny' ||
                $transactionStatus == 'expire'
            ) {
                $status = 'failed';
            } else if ($transactionStatus == 'pending') {
                $status = 'pending';
            }

            $transaction = Transaction::where('transaction_code', $transactionCode)->first();

            if ($transaction->status !== 'success') {
                $transactionAmount = $transaction->amount;
                $userId = $transaction->user_id;

                $transaction->update([
                    'status' => $status
                ]);

                if ($status === 'success') {
                    $wallet = Wallet::where('user_id', $userId)->first();
                    if ($wallet) {
                        $wallet->increment('balance', $transactionAmount);
                    } else {
                        Wallet::create([
                            'user_id' => $userId,
                            'balance' => $transactionAmount,
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction updated successfully.',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction failed: ' . $th->getMessage(),
            ], 500);
        }
    }
}
