<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable

{
    use HasApiTokens, HasFactory;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'document',
        'type'
    ];

    /**
      * The attributes that should be hidden for serialization.
      *
      * @var array<int, string>
      */
      protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed'
    ];

    public static function rules() {
        
        return [
            'name' => 'required',
            'email'=> 'required|email|unique:users',
            'document' => 'required|unique:users',
            'type' => 'required|in:company,individual',
            'password'=> 'required'
        ];

    }

    public static function feedback() {
        
        return [
            'required' => 'O atributo :atribute é obrigatório',
            'email.unique'=> 'O email já foi registrado',
            'document.unique'=> 'O documento já foi registrado',
            'in' => 'Tipo não permitido'
        ];

    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'id');
    }

}
