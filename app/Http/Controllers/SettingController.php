<?php

namespace App\Http\Controllers;

use App\Gates\User;
use App\Models\AllowedPunchRegion;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\RolesPermissions;
use App\Models\Setting;
use Brian2694\Toastr\Facades\Toastr;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;

use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{

    public function __construct()
    {
        if (!File::isDirectory(public_path('uploaded_files/company'))) {
            File::makeDirectory(public_path('uploaded_files/company'), 0755, true);
        }
    }

    // company/settings/page
    public function attendance()
    {
        return view('settings.attendance', [
            'overtime_policy_configuration' => Setting::overtime_policy_configuration(),
            'auto_over_time_policy' => Setting::auto_over_time_policy(),
            'attendance_auto_checkout' => Setting::attendance_auto_checkout(),
            'attendance_auto_checkout_time' => Setting::attendance_auto_checkout_time(),
            'attendance_auto_checkout_run_at' => Setting::attendance_auto_checkout_run_at(),
            'attendance_auto_checkout_day' => Setting::attendance_auto_checkout_day(),
            'attendance_ignore_in_between_punches' => Setting::attendance_ignore_in_between_punches(),
            'can_company_in_out_sms_notification' => Setting::can_company_in_out_sms_notification(),
            'can_in_out_sms_notification' => Setting::can_in_out_sms_notification(),
            'can_company_summary_sms_notification' => Setting::can_company_summary_sms_notification(),
            'can_summary_sms_notification' => Setting::can_summary_sms_notification(),
            'check_in_before_shift_time_config' => Setting::check_in_before_shift_time_config(),
            'check_in_before_shift_time' => Setting::check_in_before_shift_time(),
            'block_auto_inout_state' => Setting::block_auto_inout_state(),
            'duplicate_punch_configuration' => Setting::duplicate_punch_configuration(),
            'duplicate_punch_minutes' => Setting::duplicate_punch_minutes(),
            'delete_attendnace_upon_late' => Setting::delete_attendnace_upon_late(),
            'delete_attendnace_upon_late_minutes' => Setting::delete_attendnace_upon_late_minutes(),
        ]);
    }
    public function attendance_location()
    {

        return view('settings.attendance-location', [
            'location' => Setting::attendance_location(),
            'restrict_attendance_location' => Setting::restrict_attendance_location(),
            'regions' => AllowedPunchRegion::query()
                ->orderBy('name')
                ->paginate(10),
        ]);
    }
    public function attendance_update(Request $request)
    {
        // dd($request->all());
        $key = $request->key;
        if ($key === 'restrict_attendance_location') {
            Setting::restrict_attendance_location($request->value == 1);
        }

        if ($key === 'attendance_ignore_in_between_punches') {
            Setting::attendance_ignore_in_between_punches(!!$request->value);
        }
        if ($key === 'block_auto_inout_state') {
            Setting::block_auto_inout_state(!!$request->value);
        }
        if ($key === 'attendance_auto_checkout') {
            Setting::attendance_auto_checkout(!!$request->value);
        }
        if ($request->attendance_auto_checkout_time) {
            Setting::attendance_auto_checkout_time($request->attendance_auto_checkout_time);
        }
        if ($request->attendance_auto_checkout_run_at) {
            Setting::attendance_auto_checkout_run_at($request->attendance_auto_checkout_run_at);
        }
        if ($request->attendance_auto_checkout_day) {
            Setting::attendance_auto_checkout_day($request->attendance_auto_checkout_day);
        }
        if ($key == 'can_company_in_out_sms_notification') {
            Setting::can_company_in_out_sms_notification($request->value == 1);
        }
        if ($key == 'can_in_out_sms_notification') {
            Setting::can_in_out_sms_notification($request->value == 1);
        }
        if ($key == 'can_company_summary_sms_notification') {
            Setting::can_company_summary_sms_notification($request->value == 1);
        }
        if ($key == 'can_summary_sms_notification') {
            Setting::can_summary_sms_notification($request->value == 1);
        }
        if ('duplicate_punch_configuration' == $key) {
            Setting::duplicate_punch_configuration(!!$request->value);
        }
        if ($request->duplicate_punch_minutes) {
            Setting::duplicate_punch_minutes($request->duplicate_punch_minutes);
        }
        if ('overtime_policy_configuration' == $key) {
            Setting::overtime_policy_configuration(!!$request->value);
        }
        if ($request->auto_over_time_policy) {
            Setting::auto_over_time_policy($request->auto_over_time_policy);
        }
        if ('check_in_before_shift_time_config' == $key) {
            Setting::check_in_before_shift_time_config(!!$request->value);
        }
        if ($request->check_in_before_shift_time) {
            Setting::check_in_before_shift_time($request->check_in_before_shift_time);
        }
        if ($request->has('delete_attendnace_upon_late_minutes')) {
            Setting::delete_attendnace_upon_late_minutes($request->delete_attendnace_upon_late_minutes);
        }
        if ('delete_attendnace_upon_late' == $key) {
            Setting::delete_attendnace_upon_late(!!$request->value);
        }

        return redirect()->back()->with('success', 'Attendance settings updated successfully');
    }

    function companySettings()
    {
        abort_if(!role_can('company.edit'), 403, "Please get permission from Administrator");
        $company = Setting::where('key', 'company')->first();
        if ($company) {
            $info = json_decode($company->value, true);
        }
        return view('settings.company', [
            'info' => $info ?? []
        ]);
    }
    function companySettingsSave(Request $request)
    {
        abort_if(!role_can('company.edit'), 403, "Please get permission from Administrator");
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'email' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'website_url' => 'nullable|string|max:255',
            'image_base64' => 'nullable', // 10mb
            'favicon' => 'nullable|file|extensions:jpg,jpeg,png,gif,svg',
        ]);


        if ($request->hasFile('favicon')) {
            if (company_data()['favicon'] ?? null) {
                Storage::delete(company_data()['favicon']);
            }

            $data['favicon'] = Storage::put('company', $request->file('favicon'));
        } else {
            $data['favicon'] = company_data()['favicon'] ?? '';
        }


        if ($request->image_base64) {
            if (company_data()['logo'] ?? null) {
                Storage::delete(company_data()['logo']);
            }
            $data['logo'] = $this->storeBase64($request->image_base64);
        } else {
            $data['logo'] = company_data()['logo'] ?? '';
        }
        // $data['logo'] = $this->storeBase64($request->image_base64);


        // $data['logo'] = $this->storeBase64($request->image_base64) ?? '';

        $company = Setting::where('key', 'company')->first();
        if (!$company) {
            $company = Setting::create([
                'key' => 'company',
                'value' => json_encode([])
            ]);
        }
        $company->update([
            'value' => json_encode($data)
        ]);
        Toastr::success('Company settings updated successfully :)', 'Success');
        return redirect()->back();
    }

    private function storeBase64($imageBase64)

    {

        list($type, $imageBase64) = explode(';', string: $imageBase64);

        list(, $imageBase64)      = explode(',', $imageBase64);

        $imageBase64 = base64_decode($imageBase64);

        $imageName = time() . '.png';

        $path = Storage::path('company/' . $imageName);
        // $path = public_path() . "/company/" . $imageName;


        file_put_contents($path, $imageBase64);


        return "company/" . $imageName;
    }

    //---------------------------------ui setting part----------------------------------//
    function uiSettingInfo()
    {
        // abort_if(!role_can('company.edit'), 403, "Please get permission from Administrator");
        $ui_setting = Setting::where('key', 'ui_setting')->first();
        if ($ui_setting) {
            $info = json_decode($ui_setting->value, true);
        }
        return view('settings.ui_setting', [
            'info' => $info ?? []
        ]);
    }

    function uiSettingUpdate(Request $request)
    {
        // abort_if(!role_can('company.edit'), 403, "Please get permission from Administrator");
        $data = $request->validate([
            'sidebar_background_color' => 'required',
            'navbar_background_color_left' => 'required',
            'navbar_background_color_right' => 'required',
        ]);
        $company = Setting::where('key', 'ui_setting')->first();
        if (!$company) {
            $company = Setting::create([
                'key' => 'ui_setting',
                'value' => json_encode([])
            ]);
        }
        $company->update([
            'value' => json_encode($data)
        ]);
        Toastr::success('UI settings updated successfully :)', 'Success');
        return redirect()->back();
    }

    function uiSettingDelete()
    {
        $company = Setting::where('key', 'ui_setting')->first();
        if ($company) {
            $company->delete();
        }
        Toastr::success('UI settings reset successfully :)', 'Success');
        return redirect()->back();
    }
    //---------------------------------ui setting part----------------------------------//
    function min_late_minutes(Request $request)
    {
        $request->validate([
            'limit' => 'numeric|max:120'
        ]);
        $sett = Setting::where('key', 'reports.late.min_late_minutes')->first();
        if (!$sett) {
            Setting::create([
                'key' => 'reports.late.min_late_minutes',
                'value' => $request->limit ?? 0
            ]);
        } else {
            $sett->update([
                'value' => $request->limit ?? 0
            ]);
        }
        Toastr::success('Late minutes updated successfully :)', 'Success');
        return redirect()->back();
    }

    public function interests()
    {
        abort_if(!role_can('settings'), 403, "Please get permission from Administrator");
        return view('settings.interests', [
            'loan_have_interests' => Setting::loan_have_interests(),
            'loan_interest_rate' => Setting::loan_interest_rate(),
            'loan_interest_type' => Setting::loan_interest_type(),
            'provident_fund_have_interests' => Setting::provident_fund_have_interests(),
            'provident_fund_interest_rate' => Setting::provident_fund_interest_rate(),
        ]);
    }
    public function interests_update(Request $request)
    {
        abort_if(!role_can('settings'), 403, "Please get permission from Administrator");
        if ($request->key == 'loan_have_interests') {
            Setting::loan_have_interests($request->value == 1);
        }
        if ($request->loan_interest_rate) {
            Setting::loan_interest_rate($request->loan_interest_rate);
        }
        if ($request->loan_interest_type) {
            Setting::loan_interest_type($request->loan_interest_type);
        }
        if ($request->key == 'provident_fund_have_interests') {
            Setting::provident_fund_have_interests($request->value == 1);
        }
        if ($request->provident_fund_interest_rate) {
            Setting::provident_fund_interest_rate($request->provident_fund_interest_rate);
        }

        return redirect()->back()->with('success', 'Interests settings updated successfully');
    }
}
