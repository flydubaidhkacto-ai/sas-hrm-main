<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!role_can('guest_management'), 403);

        $guests = User::where('role', 'guest')
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('phone_number', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(50);

        return view('guests.index', compact('guests'));
    }

    public function create()
    {
        abort_if(!role_can('guest_management'), 403);

        return view('guests.create');
    }

    public function store(Request $request)
    {
        abort_if(!role_can('guest_management'), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company' => 'nullable|string|max:255',
            'zk_device_card' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'company' => $request->company,
            'zk_device_card' => $request->zk_device_card,
            'role' => 'guest',
            'is_active' => true,
            'password' => bcrypt('guest123'), // Default password for guests
        ]);

        Toastr::success('Guest created successfully', 'Success');
        return redirect()->route('guests.index');
    }

    public function show($id)
    {
        abort_if(!role_can('guest_management'), 403);

        $guest = User::where('role', 'guest')->findOrFail($id);
        return view('guests.show', compact('guest'));
    }

    public function edit($id)
    {
        abort_if(!role_can('guest_management'), 403);

        $guest = User::where('role', 'guest')->findOrFail($id);
        return view('guests.edit', compact('guest'));
    }

    public function update(Request $request, $id)
    {
        abort_if(!role_can('guest_management'), 403);

        $guest = User::where('role', 'guest')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $guest->id,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company' => 'nullable|string|max:255',
            'zk_device_card' => 'nullable|string|max:255',
        ]);

        $guest->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'company' => $request->company,
            'zk_device_card' => $request->zk_device_card,
        ]);

        Toastr::success('Guest updated successfully', 'Success');
        return redirect()->route('guests.index');
    }

    public function destroy($id)
    {
        abort_if(!role_can('guest_management'), 403);

        $guest = User::where('role', 'guest')->findOrFail($id);
        $guest->delete();

        Toastr::success('Guest deleted successfully', 'Success');
        return redirect()->route('guests.index');
    }

    public function toggleStatus(Request $request, $id)
    {
        abort_if(!role_can('guest_management'), 403);

        $guest = User::where('role', 'guest')->findOrFail($id);
        $guest->update(['is_active' => !$guest->is_active]);

        return response()->json([
            'success' => true,
            'status' => $guest->is_active,
            'message' => 'Guest status updated successfully'
        ]);
    }
}
