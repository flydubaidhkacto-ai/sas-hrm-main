<?php

namespace App\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BankFileExcel implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnFormatting
{
    public function __construct(
        private $data
    ) {}

/*
|--------------------------------------------------------------------------
| COLLECTION
|--------------------------------------------------------------------------
*/

public function collection()
{
    return collect($this->data);
}

/*
|--------------------------------------------------------------------------
| MAP ROW
|--------------------------------------------------------------------------
*/

public function map($row): array
{
    return [

        '', // Reason

        '', // Sender Account No

        '', // Receiving Bank Routing No

        /*
        |--------------------------------------------------------------------------
        | ACCOUNT NUMBER
        |--------------------------------------------------------------------------
        */

        $row->account_number
            ? '="' . $row->account_number . '"'
            : '',

        '', // Account Type

        /*
        |--------------------------------------------------------------------------
        | AMOUNT
        |--------------------------------------------------------------------------
        */

        (float) ($row->total_salary ?? 0),

        '', // Receiver ID

        $row->account_holder_name ?? '',

        '', // Mobile

        '', // Email
    ];
}

/*
|--------------------------------------------------------------------------
| HEADINGS
|--------------------------------------------------------------------------
*/

public function headings(): array
{
    return [

        'Reason',

        'Sender Account No',

        'Receiving Bank Routing No',

        'Beneficiary Bank Account  No',

        'Account Type',

        'Amount',

        'Receiver ID',

        'Receiver Name',

        'Receiver Mobile Number',

        'Receiver Email Address',
    ];
}

/*
|--------------------------------------------------------------------------
| COLUMN FORMAT
|--------------------------------------------------------------------------
*/

public function columnFormats(): array
{
    return [

        /*
        |--------------------------------------------------------------------------
        | ACCOUNT NUMBER COLUMN
        |--------------------------------------------------------------------------
        */

        'D' => '0',

        /*
        |--------------------------------------------------------------------------
        | AMOUNT COLUMN
        |--------------------------------------------------------------------------
        */

        'F' => '#,##0.00',
    ];
}
}