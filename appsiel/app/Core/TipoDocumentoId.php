<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoId extends Model
{
    protected $table = 'core_tipos_docs_id'; 

    protected $fillable = ['descripcion','abreviatura'];
}
