<?php

namespace App\Http\Controllers;

use App\Actions\SMSApi;
use App\Models\SmsHistory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\SendBulkSMS;
use App\Jobs\SendFineSMS;
use App\Models\Setting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Http;

class SmsController extends Controller
{
    function history()
    {
        return view('bulk-sms.history', [
            'history' => SmsHistory::latest()
                ->paginate()
        ]);
    }

    public function list(Request $request)
    {
        $employees = User::active_employees();

        if (
            role_can('user')
            || role_can('user.self.super_department')
        ) {
            if (
                !role_can('user')
                && role_can('user.self.super_department')
            ) {
                $employees->whereHas('department', function ($q) {
                    $q->where('super_department_id', user()->department?->super_department_id);
                });
            }
        }
        if ($request->department) {
            $employees->where('department_id', $request->department);
        } else
        if ($request->super_department) {
            $employees->whereHas('department', function ($q) use ($request) {
                $q->where('super_department_id', $request->super_department);
            });
        }

        if ($request->designation) {
            $employees->where('designation_id', $request->designation);
        }

        if ($request->employee) {
            $employees->where(function ($q) use ($request) {
                $q->where('id', $request->employee);
            });
        }

        // dd($employees);
        return view('bulk-sms.bulksms', [
            'employees' => $employees->orderBy('id')->get()
        ]);
    }

    public function bulk()
    {
        $employees = User::active_employees()->get();
        return view('bulk-sms.bulksms', compact('employees'));
    }

    public function send_employee(Request $request)
    {
        $selectedId = $request->users;
        return view('bulk-sms.message', compact('selectedId'));
    }

    public function send_message(Request $request)
    {
        $selectedId = $request->id;
        // dd($request->message);
        $message = $request->message;
        $employee = User::find($selectedId);
        SendBulkSMS::dispatch($employee, $message);
    }

    public function showForm(Request $request)
    {
        $message = $request->textTemplate;
        $data = Setting::where('key', 'fine_sms_formating')->first();
        if ($data) {
            Setting::where('key', 'fine_sms_formating')->update([
                'value' => $message,
            ]);
        } else {
            Setting::create([
                'key' => 'fine_sms_formating',
                'value' => $message,
            ]);
        }

        return redirect()->back();
    }

    public function sms_format_list(Request $request)
    {
        return view('bulk-sms.listformated',);
    }

    public function fine_format(Request $request)
    {
        return view('bulk-sms.format');
    }

    public function warning_format()
    {
        return view('bulk-sms.format_warning');
    }
    public function warning_format_store(Request $request)
    {
        $message = $request->textTemplate;
        $data = Setting::where('key', 'warning_sms_formating')->first();
        if ($data) {
            Setting::where('key', 'warning_sms_formating')->update([
                'value' => $message,
            ]);
        } else {
            Setting::create([
                'key' => 'warning_sms_formating',
                'value' => $message,
            ]);
        }

        return redirect()->back();
    }

    public function leav_appliccation_format()
    {
        return view('bulk-sms.format_leavapplication');
    }

    public function leave_application_format_store(Request $request)
    {
        $message = $request->textTemplate;
        $data = Setting::where('key', 'leave_application_formating')->first();
        if ($data) {
            Setting::where('key', 'leave_application_formating')->update([
                'value' => $message,
            ]);
        } else {
            Setting::create([
                'key' => 'leave_application_formating',
                'value' => $message,
            ]);
        }

        return redirect()->back();
    }

    public function loan_application_format()
    {
        return view('bulk-sms.format_loanapplication');
    }

    public function loan_application_format_store(Request $request)
    {
        $message = $request->textTemplate;
        $data = Setting::where('key', 'loan_sms_formating')->first();
        if ($data) {
            Setting::where('key', 'loan_sms_formating')->update([
                'value' => $message,
            ]);
        } else {
            Setting::create([
                'key' => 'loan_sms_formating',
                'value' => $message,
            ]);
        }

        return redirect()->back();
    }

    public function promotion_format()
    {
        return view('bulk-sms.format_promotion');
    }

    public function promotion_format_store(Request $request)
    {
        $message = $request->textTemplate;
        $data = Setting::where('key', 'promotion_sms_formating')->first();
        if ($data) {
            Setting::where('key', 'promotion_sms_formating')->update([
                'value' => $message,
            ]);
        } else {
            Setting::create([
                'key' => 'promotion_sms_formating',
                'value' => $message,
            ]);
        }

        return redirect()->back();
    }

    public function rewards_format()
    {
        return view('bulk-sms.format_rewards');
    }

    public function rewards_format_store(Request $request)
    {
        $message = $request->textTemplate;
        $data = Setting::where('key', 'rewards_sms_formating')->first();
        if ($data) {
            Setting::where('key', 'rewards_sms_formating')->update([
                'value' => $message,
            ]);
        } else {
            Setting::create([
                'key' => 'rewards_sms_formating',
                'value' => $message,
            ]);
        }

        return redirect()->back();
    }
    public function student_check_in_format()
    {
        return view('bulk-sms.format_student_check_in');
    }

    public function student_check_in_format_store(Request $request)
    {
        $message = $request->textTemplate;
        $data = Setting::where('key', 'student_check_in_sms_formating')->first();
        if ($data) {
            Setting::where('key', 'student_check_in_sms_formating')->update([
                'value' => $message,
            ]);
        } else {
            Setting::create([
                'key' => 'student_check_in_sms_formating',
                'value' => $message,
            ]);
        }

        return redirect()->back();
    }
    public function student_check_out_format()
    {
        return view('bulk-sms.format_student_check_out');
    }

    public function student_check_out_format_store(Request $request)
    {
        $message = $request->textTemplate;
        $data = Setting::where('key', 'student_check_out_sms_formating')->first();
        if ($data) {
            Setting::where('key', 'student_check_out_sms_formating')->update([
                'value' => $message,
            ]);
        } else {
            Setting::create([
                'key' => 'student_check_out_sms_formating',
                'value' => $message,
            ]);
        }

        return redirect()->back();
    }

    function api(Request $request)
    {
        if ($request->send_phone) {
            if (SMSApi::send($request->send_phone, "Test SMS from ProHR"))
                Toastr::success("Test SMS sent successfully");
            else
                Toastr::error("Test SMS failed! Check history for more info.");
            return redirect()->back();
        }

        return view('bulk-sms.api', [
            'api' => Setting::sms_api_config(),
            'use_custom_api' => Setting::use_custom_sms_api()
        ]);
    }
    function api_save(Request $request)
    {
        if ($request->use_custom_api) {
            Setting::use_custom_sms_api($request->val != 'on');
            Toastr::success("API settings saved successfully");
            return redirect()->back();
        }
        // dd($request->all());
        $data = $request->validate([
            'url' => 'required|min:1|max:50',
            'type' => 'required|in:GET,POST',
            'header_keys' => 'array',
            'header_values' => 'array',
            'body_keys' => 'array',
            'body_values' => 'array',
        ]);
        $data['headers'] = [];
        foreach ($data['header_keys'] ?? [] as $key => $value) {
            $data['headers'][$value] = $data['header_values'][$key];
        }
        $data['body'] = [];
        foreach ($data['body_keys'] ?? [] as $key => $value) {
            $data['body'][$value] = $data['body_values'][$key];
        }
        Setting::sms_api_config($data);
        Toastr::success('API settings saved successfully');
        return redirect()->back();
    }
    function api_usage(Request $request)
    {
        return ["limit" => 0,"used" => 0];
    }
}
