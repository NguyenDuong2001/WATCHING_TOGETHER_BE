<?php

namespace App\Http\Controllers;

use App\Enums\RoleType;
use App\Events\SendMessage;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @return \Illuminate\Broadcasting\PendingBroadcast|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => Auth::user()->role->name === RoleType::Customer ? 'nullable' : 'required',
            'message' => ['required', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        $room = Room::firstOrCreate([
            'user_id' => Auth::user()->role->name === RoleType::Customer ?
                Auth::user()->id : $request->get('to'),
        ]);

        if (Auth::user()->role->name === RoleType::Customer) {
            $room->admin_seen = false;
        } else {
            $room->user_seen = false;
        }

        $room->save();

        $message = Message::create([
            'sender_id' => Auth::user()->id,
            'receiver_id' => Auth::user()->role->name === RoleType::Customer ?
                null : $request->get('to'),
            'room_id' => $room->id,
            'message' => $request->get('message')
        ]);

        broadcast(new SendMessage($room->id, [
            'created_at' => $message->created_at,
            'id' => $message->id,
            'is_author' => false,
            'sender' => $message->sender,
            'receiver' => $message->receiver,
            'updated_at' => $message->updated_at,
            'message' => $message->message
        ]))->toOthers();

        return response()->json([
            'message' => 'Send message successfully!',
            'data' => $message
        ]);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        //
    }
}
