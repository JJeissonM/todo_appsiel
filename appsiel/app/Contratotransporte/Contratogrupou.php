<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Contratogrupou extends Model
{
    protected $table = 'cte_contratogrupous';
    protected $fillable = ['id', 'contrato_id', 'identificacion', 'persona', 'created_at', 'updated_at'];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }
}
