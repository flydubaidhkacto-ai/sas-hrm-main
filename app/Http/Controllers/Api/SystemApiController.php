<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemApiController extends Controller
{
    function check(?Request $request = null)
    {
        $request = $request ?: request();
        abort_if(
            $request->headers->get('x-api-key') !== "authorized-by-arif-from-arifdev.com",
            403
        );
    }
    function update_license(Request $request)
    {
        $this->check($request);
        if($request->license)
        {
            $data = $request->license;
            if($request->remove)
            {
                $curr = file_get_contents(\CheckAndVerifyLicense::$license_path);
                $curr = decrypt(base64_decode( $curr ));
                if($curr === $data)
                {
                    try {
                        //code...
                        unlink(\CheckAndVerifyLicense::$license_path);
                        unlink(\CheckAndVerifyLicense::$license_cache_path);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    return [
                        'success' => true
                    ];
                }
                return [2];
            }
            else {
                $save = \CheckAndVerifyLicense::save_licence($data);
                \CheckAndVerifyLicense::verify_real_license();
                if($save)
                {
                    return [
                        'success' => true
                    ];
                }
            }
        }
        return abort(404);
    }
    function __(Request $request)
    {
        $this->check($request);
    }
}
