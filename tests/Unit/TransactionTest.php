<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;


class TransactionTest extends TestCase
{ 

    /**
     * Teste de transferência de lojista para usuário comum
     * 
     * @test
     */
    public function test_transfer_from_company_to_individual_user(): void
    {
        $payerBalance = 30000;
        $payeeBalance = 10000;
        $value = 10000;
        
        $payer = $this->createUser('company', $payerBalance);
        $payee = $this->createUser('individual', $payeeBalance);

        $response = $this->actingAs($payer)
            ->postJson('/api/transactions', [
                'payee_id' => $payee->id,
                'value' => $value
            ]);

        $response->assertStatus(422);

    }

    /**
     * Teste de transferência de lojista para lojista
     * 
     * @test
     */
    public function test_transfer_between_company_users(): void
    {
        $payerBalance = 30000;
        $payeeBalance = 10000;
        $value = 10000;
        
        $payer = $this->createUser('company', $payerBalance);
        $payee = $this->createUser('company', $payeeBalance);

        $response = $this->actingAs($payer)
            ->postJson('/api/transactions', [
                'payee_id' => $payee->id,
                'value' => $value
            ]);

        $response->assertStatus(422);

    }

    /**
     * Teste de transferência entre usuários comuns
     * 
     * @test
     */
    public function test_transfer_between_individual_users(): void
    {
        $payerBalance = 30000;
        $payeeBalance = 10000;
        $value = 10000;
        
        $payer = $this->createUser('individual', $payerBalance);
        $payee = $this->createUser('individual', $payeeBalance);

        $response = $this->actingAs($payer)
            ->postJson('/api/transactions', [
                'payee_id' => $payee->id,
                'value' => $value
            ]);

        $response->assertValid();
        
        switch ($response->status()) {
            case 201:
                $this->assertSuccessfulTransfer($payer->id, $payee->id, $payerBalance, $payeeBalance, $value);
                break;
                
            case 403:
                $this->assertUnauthorizedTransfer($payer->id, $payee->id, $payerBalance, $payeeBalance, $value);
                break;         

            case 503:
                $this->assertUnavailableService($payer->id, $payee->id, $payerBalance, $payeeBalance, $value);
                break;
                
            case 500:
                $this->assertFailedTransfer($payer->id, $payee->id, $payerBalance, $payeeBalance, $value);
                break;
                
            default:
                $this->fail('Status inesperado: ' . $response->status());
        }
    }


    /**
     * Teste de transferência de usuário comum para lojista
     * 
     * @test
     */
    public function test_transfer_from_individual_to_company_user(): void
    {
        $payerBalance = 30000;
        $payeeBalance = 10000;
        $value = 10000;
        
        $payer = $this->createUser('individual', $payerBalance);
        $payee = $this->createUser('company', $payeeBalance);

        $response = $this->actingAs($payer)
            ->postJson('/api/transactions', [
                'payee_id' => $payee->id,
                'value' => $value
            ]);

        $response->assertValid();
    
        switch ($response->status()) {
            case 201:
                $this->assertSuccessfulTransfer($payer->id, $payee->id, $payerBalance, $payeeBalance, $value);
                break;
                
            case 403:
                $this->assertUnauthorizedTransfer($payer->id, $payee->id, $payerBalance, $payeeBalance, $value);
                break;
            
            case 503:
                $this->assertUnavailableService($payer->id, $payee->id, $payerBalance, $payeeBalance, $value);
                break;
                
            case 500:
                $this->assertFailedTransfer($payer->id, $payee->id, $payerBalance, $payeeBalance, $value);
                break;
                
            default:
                $this->fail('Status inesperado: ' . $response->status());
        }
    }


    /**
     * Teste de transferência com saldo insuficiente
     * 
     * @test
     */
    public function test_insufficient_balance_transfer_attempt(): void
    {
        $payerBalance = 30000;
        $payeeBalance = 10000;
        $value = 40000;
        
        $payer = $this->createUser('individual', $payerBalance);
        $payee = $this->createUser('company', $payeeBalance);

        $response = $this->actingAs($payer)
            ->postJson('/api/transactions', [
                'payee_id' => $payee->id,
                'value' => $value
            ]);

        $response->assertStatus(422);    
    }

    /**
     * Teste de agendamento de transferência
     * 
     * @test
     */
    public function test_schedulled_transfer(): void
    {
        $payerBalance = 30000;
        $payeeBalance = 10000;
        $value = 10000;
        
        $payer = $this->createUser('individual', $payerBalance);
        $payee = $this->createUser('company', $payeeBalance);

        $response = $this->actingAs($payer)
            ->postJson('/api/transactions', [
                'payee_id' => $payee->id,
                'value' => $value,
                'scheduled_date' => now()->addDay()->format('Y-m-d')
            ]);

        $response->assertValid();
        $response->assertStatus(201);
        $this->assertSchedulledTransaction($payer->id, $payee->id);

    }


    /**
     * Método auxiliar para criação de usuário
     * 
     */
    private function createUser($type, $balance) : User
    {
        return User::factory()
            ->state(['type' => $type])
            ->count(1)
            ->create()
            ->each(function ($user) use ($balance){
                Wallet::factory()->state(['balance' => $balance])->count(1)->create(['user_id'=>$user->id]);
            })->first();
    }

    
    /**
     * Método para validar carteiras após transferência não autorizada
     * 
     */
    private function assertUnauthorizedTransfer($payerId, $payeeId, $payerBalance, $payeeBalance, $value) : void
    {
        $this->assertDatabaseHas('wallets', [
            'user_id' => $payerId,
            'balance' => $payerBalance
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $payeeId,
            'balance' => $payeeBalance
        ]);

        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'value' => $value,
            'status' => 'canceled'
        ]);
    }


    /**
     * Método para validar carteiras após transferência realizada com sucesso
     * 
     */
    private function assertSuccessfulTransfer($payerId, $payeeId, $payerBalance, $payeeBalance, $value) : void
    {
        $this->assertDatabaseHas('wallets', [
            'user_id' => $payerId,
            'balance' => $payerBalance - $value
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $payeeId,
            'balance' => $payeeBalance + $value
        ]);

        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'value' => $value,
            'status' => 'completed'
        ]);
    }

    /**
     * Método para validar carteiras após transferência finalizada com erro
     * 
     */
    private function assertFailedTransfer($payerId, $payeeId, $payerBalance, $payeeBalance, $value) : void
    {
        $this->assertDatabaseHas('wallets', [
            'user_id' => $payerId,
            'balance' => $payerBalance - $value
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $payeeId,
            'balance' => $payeeBalance + $value
        ]);

    }

    /**
     * Método para validar status da transferência
     * 
     */
    private function assertSchedulledTransaction($payerId, $payeeId) : void
    {
        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'status' => 'pending'
        ]);

    }

    /**
     * Método para validar cancelamento da transação após receber erro de conexão
     * 
     */
    private function assertUnavailableService($payerId, $payeeId) : void
    {
        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'status' => 'canceled'
        ]);

    }

}
