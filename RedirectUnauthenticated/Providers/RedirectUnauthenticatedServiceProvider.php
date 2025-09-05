<?php

namespace Modules\RedirectUnauthenticated\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\RedirectUnauthenticated\Http\Middleware\RedirectIfNotAuthenticated;

class RedirectUnauthenticatedServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Register module views
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'redirectunauthenticated');
        
        // Register module routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        
        // Hook into the End User Portal settings page to add our toggle
        \Eventy::addFilter('freescout.mailbox.settings.end_user_portal', function ($html, $mailbox) {
            // Get the current setting value from mailbox meta
            $redirectEnabled = $mailbox->meta['redirect_unauthenticated_users'] ?? false;
            
            // Load our settings view and append it to the existing HTML
            $settingsHtml = view('redirectunauthenticated::settings', [
                'mailbox' => $mailbox,
                'redirectEnabled' => $redirectEnabled
            ])->render();
            
            return $html . $settingsHtml;
        }, 20, 2);
        
        // Hook into the save action to persist our toggle setting
        \Eventy::addAction('freescout.mailbox.settings.end_user_portal.save', function ($mailbox, $request) {
            // Get the toggle value from the request (checkbox will be 'on' if checked)
            $redirectEnabled = $request->has('redirect_unauthenticated_users');
            
            // Save to mailbox meta
            $meta = $mailbox->meta;
            $meta['redirect_unauthenticated_users'] = $redirectEnabled;
            $mailbox->meta = $meta;
            $mailbox->save();
        }, 20, 2);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register the middleware
        $this->app['router']->aliasMiddleware('redirect.unauthenticated', RedirectIfNotAuthenticated::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}