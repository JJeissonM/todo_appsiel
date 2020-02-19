<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;

class UserHasRole extends Model
{
    protected $fillable = ['role_id','user_id'];
}
