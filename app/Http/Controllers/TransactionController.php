<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Validation\ValidationException;
use App\Jobs\ProcessTransaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Client\ConnectionException;

class TransactionController extends Controller
{


    /**
     * Cria uma transferência entre contas.
     * 
     * @param Request $request Deve conter payee_id, value e scheduled_date (opcional)
     * @return JsonResponse Retorna a transação criada ou mensagem de erro
     */
    public function store(Request $request)
    {
        try {
            
            $request->merge(['payer_id' => \Auth::user()->id]);
            $request->validate(Transaction::rules(), Transaction::feedback());

            $transaction = new Transaction;
            $transaction->payer_id = $request->payer_id;
            $transaction->payee_id = $request->payee_id;
            $transaction->value = $request->value;
            $transaction->scheduled_date = $request->scheduled_date;
            $transaction->save();

            empty($request->scheduled_date) ? ProcessTransaction::dispatchSync($transaction) :
                ProcessTransaction::dispatch($transaction)->delay($transaction->scheduled_date);

            return response([
                "status" => "success",
                "message" => "Transferência realizada com sucesso",
                "transaction" => Transaction::find($transaction->id)
            ], 201);

        } catch(ValidationException $e) {
            $errors = $e->errors();
            return response(["status" => "error", "message" => reset($errors)[0]], 422);

        } catch(AuthorizationException $e) {
            return response(["status" => "error", "message" => "Transferência não autorizada"], 403);

        } catch(ConnectionException $e) {
            return response(["status" => "error", "message" => "Serviço indisponível"], 503);

        } catch(\Throwable $e) {
            return response(["status" => "error", "message" => "Erro de servidor"], 500);
        }

    }


    /**
     * Listagem transações com paginação
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {

            $pageSize = $request->pageSize ?? 10;
            return response(Transaction::simplePaginate($pageSize), 200);

        } catch (\Throwable $th) {
            return response(["status" => "error", "message" => "Erro de servidor"], 500);
        }
    }


    /**
     * Busca uma transação pelo ID
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        try {

            $transaction = Transaction::find($request->id);
            
            if(empty($transaction)){
                return response(["status" => "error", "message" => "Não foi possível localizar a transação"], 404);
            }
           
            return response(Transaction::find($request->id), 200);

        } catch (\Throwable $th) {
            return response(["status" => "error", "message" => "Erro de servidor"], 500);
        }
    }

}
