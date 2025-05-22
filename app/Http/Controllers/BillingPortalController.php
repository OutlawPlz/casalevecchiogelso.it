<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

class BillingPortalController extends Controller
{
    /**
     * @throws ApiErrorException
     */
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var ?User $authUser */
        $authUser = $request->user();

        $url = $authUser->createBillingPortalSession()->url;

        return redirect($url);
    }
}
