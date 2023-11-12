<?php

namespace App\Jobs;

use App\Models\LinkedinProfile;
use App\Services\LinkedinHttpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** @method static void dispatch(string $username) */
class ConnectLinkedinProfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(private readonly string $username)
    {
        //
    }

    public function handle(LinkedinHttpService $linkedinHttpService): void
    {
        /** @var LinkedinProfile $profile */
        $profile = LinkedinProfile::query()
            ->firstOrCreate(
                ['username' => $this->username],
                ['status' => 'pending']
            );

        if ($profile->status !== 'pending') {
            return;
        }

        $connected = $linkedinHttpService->isConnected($profile->username);

        if ($connected) {
            $profile->update(['status' => 'connected']);
        }

        $message = config('services.linkedin.message');

        $response = $linkedinHttpService->connect($profile->username, $message);

        if ($response->status() === 201) {
            $profile->update(['status' => 'requested']);
            return;
        }

        $this->release();
    }
}
