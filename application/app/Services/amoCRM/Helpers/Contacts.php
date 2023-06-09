<?php

namespace App\Services\amoCRM\Helpers;

use App\Services\amoCRM\Client;

abstract class Contacts extends Client
{
    public static function clearPhone(?string $phone): string|null
    {
        return '+'.(int)preg_replace(['/[^0-9]/', '/,,+/'], [''], $phone) ?? null;
    }

    public static function search($arrayFields, $client)
    {
        $contacts = null;

        if(key_exists('Телефоны', $arrayFields)) {

            foreach ($arrayFields['Телефоны'] as $phone) {

                $contacts = $client->service
                    ->contacts()
                    ->searchByPhone($phone);

                if ($contacts) {
                    break;
                }
            }
        }

        if ($contacts == null &&
            key_exists('Почта', $arrayFields) &&
            $arrayFields['Почта'] !== null) {

            $contacts = $client->service
                ->contacts()
                ->searchByEmail($arrayFields['Почта']);
        }

        if($contacts !== null && $contacts->first())
            return $contacts->first();
        else
            return null;
    }

    public static function update($contact, $arrayFields = [])
    {
        if(key_exists('Телефоны', $arrayFields)) {

            $phones = json_decode($arrayFields['Телефоны']);

            foreach ($phones as $phone) {

                $contact->cf('Телефон')->setValue($phone);
            }
        }

        if(key_exists('Почта', $arrayFields)) {

            $contact->cf('Email')->setValue($arrayFields['Почта']);
        }

        if(key_exists('Имя', $arrayFields)) {

            $contact->name = $arrayFields['Имя'];
        }

        if(key_exists('cf', $arrayFields)) {

            foreach ($arrayFields['cf'] as $fieldsName => $fieldValue) {

                if(strpos($fieldsName, 'Дата') == true) {

                    $contact->cf($fieldsName)->setData($fieldValue);
                }
                $contact->cf($fieldsName)->setValue($fieldValue);
            }
        }

        $contact->save();

        return $contact;
    }

    public static function create(Client $amoapi, $name = 'Неизвестно')
    {
        $contact = $amoapi->service
            ->contacts()
            ->create();

        $contact->name = $name;
        $contact->save();

        return $contact;
    }

    public static function get($client, $id)
    {
        return $client->service->contacts()->find($id);
    }
}
