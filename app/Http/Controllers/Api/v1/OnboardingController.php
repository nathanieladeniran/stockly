<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\Onboarding;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserCreateRequest;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**Add new User */
    public function addNewUser(UserCreateRequest $request)
    {
        $newUser = (new Onboarding())->createNewUser($request);
        return $newUser;
    }
}
