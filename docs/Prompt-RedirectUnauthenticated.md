**Project:** New FreeScout Module: "Redirect Unauthenticated Users"

**Objective:**
Develop a custom FreeScout module named "RedirectUnauthenticated". This module will add a new toggle setting to the "End User Portal" settings page for each mailbox. When this setting is enabled, any unauthenticated user who attempts to visit the main portal URL (`/help/{id}`) will be automatically redirected to the authentication URL (`/help/{id}/auth`).

**Background & Context:**
FreeScout is an open-source help desk application built on the Laravel PHP framework. We are using a module called "End User Portal" which, by default, displays a "Submit a ticket" form to all visitors, whether they are logged in or not.

Our goal is to restrict access to this form, forcing unauthenticated users to log in first. We have decided against client-side (JavaScript) redirects because they are unreliable and can be bypassed. We have also ruled out modifying FreeScout's core files or the End User Portal module's files directly, as these changes would be lost during future updates.

A key consideration was how to determine a user's authentication status. We observed that all users (both authenticated and unauthenticated) have a `laravel_session` cookie. This is expected Laravel behavior. The session cookie only contains an ID; the actual authentication state is stored on the server and is reliably checked via Laravel's `Auth` facade. Therefore, our solution must be a server-side implementation.

**Chosen Implementation Strategy:**
The most robust and maintainable approach is to create a self-contained, custom FreeScout module. This approach leverages the framework's intended extension points and ensures our functionality is isolated and upgrade-safe.

The core of our module will be a custom **Middleware**. This middleware will be responsible for:

1.  Checking if our new "Redirect unauthenticated users" setting is enabled for the specific mailbox being accessed.
2.  Checking if the current user is authenticated using Laravel's `Auth::guest()` helper.
3.  Performing a 302 redirect to the authentication page if both conditions are met.

**Development Plan & Directory Structure:**
You are currently in the project root directory, `FreeScout-Redirect-Unauthenticated/`. The module itself will be developed inside the `RedirectUnauthenticated/` subdirectory.

Please proceed with the following steps to build the module:

**Step 1: Generate Module Boilerplate**
First, create the standard directory and file structure for a new FreeScout module inside the `RedirectUnauthenticated/` directory. This should include:

- `module.json` for module metadata.
- `Providers/RedirectUnauthenticatedServiceProvider.php` (the module's service provider).
- `Http/` and `Routes/` directories.
- `Resources/views/` directory.

**Step 2: Add the Toggle Setting to the Admin UI**
In the `boot()` method of `RedirectUnauthenticatedServiceProvider.php`, you will use FreeScout's event system (`Eventy`) to hook into two specific events:

1.  `freescout.mailbox.settings.end_user_portal`: Use this filter to inject the HTML for our new toggle switch into the settings page. The setting's value should be stored in the mailbox's `meta` field (e.g., as `redirect_unauthenticated_users`).
2.  `freescout.mailbox.settings.end_user_portal.save`: Use this action to save the state of our toggle switch when the form is submitted.

Create a new Blade view file at `Resources/views/settings.blade.php` to contain the HTML for the toggle switch.

**Step 3: Create the Redirect Middleware**
Create a new middleware class at `Http/Middleware/RedirectIfNotAuthenticated.php`. This class will contain the core logic. Inside its `handle()` method, it must:

- Get the current mailbox instance.
- Read the `redirect_unauthenticated_users` value from the mailbox's meta field.
- If the setting is `true` and `Auth::guest()` is `true`, return a redirect to the `enduser.auth` route.
- Otherwise, allow the request to proceed.

**Step 4: Register and Apply the Middleware**
Create a `Routes/web.php` file for the module. Within this file, define the route for the end user portal's main page (`/help/{id}`) and apply your new `RedirectIfNotAuthenticated` middleware to it. This will ensure our logic is executed only for the intended page.

Please begin with Step 1 and proceed sequentially. I will be here to review the code and provide guidance.
