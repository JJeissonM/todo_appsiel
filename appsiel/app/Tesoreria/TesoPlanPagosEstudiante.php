<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

// PLAN DE PAGOS del estudiante
class TesoPlanPagosEstudiante extends Model
{
    protected $table = 'teso_cartera_estudiantes';

    // NOTA: el campo "concepto" debe cambiar por uno tipo ID, que es el que se usa en la facturas.
    protected $fillable = ['id_libreta','id_estudiante','concepto',
    						'valor_cartera','valor_pagado','saldo_pendiente','fecha_vencimiento','estado'];

    public function libreta()
    {
        return $this->belongsTo( TesoLibretasPago::class, 'id_libreta');
    }

    public function estudiante()
    {
        return $this->belongsTo( 'App\Matriculas\Estudiante', 'id_estudiante');
    }

    public function facturas_estudiantes()
    {
        return $this->hasMany( 'App\Matriculas\FacturaAuxEstudiante', 'cartera_estudiante_id');
    }

    public function get_registros_pendientes_o_vencidos_a_la_fecha( $fecha, $concepto_id = null )
    {
        $registros = TesoPlanPagosEstudiante::where( 'fecha_vencimiento', '<=', $fecha )
                                        ->orWhere(function ($query) {
                                                $query->where('estado', '=', 'Pendiente')
                                                      ->where('estado', '=', 'Vencida');
                                            })
                                        ->get();

        if ( is_null($concepto_id) )
        {
            return $registros;
        }

        return $registros->where('');

    }
}
