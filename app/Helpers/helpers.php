<?php

use App\Models\User;
use App\Models\Wallet;

function getUser($param)
{
    $user = User::where('id', $param)
        ->orWhere('email', $param)
        ->orWhere('username', $param)
        ->first();

    $wallet = Wallet::where('user_id', $user->id)->first();

    $user->profile_picture = $user->profile_picture ? url('storage/' . $user->profile_picture) : "";
    $user->ktp = $user->ktp ? url('storage/' . $user->ktp) : "";
    $user->balance = $wallet ? $wallet->balance : 0;
    $user->card_number = $wallet ? $wallet->card_number : null;
    $user->pin = $wallet ? $wallet->pin : null;

    return $user;
}

// Function to check if the user has a pin set
function pinChecker($pin) {
    $user = \Illuminate\Support\Facades\Auth::user();
    $wallet = Wallet::where('user_id', $user->id)->first();

    if (!$wallet) {
        return false;
    }

    if ($wallet->pin === null) {
        return false;
    }

    if ($wallet->pin === $pin) {
        return true;
    }

    return false;
}
