<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);
        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();

            // Get URLs
            $urlPrevious = url()->previous();
            $urlBase = url()->to('/');

            // Set the previous url that we came from to redirect to after successful login but only if is internal
            if (($urlPrevious != $urlBase . '/login') && (substr($urlPrevious, 0, strlen($urlBase)) === $urlBase)) {
                session()->put('url.intended', $urlPrevious);
                $this->redirectTo = url()->previous();
            }

            $tokenResult = $user->generateToken();

            return response()->json([
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer'


            ]);
            //return true;
        }
        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('api')->user();

        Auth::logout();

        return response()->json(['data' => 'User logged out.'], 200);
    }
}
