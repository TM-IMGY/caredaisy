<?php

// If you are using Laravel's built-in LoginController class,
// the Illuminate\Foundation\Auth\ThrottlesLogins trait will already be included in your controller.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
     * Get the maximum number of attempts to allow.
     *
     * @var int
     */
    protected $maxAttempts = 3;

    /**
     * Get the number of minutes to throttle for.
     *
     * @var int
     */
    protected $decayMinutes = 10;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'employee_number';
    }
}
