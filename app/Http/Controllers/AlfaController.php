<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\AlfaCRM\Client;
use App\Services\amoCRM\Helpers\Notes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nikitanp\AlfacrmApiPhp\Entities\Lesson;

class AlfaController extends Controller
{
    private \Nikitanp\AlfacrmApiPhp\Client $alfaApi;

    public function __construct(
        Request $request)
    {
        parent::__construct();

        Log::info(__METHOD__. ' > '.$request->url(), $request->toArray());

        $this->alfaApi = Client::init();
    }

    //посетил пробное
    public function came(Request $request)
    {
        $less = (new Lesson($this->alfaApi))->getFirst(['id' => $request->entity_id]);

        $branchId   = $less['branch_id'];
        $customerId = $less['customer_ids'][0];

        $model = Lead::query()
            ->where('alfa_branch_id', $branchId)
            ->where('alfa_client_id', $customerId)
            ->firstOrFail();

        if ($model->status == 1) {

            $statusId = match ($branchId) {
                Client::BRANCH_1_ID => Client::STATUS_ID_CAME_1,
                Client::BRANCH_2_ID => Client::STATUS_ID_CAME_2,
            };

            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($model->amo_lead_id);

            $lead->status_id = $statusId;
            $lead->save();

            Notes::addOne($lead, 'Клиент пришел на пробное, карточка обновлена');

            $model->status = Client::STATUS_CAME_1;
            $model->save();
        }
//        else {
//
//                $customer = (new Customer(
//                    $this->alfaApi,
//                    $request->branch_id,
//                ))->getFirst(['id' => $request->entity_id]);
//
//                $contact = Contacts::search([
//                    'Телефоны' => [$customer['phone']],
//                    'Почта'    => $customer['email'][0] ?? null,
//                ], $this->amoApi->service);
//
//                if ($contact) {
//
//                    $leads = $contact->leads->toArray();
//
//                    if (count($leads) > 1) {
//
//                        foreach ($leads as $lead) {
//
//                            if ($lead['status_id'] !== 142 &&
//                                $lead['status_id'] !== 143) {
//
//                                $lead = $this->amoApi
//                                    ->service
//                                    ->leads()
//                                    ->find($lead['id']);
//
//                                Log::alert(__METHOD__.' : посетил и найдена активная сделка : '.$lead->id);
//
//                                $lead->status_id = $model->type == 1 ? Client::STATUS_ID_CAME : Client::STATUS_ID_CAME_2;
//                                $lead->save();
//
//                                Notes::addOne($lead, 'Клиент пришел на пробное, карточка обновлена');
//                            }
//                        }
//                    } else {
//
//                        Log::alert(__METHOD__.' : посетил но нет сделок');
//                    }
//
//                } else {
//
//                    Log::alert(__METHOD__.' : посетил пробное, но не найден в амо');
//                }
//
//                Log::alert(__METHOD__.' : посетил пробное, есть в бд но статус не == 1');
//            }
    }

    //пропустил пробное
    public function omission(Request $request)
    {
        $less = (new Lesson($this->alfaApi))->getFirst(['id' => $request->entity_id]);

        $branchId   = $less['branch_id'];
        $customerId = $less['customer_ids'][0];

        $model = Lead::query()
            ->where('alfa_branch_id', $branchId)
            ->where('alfa_client_id', $customerId)
            ->firstOrFail();

        if ($model->status == 1) {

            $statusId = match ($branchId) {
                Client::BRANCH_1_ID => Client::STATUS_ID_OMISSION_1,
                Client::BRANCH_2_ID => Client::STATUS_ID_OMISSION_2,
            };

            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($model->amo_lead_id);

            $lead->status_id = $statusId;
            $lead->save();

            Notes::addOne($lead, 'Клиент пропустил/отменил пробное');

            $model->status = Client::STATUS_OMISSION_1;
            $model->save();
        }
    }

    //получение оплаты
    public function pay(Request $request)
    {
        $branchId   = $request->branch_id;
        $customerId = $request->fields_new['customer_id'];

        $model = Lead::query()
            ->where('alfa_branch_id', $branchId)
            ->where('alfa_client_id', $customerId)
            ->firstOrFail();

        $lead = $this->amoApi->service->leads()->find($model->amo_lead_id);

        Notes::addOne($lead, 'Клиент внес оплату на сумму '.$request->fields_new['income']);

        $lead->status_id = 142;
        $lead->save();

        $model->status = Client::STATUS_PAY;
        $model->save();
    }
}
