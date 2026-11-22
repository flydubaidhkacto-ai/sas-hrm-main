<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $guarded = [];

    static function can_everyone_special_overtime($val = null)
    {
        if ($val !== null) {
            if ($val) self::firstOrCreate(['key' => 'can_everyone_special_overtime', 'value' => 1]);
            else self::where('key', 'can_everyone_special_overtime')->delete();
        }
        return !!self::where('key', 'can_everyone_special_overtime')->first();
    }
    static function can_everyone_overtime()
    {
        return !!self::where('key', 'can_everyone_overtime')->first();
    }

    static function overtime_policy_configuration(?bool $new_val = null): bool
    {
        if ($new_val !== null) {
            if ($new_val) self::firstOrCreate(['key' => 'overtime_policy_configuration', 'value' => 1]);
            else self::where('key', 'overtime_policy_configuration')->delete();
        }
        return !!self::where('key', 'overtime_policy_configuration')->first();
    }
    static function duplicate_punch_configuration(?bool $new_val = null): bool
    {
        if ($new_val !== null) {
            if ($new_val) self::firstOrCreate(['key' => 'duplicate_punch_configuration', 'value' => 1]);
            else self::where('key', 'duplicate_punch_configuration')->delete();
        }
        return !!self::where('key', 'duplicate_punch_configuration')->first();
    }

    public static function check_in_before_shift_time_config(?bool $new_val = null): bool
    {
        if ($new_val !== null) {
            if ($new_val) self::firstOrCreate(['key' => 'check_in_before_shift_time_config', 'value' => 1]);
            else self::where('key', 'check_in_before_shift_time_config')->delete();
        }
        return !!self::where('key', 'check_in_before_shift_time_config')->first();
    }

    public static function check_in_before_shift_time(?string $new_val = null): string
    {
        if ($new_val !== null) {
            $check = self::where('key', 'check_in_before_shift_time')->first();
            if ($check) $check->update(['value' => $new_val]);
            else self::firstOrCreate(['key' => 'check_in_before_shift_time', 'value' => $new_val]);
        }
        return self::where('key', 'check_in_before_shift_time')->first()?->value ?: '';
    }

    static function auto_over_time_policy(?string $new_val = null): string
    {
        if ($new_val !== null) {
            $check = self::where('key', 'auto_over_time_policy')->first();
            if ($check) $check->update(['value' => $new_val]);
            else self::firstOrCreate(['key' => 'auto_over_time_policy', 'value' => $new_val]);
        }
        return self::where('key', 'auto_over_time_policy')->first()?->value ?: '';
    }
    static function duplicate_punch_minutes(?string $new_val = null): int
    {
        if ($new_val !== null) {
            $check = self::where('key', 'duplicate_punch_minutes')->first();
            if ($check) $check->update(['value' => $new_val]);
            else self::firstOrCreate(['key' => 'duplicate_punch_minutes', 'value' => $new_val]);
        }
        return self::where('key', 'duplicate_punch_minutes')->first()?->value ?: 0;
    }

    static function update_can_everyone_overtime($can)
    {
        if ($can) self::firstOrCreate(['key' => 'can_everyone_overtime', 'value' => 1]);
        else self::where('key', 'can_everyone_overtime')->delete();
    }

    static function attendance_ignore_in_between_punches(?bool $new_val = null): bool
    {
        if ($new_val !== null) {
            if ($new_val) self::firstOrCreate(['key' => 'attendance_ignore_in_between_punches', 'value' => 1]);
            else self::where('key', 'attendance_ignore_in_between_punches')->delete();
        }
        return !!self::where('key', 'attendance_ignore_in_between_punches')->first();
    }
    static function attendance_auto_checkout(?bool $new_val = null): bool
    {
        if ($new_val !== null) {
            if ($new_val) self::firstOrCreate(['key' => 'attendance_auto_checkout', 'value' => 1]);
            else self::where('key', 'attendance_auto_checkout')->delete();
        }
        return !!self::where('key', 'attendance_auto_checkout')->first();
    }

    static function attendance_auto_checkout_time(?string $new_val = null): string
    {
        if ($new_val !== null) {
            $check = self::where('key', 'attendance_auto_checkout_time')->first();
            if ($check) $check->update(['value' => $new_val]);
            else self::firstOrCreate(['key' => 'attendance_auto_checkout_time', 'value' => $new_val]);
        }
        return self::where('key', 'attendance_auto_checkout_time')->first()?->value ?: '';
    }
    static function attendance_auto_checkout_run_at(?string $new_val = null): string
    {
        if ($new_val !== null) {
            $check = self::where('key', 'attendance_auto_checkout_run_at')->first();
            if ($check) $check->update(['value' => $new_val]);
            else self::firstOrCreate(['key' => 'attendance_auto_checkout_run_at', 'value' => $new_val]);
        }
        return self::where('key', 'attendance_auto_checkout_run_at')->first()?->value ?: '';
    }
    static function attendance_auto_checkout_day(?string $new_val = null): string
    {
        if ($new_val !== null) {
            $check = self::where('key', 'attendance_auto_checkout_day')->first();
            if ($check) $check->update(['value' => $new_val]);
            else self::firstOrCreate(['key' => 'attendance_auto_checkout_day', 'value' => $new_val]);
        }
        return self::where('key', 'attendance_auto_checkout_day')->first()?->value ?: '';
    }

    static function fine_sms()
    {
        return !!self::where('key', 'fine_sms_formating')->first();
    }

    static function warning_sms()
    {
        return !!self::where('key', 'warning_sms_formating')->first();
    }

    static function loan_sms()
    {
        return !!self::where('key', 'loan_sms_formating')->first();
    }

    static function get_fine_sms_formating()
    {
        return self::where('key', 'fine_sms_formating')->first()?->value ?: "Dear [Company]  Employee, [Name] you are fined with Tk. [Amount] on [Time] for [Reason]. \n Thank you!";
    }
    static function get_warning_sms_formating()
    {
        return self::where('key', 'warning_sms_formating')->first()?->value ?: "Dear [Company] Employee, on [Date] you were [TimeFormatted] late. \n[DMD]";
    }
    static function get_rewards_sms_formating()
    {
        return self::where('key', 'rewards_sms_formating')->first()?->value ?: "Dear [Company]  Employee, [Name] you are rewarded with Tk. [Amount] on [Time] for [Reason]. \nThank you!";
    }
    static function get_student_check_in_sms_formating()
    {
        return self::where('key', 'student_check_in_sms_formating')->first()?->value ?: "Dear guardian of [Name], your child checked in at [Time]. \nThank you!";
    }
    static function get_student_check_out_sms_formating()
    {
        return self::where('key', 'student_check_out_sms_formating')->first()?->value ?: "Dear guardian of [Name], your child checked out at [Time]. \nThank you!";
    }
    static function get_promotion_sms_formating()
    {
        return self::where('key', 'promotion_sms_formating')->first()?->value ?: "We are pleased to inform you that your recommendation for [Name] is promoted to [Designation]. \nThank you for your support and dedication to fostering a culture of excellence within the team.";
    }
    static function get_loan_sms_formating()
    {
        return self::where('key', 'loan_sms_formating')->first()?->value ?: "Dear [Company] Employee, [Name] your loan of Tk [Amount] (applied on [Date]) has been [Approved].";
    }
    static function get_leave_application_formating()
    {
        return self::where('key', 'leave_application_formating')->first()?->value ?: "Dear [Company] Employee, [Name] your leave application [Date] has been [Decision]. \nThank you.";
    }
    static function sms_api_config(?array $data = null): array
    {
        if ($data) {
            $find = Setting::where('key', 'sms_api_config')->first();
            if (!$find) $find = Setting::firstOrCreate(['key' => 'sms_api_config', 'value' => json_encode($data)]);
            else $find->update(['value' => json_encode($data)]);
            return $data;
        }
        return json_decode(Setting::where('key', 'sms_api_config')->first()?->value, true) ?: [
            'not_set' => true,
            'url' => "http://api.smsglobal.org/v1/send-sms",
            "type" => "POST",
            "headers" => [],
            "body" => [],
        ];
    }
    static function can_company_in_out_sms_notification(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'can_company_in_out_sms_notification', 'value' => $value]);
            else
                self::where('key', 'can_company_in_out_sms_notification')->delete();
            return $value;
        }
        $check = self::where('key', 'can_company_in_out_sms_notification')->first();
        return !!$check;
    }
    static function can_in_out_sms_notification(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'can_in_out_sms_notification', 'value' => $value]);
            else
                self::where('key', 'can_in_out_sms_notification')->delete();
            return $value;
        }
        $check = self::where('key', 'can_in_out_sms_notification')->first();
        return !!$check;
    }
    static function can_company_summary_sms_notification(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'can_company_summary_sms_notification', 'value' => $value]);
            else
                self::where('key', 'can_company_summary_sms_notification')->delete();
            return $value;
        }
        $check = self::where('key', 'can_company_summary_sms_notification')->first();
        return !!$check;
    }
    static function can_summary_sms_notification(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'can_summary_sms_notification', 'value' => $value]);
            else
                self::where('key', 'can_summary_sms_notification')->delete();
            return $value;
        }
        $check = self::where('key', 'can_summary_sms_notification')->first();
        return !!$check;
    }
    static function use_custom_sms_api(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'use_custom_sms_api', 'value' => $value]);
            else
                self::where('key', 'use_custom_sms_api')->delete();
            return $value;
        }
        $check = self::where('key', 'use_custom_sms_api')->first();
        return !!$check;
    }

    static function loan_have_interests(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'loan_have_interests', 'value' => $value]);
            else
                self::where('key', 'loan_have_interests')->delete();
            return $value;
        }
        $check = self::where('key', 'loan_have_interests')->first();
        return !!$check;
    }
    static function loan_interest_rate(?float $value = null): float
    {
        if ($value !== null) {
            $check = self::where('key', 'loan_interest_rate')->first();
            if ($check) $check->update(['value' => $value]);
            else self::create(['key' => 'loan_interest_rate', 'value' => $value]);
        }
        return self::where('key', 'loan_interest_rate')->first()?->value ?: 0;
    }
    /**
     * Loan interest type
     * @param string|null $value 'fixed' | 'reducing'
     * @return string 'fixed' | 'reducing'
     */
    static function loan_interest_type(?string $value = null): string
    {
        if ($value !== null && in_array($value, ['fixed', 'reducing'])) {
            $check = self::where('key', 'loan_interest_type')->first();
            if ($check) $check->update(['value' => $value]);
            else self::create(['key' => 'loan_interest_type', 'value' => $value]);
        }
        return self::where('key', 'loan_interest_type')->first()?->value ?: 'fixed';
    }
    static function provident_fund_have_interests(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'provident_fund_have_interests', 'value' => $value]);
            else
                self::where('key', 'provident_fund_have_interests')->delete();
            return $value;
        }
        $check = self::where('key', 'provident_fund_have_interests')->first();
        return !!$check;
    }
    static function provident_fund_interest_rate(?float $value = null): float
    {
        if ($value !== null) {
            $check = self::where('key', 'provident_fund_interest_rate')->first();
            if ($check) $check->update(['value' => $value]);
            else self::create(['key' => 'provident_fund_interest_rate', 'value' => $value]);
        }
        $val = self::where('key', 'provident_fund_interest_rate')->first()?->value ?: 0;
        // dd($val);
        return $val < 0 || !is_numeric($val) ? 0 : $val;
    }
    static function restrict_attendance_location(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'restrict_attendance_location', 'value' => $value]);
            else
                self::where('key', 'restrict_attendance_location')->delete();
            return $value;
        }
        $check = self::where('key', 'restrict_attendance_location')->first();
        return !!$check;
    }
    static function attendance_location($data = null)
    {
        $get = self::query()->where('key', 'attendance_location')->first();
        if (!$get) $get = self::create(['key' => 'attendance_location', 'value' => '[]']);
        if ($data) $get->update(['value' => json_encode($data)]);
        $values = @json_decode($get->value, true);

        return [
            'latitude' => $values['latitude'] ?? 23.816013,
            'longitude' => $values['longitude'] ?? 90.364017,
            'radius' => $values['radius'] ?? 500,
            'name' => $values['name'] ?? null,
        ];
    }
    static function emp_leave_balance($emp_id, $type_id, $year, $val = null): int|float
    {
        $key = "emp_leave_balance_{$emp_id}_{$type_id}_{$year}";
        $find = self::where('key', $key)->first();
        if (is_numeric($val)) {
            if (!$find)
                $find = self::create(['key' => $key, 'value' => $val]);
            else
                $find->update(['value' => $val]);
        }

        if (!$find) {
            if (!$emp_id)
                return 0;
            else
                return self::emp_leave_balance(0, $type_id, $year);
        }
        return ($find?->value) ?: 0;
    }

    static function enabled_one_earn_leave_after_each_weekend(?bool $value = null): bool
    {
        if ($value !== null) {
            if ($value)
                self::create(['key' => 'enabled_one_earn_leave_after_each_weekend', 'value' => $value]);
            else
                self::where('key', 'enabled_one_earn_leave_after_each_weekend')->delete();
            return $value;
        }
        $check = self::where('key', 'enabled_one_earn_leave_after_each_weekend')->first();
        return !!$check;
    }
    static function one_earn_leave_after_each_weekend(?string $value = null): string
    {
        if ($value !== null) {
            $check = self::where('key', 'one_earn_leave_after_each_weekend')->first();
            if ($check) $check->update(['value' => $value]);
            else self::create(['key' => 'one_earn_leave_after_each_weekend', 'value' => $value]);
        }
        return self::where('key', 'one_earn_leave_after_each_weekend')->first()?->value ?: 'friday';
    }
    static function block_auto_inout_state(?bool $new_val = null): bool
    {
        if ($new_val !== null) {
            if ($new_val) self::firstOrCreate(['key' => 'block_auto_inout_state', 'value' => 1]);
            else self::where('key', 'block_auto_inout_state')->delete();
        }
        return !!self::where('key', 'block_auto_inout_state')->first();
    }
    static function delete_attendnace_upon_late(?bool $new_val = null): bool
    {
        if ($new_val !== null) {
            if ($new_val) self::firstOrCreate(['key' => 'delete_attendnace_upon_late', 'value' => 1]);
            else self::where('key', 'delete_attendnace_upon_late')->delete();
        }
        return !!self::where('key', 'delete_attendnace_upon_late')->first();
    }
    static function delete_attendnace_upon_late_minutes(?int $value = null): int
    {
        if ($value !== null) {
            $check = self::where('key', 'delete_attendnace_upon_late_minutes')->first();
            if ($check) $check->update(['value' => $value]);
            else self::create(['key' => 'delete_attendnace_upon_late_minutes', 'value' => $value]);
        }
        return self::where('key', 'delete_attendnace_upon_late_minutes')->first()?->value ?? 10;
    }
}
