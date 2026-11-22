<?php

namespace App\Console\Commands;

use App\Models\ProvidentFundPayment;
use App\Models\User;
use Illuminate\Console\Command;

class ImportOldProvidentFundData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-old-provident-fund-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $array = array(
            array("val0"=>"1005", "val1"=>"Amir Forman", "val2"=>"15410", "val3"=>"8400", "val4"=>"23810"),
            array("val0"=>"1009", "val1"=>"Mokhles Mes", "val2"=>"11670", "val3"=>"8400", "val4"=>"20070"),
            array("val0"=>"1015", "val1"=>"Shohag H", "val2"=>"9965", "val3"=>"8400", "val4"=>"18365"),
            array("val0"=>"1011", "val1"=>"Rabbi", "val2"=>"8865", "val3"=>"8400", "val4"=>"17265"),
            array("val0"=>"1014", "val1"=>"Sattar Arg", "val2"=>"8865", "val3"=>"8400", "val4"=>"17265"),
            array("val0"=>"1007", "val1"=>"Arju", "val2"=>"2135", "val3"=>"8400", "val4"=>"10535"),
            array("val0"=>"1012", "val1"=>"Murad", "val2"=>"0", "val3"=>"2800", "val4"=>"2800"),
            array("val0"=>"1006", "val1"=>"Abdur Rahim", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1059", "val1"=>"Ripon Chandra", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1008", "val1"=>"Nur Alam", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1055", "val1"=>"Shikh Forid", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1016", "val1"=>"Monir Mes", "val2"=>"16125", "val3"=>"8400", "val4"=>"24525"),
            array("val0"=>"1017", "val1"=>"Anik H. Mes", "val2"=>"8865", "val3"=>"8400", "val4"=>"17265"),
            array("val0"=>"1018", "val1"=>"Ibrahim H", "val2"=>"26708", "val3"=>"8400", "val4"=>"35108"),
            array("val0"=>"1013", "val1"=>"Shohag", "val2"=>"3588", "val3"=>"8400", "val4"=>"11988"),
            array("val0"=>"1019", "val1"=>"Mehedi", "val2"=>"0", "val3"=>"3500", "val4"=>"3500"),
            array("val0"=>"1031", "val1"=>"Humayun", "val2"=>"8865", "val3"=>"8400", "val4"=>"17265"),
            array("val0"=>"1032", "val1"=>"Ahammad", "val2"=>"8193", "val3"=>"8400", "val4"=>"16593"),
            array("val0"=>"1030", "val1"=>"Shihab", "val2"=>"8865", "val3"=>"8400", "val4"=>"17265"),
            array("val0"=>"1021", "val1"=>"Sopon Formen", "val2"=>"16675", "val3"=>"8400", "val4"=>"25075"),
            array("val0"=>"1024", "val1"=>"Shamim", "val2"=>"0", "val3"=>"2100", "val4"=>"2100"),
            array("val0"=>"1022", "val1"=>"Babul", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1042", "val1"=>"Anarul Stock", "val2"=>"16125", "val3"=>"8400", "val4"=>"24525"),
            array("val0"=>"1041", "val1"=>"Abu Bakka (Titu)", "val2"=>"12165", "val3"=>"8400", "val4"=>"20565"),
            array("val0"=>"1038", "val1"=>"Salim (Dri)", "val2"=>"27450", "val3"=>"8400", "val4"=>"35850"),
            array("val0"=>"1039", "val1"=>"Azizul", "val2"=>"3588", "val3"=>"8400", "val4"=>"11988"),
            array("val0"=>"1040", "val1"=>"Mamun", "val2"=>"0", "val3"=>"1400", "val4"=>"1400"),
            array("val0"=>"1035", "val1"=>"Rashed (Joy) Mis", "val2"=>"4323", "val3"=>"8400", "val4"=>"12723"),
            array("val0"=>"1027", "val1"=>"Jesmin P", "val2"=>"22246", "val3"=>"8400", "val4"=>"30646"),
            array("val0"=>"1026", "val1"=>"Farhan", "val2"=>"5065", "val3"=>"8400", "val4"=>"13465"),
            array("val0"=>"1036", "val1"=>"Munna", "val2"=>"1417", "val3"=>"7700", "val4"=>"9117"),
            array("val0"=>"1025", "val1"=>"Ashik", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1028", "val1"=>"Jihad", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1063", "val1"=>"Emran Hossain", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1029", "val1"=>"Ashraful", "val2"=>"0", "val3"=>"700", "val4"=>"700"),
            array("val0"=>"1044", "val1"=>"Hanif Super.V", "val2"=>"11065", "val3"=>"8400", "val4"=>"19465"),
            array("val0"=>"1058", "val1"=>"SHAMRAT", "val2"=>"0", "val3"=>"8400", "val4"=>"8400"),
            array("val0"=>"1001", "val1"=>"RIEAZ", "val2"=>"9400", "val3"=>"8400", "val4"=>"17800"),
            array("val0"=>"1047", "val1"=>"SALIM (M)", "val2"=>"8865", "val3"=>"8400", "val4"=>"17265"),
            array("val0"=>"1037", "val1"=>"SHOHEL", "val2"=>"2858", "val3"=>"8400", "val4"=>"11258"),
            array("val0"=>"1004", "val1"=>"ANOWAR HOSAIN", "val2"=>"0", "val3"=>"8400", "val4"=>"8400"),
            array("val0"=>"1045", "val1"=>"Nayan", "val2"=>"0", "val3"=>"1400", "val4"=>"1400"),
            array("val0"=>"1046", "val1"=>"Solainam Ali", "val2"=>"0", "val3"=>"700", "val4"=>"700")
        );

        foreach ($array as $item) {
            // array("val0"=>"ID", "val1"=>"NAME", "val2"=>"2023 P.FUND", "val3"=>"2024 P. FUND", "val4"=>"TOTAL"),
            $id = $item['val0'];
            $name = $item['val1'];
            $pf2023 = $item['val2'];
            $pf2024 = $item['val3'];
            $total = $item['val4'];
            User::query()
                ->where('id', $id)
                ->update([
                    'initial_pf_balance' => $total + ($total * 0.1)
                ]);
            // $fund = ProvidentFundPayment::where('user_id', $id)->first();
            // // dd($fund);
            // if ($fund) {
            //     $interest_rate = 0.1;
            //     // Calculate interest for 2023 and 2024
            //     $interest_2023 = $pf2023 * $interest_rate; // 10% interest for 2023
            //     $total_2023 = $pf2023 + $interest_2023 ;

            //     $interest_2024 = $pf2024 * $interest_rate; // 10% interest for 2024
            //     $total_2024 = $pf2024 + $interest_2024;

            //     // Final old balance with interest
            //     $old_balance_with_interest = $total_2024 + $total_2023;

            //     // Get current balance for 2025 (if exists)
            //     $current_balance = $fund->balance;

            //     // Calculate new balance (Old Balance + Interest + Current Balance)
            //     $new_balance = $old_balance_with_interest + $current_balance;

            //     $fund->update([
            //         'balance' => $new_balance,
            //         'interest_amount' => $interest_2024 + $interest_2023,
            //         'balance_with_interest' => $new_balance,
            //     ]);
            // }
        }
    }
}
