<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;


    /*
    we could use
    DB::listen(function ($sql){
        var_dump($sql->sql,$sql->bindings);
    });
        to show the query excuted


    DB::enableQueryLog();  // to store the log of database querys
    DB::getQueryLog();

    */
}
