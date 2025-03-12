<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Notifications\CommonNotification;
use App\Models\Fcm;
use App\Models\User;
class sendNotifications implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $students, public $name)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach($this->students as $student)
        {    if(Fcm::where('user_id',$student->id)->exists())
            { 
             User::find($student->id)->notify(new CommonNotification('paper','Paper'. $this->name));
           
            }

        }
    }
}
