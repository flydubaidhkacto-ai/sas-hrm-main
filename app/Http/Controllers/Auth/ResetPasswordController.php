<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\User;
use Hash;
use Brian2694\Toastr\Facades\Toastr;

class ResetPasswordController extends Controller
{
    public function getPassword($token)
    {
       return view('auth.passwords.reset', ['token' => $token]);
    }
    public function updatePassword(Request $request)
    {
        abort_if(config('app.is_demo'), 403, 'This action is not allowed in the demo version.');
        $request->validate([
            // 'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $updatePassword = DB::table('password_resets')->where(['token' => $request->token])->first();
        if(!$updatePassword)
        {
            Toastr::error('Invalid token! :)','Error');
            return back();
        }
        else{
            // dd($updatePassword);
            $user = User::where('email', $updatePassword->email)->update(['password' => Hash::make($request->password)]);
            DB::table('password_resets')->where(['email'=> $updatePassword->email])->delete();
            Toastr::success('Your password has been changed! :)','Success');
            return redirect('/login');
        }
    }
}
