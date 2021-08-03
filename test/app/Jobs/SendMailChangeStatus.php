<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Booking;
use Illuminate\Support\Facades\Log;
class SendMailChangeStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $ids;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $bookings = Booking::where('id',$this->ids)->get();
        if(!empty($bookings)){
            $i=1;
            foreach ($bookings as $booking) {
                Log::info('mail job run '.$i++);
                sendnotification($booking);
            }
        }
    }
}
