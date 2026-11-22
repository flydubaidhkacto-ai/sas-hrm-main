<?php

namespace App\Actions;

use App\Http\Controllers\SmsController;
use App\Models\Setting;
use App\Models\SmsHistory;
use App\Models\SMSQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SMSApi
{
    static function send($phone, $text, $is_queue = false)
    {
        $config = Setting::sms_api_config();
        // if($config['not_set'] ?? false) return 1; // not configured

        if($is_queue)
        {
            return SMSQueue::create([
                'phone' => $phone,
                'message' => $text
            ]);
        }
        if (Str::startsWith($phone, '01')) $phone = '88' . $phone;
        if (Str::startsWith($phone, '1')) $phone = '880' . $phone;
        if(config('app.debug') && config('app.env') === 'local') {
            $text .= "\nDebug Mode: SMS intended for $phone";
            $phone = '8801602641073';
        }

        $url = replaceSmsPlaceholders($config['url'] ?? '', [
            '[Phone]' => $phone,
            '[Message]' => urlencode($text)
        ]);
        $is_post = $config['type'] === 'POST';
        $headers = [];
        foreach ($config['headers'] ?? [] as $key => $value) {
            $headers[$key] = replaceSmsPlaceholders($value ?: "", [
                '[Phone]' => $phone,
                '[Message]' => ($text)
            ]);
        }
        $body = [];
        foreach ($config['body'] ?? [] as $key => $value) {
            $body[$key] = replaceSmsPlaceholders($value ?: "", [
                '[Phone]' => $phone,
                '[Message]' => ($text)
            ]);
        }

        // dump($data, $url, $key, $senderID, $ver);
        $en = SmsHistory::entry($phone, $text);

        if(Setting::use_custom_sms_api()) {
            try {
                if ($is_post) {
                    $htt = Http::timeout(5)
                        ->retry(3, 100)
                        ->withHeaders($headers)
                        ->post($url, $body);
                } else {
                    $htt = Http::timeout(5)
                        ->retry(3, 100)
                        ->withHeaders($headers)
                        ->get($url);
                }
                if ($htt->successful()) {
                    $en->update(['status' => $htt->body()]);
                    return true;
                }
            } catch (\Throwable $th) {
                $en->update(['status' => $th->getMessage()]);
                return false;
            }
        }else {
            // send request to prohr website api
            // $url = config('app.env') == 'local' && config('app.debug') ? "http://localhost:8001/internal-action" : "https://app.prohrbd.com/internal-action";
            try{

            } catch(\Throwable $th) {
                $en->update(['status' => $th->getMessage()]);
                return false;
            }
        }
        $en->update(['status' => 'failed!']);
        return false;
    }
}
