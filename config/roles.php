<?php 
/**
 *
 * attendance.*
 * api_key.* 
 * payroll.*
 * deduction.*
 * earn_heads.* 
 * leaves.* 
 * users.* 
 * "*" => can be view, edit, delete, create 
 * + users.terminate, users.reactivate
 */
return [
    // 'super_admin' => [
    //     'name' => 'Super Admin',
    //     'permissions' => [
    //         'users.create', 'users.view', 'users.edit', 'users.delete', 'users.terminate', 'users.reactivate',
    //         'api_key.create', 'api_key.view', 'api_key.edit', 'api_key.delete',
    //         'attendance.create', 'attendance.view', 'attendance.edit', 'attendance.delete',
    //         'deduction.create', 'deduction.view', 'deduction.edit', 'deduction.delete',
    //         'earn_heads.create', 'earn_heads.view', 'earn_heads.edit', 'earn_heads.delete',
    //         'leaves.create', 'leaves.view', 'leaves.edit', 'leaves.delete',
    //     ]
    // ],
    'admin' => [
        'name' => 'Admin',
        'permissions' => [
            'users.create', 'users.view', 'users.edit', 'users.delete', 'users.terminate', 'users.reactivate',
            'api_key.create', 'api_key.view', 'api_key.edit', 'api_key.delete',
            'attendance.create', 'attendance.view', 'attendance.edit', 'attendance.delete',
            'deduction.create', 'deduction.view', 'deduction.edit', 'deduction.delete',
            'earn_heads.create', 'earn_heads.view', 'earn_heads.edit', 'earn_heads.delete',
            'leaves.create', 'leaves.view', 'leaves.edit', 'leaves.delete',
            'payroll.edit'
        ]
    ],
    'manager' => [
        'name' => 'Manager',
        'permissions' => [
            'users.view',  
            'users.delete',  
            'attendance.view', 'attendance.edit', 'attendance.create',
            'deduction.view',
            'leaves.view', 'leaves.edit', 'leaves.create',
            'earn_heads.view',
        ]
    ],
    'employee' => [
        'name' => 'Employee',
        'permissions' => [
            
        ]
    ]
];