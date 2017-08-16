<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aloha\Twilio\Support\Laravel\Facade as Phone;
use Illuminate\Support\Facades\Input;
use Mockery\Exception;

class WebRtcController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('webrtc', ['viewname' => 'webrtc']);
    }

    public function sendMessage(){

        try {
            $number = Input::get('number');
            $text = Input::get('text');

            Phone::message($number, $text);

            $response = "Sent!";

            $status = "success";

        } catch(\Services_Twilio_RestException $e){

            $status = "error";

            $response = $e->getMessage();

        }

        return response()->json([
            'status' => $status,
            'data'   => $response
        ]);

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
