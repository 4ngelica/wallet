<?php

namespace App\Services;

class NotificationService
{

    public function sendNotification($user, $transactionData)
    {

        $res = \Http::post('https://util.devi.tools/api/v1/notify');
        
        if ($res->failed()) {
            throw new \Exception($res->body());
        }

        $value = number_format((float)($transactionData['value'] / 100), 2, '.', '');

        \Log::info("'Notificação enviada para: $user->email\n\nVocê recebeu uma transferência de {$transactionData['payer']} no valor de R$ $value");
        
    }

}