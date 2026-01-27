<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

class CreateEmpleadoRole extends Migration
{
    public function up()
    {
        if ( !Role::where('name', 'Empleado')->exists() )
        {
            Role::create([ 'name' => 'Empleado' ]);
        }
    }

    public function down()
    {
        Role::where('name', 'Empleado')->delete();
    }
}
