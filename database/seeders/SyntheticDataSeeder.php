<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SyntheticDataSeeder extends Seeder
{
    /**
     * Run the database seeds — synthetic employee data for development only.
     */
    public function run(): void
    {
        // Prevent accidental production seeding
        if (config('app.env') === 'production') {
            $this->command->error('This seeder is for development only. Aborting.');
            return;
        }

        $this->command->info('Seeding synthetic development data...');

        $this->seedSuperDepartments();
        $this->seedDepartments();
        $this->seedDesignations();
        $this->seedShifts();
        $employeeStartId = $this->seedEmployees();
        $this->seedLeaveBalances($employeeStartId);

        $this->command->info('Synthetic data seeding completed.');
    }

    /* -----------------------------------------------------------------
       Super Departments
       ----------------------------------------------------------------- */
    private function seedSuperDepartments(): void
    {
        $superDepartments = [
            ['id' => 1, 'name' => 'Operations'],
            ['id' => 2, 'name' => 'Ground Handling'],
            ['id' => 3, 'name' => 'Corporate'],
        ];

        foreach ($superDepartments as $sd) {
            DB::table('super_departments')->updateOrInsert(
                ['id' => $sd['id']],
                ['name' => $sd['name'], 'description' => null, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /* -----------------------------------------------------------------
       Departments
       ----------------------------------------------------------------- */
    private function seedDepartments(): void
    {
        $departments = [
            ['id' => 1, 'name' => 'Flight Operations', 'super_department_id' => 1],
            ['id' => 2, 'name' => 'Ramp Services', 'super_department_id' => 2],
            ['id' => 3, 'name' => 'Customer Service', 'super_department_id' => 2],
            ['id' => 4, 'name' => 'Human Resources', 'super_department_id' => 3],
            ['id' => 5, 'name' => 'Finance & Accounts', 'super_department_id' => 3],
            ['id' => 6, 'name' => 'IT Support', 'super_department_id' => 3],
            ['id' => 7, 'name' => 'Cargo Handling', 'super_department_id' => 2],
            ['id' => 8, 'name' => 'Safety & Compliance', 'super_department_id' => 1],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->updateOrInsert(
                ['id' => $dept['id']],
                [
                    'name' => $dept['name'],
                    'description' => null,
                    'super_department_id' => $dept['super_department_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /* -----------------------------------------------------------------
       Designations
       ----------------------------------------------------------------- */
    private function seedDesignations(): void
    {
        $designations = [
            ['id' => 1, 'name' => 'General Manager'],
            ['id' => 2, 'name' => 'Deputy Manager'],
            ['id' => 3, 'name' => 'Senior Officer'],
            ['id' => 4, 'name' => 'Officer'],
            ['id' => 5, 'name' => 'Senior Executive'],
            ['id' => 6, 'name' => 'Executive'],
            ['id' => 7, 'name' => 'Supervisor'],
            ['id' => 8, 'name' => 'Team Leader'],
            ['id' => 9, 'name' => 'Ramp Agent'],
            ['id' => 10, 'name' => 'Customer Service Agent'],
            ['id' => 11, 'name' => 'Cargo Handler'],
            ['id' => 12, 'name' => 'IT Support Engineer'],
            ['id' => 13, 'name' => 'Accountant'],
            ['id' => 14, 'name' => 'HR Coordinator'],
            ['id' => 15, 'name' => 'Safety Officer'],
        ];

        foreach ($designations as $des) {
            DB::table('designations')->updateOrInsert(
                ['id' => $des['id']],
                ['name' => $des['name'], 'description' => null, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /* -----------------------------------------------------------------
       Shifts
       ----------------------------------------------------------------- */
    private function seedShifts(): void
    {
        $shifts = [
            [
                'id' => 1,
                'name' => 'Day Shift',
                'start' => '09:00:00',
                'end' => '18:00:00',
                'break_time_minutes' => 60,
                'can_overtime' => true,
            ],
            [
                'id' => 2,
                'name' => 'Morning Shift',
                'start' => '06:00:00',
                'end' => '14:00:00',
                'break_time_minutes' => 45,
                'can_overtime' => true,
            ],
            [
                'id' => 3,
                'name' => 'Evening Shift',
                'start' => '14:00:00',
                'end' => '22:00:00',
                'break_time_minutes' => 60,
                'can_overtime' => true,
            ],
            [
                'id' => 4,
                'name' => 'Night Shift',
                'start' => '22:00:00',
                'end' => '06:00:00',
                'break_time_minutes' => 30,
                'can_overtime' => false,
            ],
        ];

        foreach ($shifts as $shift) {
            DB::table('shifts')->updateOrInsert(
                ['id' => $shift['id']],
                array_merge($shift, [
                    'sunday' => false,
                    'monday' => true,
                    'tuesday' => true,
                    'wednesday' => true,
                    'thursday' => true,
                    'friday' => true,
                    'saturday' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /* -----------------------------------------------------------------
       Employees
       ----------------------------------------------------------------- */
    private function seedEmployees(): int
    {
        $startId = DB::table('users')->max('id') ?? 0;

        $employees = [
            // Flight Operations
            [
                'employee_id' => 'EMP001', 'name' => 'Rafiq Ahmed', 'email' => 'rafik.ahmed@dev.test',
                'phone_number' => '+8801711111111', 'department_id' => 1, 'designation_id' => 2,
                'super_department_id' => 1, 'join_date' => '2022-03-15', 'birth_date' => '1990-05-12',
                'gender' => 'male', 'blood_group' => 'A+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Married', 'employee_type' => 'Permanent',
                'father_name' => 'Abdul Ahmed', 'mother_name' => 'Fatema Ahmed',
                'address' => 'House 12, Road 5, Banani, Dhaka 1213',
                'nid_or_passport' => 'NID-1234567890',
            ],
            [
                'employee_id' => 'EMP002', 'name' => 'Nasreen Akter', 'email' => 'nasreen.akter@dev.test',
                'phone_number' => '+8801711111112', 'department_id' => 1, 'designation_id' => 4,
                'super_department_id' => 1, 'join_date' => '2023-01-10', 'birth_date' => '1995-08-20',
                'gender' => 'female', 'blood_group' => 'B+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Permanent',
                'father_name' => 'Karim Akter', 'mother_name' => 'Salma Akter',
                'address' => 'House 8, Road 12, Dhanmondi, Dhaka 1205',
                'nid_or_passport' => 'NID-1234567891',
            ],
            // Ramp Services
            [
                'employee_id' => 'EMP003', 'name' => 'Md. Kamal Hossain', 'email' => 'kamal.hossain@dev.test',
                'phone_number' => '+8801711111113', 'department_id' => 2, 'designation_id' => 7,
                'super_department_id' => 2, 'join_date' => '2021-06-01', 'birth_date' => '1988-11-03',
                'gender' => 'male', 'blood_group' => 'O+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Married', 'employee_type' => 'Permanent',
                'father_name' => 'Hossain Ali', 'mother_name' => 'Rashida Begum',
                'address' => 'House 45, Sector 7, Uttara, Dhaka 1230',
                'nid_or_passport' => 'NID-1234567892',
            ],
            [
                'employee_id' => 'EMP004', 'name' => 'Jahangir Alam', 'email' => 'jahangir.alam@dev.test',
                'phone_number' => '+8801711111114', 'department_id' => 2, 'designation_id' => 9,
                'super_department_id' => 2, 'join_date' => '2023-07-20', 'birth_date' => '1998-02-14',
                'gender' => 'male', 'blood_group' => 'AB+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Contract',
                'father_name' => 'Alamgir Hossain', 'mother_name' => 'Munira Begum',
                'address' => 'House 22, Road 3, Mirpur-1, Dhaka 1216',
                'nid_or_passport' => 'NID-1234567893',
            ],
            // Customer Service
            [
                'employee_id' => 'EMP005', 'name' => 'Tahmina Begum', 'email' => 'tahmina.begum@dev.test',
                'phone_number' => '+8801711111115', 'department_id' => 3, 'designation_id' => 10,
                'super_department_id' => 2, 'join_date' => '2022-09-05', 'birth_date' => '1996-04-18',
                'gender' => 'female', 'blood_group' => 'A-', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Permanent',
                'father_name' => 'Abdul Majed', 'mother_name' => 'Sultana Begum',
                'address' => 'House 15, Road 8, Gulshan-2, Dhaka 1212',
                'nid_or_passport' => 'NID-1234567894',
            ],
            [
                'employee_id' => 'EMP006', 'name' => 'Sumaiya Rahman', 'email' => 'sumaiya.rahman@dev.test',
                'phone_number' => '+8801711111116', 'department_id' => 3, 'designation_id' => 8,
                'super_department_id' => 2, 'join_date' => '2021-11-12', 'birth_date' => '1992-07-25',
                'gender' => 'female', 'blood_group' => 'O-', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Married', 'employee_type' => 'Permanent',
                'father_name' => 'Rahman Chowdhury', 'mother_name' => 'Parveen Sultana',
                'address' => 'House 30, Road 1, Banani, Dhaka 1213',
                'nid_or_passport' => 'NID-1234567895',
            ],
            // Human Resources
            [
                'employee_id' => 'EMP007', 'name' => 'Md. Shahidul Islam', 'email' => 'shahidul.islam@dev.test',
                'phone_number' => '+8801711111117', 'department_id' => 4, 'designation_id' => 3,
                'super_department_id' => 3, 'join_date' => '2020-04-01', 'birth_date' => '1987-01-30',
                'gender' => 'male', 'blood_group' => 'B-', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Married', 'employee_type' => 'Permanent',
                'father_name' => 'Abdul Islam', 'mother_name' => 'Nasrin Akter',
                'address' => 'House 7, Road 11, Dhanmondi, Dhaka 1209',
                'nid_or_passport' => 'NID-1234567896',
            ],
            [
                'employee_id' => 'EMP008', 'name' => 'Faria Islam', 'email' => 'faria.islam@dev.test',
                'phone_number' => '+8801711111118', 'department_id' => 4, 'designation_id' => 14,
                'super_department_id' => 3, 'join_date' => '2023-03-20', 'birth_date' => '1997-09-08',
                'gender' => 'female', 'blood_group' => 'AB-', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Permanent',
                'father_name' => 'Shamsul Islam', 'mother_name' => 'Roksana Akter',
                'address' => 'House 19, Road 6, Mohakhali, Dhaka 1212',
                'nid_or_passport' => 'NID-1234567897',
            ],
            // Finance & Accounts
            [
                'employee_id' => 'EMP009', 'name' => 'Abdul Matin', 'email' => 'abdul.matin@dev.test',
                'phone_number' => '+8801711111119', 'department_id' => 5, 'designation_id' => 13,
                'super_department_id' => 3, 'join_date' => '2021-08-15', 'birth_date' => '1991-12-05',
                'gender' => 'male', 'blood_group' => 'A+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Married', 'employee_type' => 'Permanent',
                'father_name' => 'Md. Matin', 'mother_name' => 'Hossain Ara',
                'address' => 'House 33, Road 10, Banani, Dhaka 1213',
                'nid_or_passport' => 'NID-1234567898',
            ],
            // IT Support
            [
                'employee_id' => 'EMP010', 'name' => 'Tanvir Hasan', 'email' => 'tanvir.hasan@dev.test',
                'phone_number' => '+8801711111120', 'department_id' => 6, 'designation_id' => 12,
                'super_department_id' => 3, 'join_date' => '2022-02-10', 'birth_date' => '1994-06-17',
                'gender' => 'male', 'blood_group' => 'O+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Permanent',
                'father_name' => 'Hasan Ali', 'mother_name' => 'Zahida Begum',
                'address' => 'House 5, Road 2, Uttara Sector 7, Dhaka 1230',
                'nid_or_passport' => 'NID-1234567899',
            ],
            // Cargo Handling
            [
                'employee_id' => 'EMP011', 'name' => 'Rashidul Haque', 'email' => 'rashidul.haque@dev.test',
                'phone_number' => '+8801711111121', 'department_id' => 7, 'designation_id' => 11,
                'super_department_id' => 2, 'join_date' => '2023-05-01', 'birth_date' => '1999-03-22',
                'gender' => 'male', 'blood_group' => 'B+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Contract',
                'father_name' => 'Abul Haque', 'mother_name' => 'Momin Begum',
                'address' => 'House 28, Road 4, Mirpur-10, Dhaka 1216',
                'nid_or_passport' => 'NID-1234567900',
            ],
            // Safety & Compliance
            [
                'employee_id' => 'EMP012', 'name' => 'Shakila Khatun', 'email' => 'shakila.khatun@dev.test',
                'phone_number' => '+8801711111122', 'department_id' => 8, 'designation_id' => 15,
                'super_department_id' => 1, 'join_date' => '2022-07-18', 'birth_date' => '1993-10-11',
                'gender' => 'female', 'blood_group' => 'A-', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Married', 'employee_type' => 'Permanent',
                'father_name' => 'Abdul Khatun', 'mother_name' => 'Jahangira Begum',
                'address' => 'House 11, Road 9, Dhanmondi, Dhaka 1205',
                'nid_or_passport' => 'NID-1234567901',
            ],
            // Additional employees for variety
            [
                'employee_id' => 'EMP013', 'name' => 'Imran Hossain', 'email' => 'imran.hossain@dev.test',
                'phone_number' => '+8801711111123', 'department_id' => 1, 'designation_id' => 6,
                'super_department_id' => 1, 'join_date' => '2024-01-08', 'birth_date' => '2000-01-15',
                'gender' => 'male', 'blood_group' => 'O+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Permanent',
                'father_name' => 'Ghulam Hossain', 'mother_name' => 'Ayesha Khatun',
                'address' => 'House 17, Road 14, Dhanmondi, Dhaka 1209',
                'nid_or_passport' => 'NID-1234567902',
            ],
            [
                'employee_id' => 'EMP014', 'name' => 'Nusrat Jahan', 'email' => 'nusrat.jahan@dev.test',
                'phone_number' => '+8801711111124', 'department_id' => 5, 'designation_id' => 6,
                'super_department_id' => 3, 'join_date' => '2023-11-22', 'birth_date' => '1996-05-30',
                'gender' => 'female', 'blood_group' => 'B+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Permanent',
                'father_name' => 'Jahangir Alam', 'mother_name' => 'Rashida Khatun',
                'address' => 'House 9, Road 3, Gulshan-1, Dhaka 1212',
                'nid_or_passport' => 'NID-1234567903',
            ],
            [
                'employee_id' => 'EMP015', 'name' => 'Md. Arifuzzaman', 'email' => 'arifuzzaman@dev.test',
                'phone_number' => '+8801711111125', 'department_id' => 2, 'designation_id' => 9,
                'super_department_id' => 2, 'join_date' => '2024-03-01', 'birth_date' => '2001-07-08',
                'gender' => 'male', 'blood_group' => 'AB+', 'nationality' => 'Bangladeshi',
                'religion' => 'Islam', 'marital_status' => 'Single', 'employee_type' => 'Probation',
                'father_name' => 'Zaman Hossain', 'mother_name' => 'Kamal Unnessa',
                'address' => 'House 41, Road 7, Uttara Sector 6, Dhaka 1230',
                'nid_or_passport' => 'NID-1234567904',
            ],
        ];

        foreach ($employees as $index => $emp) {
            $userId = $startId + 1 + $index;

            DB::table('users')->insert([
                'id' => $userId,
                'name' => $emp['name'],
                'email' => $emp['email'],
                'phone_number' => $emp['phone_number'],
                'join_date' => $emp['join_date'],
                'status' => 'active',
                'role' => 'employee',
                'is_active' => true,
                'employee_type' => $emp['employee_type'],
                'avatar' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'birth_date' => $emp['birth_date'],
                'gender' => $emp['gender'],
                'branch_id' => null,
                'department_id' => $emp['department_id'],
                'designation_id' => $emp['designation_id'],
                'employee_id' => $emp['employee_id'],
                'father_name' => $emp['father_name'],
                'mother_name' => $emp['mother_name'],
                'address' => $emp['address'],
                'religion' => $emp['religion'],
                'blood_group' => $emp['blood_group'],
                'marital_status' => $emp['marital_status'],
                'nationality' => $emp['nationality'],
                'nid_or_passport' => $emp['nid_or_passport'],
                'super_department_id' => $emp['super_department_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("  Seeded " . count($employees) . " synthetic employees.");
        return $startId;
    }

    /* -----------------------------------------------------------------
       Leave Balances
       ----------------------------------------------------------------- */
    private function seedLeaveBalances(int $employeeStartId): void
    {
        $employeeCount = 15;
        $currentYear = date('Y');

        $balances = [];
        for ($i = 1; $i <= $employeeCount; $i++) {
            $balances[] = [
                'user_id' => $employeeStartId + $i,
                'paid_amount' => 30,
                'unpaid_amount' => 15,
                'year' => $currentYear,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('leave_balances')->insert($balances);
        $this->command->info("  Seeded leave balances for $employeeCount employees.");
    }
}
