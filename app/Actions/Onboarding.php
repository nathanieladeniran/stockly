<?php

namespace App\Actions;

use App\Http\Requests\UserCreateRequest;
use App\Models\Temp;
use App\Models\User;
use App\Notifications\RegistrationOtpNotification;
use App\Traits\HasJsonResponse;
use App\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Onboarding
{
    use HasJsonResponse, ModelTrait, Notifiable;

    //Create a new User Account
    public function createNewUser(UserCreateRequest $request)
    {
        $newUser = User::where('email', $request->email)->first();

        abort_if($newUser, HTTP_BAD_REQUEST, "An account with this email already exist");

        $referrer = $request->has('ref') ? $request->query('ref') : $request->referrer;

        //save temporarily before verifying the OTP
        $emailOtp = random_int(100000, 999999);
        $phoneOtp = random_int(100000, 999999);

        $saveData = Temp::updateOrCreate(
            ['email' => $request->email],
            [
                'referrer' => $referrer ? $referrer : null,
                'referral_token' => random_int(10000000, 99999999),
                'email' => $request->email,
                'email_otp' => $emailOtp,
                'email_otp_expires_at' => Carbon::now()->addHours(2),
                'mobile_phone' => $request->mobile_phone,
                'country_id' => $request->country_id,
                'phone_otp' => $phoneOtp,
                'phone_otp_expires_at' => Carbon::now()->addHours(2),
                'policy_agreement' => $request->policy_agreement,
                'password' => Hash::make($request->password)
            ]
        );

        if ($saveData) {
            $saveData->notify(new RegistrationOtpNotification($emailOtp));
            return $this->jsonResponse(HTTP_CREATED, "Data Saved, and OTP send to the registered email for account validation", ['uuid' => $saveData->uuid]);
        }
    }
}
