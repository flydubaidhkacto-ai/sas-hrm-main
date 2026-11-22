<?php

namespace App\Http\Controllers;

use App\Models\AutoReward;
use Illuminate\Http\Request;


class AutoRewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $autoReward = AutoReward::first();
        return view('auto_reward.index', compact('autoReward'));
    }


    /**
     * Create or update the AutoReward record.
     */
    public function create_or_update(Request $request)
    {
        $formData = $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|boolean',
        ]);

        $autoReward = AutoReward::find(1);

        if ($autoReward) {
            $autoReward->amount = $formData['amount'];
            $autoReward->type = $formData['type'];
            $autoReward->save();
        } else {
            AutoReward::create($formData);
        }

        return redirect()->route('auto-rewards.index')->with('success', 'Auto Reward created or updated successfully');
    }
}
