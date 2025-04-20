<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Rules\UserIsAllowed;
use App\Rules\HasBalance;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_id',
        'payee_id',
        'value',
        'status',
        'scheduled_date'
    ];

    protected $casts = [
      'scheduled_date' => 'datetime'
    ];

    public static function rules()
    {
      $payer_id = \Auth::user()->id;
      
      return [
        'payee_id' => ['required', 'exists:users,id', 'not_in:' . $payer_id, new UserIsAllowed()],
        'value' => ['required', 'integer', new HasBalance()],
        'scheduled_date' => ['nullable', 'date', 'date_format:format,Y-m-d', 'after:'. now()]
      ];

    }

    public static function feedback()
    {
      return [
        'required' => 'O atributo :attribute é obrigatório',
        'exists' => 'A carteira de destino não foi localizada',
        'not_in' => 'Não é possível realizar transferências para a mesma carteira',
        'date' => 'Data de agendamento inválida',
        'after' => 'É permitido agendamento a partir de ' . now()->addDay()->format('Y-m-d')
      ];
    }

}
