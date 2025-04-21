<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Model\User;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance'
    ];

    
    /**
     * Relacionamento com o modelo User
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

}
