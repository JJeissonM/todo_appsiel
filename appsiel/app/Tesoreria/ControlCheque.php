<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ControlCheque extends Model
{
    protected $table = 'teso_control_cheques';

    // fuente = { propio | de_tercero }
    // estado = { Recibido | Emitido | Gastado | Anulado }
    protected $fillable = [ 'fuente', 'tercero_id', 'fecha_emision', 'fecha_cobro', 'numero_cheque', 'referencia_cheque', 'entidad_financiera_id', 'valor', 'detalle', 'creado_por', 'modificado_por', 'core_tipo_transaccion_id_origen', 'core_tipo_doc_app_id_origen', 'consecutivo', 'core_tipo_transaccion_id_consumo', 'core_tipo_doc_app_id_consumo', 'consecutivo_doc_consumo', 'teso_caja_id', 'tipo', 'estado'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Fecha emisión', 'Tercero', 'Fecha cobro', 'Número cheque', 'Referencia', 'Valor', 'Banco', 'Doc. Origen', 'Doc. Consumo', 'Estado'];		


    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'tercero_id');
    }

    public function entidad_financiera()
    {
        return $this->belongsTo(TesoEntidadFinanciera::class, 'entidad_financiera_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = ControlCheque::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_control_cheques.core_tipo_doc_app_id_origen')
                            ->leftJoin('core_tipos_docs_apps AS tipo_doc_consumo', 'tipo_doc_consumo.id', '=', 'teso_control_cheques.core_tipo_doc_app_id_consumo')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_control_cheques.tercero_id')
                            ->leftJoin('teso_entidades_financieras', 'teso_entidades_financieras.id', '=', 'teso_control_cheques.entidad_financiera_id')
    			            ->select(
    			            			'teso_control_cheques.fecha_emision AS campo1',
    			            			DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo2'),
    			            			'teso_control_cheques.fecha_cobro AS campo3',
    			            			'teso_control_cheques.numero_cheque AS campo4',
    			            			'teso_control_cheques.referencia_cheque AS campo5',
    			            			'teso_control_cheques.valor AS campo6',
    			            			'teso_entidades_financieras.descripcion AS campo7',
                                        DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_control_cheques.consecutivo) AS campo8'),
                                        DB::raw('CONCAT(tipo_doc_consumo.prefijo," ",teso_control_cheques.consecutivo_doc_consumo) AS campo9'),
    			            			'teso_control_cheques.estado AS campo10',
    			            			'teso_control_cheques.id AS campo11')
    			            ->orderBy('teso_control_cheques.fecha_emision','DESC')
                            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if ( self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8, $c->campo9, $c->campo10, $c->campo11], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if ( empty($nuevaColeccion) )
        {
            return $array = new LengthAwarePaginator([], 1, 1, 1, [
                                                                    'path' => $request->url(),
                                                                    'query' => $request->query(),
                                                                ]);
        }
        
        //obtenemos el numero de la página actual, por defecto 1
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $total = count($nuevaColeccion); //Total para contar los registros mostrados
        $starting_point = ($page * $nro_registros) - $nro_registros; // punto de inicio para mostrar registros
        $array = $nuevaColeccion->slice($starting_point, $nro_registros); //indicamos desde donde y cuantos registros mostrar
        $array = new LengthAwarePaginator($array, $total, $nro_registros, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]); //finalmente se pagina y organiza la coleccion a devolver con todos los datos

        return $array;
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param array $valores_campos_seleccionados de campos donde se busca
     * @param string $searchTerm termino de busqueda
     * @return bool
     */
    public static function likePhp($valores_campos_seleccionados, $searchTerm)
    {
        $encontrado = false;
        $searchTerm = str_slug($searchTerm); // Para eliminar acentos
        foreach ($valores_campos_seleccionados as $valor_campo)
        {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false)
            {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    public static function sqlString($search)
    {
        $string = ControlCheque::select('teso_control_cheques.fuente AS campo1', 'teso_control_cheques.tercero_id AS campo2', 'teso_control_cheques.fecha_emision AS campo3', 'teso_control_cheques.fecha_cobro AS campo4', 'teso_control_cheques.numero_cheque AS campo5', 'teso_control_cheques.referencia_cheque AS campo6', 'teso_control_cheques.valor AS campo7', 'teso_control_cheques.detalle AS campo8', 'teso_control_cheques.core_tipo_transaccion_id_origen AS campo9', 'teso_control_cheques.estado AS campo10', 'teso_control_cheques.id AS campo11')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE CHEQUES";
    }

    public static function opciones_campo_select()
    {
        $opciones = ControlCheque::leftJoin('core_terceros','core_terceros.id','=','teso_control_cheques.tercero_id')
                                ->where('teso_control_cheques.estado','Recibido')
                                ->select('teso_control_cheques.id','teso_control_cheques.numero_cheque','teso_control_cheques.referencia_cheque','teso_control_cheques.valor','core_terceros.descripcion')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = '# ' . $opcion->numero_cheque . ' Ref. ' . $opcion->referencia_cheque . ' (' . $opcion->descripcion . ') > $' . number_format($opcion->valor,2,',','.');
        }

        return $vec;
    }
}
