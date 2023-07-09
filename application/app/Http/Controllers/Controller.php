<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\EloquentStorage;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected Client $amoApi;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        try {
            $account = Account::query()->first();

            $this->amoApi = (new Client($account))->init();

        } catch (\Throwable $e) {

            Log::error(__METHOD__.' : '.$e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }
    }
}
