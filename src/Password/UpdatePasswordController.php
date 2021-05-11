<?php

namespace Larapress\Auth\Password;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Larapress\Auth\Signin\ISigninService;

class UpdatePasswordController extends Controller
{
    public static function registerRoutes()
    {
        Route::post('/update-password', '\\' . self::class . '@updatePassword')
            ->name('users.any.update.password');
    }

    /**
     * Update password
     *
     * @param ISigninService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(ISigninService $service, UpdatePasswordRequest $request)
    {
        return response()->json(
            $service->updatePassword(
                Auth::user(),
                $request->getOldPassword(),
                $request->getNewPassword()
            )
        );
    }
}
