<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'amo_contact_id',
        'amo_contact_phone',
        'amo_contact_email',
        'amo_lead_source',
        'amo_lead_instagram',
        'amo_lead_vk',
        'amo_lead_notes',
        'amo_lead_id',
        'amo_lead_name',
        'customer_reject_id',
        'amo_contact_name',
        'alfa_client_id',
        'alfa_branch_id',
        'status',

        'amo_children_1_bd',
        'amo_children_2_name',
        'amo_children_1_name',
        'amo_children_2_bd',

        'type',
    ];

    public static function matchLostReason(?string $reason): int
    {
        return match ($reason) {
            '2' => 50072617,
            '9' => 50134540,
            '8' => 50134543,
            '1' => 50342230,
            '6' => 143,//своя логика
            default => 50342212, //не продлили
        };
    }
}
