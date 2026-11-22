<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;
use App\Services\PayrollApprovalService;

class PayrollApprovalController extends Controller
{
    public function approve(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:payslips,id'],
            'payment_method' => ['nullable', 'in:cash,bank'], //
            // note optional here
        ]);

        $returnUrl = $request->get('return_url', route('payslip.draft'));

        $service = new PayrollApprovalService();
        $user = auth()->user();

        $processed = 0;

        foreach ($request->ids as $id) {

            $payslip = Payslip::find($id);

            if (!$payslip) {
                continue;
            }

            $result = $service->approve($request->all(), $payslip, $user);

            if ($result) {
                $processed++;
            }
        }

        // ✅ SUCCESS
        if ($processed > 0) {
            return response()->json([
                'status' => true,
                'message' => 'Approved successfully',
                'redirect' => $returnUrl,
                'processed' => $processed
            ]);
        }

        // ❌ NOTHING PROCESSED
        return response()->json([
            'status' => false,
            'message' => 'No payslip was approved'
        ], 422);
    }

    public function next(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:payslips,id'],
            // note optional
        ]);

        $returnUrl = $request->get('return_url', route('payslip.draft'));

        $service = new PayrollApprovalService();
        $user = auth()->user();

        $processed = 0;

        foreach ($request->ids as $id) {

            $payslip = Payslip::find($id);

            if (!$payslip) {
                continue;
            }

            $result = $service->sendToNextStage($request->all(), $payslip, $user);

            if ($result) {
                $processed++;
            }
        }

        // ✅ SUCCESS
        if ($processed > 0) {
            return response()->json([
                'status' => true,
                'message' => 'Moved to next stage',
                'redirect' => $returnUrl,
                'processed' => $processed
            ]);
        }

        // ❌ NOTHING PROCESSED
        return response()->json([
            'status' => false,
            'message' => 'No payslip moved to next stage'
        ], 422);
    }

    public function return(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:payslips,id'],
            'note' => ['required', 'string', 'min:1'],
            'target_stage' => ['required', 'string'], // 🔥 NEW
        ]);

        $returnUrl = $request->get('return_url', route('payslip.draft'));

        $service = new PayrollApprovalService();
        $user = auth()->user();

        $processed = 0;

        foreach ($request->ids as $id) {

            $payslip = Payslip::find($id);

            if (!$payslip) continue;

            // 🔥 VALIDATION: target must be previous stage
            if (!in_array($request->target_stage, $payslip->allPreviousStages())) {
                continue;
            }

            $result = $service->backToStage([
                'note' => $request->note,
                'target_stage' => $request->target_stage
            ], $payslip, $user);

            if ($result) $processed++;
        }

        if ($processed > 0) {
            return response()->json([
                'status' => true,
                'message' => 'Returned successfully',
                'redirect' => $returnUrl,
                'processed' => $processed
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No payslip was returned'
        ], 422);
    }

    public function reGenerate(Request $request)
    {

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:payslips,id'],
            'note' => ['required', 'string', 'min:1'],
            'payment_method' => ['nullable', 'in:cash,bank'], //
        ]);


        $returnUrl = $request->get('return_url', route('payslip.draft'));

        $service = new PayrollApprovalService();
        $user = auth()->user();

        $processed = 0;

        foreach ($request->ids as $id) {

            $payslip = Payslip::find($id);

            if (!$payslip) {
                continue;
            }

            $result = $service->reGenerate(
                $request->all(),
                $payslip,
                $user
            );

            if ($result) {
                $processed++;
            }
        }

        // ✅ SUCCESS
        if ($processed > 0) {
            return response()->json([
                'status' => true,
                'message' => 'Re Generate Successfully',
                'redirect' => $returnUrl,
                'processed' => $processed
            ]);
        }

        // ❌ ELSE (NOTHING PROCESSED)
        return response()->json([
            'status' => false,
            'message' => 'No payslip was re-generated'
        ], 422);
    }

    public function paymentConfirm(Request $request)
    {

        if (!Payslip::canPaymentConfirm(auth()->user())) {

            return response()->json([

                'status' => false,

                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $service = new PayrollApprovalService();

        $result = $service->paymentConfirm(
            $request->all(),
            auth()->user()
        );

        if ($result) {

            return response()->json([
                'status' => true,
                'message' => 'Payment confirmed successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No approved payslip found'
        ], 422);
    }

    public function bankFile(Request $request)
    {
        $user = auth()->user();

        if (!Payslip::canPaymentConfirm($user)) {

            abort(403);
        }

        $filters = [

            'employee' => $request->employee,

            'department' => $request->department,

            'super_department' => $request->super_department,

            'month' => $request->month,
        ];

        $data = Payslip::bankFileData($filters);
        $service = new PayrollApprovalService();

        return $service->bankFile([

            'data' => $data,
            'reportName' => 'Bank_File',
        ]);
    }

    public function updateHeads(Request $request, PayrollApprovalService $service)
    {
        $request->validate([
            'payslip_id' => 'required|exists:payslips,id',
            'note' => ['required', 'string', 'min:2'],
            'earn_heads' => 'required|array',
            'deductions' => 'required|array',
        ]);

        $user = auth()->user();

        $payslip = Payslip::findOrFail($request->payslip_id);

        $result = $service->updateHeads($request->all(), $payslip, $user);

        if (!$result) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to update this payslip'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payslip updated successfully'
        ]);
    }
}