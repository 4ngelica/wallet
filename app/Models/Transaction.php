<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Rules\CanSendMoney;
use App\Rules\HasEnoughBalance;

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

    
    /**
     * Retorna as regras de validação para uma transação
     * 
     * @return array
     */
    public static function rules()
    {

      return [
        'payer_id' => ['required', 'exists:users,id', new CanSendMoney()],
        'payee_id' => ['required', 'exists:users,id', 'different:payer_id'],
        'value' => ['required', 'integer', new HasEnoughBalance()],
        'scheduled_date' => ['nullable', 'date', 'date_format:format,Y-m-d', 'after:'. now()]
      ];

    }


    /**
     * Retorna as mensagens de feedback para as regras de validação
     * 
     * @return array
     */
    public static function feedback()
    {
      return [
        'required' => 'O atributo :attribute é obrigatório',
        'exists' => 'A carteira de destino não foi localizada',
        'different' => 'Não é possível realizar transferências para a mesma carteira',
        'date' => 'Data de agendamento inválida',
        'after' => 'É permitido agendamento a partir de ' . now()->addDay()->format('Y-m-d'),
        'integer' => 'O atributo :attribute deve ser um inteiro',
        'date_format' => 'Formato de data incorreto. Formato permitido: YYYY-MM-DD'
      ];

    }


    /**
     * Relacionamento com o usuário pagador
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payer()
    {
        return $this->hasOne(User::class, 'id', 'payer_id');
    }


    /**
     * Relacionamento com o usuário beneficiário
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payee()
    {
        return $this->hasOne(User::class, 'id', 'payee_id');
    }

}
