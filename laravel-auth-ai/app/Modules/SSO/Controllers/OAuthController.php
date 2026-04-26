<?php
/*
|--------------------------------------------------------------------------
| OAuthController.php
|--------------------------------------------------------------------------
| Handles OAuth2 Authorization flow.
| This controller delegates most of the logic to Laravel Passport's
| built-in controllers to ensure compatibility and security.
|--------------------------------------------------------------------------
*/

namespace App\Modules\SSO\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizationController as BaseAuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class OAuthController
{
    public function __construct(
        private readonly BaseAuthorizationController $baseController,
        private readonly ApproveAuthorizationController $approveController,
        private readonly DenyAuthorizationController $denyController,
    ) {}

    /**
     * GET /oauth/authorize
     *
     * Shows the consent page or auto-approves if possible.
     */
    public function show(
        ServerRequestInterface $psrRequest,
        Request $request,
        ResponseInterface $psrResponse,
        AuthorizationViewResponse $viewResponse
    ): Response|AuthorizationViewResponse {
        // Ensure user is logged in before proceeding with OAuth flow
        if (! Auth::check()) {
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect()->route('login');
        }

        // Delegate to Passport's AuthorizationController.
        // It handles validation, auto-approval, and session state.
        return $this->baseController->authorize(
            $psrRequest,
            $request,
            $psrResponse,
            $viewResponse
        );
    }

    /**
     * POST /oauth/authorize
     *
     * Handles user approval.
     */
    public function approve(Request $request, ResponseInterface $psrResponse): Response
    {
        return $this->approveController->approve($request, $psrResponse);
    }

    /**
     * DELETE /oauth/authorize
     *
     * Handles user denial.
     */
    public function deny(Request $request, ResponseInterface $psrResponse): Response
    {
        return $this->denyController->deny($request, $psrResponse);
    }
}
