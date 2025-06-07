<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Melihovv\Base64ImageDecoder\Base64ImageDecoder;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'pin' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        } 

        $user = User::where('email', $request->email)->exists();

        if ($user) {
            return response()->json(['message' => 'Email already exists'], 409);
        }

        DB::beginTransaction();

        try {
            $profilePicture = null;
            $ktp = null;

            if ($request->profile_picture) {
                $profilePicture = $this->uploadBase64Image($request->profile_picture);
            }

            if ($request->ktp) {
                $ktp = $this->uploadBase64Image($request->ktp);
            }

            // Create a new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->email, // Use email as username
                'password' => bcrypt($request->password),
                'profile_picture' => $profilePicture,
                'ktp' => $ktp,
                'verified' => ($ktp) ? true : false,
            ]);

            // Create a wallet for the user
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pin' => $request->pin,
                'card_number' => $this->generateCardNumber(16), // Generate a random 16-digit card number
            ]);

            DB::commit();
            $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password]);
            
            $userResponse = getUser($request->email); 
            $userResponse->token = $token; // Add token to user response
            $userResponse->token_expires_in = JWTAuth::factory()->getTTL() * 60; // Token expiration time in seconds
            $userResponse->token_type = 'Bearer'; // Token type

            return response()->json($userResponse, 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Registration failed', 'error' => $th->getMessage()], 500);
        }
    }

    // Login a user
    public function login(Request $request)
    {
        $credential = $request->all();
        $validator = Validator::make($credential, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        try {
            $token = JWTAuth::attempt($credential);
            if (!$token) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $userResponse = getUser($request->email); 
            $userResponse->token = $token; // Add token to user response
            $userResponse->token_expires_in = JWTAuth::factory()->getTTL() * 60; // Token expiration time in seconds
            $userResponse->token_type = 'Bearer'; // Token type

            return response()->json($userResponse, 200);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['errors' => $e->getMessage()], 500);
        }
    }

    // Generate a random card number
    private function generateCardNumber($length)
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        $wallet = Wallet::where('card_number', $result)->exists();
        if ($wallet) {
            return $this->generateCardNumber($length); // Regenerate if card number already exists
        }

        return $result;
    }

    // Handle base64 image upload
    private function uploadBase64Image($base64Image)
    {
        $decoder = new Base64ImageDecoder($base64Image, $allowedFormats = ['jpg', 'jpeg', 'png']);

        $decodedContent = $decoder->getDecodedContent();
        $format = $decoder->getFormat();
        $image = Str::random(10) . '.' . $format; // Generate a random filename
        Storage::disk('public')->put('images/' . $image, $decodedContent);

        return $image;
    }
}
