<?php

namespace App\Services;
use \Illuminate\Auth\Access\AuthorizationException;


class AuthorizationService
{

    public function authorize()
    {

        $res = \Http::get('https://util.devi.tools/api/v2/authorize');
        
        if ($res->failed()) {
            throw new AuthorizationException();
        }
        
    }

}