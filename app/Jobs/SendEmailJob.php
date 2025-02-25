<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

//use Mail;
class SendEmailJob implements ShouldQueue
{
    use  InteractsWithQueue, Queueable, SerializesModels;

    protected $details;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = data_get($this->details, 'data.email');
        $body = data_get($this->details, 'data.email_body');
        if (isset($email) && isset($body)) {
            Mail::to($email)->send($body);
        }
    }
}