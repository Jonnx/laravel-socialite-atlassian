# Laravel Socialite Atlassian Driver

[![CircleCI](https://circleci.com/gh/Jonnx/laravel-socialite-atlassian/tree/master.svg?style=svg)](https://circleci.com/gh/Jonnx/laravel-socialite-atlassian/tree/master)

Leverage Laravel Socialtie to provide login and api access authorization for you application with the Connected Apps API for Atlassian Cloud products. 

## Getting Started

There are only a few steps to register the ```atlassian``` socialite driver. After that you can leverage the generic Socialite implemententation to authenticate users.

#### Install Composer Package
```
composer require jonnx/laravel-socialite-atlassian
```

#### Update Configuration
You will need to add your client application configuration to the `config/services.php` file. You can generate these keys registering
your application on https://developer.atlassian.com.

```
    'atlassian' => [
        'client_id'        => env('ATLASSIAN_APP_ID'),
        'client_secret'    => env('ATLASSIAN_APP_SECRET'),
        'redirect'         => '/login/callback',
        'base_uri'         => 'https://id.atlassian.com',
    ],
```

Make sure you add & set the following 2 values in your ```.env``` file:
```
ATLASSIAN_APP_KEY=
ATLASSIAN_APP_SECRET=
```

#### Register Atlassian Socialite Driver
Update the ```AppServiceProvider.php``` boot function to call a private method to extend Laravel Socialite with the new driver.

```
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootAtlassianSocialite();
    }

    private function bootAtlassianSocialite()
    {
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'atlassian',
            function ($app) use ($socialite) {
                $config = $app['config']['services.atlassian'];
                return $socialite->buildProvider(AtlassianSocialiteProvider::class, $config);
            }
        );
    }
```

#### Done
Now you should be able to easily redirect users to Atlassian to login and request permissions:

```
return Socialite::with('atlassian')
    ->scopes([
        'read:me', 
        'read:jira-work', 
        'write:jira-work' 
        'offline_access'
    ])
    ->redirect();
```

and resolve the user information from Atlassian on callback:

```
$atlassianUser = Socialite::driver('atlassian')->user();
```

## License

The Laravel Socialite Atlassian driver is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).