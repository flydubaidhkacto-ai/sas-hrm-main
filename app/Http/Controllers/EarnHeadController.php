<?php

namespace App\Http\Controllers;

use App\Models\EarnHead;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class EarnHeadController extends Controller
{
    function index()
    {
        return view('earn-head.index', [
            'data' => EarnHead::latest()->get()
        ]);
    }
    function create()
    {
        return view('earn-head.create', [
            'categories' => EarnHead::$categories,
        ]);
    }
    function store(Request $request)
    {
        abort_if(!role_can('earn_head'), 403);
        $data = $request->validate([
            'code' => "required|max:50|unique:earn_heads,code",
            'name' => "required|max:50|unique:earn_heads,name",
            'amount' => 'nullable|required_if:category,allowance',
            'amount_type' => 'required|string|in:percentage,fixed,other',
            'category' => 'required|string|in:' . join(',', array_keys(EarnHead::$categories)),
        ]);
        // if ($request->category == 'basic' || $request->category == 'rewards') {
        //     $data['amount'] = 0;
        //     $data['amount_type'] = 'fixed';
        // }
        if ($request->category !== 'allowance') {
            $request->validate([
                'category' => 'unique:earn_heads,category',
            ]);
        }
        if (!isset($data['amount'])) {
            $data['amount'] = 0;
        }

        $head = EarnHead::create($data);
        Toastr::success('Earn head added');
        return redirect()->route('earn-head.index');
    }
    function edit($id)
    {
        abort_if(!role_can('earn_head'), 403);
        return view('earn-head.edit', [
            'head' => EarnHead::findOrFail($id)
        ]);
    }
    function update($id, Request $request)
    {
        abort_if(!role_can('earn_head'), 403);
        $head = EarnHead::findOrFail($id);
        $data = $request->validate([
            'code' => "required|max:50|unique:earn_heads,code," . $id,
            'name' => "required|max:50|unique:earn_heads,name," . $id,
            'amount' => 'nullable|required_if:category,allowance',
            'amount_type' => 'required|string|in:percentage,fixed,other',
            'category' => 'required|string|in:' . join(',', array_keys(EarnHead::$categories)),
        ]);
        // if ($request->category == 'basic' || $request->category == 'rewards') {
        //     $data['amount'] = 0;
        //     $data['amount_type'] = 'fixed';
        // }
        if ($request->category !== 'allowance') {
            $request->validate([
                'category' => 'unique:earn_heads,category,' . $id,
            ]);
        }

        if (!isset($data['amount'])) {
            $data['amount'] = 0;
        }

        $head->update($data);
        Toastr::success('Earn head edited');
        return redirect()->route('earn-head.index');
    }
    function delete($id)
    {
        abort_if(!role_can('earn_head'), 403);
        $head = EarnHead::findOrFail($id);
        $head->delete();
        Toastr::success('Earn head deleted');
        return redirect()->route('earn-head.index');
    }
}
