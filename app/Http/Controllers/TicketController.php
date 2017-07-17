<?php

namespace App\Http\Controllers;

use App\Enums\EnumOriginType;
use App\Enums\EnumPriorityType;
use App\Enums\EnumStatusType;
use App\Enums\EnumTicketStatusType;
use App\Enums\EnumTicketType;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $tickets = DB::table('ticket')->orderBy('ticket_type')->get();

        $tickets = Ticket::orderBy('status_type')->get();

//        foreach ($tickets as $t){
//            $ticket = new \stdClass();
//
//            $ticket->id = $t->id;
//            $ticket->entity_name = $t->entity->contact->name->name;
//            $ticket->origin = EnumOriginType::getValueByKey($t->origin_type);
//            $ticket->type = EnumTicketType::getValueByKey($t->ticket_type);
//            $ticket->priority = EnumPriorityType::getValueByKey($t->origin_type);
//            $ticket->status = EnumTicketStatusType::getValueByKey($t->origin_type);
//
//            dd($ticket);
//
//        }

        return view('tickets', ['tickets' => $tickets, 'viewname' => 'Cases']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        session(['currentticket' => $id]);

        $ticket = Ticket::getObjectById($id);

        session(['randomnumber' => rand(1,5)]);

        return view('ticket', ['viewname' => 'ticket','ticket' => $ticket, 'number' => session('randomnumber')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}