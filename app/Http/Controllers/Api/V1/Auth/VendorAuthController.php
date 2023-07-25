<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerification;
use App\Model\BusinessSetting;
use App\Model\EmailVerifications;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helpers;
use App\Helpers\Cmf;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Socialite;
use Auth;
use Illuminate\Support\Facades\Hash;
/**
 * @group Auth Page

 *APi for Autentication

*/


class VendorAuthController extends Controller
{
    

    public function userregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phonenumber' => 'required|numeric',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'errors' => error_processor($validator)], 403);
        }

        // dd($request->all());
        // die('here');
        $Vandor = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phonenumber' => $request->phonenumber,
            'country' => $request->country,
            'state' => $request->state,
            'address' => $request->address,
            'address2' => $request->address2,
            'active' => 1,
            'role_id' => 1,
            'is_admin' =>0,
            'user_type' =>'customer',
            'password' => Hash::make($request->password),
        ]);

        $token = $Vandor->createToken('VandorAuth')->accessToken;

        return response()->json(['token' => $token, 'status' => true, 'VandorData'=>$Vandor], 200);
    }


    public function password_reset()
    {
        
    }


    public function register(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users',
            'phonenumber' => 'required|unique:users',
            'business_name' => 'required',
            'home_phone' => 'required',
            'business_phone' => 'required',
            'fax' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'website' => 'required',
            'cnic_front' => 'required|mimes:png,jpg',
            'cnic_back' => 'required|mimes:png,jpg|',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'errors' => error_processor($validator)], 403);
        }

        $cnic_front = null;
        $cnic_back = null;
        if(!empty($request->cnic_front))
        {
            $cnic_front = Cmf::sendimagetodirectory($request->cnic_front);
        }
        if(!empty($request->cnic_back))
        {
            $cnic_back = Cmf::sendimagetodirectory($request->cnic_back);
        }

        // dd($request->all());
        // die('here');
        $Vandor = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phonenumber' => $request->phonenumber,
            'business_name' => $request->business_name,
            'home_phone' => $request->home_phone,
            'business_phone' => $request->business_phone,
            'fax' => $request->fax,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'address' => $request->address,
            'website' => $request->website,
            'cnic_back' => $cnic_back,
            'cnic_front' => $cnic_front,
            'active' => 1,
            'role_id' => 1,
            'is_admin' =>0,
            'user_type' =>'vendor',
            'password' => bcrypt($request->password)
        ]);

        $token = $Vandor->createToken('VandorAuth')->accessToken;

        return response()->json(['token' => $token, 'status' => true, 'VandorData'=>$Vandor], 200);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => error_processor($validator)], 403);
        }

        $data = [
            'email' => $request->username,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {

            $token = Auth::user()->createToken('name'); 

            return response()->json(['token' => $token,'status' => true,'UserData'=>auth()->user()], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'status' => false, 'message' => trans('Credentials are Wrong')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }
    

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback()
    {
        try {
    
            $user = Socialite::driver('google')->user();
     
            $finduser = User::where('google_id', $user->id)->first();
     
            if($finduser){
         
                 if (auth()->attempt($finduser)) {
                        $token = auth()->user()->createToken('VandorAuth')->accessToken;
                        return response()->json(['token' => $token,'status' => true,'UserData'=>auth()->user()], 200);
                    } else {
                        $errors = [];
                        array_push($errors, ['code' => 'auth-001', 'status' => false, 'message' => trans('custom.login_failed')]);
                        return response()->json([
                            'errors' => $errors
                        ], 401);
                    }
     
            }else{
                $newuser = new User;
                $newuser->name = $user->name;
                $newuser->email = $user->email;
                $newuser->password = Hash::make(123456789);
                $newuser->user_type ='customer';
                $newuser->active =1;
                $newuser->is_admin =0;
                $newuser->save();
         
                if (auth()->attempt($newuser)) {
                    $token = auth()->user()->createToken('VandorAuth')->accessToken;
                    return response()->json(['token' => $token,'status' => true,'UserData'=>auth()->user()], 200);
                } else {
                    $errors = [];
                    array_push($errors, ['code' => 'auth-001', 'status' => false, 'message' => trans('custom.login_failed')]);
                    return response()->json([
                        'errors' => $errors
                    ], 401);
                }
            }
    
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
    /**

        * Send OTP
        * Send the Phone Number And Get OTP On Mobile
        * @bodyParam  phone int required (min:11) (max:14) (unique) Phone number For Otp. Example: 03025199794


             * @response  {
             *  "message": "OTP send to your mobile number",
             *  "code": "succ_otp_send",
             *  "status": true
             * }
             */

    public function send_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:11|max:14|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 403);
        }


        $response = send_otp_code($request->phone);
        
        if($response)
        {
             return response()->json([
            'message' => trans('custom.send_otp_success'),
            'code' => 'succ_otp_send',
            'status' => true
            ], 200);

        }

        else
        {
            return response()->json([
                'message' => trans('custom.send_otp_failed'),
                'code' => 'rejected_otp_send',
                'status' => false
            ], 200);
        }
    }


        /**

        * Verify OTP
        * Customer Send the Phone Number And Get OTP On Mobile
        * @bodyParam  phone int required (min:11)(max:14)(unique) Phone number For Otp. Example: 03025199794
        * @bodyParam  otp int required must have 6 characters. Example: 123456


             * @response  {
             *  "message": "OTP send to your mobile number",
             *  "code": "succ_otp_send",
             *  "status": true
             * }
             */



    public function verify_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:11|max:14|unique:users',
            'otp' => 'required|regex:/[0-9]{6}/|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 403);
        }


        $response = verify_otp_code($request->phone,$request->otp);

       
       if($response)
        {
             return response()->json([
            'message' => trans('custom.verify_otp_success'),
            'code' => 'succ_otp_approved',
            'status' => true
            ], 200);

        }

        else
        {
            return response()->json([
                'message' => trans('custom.verify_otp_failed'),
                'code' => 'rejected_otp_verify',
                'status' => false
            ], 200);
        }

    }

    public function check_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (BusinessSetting::where(['key' => 'email_verification'])->first()->value) {
            $token = rand(1000, 9999);
            DB::table('email_verifications')->insert([
                'email' => $request['email'],
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Mail::to($request['email'])->send(new EmailVerification($token));

            return response()->json([
                'message' => trans('custom.email_registration_ready'),
                'token' => 'active'
            ], 200);
        } else {
            return response()->json([
                'message' => trans('custom.email_registration_ready'),
                'token' => 'inactive'
            ], 200);
        }
    }

    public function verify_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $verify = EmailVerifications::where(['email' => $request['email'], 'token' => $request['token']])->first();

        if (isset($verify)) {
            $verify->delete();
            return response()->json([
                'message' => 'Token verified!',
            ], 200);
        }

        return response()->json(['errors' => [
            ['status' => false, 'code' => 'token', 'message' => trans('custom.token_not_found')]
        ]], 404);
    }



    public function logout(Request $request)
    {   
        $user = $request->user()->tokens();
        //$user->revoke();

        
        //$user = auth()->user();
        dd($user);
        die();
        $user->revoke();
            return response()->json([
                'code' => 'succ_logout',
                'status' => true,
                'message' => 'logout Success!',
            ], 200);
        

        return response()->json(['errors' => [
            ['status' => false, 'code' => 'token', 'message' => trans('custom.token_not_found')]
        ]], 404);
    }

    

    
}
