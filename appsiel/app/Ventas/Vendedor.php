<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use App\Core\Tercero;
use App\User;
use App\Ventas\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Vendedor extends Model
{
    protected $table = 'vtas_vendedores';

    // El vendedor debe estar creado primero como un cliente (cliente_id)
    protected $fillable = ['core_tercero_id', 'equipo_ventas_id', 'clase_vendedor_id', 'user_id', 'cliente_id', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tercero', 'Núm. identificación', 'Equipo de ventas', 'Clase de vendedor', 'Estado'];

    public $vistas = '{"create":"layouts.create"}';

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'core_tercero_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function equipo_ventas()
    {
        return $this->belongsTo(EquipoVentas::class, 'equipo_ventas_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return Vendedor::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_vendedores.core_tercero_id')
            ->leftJoin('vtas_equipos_ventas', 'vtas_equipos_ventas.id', '=', 'vtas_vendedores.equipo_ventas_id')
            ->leftJoin('vtas_clases_vendedores', 'vtas_clases_vendedores.id', '=', 'vtas_vendedores.clase_vendedor_id')
            ->select(
                'core_terceros.descripcion AS campo1',
                'core_terceros.numero_identificacion AS campo2',
                'vtas_equipos_ventas.descripcion AS campo3',
                'vtas_clases_vendedores.descripcion AS campo4',
                'vtas_vendedores.estado AS campo5',
                'vtas_vendedores.id AS campo6'
            )
            ->where("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("vtas_equipos_ventas.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_clases_vendedores.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_vendedores.estado", "LIKE", "%$search%")
            ->orderBy('vtas_vendedores.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Vendedor::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_vendedores.core_tercero_id')
            ->leftJoin('vtas_equipos_ventas', 'vtas_equipos_ventas.id', '=', 'vtas_vendedores.equipo_ventas_id')
            ->leftJoin('vtas_clases_vendedores', 'vtas_clases_vendedores.id', '=', 'vtas_vendedores.clase_vendedor_id')
            ->select(
                'core_terceros.descripcion AS TERCERO',
                'core_terceros.numero_identificacion AS NÚM_IDENTIFICACIÓN',
                'vtas_equipos_ventas.descripcion AS EQUIPO_DE_VENTAS',
                'vtas_clases_vendedores.descripcion AS CLASE_DE_VENDEDOR',
                'vtas_vendedores.estado AS ESTADO'
            )
            ->where("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("vtas_equipos_ventas.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_clases_vendedores.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_vendedores.estado", "LIKE", "%$search%")
            ->orderBy('vtas_vendedores.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE VENDEDORES";
    }

    public static function opciones_campo_select()
    {
        $raw = 'CONCAT(core_terceros.apellido1, " ",core_terceros.apellido2, " ",core_terceros.nombre1, " ",core_terceros.otros_nombres) AS descripcion';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS descripcion';
        }

        $opciones = Vendedor::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_vendedores.core_tercero_id')->where('vtas_vendedores.estado', 'Activo')
            ->select(
                'vtas_vendedores.id', 
                DB::raw($raw),
                'vtas_vendedores.user_id'
                )
            ->get();

        $vec['']='';
        $user =Auth::user();
        
        foreach ($opciones as $opcion) {

            if ($user->hasRole('Vendedor') && ($opcion->user_id != $user->id)) {
                continue;
            }

            $vec[$opcion->id] = $opcion->descripcion;
        }
        
        return $vec;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"vtas_clientes",
                                    "llave_foranea":"vendedor_id",
                                    "mensaje":"Vendedor está asociado a un Cliente."
                                },
                            "1":{
                                    "tabla":"vtas_doc_encabezados",
                                    "llave_foranea":"vendedor_id",
                                    "mensaje":"Vendedor está relacionado en documentos de ventas."
                                },
                            "2":{
                                    "tabla":"vtas_movimientos",
                                    "llave_foranea":"vendedor_id",
                                    "mensaje":"Vendedor está relacionado en movimientos de ventas."
                                },
                            "3":{
                                    "tabla":"vtas_pos_doc_encabezados",
                                    "llave_foranea":"vendedor_id",
                                    "mensaje":"Vendedor está relacionado en documentos de ventas POS."
                                },
                            "4":{
                                    "tabla":"vtas_pos_movimientos",
                                    "llave_foranea":"vendedor_id",
                                    "mensaje":"Vendedor está relacionado en movimientos de ventas POS."
                                }
                        }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            if ( !Schema::hasTable( $una_tabla->tabla ) )
            {
                continue;
            }
            
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
