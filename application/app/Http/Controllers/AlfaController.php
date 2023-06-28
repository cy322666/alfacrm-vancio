<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\AlfaCRM\Client;
use App\Services\amoCRM\Helpers\Notes;
use Exception;
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

    /**
     * @throws Exception
     */
    public function came(Request $request)
    {
        $less = (new Lesson($this->alfaApi))->getFirst([
            'id' => $request->entity_id,
        ]);

        foreach ($less['details'] as $customer) {

            //не был
            $isCame = !($customer['reason_id'] == 4);

            if ($isCame) {

                $text = 'Клиент пришел на пробное, карточка обновлена';

                $statusId = match ($customer['branch_id']) {
                    Client::BRANCH_1_ID => Client::STATUS_ID_CAME_1,
                    Client::BRANCH_2_ID => Client::STATUS_ID_CAME_2,
                };
            } else {

                $text = 'Клиент пропустил/отменил пробное';

                $statusId = match ($customer['branch_id']) {
                    Client::BRANCH_1_ID => Client::STATUS_ID_OMISSION_1,
                    Client::BRANCH_2_ID => Client::STATUS_ID_OMISSION_2,
                };
            }

            $model = Lead::query()
                ->where('alfa_branch_id', $customer['branch_id'])
                ->where('alfa_client_id', $customer['customer_id'])
                ->first();

            if ($model) {

                $lead = $this->amoApi
                    ->service
                    ->leads()
                    ->find($model->amo_lead_id);

                $lead->status_id = $statusId;
                $lead->save();

                Notes::addOne($lead, $text);

                $model->status = Client::STATUS_ID_CAME_1;
                $model->save();
            }
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
