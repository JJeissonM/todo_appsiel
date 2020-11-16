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

    public $encabezado_tabla = ['Tipo Documento', 'Número Documento', 'Conductor', 'Email/Usuario', 'Estado', 'Acción'];

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

    public static function consultar_registros2()
    {
        return Conductor::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->leftJoin('users', 'users.id', '=', 'core_terceros.user_id')
            ->select(
                'core_tipos_docs_id.abreviatura AS campo1',
                'core_terceros.numero_identificacion AS campo2',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'users.email AS campo4',
                'cte_conductors.estado AS campo5',
                'cte_conductors.id AS campo6'
            )
            ->orderBy('cte_conductors.created_at', 'DESC')
            ->paginate(100);
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

    public function store_adicional( $datos, $registro )
    {
        $tercero = Conductor::find( $registro->id )->tercero;
        
        $usuario = User::crear_y_asignar_role( $tercero->nombre1 . " " . $tercero->otros_nombres . " " . $tercero->apellido1 . " " . $tercero->apellido2, $datos['email'], 19); // 19 = Conductor

        $tercero->user_id = $usuario->id;
        $tercero->save();
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $tercero = Conductor::find( $registro->id )->tercero;

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'email':
                    $usuario = User::find( $tercero->user_id );
                    if( !is_null( $usuario ) )
                    {
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

    public function update_adicional($datos, $id)
    {
        $propietario = Conductor::find( $id );
        $tercero = $propietario->tercero;
        $usuario = User::find( $tercero->user_id );

        if( is_null( $usuario ) )
        {
            $usuario = User::crear_y_asignar_role( $tercero->nombre1 . " " . $tercero->otros_nombres . " " . $tercero->apellido1 . " " . $tercero->apellido2, $datos['email'], 19); // 19 = Conductor

            $tercero->user_id = $usuario->id;
            $tercero->save();
        }else{
            $usuario->email = $datos['email'];
            $usuario->save();
        }

        return 0;
    }
}
