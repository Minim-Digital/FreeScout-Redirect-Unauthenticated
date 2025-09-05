<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RedirectUnauthenticated Module Routes
|--------------------------------------------------------------------------
|
| Here we apply our middleware to the End User Portal main page route.
| We're using a route group with high priority to ensure our middleware
| runs before the default End User Portal route is processed.
|
*/

// Apply middleware to the main End User Portal route
// Note: We only apply it to the main portal page, NOT to the auth page to avoid redirect loops
Route::group(['middleware' => 'web'], function () {
    // Override the main portal route with our middleware
    Route::get('/help/{mailbox_id}', function ($mailboxId) {
        // This route definition ensures our middleware is applied
        // The actual handling will be done by the End User Portal module
        // We're just intercepting to check authentication
        
        // Get the controller from the End User Portal module
        $controller = app()->make('Modules\EndUserPortal\Http\Controllers\EndUserPortalController');
        
        // Call the index method
        return $controller->index(request(), $mailboxId);
    })->middleware('redirect.unauthenticated')
      ->where('mailbox_id', '[0-9]+')
      ->name('enduser.portal.redirect');
});