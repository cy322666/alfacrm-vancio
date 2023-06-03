<?php

namespace App\Services\AlfaCRM;

use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use GuzzleHttp\Client as Guzzle;

class Client
{
    const BRANCH_1_ID = 1;//ромама
    const PIPELINE_1_ID = 6850990;

    const STATUS_ID_RECORDED_1 = 57761682;
    const STATUS_ID_OMISSION_1 = 57847290;
    const STATUS_ID_CAME_1 = 57847294;

    const STATUS_RECORDED_1 = 1;
    const STATUS_OMISSION_1 = 26;
    const STATUS_CAME_1 = 3;

    const BRANCH_2_ID = 2;//чисинай
    const PIPELINE_2_ID = 6850994;

    const STATUS_RECORDED_2 = 1;
    const STATUS_OMISSION_2 = 26;
    const STATUS_CAME_2 = 3;

    const STATUS_ID_RECORDED_2 = 57761682;
    const STATUS_ID_OMISSION_2 = 57847290;
    const STATUS_ID_CAME_2 = 57847294;

    const CLIENT_TYPE_ID = 1; //физик
    const CLIENT_STUDY = 0; //is_study 0 - лид 1 - клиент

    const STATUS_PAY = 10;

    public static function init(): \Nikitanp\AlfacrmApiPhp\Client
    {
        $apiClient = new \Nikitanp\AlfacrmApiPhp\Client(
            new Guzzle,
            new RequestFactory,
            new StreamFactory,
        );
        $apiClient->setDomain(env('ALFACRM_DOMAIN'));
        $apiClient->setEmail(env('ALFACRM_EMAIL'));
        $apiClient->setApiKey(env('ALFACRM_API_KEY'));
        $apiClient->authorize();

        return $apiClient;
    }
}
