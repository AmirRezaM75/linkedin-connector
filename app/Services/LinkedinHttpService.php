<?php

namespace App\Services;

use App\Exceptions\LinkedinChallengeException;
use App\Exceptions\MaxMessageLengthException;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LinkedinHttpService
{
    public function __construct(private readonly string $username, private readonly string $password)
    {
    }

    /** @throws LinkedinChallengeException */
    private function login(): void
    {
        if ($this->getCookies()) {
            return;
        }

        $this->initCookies();

        $response = Http::asForm()
            ->withHeaders($this->getAuthenticationHeaders())
            ->withCookies($this->getCookies(), 'linkedin.com')
            ->post("https://www.linkedin.com/uas/authenticate", [
                'session_key' => $this->username,
                'session_password' => $this->password,
                'JSESSIONID' => $this->getCsrfToken(),
            ]);

        if ($response->json('login_result') !== 'PASS') {
            throw new LinkedinChallengeException;
        }

        $this->setCookies($response->cookies);
    }

    private function setCookies(CookieJar $cookies): void
    {
        $expiration = collect($cookies->toArray())
            ->filter(fn($cookie) => $cookie['Expires'] !== null)
            ->min('Expires');

        $ttl = Carbon::parse($expiration)->diffInSeconds(Carbon::now());

        $items = [];

        foreach ($cookies as $cookie) {
            $items[$cookie->getName()] = trim($cookie->getValue(), '"');
        }

        Cache::remember('linkedin-cookies', $ttl, fn() => $items);
    }

    private function getCookies(): array|null
    {
        // LinkedIn will ask you to solve a challenge in the form of captcha
        // When detected unusual traffic from your account.
        // It's only possible with image processing.
        // In order to don't stuck in this step. We recommend you to login into your account via browser
        // open the console and execute `document.cookie` and past the value into `storage/cookies.txt` file.
        $content = file_get_contents(storage_path('app/cookies.txt'));

        if (!empty($content)) {
            return $this->cookieAsArray($content);
        }

        return Cache::get('linkedin-cookies');
    }

    private function getProfileView(string $username): Response
    {
        $this->login();

        return Http::withHeaders($this->getProfileHeaders())
            ->withCookies($this->getCookies(), 'linkedin.com')
            ->get("https://www.linkedin.com/voyager/api/identity/profiles/$username/profileView");
    }

    private function getProfileHeaders(): array
    {
        return [
            "accept" => "*/*",
            "Accept-Encoding" => "gzip, deflate",
            "Connection" => "keep-alive",
            "x-li-lang" => "en_US",
            "x-restli-protocol-version" => "2.0.0",
            "accept-language" => "en-AU,en-GB;q=0.9,en-US;q=0.8,en;q=0.7",
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:102.0) Gecko/20100101 Firefox/102.0',
            'csrf-token' => $this->getCsrfToken(),
        ];
    }

    private function getNetworkInfo(string $username): Response
    {
        $this->login();

        return Http::withHeaders($this->getProfileHeaders())
            ->withCookies($this->getCookies(), 'linkedin.com')
            ->get("https://www.linkedin.com/voyager/api/identity/profiles/$username/networkinfo");
    }

    public function isConnected(string $username): bool
    {
        $response = $this->getNetworkInfo($username);

        return $response->status() === 200 and $response->json('distance.value') !== 'OUT_OF_NETWORK';
    }

    /** @throws MaxMessageLengthException */
    public function connect(string $username, string $message = ''): Response
    {
        $response = $this->getProfileView($username);
        $urn = Str::afterLast($response->json('profile.entityUrn'), ':');

        if (strlen($message) > 300) {
            throw new MaxMessageLengthException();
        }

        $payload = [
            "trackingId" => $this->generateTrackingId(),
            "message" => $message,
            "invitations" => [],
            "excludeInvitations" => [],
            "invitee" => [
                "com.linkedin.voyager.growth.invitation.InviteeProfile" => [
                    "profileId" => $urn
                ]
            ],
        ];

        return Http::withHeaders(
            ["accept" => "application/vnd.linkedin.normalized+json+2.1"] + $this->getProfileHeaders()
        )
            ->withCookies($this->getCookies(), 'linkedin.com')
            ->post("https://www.linkedin.com/voyager/api/growth/normInvitations", $payload);

    }

    private function getAuthenticationHeaders(): array
    {
        return [
            "X-Li-User-Agent" => "LIAuthLibrary:3.2.4 com.linkedin.LinkedIn:8.8.1 iPhone:8.3",
            "User-Agent" => "LinkedIn/8.8.1 CFNetwork/711.3.18 Darwin/14.0.0",
            "X-User-Language" => "en",
            "X-User-Locale" => "en_US",
            "Accept-Language" => "en-us",
        ];
    }

    private function initCookies(): void
    {
        $response = Http::withHeaders($this->getAuthenticationHeaders())
            ->get("https://www.linkedin.com/uas/authenticate");

        $this->setCookies($response->cookies);
    }

    private function getCsrfToken(): string
    {
        return $this->getCookies()['JSESSIONID'];
    }

    private function generateTrackingId(): string
    {
        $numbers = [];
        for ($i = 0; $i < 16; $i++) {
            $numbers[] = mt_rand(0, 255);
        }

        $byte = pack('C*', ...$numbers);
        return base64_encode($byte);
    }

    private function cookieAsArray(string $content): array {

        $content = trim($content);

        $cookies = explode(';', $content);

        $output = array();

        foreach ($cookies as $cookie) {
            $parts = explode('=', $cookie, 2);

            $name = trim($parts[0]);
            $value = isset($parts[1]) ? urldecode(trim($parts[1])) : '';

            // Store the cookie in the PHP array
            $output[$name] = $value;
        }

        return $output;
    }
}
