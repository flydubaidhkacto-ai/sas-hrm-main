<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class AssetCategoryController extends Controller
{
    public function index()
    {
        return view('asset_category.index', [
            'data' => AssetCategory::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        abort_if(!role_can('asset-category'), 403);
        $request->validate([
            'name' =>'required|string|max:255|unique:asset_categories,name',
        ]);
        $data['name'] = $request->name;
        AssetCategory::create($data);

        Toastr::success('Asset Category added');
        return redirect()->route('asset_category.index');
    }
    public function update(Request $request){
        abort_if(!role_can('asset-category'), 403);
        $request->validate([
            'name' =>'required|string|max:255|unique:asset_categories,name,'.$request->id,
        ]);
        $data['name'] = $request->name;
        AssetCategory::find($request->id)->update($data);

        Toastr::success('Asset Category updated');
        return redirect()->route('asset_category.index');
    }

    function delete($id)
    {
        abort_if(!role_can('asset-category'), 403);
        $find = AssetCategory::findOrFail($id);
        // echo $find->asset()->count();
        if($find->asset()->count() > 0) {
            Toastr::error('Asset category cannot be deleted because it is assigned to someone');
            return back();
        }
        $find->delete();
        Toastr::success('Asset Category deleted');
        return back();
    }
}
