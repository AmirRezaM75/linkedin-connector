<?php

namespace App\Http\Controllers;

use App\Jobs\ConnectLinkedinProfile;
use App\Services\LinkedinUsernameExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkedinConnectionController extends Controller
{
    public function store(Request $request, LinkedinUsernameExtractor $extractor)
    {
        $content = $request->file('file')->get();

        $usernames = $extractor->handle($content);

        $usernames->each(fn($username) => ConnectLinkedinProfile::dispatch($username));

//        return redirect()->to();
    }
}
