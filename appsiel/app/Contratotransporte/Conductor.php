<?php

namespace App\Contratotransporte;

use App\Core\Tercero;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\User;

class Conductor extends Model
{
    protected $table = 'cte_conductors';
    protected $fillable = ['id', 'tercero_id', 'estado', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tipo Documento', 'Número Documento', 'Conductor', 'Estado'];

    public $urls_acciones = '{"eliminar":"cte_conductores/id_fila/eliminar"}';

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Conductor::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
            ->select('cte_conductors.id', 'core_terceros.descripcion', 'core_terceros.numero_identificacion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->numero_identificacion . ' ' . $opcion->descripcion;
        }

        return $vec;
    }

    public static function consultar_registros2($nro_registros)
    {
        return Conductor::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->leftJoin('users', 'users.id', '=', 'core_terceros.user_id')
            ->select(
                'core_tipos_docs_id.abreviatura AS campo1',
                'core_terceros.numero_identificacion AS campo2',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'cte_conductors.estado AS campo4',
                'cte_conductors.id AS campo5'
            )
            ->orderBy('cte_conductors.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public function documentosconductors()
    {
        return $this->hasMany(Documentosconductor::class);
    }

    public function tercero()
    {
        return $this->belongsTo(Tercero::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }

    public function planillaconductors()
    {
        return $this->hasMany(Planillaconductor::class);
    }

    public function vehiculoconductors()
    {
        return $this->hasMany(Vehiculoconductor::class);
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $tercero = Conductor::find($registro->id)->tercero;

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            switch ($lista_campos[$i]['name']) {
                case 'email':
                    $usuario = User::find($tercero->user_id);
                    if (!is_null($usuario)) {
                        $lista_campos[$i]['value'] = $usuario->email;
                    }

                    break;

                case 'tercero_id':
                    //dd( $lista_campos[$i] );
                    $lista_campos[$i]['tipo'] = 'bsText';
                    $lista_campos[$i]['value'] = $tercero->descripcion;
                    break;

                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }
}
