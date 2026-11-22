<?php

namespace App\Http\Controllers;

use App\Jobs\SendPromotionApproveSMS;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Notification;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

class PromotionController extends Controller
{
    public function apply($id)
    {
        $designations = Designation::ordered()->get();
        $department = Department::ordered()->get();
        $employee = User::find($id);
        return view('promotion.applay_promotion', compact('employee', 'designations', 'department'));
    }

    public function destroy(Request $request)
    {
        
        $promotion=Promotion::findOrFail($request->id);
        $promotion->delete();

        Toastr::success('Notification Deleted successfully :)','Success');
        return redirect()->back();
    }

    public function select()
    {
        $employees = User::active_employees()->get();
        return view('promotion.select_employee', compact('employees'));
    }

    public function requested(){
        $promotions = Promotion::where('requsted_id', user()->id)->get();
        return view('promotion.requested_promotion',compact('promotions'));
    }

    public function finale_list(){
        $promotions = Promotion::where('status', 1)->get();
        return view('promotion.final_approve',compact('promotions'));
    }

    public function pending() {
        $promotions = Promotion::all();
        return view('promotion.pending_promotion', compact('promotions'));
    }

    public function store(Request $request)
    {
         Promotion::create([
            'employee_id' => $request->employee_id,
            'new_designation' => $request->new_designation,
            'new_department' => $request->new_department,
            'requsted_id' => user()->id
        ]);



        Toastr::success('Promotion Request sent sucess');
        return redirect()->back()->withInput();
    }

    public function approve($id){
        $select = Promotion::find($id);
        $employee = User::find($select->employee_id);
        // $employee->update([
        //     'designation_id' => $select->new_designation
        // ]);

        // if($select->new_department){
        //     $employee->update([
        //         'department_id' => $select->new_department
        //     ]);
        // }

        $select->update([
            'status' => 1,
            'first_approve_id' => user()->id
        ]);

        // $submittedTime = Carbon::now();
        // $titel = $employee->name.'`s promotion requsest is approved';
        // $message = 'We are pleased to inform you that your recommendation for '.$employee->name.'s promotion has been approved! Thank you for your support and dedication to fostering a culture of excellence within the team.';

        // Notification::create([
        //     'title' => $titel,
        //     'message' => $message,
        //     'time_of_notify' => $submittedTime,
        //     'user_id' =>   $select->requsted_id
        // ]);

        Toastr::success('Promotion Acepeted');
        return redirect()->back()->withInput();
    }

    public function finale_approve($id){
        $select = Promotion::find($id);
        $employee = User::find($select->employee_id);
         $employee->update([
             'designation_id' => $select->new_designation
         ]);

         if($select->new_department){
             $employee->update([
                 'department_id' => $select->new_department
             ]);
         }

        $select->update([
            'second_status' => 1,
            'second_approve_id' => user()->id
        ]);

        $submittedTime = Carbon::now();
        $titel = $employee->name.'`s promotion requsest is approved';
        $message = 'We are pleased to inform you that your recommendation for '.$employee->name.'s promotion has been approved! Thank you for your support and dedication to fostering a culture of excellence within the team.';

        Notification::create([
            'title' => $titel,
            'message' => $message,
            'time_of_notify' => $submittedTime,
            'user_id' =>   $select->requsted_id
         ]);
         SendPromotionApproveSMS::dispatch($employee);

        Toastr::success('Promotion Acepeted');
        return redirect()->back()->withInput();
    }

    public function reject($id){
        $select = Promotion::find($id);
        $employee = User::find($select->employee_id);

        $select->update([
            'status' => 2,
            'first_approve_id' => user()->id
        ]);

        $submittedTime = Carbon::now();
        $titel = $employee->name.'`s promotion Requsest is Rejected';
        $message = 'After careful consideration, we regret to inform you that your application has not been successful at this time. Please feel free to reach out if you have any questions or would like feedback on your application.';

        Notification::create([
            'title' => $titel,
            'message' => $message,
            'time_of_notify' => $submittedTime,
            'user_id' =>   $select->requsted_id
        ]);

        Toastr::error('Promotion Reject');
        return redirect()->back()->withInput();
    }

    public function finale_reject($id){
        $select = Promotion::find($id);
        $employee = User::find($select->employee_id);

        $select->update([
            'second_status' => 2,
            'second_approve_id' => user()->id
        ]);

        $submittedTime = Carbon::now();
        $titel = $employee->name.'`s promotion Requsest is Rejected';
        $message = 'After careful consideration, we regret to inform you that your application has not been successful at this time. Please feel free to reach out if you have any questions or would like feedback on your application.';

        Notification::create([
            'title' => $titel,
            'message' => $message,
            'time_of_notify' => $submittedTime,
            'user_id' =>   $select->requsted_id
        ]);

        Toastr::error('Promotion Reject');
        return redirect()->back()->withInput();
    }
}
