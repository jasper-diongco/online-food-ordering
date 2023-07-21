<?php

namespace App\Invocable;

use App\Models\Schedule;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateStoreLocation
{
    public function __invoke()
    {
        $schedules = Schedule::all();
        $now = Carbon::now();

        $dayIndex = $now->dayOfWeekIso - 1;

        foreach ($schedules as $schedule) {
            $days_arr = json_decode($schedule->days);

            if ($days_arr[$dayIndex]) {
                $start = Carbon::createFromTimeString($schedule->start_time);
                $end = Carbon::createFromTimeString($schedule->end_time);

                if ($now->between($start, $end)) {
                    $store = Store::find($schedule->store_id);

                    $store->latitude = $schedule->latitude;
                    $store->longitude = $schedule->longitude;
                    $store->location_description = $schedule->location_description;
                    $store->update();

                    $this->sendNotificationToSubscribers($store);
                }
            }
        }
    }

    public function sendNotificationToSubscribers($store) {

    }
}