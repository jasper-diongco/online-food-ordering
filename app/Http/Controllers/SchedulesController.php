<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchedulesController extends Controller
{
    public function index(Request $request) {
        $store_id = $request->store_id ?? '';

        $schedules = Schedule::where('store_id', $store_id)->get();

        return [
            'schedules' => $schedules
        ];
    }

    public function show($schedule_id) {
        $schedule = Schedule::findOrFail($schedule_id);

        return [
            'schedule' => $schedule
        ];
    }

    public function store(Request $request) {
        $request->validate([
            'store_id' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'location_description' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'days' => 'required',
        ]);
        
        $this->validateTime($request, 0);

        $schedule = Schedule::create($request->all());

        return [
            'schedule' => $schedule
        ];
    }

    public function update(Request $request, $schedule_id) {
        $request->validate([
            'store_id' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'location_description' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'days' => 'required',
        ]);

        $this->validateTime($request, $schedule_id);

        $schedule = Schedule::findOrFail($schedule_id);

        $schedule->update($request->all());

        return [
            'schedule' => $schedule
        ];
    }

    public function validateTime(Request $request, $schedule_id) {
        $conflict_message = '';

        $schedules = Schedule::where('store_id', $request->store_id)->whereNot('id', $schedule_id)->get();
        $days_str = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($schedules as $_schedule) {

            $days_param = json_decode($request->days);
            $days_sched = json_decode($_schedule->days);

            for ($i = 0; $i < count($days_param); $i++) {
                if ($days_param[$i] == true && $days_sched[$i] == true) {
                    $start = Carbon::createFromTimeString($_schedule->start_time);
                    $end = Carbon::createFromTimeString($_schedule->end_time);

                    $start_param = Carbon::createFromTimeString($request->start_time);
                    $end_param = Carbon::createFromTimeString($request->end_time);

                    if ($start_param->between($start, $end) || $end_param->between($start, $end) || $start->between($start_param, $end_param) || $end->between($start_param, $end_param)) {
                        $conflict_message = 'You have conflict schedule. You already have '. $days_str[$i] . ' ' . $start->format('g:i A') . ' - ' . $end->format('g:i A'); 
                        abort(422, $conflict_message);
                    }
                }
            }
        }
    }

    public function destroy($schedule_id) {

        $schedule = Schedule::findOrFail($schedule_id);

        $schedule->delete();

        return [
            'message' => 'Schedule Deleted'
        ];
    }
}
