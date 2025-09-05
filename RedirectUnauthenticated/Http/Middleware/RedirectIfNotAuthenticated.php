<?php

namespace Modules\RedirectUnauthenticated\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Mailbox;

class RedirectIfNotAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the mailbox ID from the route parameter
        $mailboxId = $request->route('mailbox_id');
        
        if (!$mailboxId) {
            // If no mailbox ID is found, let the request continue
            return $next($request);
        }
        
        // Get the mailbox instance
        $mailbox = Mailbox::find($mailboxId);
        
        if (!$mailbox) {
            // If mailbox not found, let the request continue (will be handled elsewhere)
            return $next($request);
        }
        
        // Check if the redirect setting is enabled for this mailbox
        $redirectEnabled = $mailbox->meta['redirect_unauthenticated_users'] ?? false;
        
        // If the setting is enabled and the user is a guest (not authenticated)
        if ($redirectEnabled && Auth::guest()) {
            // Redirect to the authentication page for this mailbox
            return redirect()->route('enduser.auth', ['mailbox_id' => $mailboxId]);
        }
        
        // Otherwise, allow the request to proceed normally
        return $next($request);
    }
}