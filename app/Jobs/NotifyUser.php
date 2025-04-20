<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;
use App\Models\User;

class NotifyUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    private $user;

    private $transactionData;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $transactionData)
    {
        $this->user = $user;
        $this->transactionData = $transactionData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notificationService = new NotificationService;
        $notificationService->sendNotification($this->user, $this->transactionData);
    }
}
