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


    /**
     * Relacionamento com a carteira
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'id');
    }

}
