<?php

namespace App\Services;

use App\Services\AuthorizationService;
use App\Jobs\NotifyUser;

class TransactionService
{
   
   /**
    * Processa a transação
    *
    * @param Transaction $transaction Transação
    * 
    * @return object
    */
    public function startTransaction($transaction)
    {


        try {

            \DB::beginTransaction();
            
            $authorizationService = new AuthorizationService();
            $authorizationService->authorize();

            $payee = $transaction->payee()->first();
            $payer = $transaction->payer()->first();
            $payerWallet = $payer->wallet()->first();
            $payeeWallet = $payee->wallet()->first();

            $payerWallet->update([
                "balance" => $payerWallet->balance - $transaction->value
            ]);
    
            $payeeWallet->update([
                "balance" => $payeeWallet->balance + $transaction->value
            ]);

            $transaction->update([
                "status" => "completed"
            ]);

            \DB::commit();

            NotifyUser::dispatch($payee, [
                'value' => $transaction->value,
                'payer' => $payer->name
            ])->onQueue('notify');

        } catch (\Throwable $th) {
            \DB::rollback();
            throw $th;
        }
    }

}