<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Project;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index()
    {
        abort_if(!role_can('work-admin'), 403);
        $clients = Client::all();
        return view('works.clients.index', compact('clients'));
    }

    public function create()
    {
        abort_if(!role_can('work-admin'), 403);
        return view('works.clients.create');
    }

    public function store(Request $request)
    {
        abort_if(!role_can('work-admin'), 403);
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'client_address' => 'nullable',
            'company_name' => 'nullable',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable',
            'company_website' => 'nullable',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:500',
            'company_address' => 'nullable',
            'shipping_address' => 'nullable',
            'assigned_by' => 'nullable',
        ]);
        // Client::create($request->all());
        $client = new Client();
        $client->name = $request->name;
        $client->email = $request->email;
        $client->phone = $request->phone;
        $client->client_address = $request->client_address;
        $client->company_name = $request->company_name;
        $client->company_email = $request->company_email;
        $client->company_phone = $request->company_phone;
        $client->company_website = $request->company_website;
        $client->company_address = $request->company_address;
        $client->shipping_address = $request->shipping_address;
        $client->assigned_by = $request->assigned_by;

        if ($request->hasFile('company_logo')) {
            $client->company_logo = Storage::put('clients/company_logos', $request->file('company_logo'));
        }

        $client->save();
        Toastr::success('Client created successfully.');
        return redirect()->route('clients.index');
    }

    public function edit(Client $client)
    {
        abort_if(!role_can('work-admin'), 403);
        return view('works.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        abort_if(!role_can('work-admin'), 403);
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'client_address' => 'nullable',
            'company_name' => 'nullable',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable',
            'company_website' => 'nullable',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:500',
            'company_address' => 'nullable',
            'shipping_address' => 'nullable',
            'assigned_by' => 'nullable',
        ]);
        // $client->update($request->all());
        $client->name = $request->name;
        $client->email = $request->email;
        $client->phone = $request->phone;
        $client->client_address = $request->client_address;
        $client->company_name = $request->company_name;
        $client->company_email = $request->company_email;
        $client->company_phone = $request->company_phone;
        $client->company_website = $request->company_website;
        $client->company_address = $request->company_address;
        $client->shipping_address = $request->shipping_address;
        $client->assigned_by = $request->assigned_by;

        if ($request->hasFile('company_logo')) {
            if ($client->company_logo && Storage::exists($client->company_logo)) {
                Storage::delete($client->company_logo);
            }
            $client->company_logo = Storage::put('clients/company_logos', $request->file('company_logo'));
        }

        $client->save();
        Toastr::success('Client updated successfully.');
        return redirect()->route('clients.index');
    }

    public function destroy(Client $client)
    {
        abort_if(!role_can('work-admin'), 403);
        if ($client->projects()->exists()) {
            Toastr::error('Client has projects and cannot be deleted.');
            return redirect()->route('clients.index');
        }

        if ($client->company_logo && Storage::exists($client->company_logo)) {
            Storage::delete($client->company_logo);
        }
        $client->delete();
        Toastr::success('Client deleted successfully.');
        return redirect()->route('clients.index');
    }
}
