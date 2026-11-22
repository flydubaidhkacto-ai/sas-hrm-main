<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Models\TicketMessage;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function index()
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        if (role_can('work-employee') && !user()->is_super_admin()) {
            $tickets = Ticket::with('project', 'ticketUser')->where('user_id', Auth::id())->latest()->get();
        } else {
            $tickets = Ticket::with('project', 'ticketUser')->latest()->get();
        }
        return view('works.ticket.index', compact('tickets'));
    }

    public function create()
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $projects = Project::all();
        return view('works.ticket.create', compact('projects'));
    }

    public function store(Request $request)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'open',
            'user_id' => Auth::id(),
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
            'message' => $request->description,
        ]);

        Toastr::success('Ticket created successfully.');
        return redirect()->route('tickets.index');
    }

    public function show(Ticket $ticket)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        // $messages = TicketMessage::with('ticketReplyUser')->where('project_id', $ticket->project_id)->where('ticket_id', $ticket->id)->get();
        // return view('works.ticket.show', compact('ticket', 'messages'));

        $messages = TicketMessage::with('ticketReplyUser')
            ->where('project_id', $ticket->project_id)
            ->where('ticket_id', $ticket->id)
            ->get()
            ->map(function ($message) use ($ticket) {
                $message->is_sender = $message->user_id === $ticket->user_id;
                return $message;
            });
        return view('works.ticket.show', compact('ticket', 'messages'));
    }

    // public function edit(Ticket $ticket)
    // {
    //     abort_if((!role_can('work-admin')), 403);
    //     $projects = Project::all();
    //     return view('works.ticket.edit', compact('ticket', 'projects'));
    // }

    // public function update(Request $request, Ticket $ticket)
    // {
    //     abort_if((!role_can('work-admin')), 403);
    //     $request->validate([
    //         'project_id' => 'required|exists:projects,id',
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'status' => 'required|in:open,in_progress,solved,rejected',
    //     ]);

    //     $ticket->update($request->all());

    //     Toastr::success('Ticket updated successfully.');
    //     return redirect()->route('tickets.index');
    // }

    public function destroy(Ticket $ticket)
    {
        abort_if((!role_can('work-admin')), 403);
        if ($ticket->status == 'solved') {
            Toastr::error('Ticket is solved, you can not delete it.');
            return redirect()->route('tickets.index');
        }
        $ticket->delete();
        Toastr::success('Ticket deleted successfully.');
        return redirect()->route('tickets.index');
    }

    public function reply(Request $request, Ticket $ticket)
    {

        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $request->validate([
            'description' => 'required|string',
        ]);

        if ($ticket->status == 'solved' || $ticket->status == 'rejected') {
            Toastr::error('Ticket is ' . $ticket->status . ', you can not reply to it.');
            return redirect()->route('tickets.show', $ticket->id);
        }

        $ticket->status = 'in_progress';
        $ticket->save();

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'project_id' => $ticket->project_id,
            'message' => $request->description,
        ]);

        Toastr::success('Ticket replied successfully.');
        return redirect()->route('tickets.show', $ticket->id);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        abort_if((!role_can('work-admin')), 403);
        $ticket->update(['status' => $request->status]);
        Toastr::success('Ticket status updated successfully');
        return back();
    }
}
