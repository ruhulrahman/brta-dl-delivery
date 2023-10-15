<?php

namespace App\Jobs;

use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class DlStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $list, $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($list, $user_id)
    {
        $this->list = $list;
        $this->$user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->list as $item){

            if ($item) {
                model('DlStock')::create([
                    'reference_number' => $item['reference_number'] ? $item['reference_number'] : NULL,
                    'serial_number' => $item['serial_number'] ? $item['serial_number'] : NULL,
                    'entry_box_number' => $item['entry_box_number'] ? $item['entry_box_number'] : NULL,
                    'receiving_box_number' => $item['receiving_box_number'] ? $item['receiving_box_number'] : NULL,
                    'receive_date' => $item['receive_date'] ? new Carbon($item['receive_date']) : NULL,
                    // 'delivery_date' => new Carbon($item['delivery_date']),
                    'delivery_date' => $item['delivery_date'] ? $item['delivery_date'] : NULL,
                    'comment' => $item['comment'] ? $item['comment'] : NULL,
                    'creator_id' => $this->user_id,
                    'created_at' => Carbon::now(),
                ]);
            }
        }
    }
}
