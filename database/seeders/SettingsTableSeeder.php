<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            ['key' => 'warning_sms_formating', 'value' => "Dear [Company] Employee, on [Date] you were [TimeFormatted] late. \n[DMD]"],

            ['key' => 'fine_sms_formating', 'value' => "Dear [Company]  Employee, [Name] you are fined with Tk. [Amount] on [Time] for [Reason]. \nThank you!"],

            ['key' => 'leave_application_formating', 'value' => " Dear [Company] Employee, [Name] your leave application [Date] has been [Decision]. \nThank you."],

            ['key' => 'loan_sms_formating', 'value' => " Dear [Company] Employee, [Name] your loan of Tk [Amount] (applied on [Date]) has been [Approved]."],

            ['key' => 'promotion_sms_formating', 'value' => " We are pleased to inform you that your recommendation for [Name] is promoted to [Designation].\n Thank you for your support and dedication to fostering a culture of excellence within the team."],

            ['key' => 'rewards_sms_formating', 'value' => " Dear [Company]  Employee, [Name] you are rewarded with Tk. [Amount] on [Time] for [Reason].\n Thank you!"]

        ];

        DB::table('settings')->insert($settings);
    }
}
