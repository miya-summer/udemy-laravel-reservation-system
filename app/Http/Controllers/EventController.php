<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\EventService;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today();

        $reservedPeople = DB::table('reservations')
        ->select('event_id', DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id');

        $events = DB::table('events')
        ->leftJoinSub($reservedPeople, 'reservedPeople',
            function($join){
            $join->on('events.id', '=', 'reservedPeople.event_id');
        })
        ->whereDate('events.start_date', '>=', $today)
        ->orderBy('events.start_date', 'desc')
        ->paginate(10);

        return view('manager.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manager.events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        //dd($request);

        // 重複チェック
        $check = EventService::checkEventDeplication($request['event_date'], $request['start_time'], $request['end_time']);

        if ($check) {
            session()->flash('status', '既に同じ日時でイベントが登録されています');
            return view('manager.events.create');
        }

        $startDate = EventService::joinDateAndTime($request['event_date'], $request['start_time']);
        $endDate = EventService::joinDateAndTime($request['event_date'], $request['end_time']);

        Event::create([
            'name' => $request['event_name'],
            'information' => $request['information'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_people' => $request['max_people'],
            'is_visible' => $request['is_visible'],
        ]);

        session()->flash('status', '登録okです');

        return to_route('events.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // $event = Event::findOrFail($event->id);
        $eventDate = $event->eventDate;
        $startTime = $event->startTime;
        $endTime = $event->endTime;
        $users = $event->users;
        $reservations = []; // 予約情報を格納する配列

        foreach($users as $user)
        {
            $reservedInfo = [
                'name' => $user->name,
                'number_of_people' => $user->pivot->number_of_people,
                'canceled_date' => $user->pivot->canceled_date
            ];

            array_push($reservations, $reservedInfo); // 連想配列に追加
        }

        // dd($eventDate, $startTime, $endTime);
        return view('manager.events.show', compact('event', 'reservations', 'users', 'eventDate', 'startTime', 'endTime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $today = Carbon::today()->format('Y年m月d日');
        // 過去のイベントは編集できないようにする
        if($event->eventDate < $today){
            return abort(404);
        }

        // $event = Event::findOrFail($event->id);
        $eventDate = $event->editEventDate;
        $startTime = $event->startTime;
        $endTime = $event->endTime;
        
        return view('manager.events.edit', compact('event', 'eventDate', 'startTime', 'endTime'));        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        // 重複チェック
        $check = EventService::countEventDuplication($request['event_date'], $request['start_time'], $request['end_time']);

        if ($check > 1) {
            // $event = Event::findOrFail($event->id);
            // $eventDate = $event->editEventDate;
            // $startTime = $event->startTime;
            // $endTime = $event->endTime;
            // session()->flash('status', '既に同じ日時でイベントが登録されています');
            // return view('manager.events.edit', compact('event', 'eventDate', 'startTime', 'endTime'));
            
            // こっちの方が簡潔にかけて良さそうなので、こちらを採用
            return back()->withInput()->with('status', '既に同じ日時でイベントが登録されています');
        }

        $startDate = EventService::joinDateAndTime($request['event_date'], $request['start_time']);
        $endDate = EventService::joinDateAndTime($request['event_date'], $request['end_time']);

        // $event = Event::findOrFail($event->id);
        $event->name = $request['event_name'];
        $event->information = $request['information'];
        $event->start_date = $startDate;
        $event->end_date = $endDate;
        $event->max_people = $request['max_people'];
        $event->is_visible = $request['is_visible'];
        $event->save();

        session()->flash('status', '更新しました。');

        return to_route('events.index');
    }

    public function past()
    {
        $today = Carbon::today();

        $reservedPeople = DB::table('reservations')
        ->select('event_id', DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id');

        $events = DB::table('events')
        ->leftJoinSub($reservedPeople, 'reservedPeople',
            function($join){
            $join->on('events.id', '=', 'reservedPeople.event_id');
        })
        ->whereDate('events.start_date', '<', $today)
        ->orderBy('events.start_date', 'desc')
        ->paginate(10);

        return view('manager.events.past', compact('events'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
    }
}
