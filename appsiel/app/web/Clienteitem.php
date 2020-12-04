<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Clienteitem extends Model
{
    protected $table = 'pw_clienteitems';
    protected $fillable = ['id', 'nombre', 'logo', 'enlace', 'cliente_id', 'created_at', 'updated_at'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
