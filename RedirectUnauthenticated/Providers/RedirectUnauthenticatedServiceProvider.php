<?php

namespace Modules\RedirectUnauthenticated\Providers;

use Illuminate\Support\ServiceProvider;

class RedirectUnauthenticatedServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Register module views with higher priority
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'redirectunauthenticated');
        
        // Override the End User Portal settings view
        \View::creator('enduserportal::settings', function ($view) {
            $mailbox = $view->getData()['mailbox'];
            $redirectEnabled = $mailbox->meta['redirect_unauthenticated_users'] ?? false;
            
            // Pass our setting to the view
            $view->with('redirect_unauthenticated_users', $redirectEnabled);
        });
        
        // Hook into the route processing to intercept settings save
        $this->app['events']->listen('Illuminate\Routing\Events\RouteMatched', function ($event) {
            $route = $event->route;
            $request = $event->request;
            
            // Check if this is the EndUserPortal settings save route (POST to /mailbox/{id}/end-user-portal)
            if ($route && $request->isMethod('POST') && strpos($route->uri(), 'mailbox/{mailbox_id}/end-user-portal') !== false) {
                // Check if this is a settings save action
                if ($request->has('eup_action') && $request->get('eup_action') === 'save_settings') {
                    // Get the mailbox ID from the route parameter
                    $mailboxId = $route->parameter('mailbox_id');
                    if ($mailboxId) {
                        $mailbox = \App\Mailbox::find($mailboxId);
                        if ($mailbox) {
                            // Get the toggle value
                            $redirectEnabled = $request->has('redirect_unauthenticated_users');
                            
                            // Save our setting to mailbox meta
                            $mailbox->setMetaParam('redirect_unauthenticated_users', $redirectEnabled);
                            $mailbox->save();
                        }
                    }
                }
            }
        });
        
        // Hook into the End User Portal to perform redirect
        \Eventy::addAction('enduserportal.init', function($mailbox) {
            // Check if redirect is enabled for this mailbox
            $redirectEnabled = $mailbox->meta['redirect_unauthenticated_users'] ?? false;
            
            // If redirect is enabled and user is not authenticated
            if ($redirectEnabled && \Auth::guest()) {
                // Get the encoded mailbox ID
                $encodedId = class_exists('\EndUserPortal') ? 
                    \EndUserPortal::encodeMailboxId($mailbox->id) : $mailbox->id;
                
                // Redirect to auth page
                header('Location: ' . route('enduserportal.login', ['mailbox_id' => $encodedId]));
                exit;
            }
        }, 5, 1);
        
        // Alternative approach: Check on route access
        $this->app['router']->matched(function ($event) {
            $route = $event->route;
            if ($route && $route->getName() === 'enduserportal.submit') {
                $mailboxId = $route->parameter('mailbox_id');
                
                if ($mailboxId && class_exists('\EndUserPortal')) {
                    $decodedId = \EndUserPortal::decodeMailboxId($mailboxId);
                    $mailbox = \App\Mailbox::find($decodedId);
                    
                    if ($mailbox) {
                        $redirectEnabled = $mailbox->meta['redirect_unauthenticated_users'] ?? false;
                        
                        if ($redirectEnabled && \Auth::guest()) {
                            // Redirect to auth page
                            header('Location: ' . route('enduserportal.login', ['mailbox_id' => $mailboxId]));
                            exit;
                        }
                    }
                }
            }
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register view namespace with higher priority
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'enduserportal');
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