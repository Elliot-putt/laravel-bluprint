<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\InvalidStateException;

class HandleAccountController extends Controller
{
    public function __invoke(Request $request, AccountService $service)
    {
        if ($this->userCancelledAuth($request)) {
            return $this->redirectToLoginWithError('GitHub authentication was cancelled.');
        }

        try {
            $service->handleCallback();
            return to_route('dashboard');
        } catch (InvalidStateException|Exception $e) {
            return $this->redirectToLoginWithError('Authentication failed. Please try again.');
        }
    }

    private function userCancelledAuth(Request $request): bool
    {
        return $request->has('error') && $request->get('error') === 'access_denied';
    }

    private function redirectToLoginWithError(string $message)
    {
        return redirect()->route('login')->with('error', $message);
    }

}
