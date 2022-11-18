<?php

namespace App\Http\Controllers;

use App\Enums\RoleType;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            if (Auth::user()->role === RoleType::Customer)
            {
                return response()->json([
                    'room' => Room::where('user_id', Auth::user()->id)->firstOrFail()
                ]);
            }

            return response()->json([
                'rooms' => Room::when($request->get('search'), function ($query) use ($request) {
                    $users = User::where('name', 'like', "%{$request->get('search')}%")->get();
                    $query->whereBelongsTo($users);
                })->get()->sortBy(
                    fn ($room) => [$room['message_end']?->created_at, $room['created_at'], $room['id']]
                ,SORT_REGULAR,  true)->values()
            ]);
        } catch (\Exception $e)
        {
            return response()->json([
                'rooms' => []
            ]);
        }
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
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Room $room)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function edit(Room $room)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Room $room)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy(Room $room)
    {
        //
    }

    public function messages(Request $request, $id = null)
    {
        try {
            if ($id) {
                $room = Room::findOrFail($id);
            }
            else {
                $room = Room::where('user_id', Auth::user()->id)->firstOrFail();
            }
            $this->authorize('view', $room);

            return response()->json([
                'messages' => $room->messages()
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->paginate($request->query('limit') ?: 10),
                'room_id' => $room->id
            ]);
        } catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Not found room'
            ], 500);
        }
    }

    public function seen(Request $request, $id = null) {
        try {
            if ($id && Auth::user()->role != RoleType::Customer) {
                $room = Room::findOrFail($id);
                $room->admin_seen = true;
            } else {
                $room = Room::where('user_id', Auth::user()->id)->firstOrFail();
                $room->user_seen = true;
            }

            $room->save();

            return response()->json([
                'message' => 'Seen successfully!'
            ]);
        } catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Not found room'
            ], 500);
        }
    }
}
