<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function getEmail()
    {
       return view('auth.passwords.email');
    }

    public function postEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $token = Str::random(60);

        DB::table('password_resets')->insert(
            ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
        );

        $company_info = company_data();
        Mail::send('auth.verify',['token' => $token], function($message) use ($request, $company_info) {
            $message->from(config('mail.from.address'));
            $message->to($request->email);
            $message->subject( $company_info['name'] ?? config('app.name') . ' - Reset Password Notification');
        });
        Toastr::success('We have e-mailed your password reset link! :)','Success');
        return back();
    }
}
