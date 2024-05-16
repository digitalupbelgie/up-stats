## Installation

1. Add the git repository in the `composer.json` file:

    ```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/spatie/laravel-package-tools"
        }
    ]
    ```

2. Install the package via Composer:

    ```bash
    composer require yonidebleeker/upstats
    ```

3. Execute the package installation command:

    ```bash
    php artisan upstats::install
    ```

4. Ensure that Tailwind CSS is installed in your project. Append the following line to the `tailwind.config.js` file at the end of the `content` object:

    ```javascript
    "./vendor/yonidebleeker/upstats/resources/views/*.blade.php"
    ```

    Additionally, copy the following code into the `tailwind.config.js` if you want to customize the colors you can change them:

    ```javascript
    colors: {
      'upstats-bg-color': '#e2e8f0',
      'upstats-text-color': '#000000',
      'upstats-widget-color': '#ffffff',
    },
    ```
   
    If you wish to alter the color scheme of the graphs, you'll need to publish the dashboard view and make the adjustments directly.
    ```bash
    php artisan vendor:publish --tag=uptags-views
    ```

5. Create a route named `upstats.goback` to provide a return point when the user wants to click on the back button:

    ```php
    Route::get('/url', function () {
        // redirect here 
    })->name('upstats.goback');
    ```

## Usage

1. In your `app.php`, within the `->withMiddleware(function (Middleware $middleware) {` section, add the following:

    ```php
    $middleware->alias([
        'cookie' => AssignCookie::class,
    ]);
    ```

    Don't forget to import the middleware!

    ```php
    use Yonidebleeker\UpStats\Http\Middleware\AssignCookie;
    ```

2. To use this package, group your routes and apply the middleware:

    ```php
    Route::middleware(['cookie'])->group(function () {
        // Your routes here
    });
    ```

3. Navigate to `domain.com/upstats` to access and utilize the package dashboard within your Laravel application!

## Credits

- [Yoni De Bleeker](https://github.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
