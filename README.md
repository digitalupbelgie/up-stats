## Requirements

- **PHP:** version 8.1 or higher
- **Tailwind:** version 3.4.3
- **Laravel:** version 10 or higher

## Installation

1. Add the git repository in the `composer.json` file:

    ```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/digitalupbelgie/up-stats"
        }
    ]
    ```

2. Install the package via Composer:

    ```bash
    composer require digitalup/upstats
    ```

3. Execute the package installation command:

    ```bash
    php artisan upstats:install
    ```

4. Ensure that Tailwind CSS is installed in your project. Append the following line to the `tailwind.config.js` file at the end of the `content` object:

    ```javascript
    "./vendor/digitalup/upstats/resources/views/*.blade.php"
    ```

 5. Additionally, copy the following code into tailwind.config.js if you want to customize the colors. To modify them, add this to the theme -> extend object:

    ```javascript
    colors: {
      'upstats-bg-color': '#e2e8f0',
      'upstats-text-color': '#000000',
      'upstats-widget-color': '#ffffff',
      'upstats-widget-title-color': '#000000',
      'upstats-widget-widget-text-color': '#000000',
      'upstats-backbutton-color': '#ffffff',
      'upstats-backbutton-text-color': '#000000'
    },
    ```
   
    If you wish to alter the color scheme of the graphs, you'll need to publish the dashboard view and make the adjustments directly.
    ```bash
    php artisan vendor:publish --tag=upstats-views
    ```

5. Create a route named `upstats.goback` to provide a return point when the user wants to click on the back button. This is required.

    ```php
    Route::get('/url', function () {
        // redirect here 
    })->name('upstats.goback');
    ```

6. To protect your routes, implement the UpStatsUser interface in your user model and import the required function. This step is crucial! If you wish to define your own access rules, create a custom middleware and associate it with the name "upstatsAdmin". By doing so, you can bypass step 7.
    ```php
    public function canAccessStats(): bool {
        // return true or false
    }
    ```

7. **In Laravel 11**, within your `bootstrap/app.php`, within the `->withMiddleware(function (Middleware $middleware) {` section, implement the middleware to give specific users access to the dashboard.

    ```php
    $middleware->alias([
        'upstatsAdmin' => UpstatsAdmin::class,
    ]);
    ```
    Don't forget to import the middleware!

    ```php
    use Digitalup\UpStats\Http\Middleware\UpstatsAdmin;
    ```

    **In Laravel 10**, you need to add this line :
    ```php
    'cookie' =>  \Digitalup\UpStats\Http\Middleware\AssignCookie::class,
    ``` 
    in `app/http/kernel.php` in the `$middlewareAliases` array.

## Usage

1. **In Laravel 11**, within your `bootstrap/app.php`, within the `->withMiddleware(function (Middleware $middleware) {` section, add the following in the `middleware -> alias` array:

    ```php
        'cookie' => AssignCookie::class,
    ```

    Don't forget to import the middleware!

    ```php
    use Digitalup\UpStats\Http\Middleware\AssignCookie;
    ```
    **In Laravel 10**, you need to add this line :
     ```php
    'upstatsAdmin' => \Digitalup\UpStats\Http\Middleware\UpStatsAdmin::class,
    ``` 
    in `app/http/kernel.php` in the `$middlewareAliases` array.

2. To utilize this package, group the routes you wish to monitor for statistics and then apply the middleware:

    ```php
    Route::middleware(['cookie'])->group(function () {
        // Place your routes to be monitored here
    });
    ```

3. Navigate to `domain.com/upstats` to access and utilize the package dashboard within your Laravel application!

## Cookies

**Note:** This package uses cookies, specifically the `upstats_user_cookie` cookie. Therefore, you must comply with a cookie policy and have a cookie banner in place.

## Credits

- [Yoni De Bleeker](https://github.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
