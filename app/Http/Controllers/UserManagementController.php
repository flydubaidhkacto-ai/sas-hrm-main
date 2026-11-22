<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    public function users(Request $request)
    {
        $users = User::query();
        return view('users.index', [
            'users' => $users->paginate(500),
        ]);
    }
    function show(User $user)
    {
        return view('users.show', ['user' => $user]);
    }
}









