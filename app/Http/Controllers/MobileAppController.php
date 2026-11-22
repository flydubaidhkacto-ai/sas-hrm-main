<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MobileAppController extends Controller
{
    /**
     * Show mobile app download page with QR code
     */
    public function index()
    {
        // Generate a unique QR code token for this session
        $qrToken = $this->generateQRToken();
        // Get current URL for QR code
        $currentUrl = getSchemeAndHttpHost();

        // Create QR code data with authentication endpoint
        $qrData = [
            'url' => $currentUrl,
            'token' => $qrToken,
            'timestamp' => now()->timestamp
        ];

        // Store QR token in cache for 5 minutes
        Cache::put("qr_token_{$qrToken}", [
            'created_at' => now(),
            'used' => false,
            'user_id' => user()?->id
        ], now()->addMinutes(15));

        // Generate QR code as SVG
        $qrCodeSvg = QrCode::size(300)
            ->style('round')
            ->eye('circle')
            ->margin(1)
            ->generate(json_encode(!user() ? ['url' => $currentUrl] : $qrData));

        return view('mobile-app.download', compact('qrCodeSvg', 'qrToken'));
    }

    /**
     * Generate QR code for authenticated user
     */
    public function generateAuthQR()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'User must be authenticated'], 401);
        }

        $user = Auth::user();
        $qrToken = $this->generateQRToken();

        // Store user authentication data in cache for 5 minutes
        Cache::put("qr_auth_{$qrToken}", [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'created_at' => now(),
            'used' => false
        ], now()->addMinutes(5));
        // Create QR code data
        $qrData = [
            'type' => 'auth',
            'url' => getSchemeAndHttpHost(),
            'token' => $qrToken,
            'timestamp' => now()->timestamp
        ];

        // Generate QR code as SVG
        $qrCodeSvg = QrCode::size(300)
            ->style('round')
            ->eye('circle')
            ->margin(1)
            ->generate(json_encode($qrData));

        return response()->json([
            'qr_code' => $qrCodeSvg,
            'token' => $qrToken,
            'expires_at' => now()->addMinutes(5)->toISOString()
        ]);
    }

    /**
     * Authenticate user via QR code token (API endpoint for mobile app)
     */
    public function authenticateQR(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->token;
        $authData = Cache::get("qr_token_{$token}");
        Log::info('qrcode', [$authData, $token]);
        if (!$authData) {
            return response()->json([
                'success' => false,
                'message' => 'QR code has expired or is ---- invalid'
            ], 404);
        }

        if ($authData['used']) {
            return response()->json([
                'success' => false,
                'message' => 'QR code has already been used'
            ], 410);
        }

        // Get user data
        $user = User::with([
            'designation',
            'department.super_department',
            'shift',
            'assinged_role',
            'managed_employees'
        ])->find($authData['user_id']);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Mark QR code as used
        Cache::put("qr_token_{$token}", array_merge($authData, ['used' => true]), now()->addMinutes(5));

        // Create API token for mobile app
        $apiToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'live_location_track' => $user->live_location_track,
            'access_token' => $apiToken,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Check QR code status (for polling)
     */
    public function checkQRStatus(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->token;
        $authData = Cache::get("qr_auth_{$token}");

        if (!$authData) {
            return response()->json([
                'status' => 'expired',
                'message' => 'QR code has expired'
            ]);
        }

        if ($authData['used']) {
            return response()->json([
                'status' => 'used',
                'message' => 'QR code authentication completed'
            ]);
        }

        return response()->json([
            'status' => 'waiting',
            'message' => 'Waiting for QR code scan',
            'expires_at' => $authData['created_at']->addMinutes(5)->toISOString()
        ]);
    }

    /**
     * Download APK file
     */
    public function downloadAPK()
    {
        return redirect("https://app.prohrbd.com/prohr.apk");
        // You should place your APK file in the public/downloads directory
        $apkPath = public_path('downloads/prohr-mobile.apk');

        if (!file_exists($apkPath)) {
            abort(404, 'APK file not found. Please contact administrator.');
        }

        return response()->download($apkPath, 'ProHR-Mobile.apk', [
            'Content-Type' => 'application/vnd.android.package-archive'
        ]);
    }

    /**
     * Generate a unique QR token
     */
    private function generateQRToken()
    {
        return Str::random(32) . '_' . time();
    }
}
