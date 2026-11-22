<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DeviceSession;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

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
        $this->middleware('guest')->except([
            'logout',
            'locked',
            'unlock'
        ]);
    }

    public function login()
    {
        return view('auth.login');
    }

  public function authenticate(Request $request)
{
    $request->validate([
        'email'    => 'required|string|max:255',
        'password' => 'required|string|max:255',
    ]);

    // 🔥 find user from all possible fields
    $user = User::where('email', $request->email)
        ->orWhere('phone_number', $request->email)
        ->orWhere('student_id', $request->email)
        ->first();

    if (!$user) {
        Toastr::error('User not found');
        return back()->withInput();
    }

    if (!$user->is_active) {
        Toastr::error('Your account is not active. Please contact support.');
        return back()->withInput();
    }

    // 🔥 correct password check
    if (!\Hash::check($request->password, $user->password)) {
        Toastr::error('Wrong password');
        return back()->withInput();
    }

    // 🔥 login manually (BEST FOR YOUR CASE)
    Auth::login($user);

    // 🔥 important session regenerate
    $request->session()->regenerate();

    // track device
    $this->trackDeviceSession($user, $request);

    Toastr::success('Login successfully :)', 'Success');

    return redirect()->intended('home');
}

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Terminate current web device session
            $userAgent = $request->header('User-Agent', '');
            $deviceId = DeviceSession::generateDeviceId($userAgent, $request->ip());

            $deviceSession = DeviceSession::where('user_id', $user->id)
                ->where('device_id', $deviceId)
                ->where('session_token', session()->getId())
                ->first();

            if ($deviceSession) {
                $deviceSession->delete();
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Toastr::success('Logout successfully :)','Success');
        return redirect('login');
    }

    /**
     * Track device session for web login
     */
    protected function trackDeviceSession(User $user, Request $request)
    {
        $userAgent = $request->header('User-Agent', '');
        $ipAddress = $request->ip();
        $sessionToken = session()->getId();

        DeviceSession::createOrUpdateSession(
            $user,
            $userAgent,
            $ipAddress,
            null, // No personal access token for web sessions
            $sessionToken
        );
    }

}
