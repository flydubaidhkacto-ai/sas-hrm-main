<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Specify the directory inside the storage folder
        $directory = 'system_backup'; // Replace with your directory name

        // Get the full path to the directory
        $path = storage_path('app/' . $directory);

        // Check if the directory exists
        if (!is_dir($path)) {
            Storage::disk('local')->deleteDirectory($directory);
            // return abort(403, 'Directory does not exist.');
        }

        // Get all files in the directory
        $files = array_diff(scandir($path), ['.', '..']);
        foreach($files as $i => $f)
        {
            if(str_contains($f, '.gitignore'))
            {
                unset($files[$i]);
            }
        }
        return view('backup.index', compact('files'));
    }

    public function download($backup)
    {
        // Specify the directory inside the storage folder
        $directory = 'system_backup'; // Replace with your directory name

        // Get the full path to the file
        $path = storage_path('app/' . $directory . '/' . $backup);

        // Check if the file exists
        if (!file_exists($path)) {
            Storage::disk('local')->deleteDirectory($directory);
            // return response()->json(['message' => 'File not found.'], 404);
        }

        // Return the file as a download response
        return response()->download($path);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            Queue::push(function ($job) {
                Artisan::call('backup:run');
                $job->delete();
            });

            return redirect()->back()->with('success', 'Backup is creating successfully. Please wait for some time.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Backup $backup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Backup $backup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Backup $backup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($backup)
    {
        // Specify the directory inside the storage folder
        $directory = 'system_backup'; // Replace with your directory name

        // Get the full path to the file
        $path = storage_path('app/' . $directory . '/' . $backup);

        // Check if the file exists
        if (!file_exists($path)) {
            Storage::disk('local')->deleteDirectory($directory);
            // return response()->json(['message' => 'File not found.'], 404);
        }

        // Delete the file
        if (unlink($path)) {
            return redirect()->back()->with('error', 'Backup Deleted successfully');
        } else {
            // return redirect()->back()->with('info', 'Failed to delete the file');
            return response()->json(['message' => 'Failed to delete the file.'], 500);
        }
    }
}
