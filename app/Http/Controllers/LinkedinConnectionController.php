<?php

namespace App\Http\Controllers;

use App\Jobs\ConnectLinkedinProfile;
use App\Models\LinkedinProfile;
use App\Services\LinkedinUsernameExtractor;
use Illuminate\Http\Request;

class LinkedinConnectionController extends Controller
{
    public function index()
    {
        $profiles = LinkedinProfile::query()->paginate();

        return view('linkedin-profiles', compact('profiles'));
    }

    public function store(Request $request, LinkedinUsernameExtractor $extractor)
    {
        $content = $request->file('file')->get();

        $usernames = $extractor->handle($content);

        $usernames->each(
            // When dealing with hundreds of data, it's a better to dispatch jobs with a delay in order to avoid
            // falling into rate limiting trap.
            fn($username, $index) => ConnectLinkedinProfile::dispatch($username)->delay($index * 5 + 1)
        );

        return redirect()->route('linkedin-connections.index');
    }
}
