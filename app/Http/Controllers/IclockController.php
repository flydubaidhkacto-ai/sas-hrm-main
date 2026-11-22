<?php

namespace App\Http\Controllers;

use App\Jobs\EmployeePunchedJob;
use App\Models\InOutRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class IclockController extends Controller
{
    // handshake
    public function handshake(Request $request)
    {
        $data = [
            'url' => json_encode($request->all()),
            'data' => $request->getContent(),
            'sn' => $request->input('SN'),
            'option' => $request->input('option'),
        ];

        $r = "GET OPTION FROM: {$request->input('SN')}\r\n" .
            "Stamp=9999\r\n" .
            "OpStamp=" . time() . "\r\n" .
            "ErrorDelay=60\r\n" .
            "Delay=30\r\n" .
            "ResLogDay=18250\r\n" .
            "ResLogDelCount=10000\r\n" .
            "ResLogCount=50000\r\n" .
            "TransTimes=00:00;14:05\r\n" .
            "TransInterval=1\r\n" .
            "TransFlag=1111000000\r\n" .
            //  "TimeZone=7\r\n" .
            "Realtime=1\r\n" .
            "Encrypt=0";

        return $r;
    }
    //$r = "GET OPTION FROM:%s{$request->SN}\nStamp=".strtotime('now')."\nOpStamp=1565089939\nErrorDelay=30\nDelay=10\nTransTimes=00:00;14:05\nTransInterval=1\nTransFlag=1111000000\nTimeZone=7\nRealtime=1\nEncrypt=0\n";
    // implementasi https://docs.nufaza.com/docs/devices/zkteco_attendance/push_protocol/
    // setting timezone
    // request absensi
    public function receiveRecords(Request $request)
    {
        // Log::info('receiveRecords', [$request->getContent()]);
        //DB::connection()->enableQueryLog();
        $content['url'] = json_encode($request->all());
        $content['data'] = $request->getContent();
        // Log::info('finger_log', $request->all());
        try {
            // $post_content = $request->getContent();
            //$arr = explode("\n", $post_content);
            $arr = preg_split('/\\r\\n|\\r|,|\\n/', $request->getContent());
            // $tot = count($arr);
            $tot = 0;
            //operation log
            if ($request->input('table') == "OPERLOG") {
                // $tot = count($arr) - 1;
                foreach ($arr as $rey) {
                    if (isset($rey)) {
                        $tot++;
                    }
                }
                return "OK: " . $tot;
            }
            //attendance
            foreach ($arr as $rey) {
                // $data = preg_split('/\s+/', trim($rey));
                if (empty($rey)) {
                    continue;
                }
                $data = preg_split('/\s+/', trim($rey));
                $data = explode("\t", $rey);
                // Log::info('attendances', $data);
                // $q['sn'] = $request->input('SN');
                // $q['table'] = $request->input('table');
                // $q['stamp'] = $request->input('Stamp');
                $q['employee_id'] = $data[0];
                $q['timestamp'] = $data[1];
                // // $q['status1'] = $this->validateAndFormatInteger($data[2] ?? null);
                // // $q['status2'] = $this->validateAndFormatInteger($data[3] ?? null);
                // // $q['status3'] = $this->validateAndFormatInteger($data[4] ?? null);
                // // $q['status4'] = $this->validateAndFormatInteger($data[5] ?? null);
                // // $q['status5'] = $this->validateAndFormatInteger($data[6] ?? null);
                // $q['created_at'] = now();
                // $q['updated_at'] = now();
                //dd($q);
                if(
                    !InOutRecord::where('user_id', $q['employee_id'])->where('date_time', $q['timestamp'])->exists()
                ){
                    $check = InOutRecord::firstOrCreate([
                        'user_id' => $q['employee_id'],
                        'status' => 1,
                        'date_time' => $q['timestamp'],
                        'original_date_time' => $q['timestamp'],
                        'device_mac' => null,
                        'device_uid' => $request->input('SN'),
                        'device_attendance_uid' => $request->input('SN'),
                        'unique_data' => $q['timestamp'] . '-' . $q['employee_id'],
                        'added' => 0
                    ]);
                    EmployeePunchedJob::dispatch($check);
                }
                $tot++;
                // dd(DB::getQueryLog());
            }
            return "OK: " . $tot;
        } catch (Throwable $e) {
            // Log::error('error_log', $request->all());
            return "ERROR: " . $tot . "\n";
        }
    }
    public function test(Request $request)
    {
        $log['data'] = $request->getContent();
        // Log::info('finger_log', $log);
    }
    public function getrequest(Request $request)
    {
        return "GET OPTION FROM: ".$request->SN."\nStamp=".strtotime('now')."\nOpStamp=".strtotime('now')."\nErrorDelay=60\nDelay=30\nResLogDay=18250\nResLogDelCount=10000\nResLogCount=50000\nTransTimes=00:00;14:05\nTransInterval=1\nTransFlag=1111000000\nRealtime=1\nEncrypt=0";
        // return "OK";
    }
    private function validateAndFormatInteger($value)
    {
        return isset($value) && $value !== '' ? (int)$value : null;
    }
    public function registry(Request $request)
    {
        // Log::info('registry', [$request->all()]);
        return "RegistryCode=Uy47fxftP3";
    }
}
