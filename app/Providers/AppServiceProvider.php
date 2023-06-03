<?php

namespace App\Providers;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\EloquentStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Client::class, function ($app) {

            $account = Account::query()->first();

            return (new Client())->init(new EloquentStorage([
                'domain'    => $account->subdomain,
                'client_id' => $account->client_id,
                'client_secret' => $account->client_secret,
                'redirect_uri'  => $account->redirect_uri,
            ], $account));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
