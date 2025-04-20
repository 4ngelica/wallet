<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{


    public function store(Request $request)
    {
        try {

            $request->validate(Transaction::rules(), Transaction::feedback());

            $transaction = new Transaction;
            $transaction->payer_id = \Auth::user()->id;
            $transaction->payee_id = $request->payee_id;
            $transaction->value = $request->value;
            $transaction->scheduled_date = $request->scheduled_date;
            $transaction->save();

            return response($transaction, 200);

        }catch(ValidationException $e){
            
            return response()->json([
                "message" => "Erro ao realizar transferência",
                "errors" => $e->errors()
            ], $e->status);

        }
        catch(\Throwable $e){
            return response()->json(["message" => "Server error"], 500);
        }

    }

    public function index(Request $request)
    {
        try {

            $pageSize = $request->pageSize ?? 10;
            return response(Transaction::simplePaginate($pageSize), 200);

        } catch (\Throwable $th) {
            return response()->json(["message" => "Server error"], 500);
        }
    }

    public function get(Request $request)
    {
        try {

            $transaction = Transaction::find($request->id);
            
            if(empty($transaction)){
                return response()->json(["message" => "Não foi possível localizar a transação"], 404);
            }
           
            return response(Transaction::find($request->id), 200);

        } catch (\Throwable $th) {
            return response()->json(["message" => "Server error"], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {

            $transaction = Transaction::find($request->id);

            if(empty($transaction)){
                return response()->json(["message" => "Não foi possível localizar a transação"], 404);
            }
    
            if ($transaction->status !== 'pending') {
                return response()->json(["message" => "Operação não autorizada"], 403);
            }
    
            $transaction->delete();
            return response()->json(["message" => "Transação cancelada"], 200);


        } catch (\Throwable $th) {
            return response()->json(["message" => "Server error"], 500);
        }

    }
}
