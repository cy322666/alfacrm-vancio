<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\AlfaCRM\Client;
use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Notes;
use Illuminate\Http\Request;
use App\Services\amoCRM\Client as amoApi;
use App\Services\AlfaCRM\Client as alfaApi;
use Illuminate\Support\Facades\Log;
use Nikitanp\AlfacrmApiPhp\Entities\Customer;

class amoController extends Controller
{
    private \Nikitanp\AlfacrmApiPhp\Client $alfaApi;
    protected amoApi $amoApi;

    public function __construct(
        Request $request,
    ) {
        parent::__construct();

        Log::info(__METHOD__. ' > '.$request->method(), $request->toArray());

        $this->alfaApi = alfaApi::init();
    }

    //записан на пробное
    public function recorded(Request $request)
    {
        try {
            $requestArr = $request->toArray()['leads']['status'][0] ?? $request->toArray()['leads']['add'][0];

            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($requestArr['id']);

            $contact = $lead->contact;

            $branchId = match ($lead->pipeline_id) {
                Client::PIPELINE_1_ID => Client::BRANCH_1_ID,
                Client::PIPELINE_2_ID => Client::BRANCH_2_ID,
            };

            $studyId = match ($lead->status_id) {
                Client::STATUS_ID_RECORDED_1 => Client::STATUS_RECORDED_1,
                Client::STATUS_ID_OMISSION_1 => Client::STATUS_OMISSION_1,
                Client::STATUS_ID_CAME_1     => Client::STATUS_CAME_1,

                Client::STATUS_ID_RECORDED_2 => Client::STATUS_RECORDED_2,
                Client::STATUS_ID_OMISSION_2 => Client::STATUS_OMISSION_2,
                Client::STATUS_ID_CAME_2     => Client::STATUS_CAME_2,
            };

            $model = Lead::query()
                ->where('contact_id', $contact->id)
                ->first();

            if (!$model) {

                $model = Lead::query()
                    ->create([
                        'amo_contact_id'    => $contact->id ?? null,
                        'amo_contact_phone' => Contacts::clearPhone($contact->cf('Телефон')->getValue()),
                        'amo_contact_email' => $contact->cf('Email')->getValue(),
                        'amo_contact_name'  => $contact->name,
                        'alfa_branch_id'    => $branchId,
                        'amo_lead_id'       => $lead->id,
                    ]);

                $response = (new Customer($this->alfaApi))
                    ->create([
                        'name'       => $model->amo_contact_name,
                        'branch_ids' => [$model->alfa_branch_id],
                        'is_study'   => Client::CLIENT_STUDY,
                        'legal_type' => Client::CLIENT_TYPE_ID,
                        'phone'      => $model->amo_contact_phone,
                        'legal_name' => $model->amo_contact_name,
                        'email'      => $model->amo_contact_email,
                        'lead_status_id' => $studyId,
                        'web'        => [
                            "https://".env('AMO_SUBDOMAIN').".amocrm.ru/contacts/detail/$contact->id",
                            "https://".env('AMO_SUBDOMAIN').".amocrm.ru/leads/detail/$lead->id",
                        ]
                    ]);

            } else {

                $response = (new Customer($this->alfaApi))
                    ->update($model->alfa_client_id, [
                        'name'       => $model->amo_contact_name,
                        'branch_ids' => [$model->alfa_branch_id],
                        'is_study'   => Client::CLIENT_STUDY,
                        'legal_type' => Client::CLIENT_TYPE_ID,
                        'phone'      => $model->amo_contact_phone,
                        'legal_name' => $model->amo_contact_name,
                        'email'      => $model->amo_contact_email,
                        'lead_status_id' => $studyId,
                        'web'        => [
                            "https://".env('AMO_SUBDOMAIN').".amocrm.ru/contacts/detail/$contact->id",
                            "https://".env('AMO_SUBDOMAIN').".amocrm.ru/leads/detail/$lead->id",
                        ]
                    ]);
            }

            if ($response['success'] == true)

                $customerId = $response['model']['id'];
            else
                Log::error(__METHOD__, $response);

            $model->alfa_client_id = $customerId ?? null;
            $model->status = $studyId;
            $model->save();

            Notes::addOne($lead, 'Успешно отправлен в AlfaCRM');

            $lead->cf('Link AlfaCRM')
                ->setValue(
                    env('ALFACRM_DOMAIN')."/company/$model->alfa_branch_id/customer/view?id=$model->alfa_client_id"
                );
            $lead->save();

        } catch (\Exception $exception) {

            Log::error(__METHOD__.' : '.$exception->getMessage());
        }
    }
}
