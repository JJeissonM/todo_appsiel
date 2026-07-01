<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use App\Compras\ClaseProveedor;
use App\Contabilidad\ContabCuenta;
use App\Sistema\Services\CrudService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Proveedor extends Model
{
    protected $table = 'compras_proveedores';

    protected $fillable = ['core_tercero_id', 'clase_proveedor_id', 'inv_bodega_id', 'liquida_impuestos', 'condicion_pago_id', 'codigo', 'declarante_renta', 'retencion_fuente_concepto_default_id', 'estado'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($proveedor) {
            if (empty($proveedor->clase_proveedor_id)) {
                $proveedor->clase_proveedor_id = static::getDefaultClaseProveedorId();
            }

            if (!static::validInvBodegaId($proveedor->inv_bodega_id)) {
                $proveedor->inv_bodega_id = static::getDefaultInvBodegaId();
            }

            if (static::isBlank($proveedor->liquida_impuestos)) {
                $proveedor->liquida_impuestos = 1;
            }

            if (empty($proveedor->condicion_pago_id)) {
                $proveedor->condicion_pago_id = static::getDefaultCondicionPagoId();
            }

            if (static::isBlank($proveedor->codigo)) {
                $proveedor->codigo = '';
            }

            if (static::isBlank($proveedor->declarante_renta)) {
                $proveedor->declarante_renta = 'declarante';
            }

            if (static::isBlank($proveedor->retencion_fuente_concepto_default_id)) {
                $proveedor->retencion_fuente_concepto_default_id = 0;
            }

            if (static::isBlank($proveedor->estado)) {
                $proveedor->estado = 'Activo';
            }
        });
    }

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Identificación', 'Tercero',  'Establecimiento', 'Dirección', 'Teléfono', 'Clase de proveedor', 'Liquida impuestos', 'Condición de pago', 'Estado'];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function retencion_fuente_concepto_default()
    {
        return $this->belongsTo(RetencionFuenteConceptoAnual::class, 'retencion_fuente_concepto_default_id');
    }

    public static function getDefaultInvBodegaId()
    {
        if (!Schema::hasTable('inv_bodegas')) {
            return (int)config('inventarios.item_bodega_principal_id');
        }

        $empresa_id = static::getEmpresaIdActual();

        $ids_config = [
            (int)config('inventarios.item_bodega_principal_id'),
            (int)config('ventas.inv_bodega_id')
        ];

        foreach ($ids_config as $inv_bodega_id) {
            if (static::validInvBodegaId($inv_bodega_id, $empresa_id)) {
                return $inv_bodega_id;
            }
        }

        $query = DB::table('inv_bodegas')->where('estado', 'Activo');
        if ($empresa_id > 0) {
            $query->where('core_empresa_id', $empresa_id);
        }

        $inv_bodega_id = (int)$query->orderBy('id')->value('id');
        if ($inv_bodega_id > 0) {
            return $inv_bodega_id;
        }

        $inv_bodega_id = (int)DB::table('inv_bodegas')->orderBy('id')->value('id');
        if ($inv_bodega_id > 0) {
            return $inv_bodega_id;
        }

        return static::crearBodegaPrincipal($empresa_id);
    }

    public static function validInvBodegaId($inv_bodega_id, $empresa_id = 0)
    {
        if (!Schema::hasTable('inv_bodegas')) {
            return false;
        }

        $inv_bodega_id = (int)$inv_bodega_id;
        if ($inv_bodega_id <= 0) {
            return false;
        }

        if ((int)$empresa_id <= 0) {
            $empresa_id = static::getEmpresaIdActual();
        }

        $query = DB::table('inv_bodegas')->where('id', $inv_bodega_id);
        if ((int)$empresa_id > 0) {
            $query->where('core_empresa_id', (int)$empresa_id);
        }

        return $query->exists();
    }

    protected static function getEmpresaIdActual()
    {
        if (Auth::check()) {
            return (int)Auth::user()->empresa_id;
        }

        if (Schema::hasTable('core_empresas')) {
            $empresa_id = (int)DB::table('core_empresas')->orderBy('id')->value('id');
            if ($empresa_id > 0) {
                return $empresa_id;
            }
        }

        return 1;
    }

    protected static function crearBodegaPrincipal($empresa_id)
    {
        $datos = [
            'core_empresa_id' => (int)$empresa_id,
            'descripcion' => 'Bodega principal',
            'estado' => 'Activo',
        ];

        $defaultId = (int)config('inventarios.item_bodega_principal_id');
        if ($defaultId > 0 && !DB::table('inv_bodegas')->where('id', $defaultId)->exists()) {
            $datos['id'] = $defaultId;
        }

        $now = date('Y-m-d H:i:s');
        if (Schema::hasColumn('inv_bodegas', 'created_at')) {
            $datos['created_at'] = $now;
        }
        if (Schema::hasColumn('inv_bodegas', 'updated_at')) {
            $datos['updated_at'] = $now;
        }

        return (int)DB::table('inv_bodegas')->insertGetId($datos);
    }

    public function get_campos_adicionales_create($lista_campos)
    {
        return $this->prepararCampoBodegaProveedor($lista_campos);
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        return $this->prepararCampoBodegaProveedor($lista_campos, $registro->inv_bodega_id);
    }

    protected function prepararCampoBodegaProveedor($lista_campos, $inv_bodega_id = null)
    {
        $cantidad_campos = count($lista_campos);
        for ($i = 0; $i < $cantidad_campos; $i++) {
            if ($lista_campos[$i]['name'] == 'inv_bodega_id') {
                $lista_campos[$i]['requerido'] = 0;
                $lista_campos[$i]['tipo'] = 'hidden';
                $lista_campos[$i]['value'] = static::validInvBodegaId($inv_bodega_id) ? (int)$inv_bodega_id : static::getDefaultInvBodegaId();

                if (isset($lista_campos[$i]['atributos']) && is_array($lista_campos[$i]['atributos'])) {
                    unset($lista_campos[$i]['atributos']['required']);
                }
            }
        }

        return $lista_campos;
    }

    public static function getDefaultClaseProveedorId()
    {
        $clase_proveedor_id = (int) config('compras.clase_proveedor_id');

        if ($clase_proveedor_id > 0) {
            return $clase_proveedor_id;
        }

        $clase_proveedor_id = (int) ClaseProveedor::where('estado', 'Activo')->orderBy('id')->value('id');

        return $clase_proveedor_id > 0 ? $clase_proveedor_id : 1;
    }

    public static function getDefaultCondicionPagoId()
    {
        $condicion_pago_id = (int) config('compras.condicion_pago_id');

        if ($condicion_pago_id > 0) {
            return $condicion_pago_id;
        }

        $condicion_pago_id = (int) CondicionPagoProv::where('estado', 'Activo')->orderBy('id')->value('id');

        return $condicion_pago_id > 0 ? $condicion_pago_id : 1;
    }

    protected static function isBlank($value)
    {
        return is_null($value) || $value === '';
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return Proveedor::leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_proveedores.core_tercero_id')->leftJoin('compras_clases_proveedores', 'compras_clases_proveedores.id', '=', 'compras_proveedores.clase_proveedor_id')->leftJoin('compras_condiciones_pago', 'compras_condiciones_pago.id', '=', 'compras_proveedores.condicion_pago_id')
            ->select(
                'core_terceros.numero_identificacion AS campo1',
                'core_terceros.descripcion AS campo2',
                'core_terceros.razon_social AS campo3',
                'core_terceros.direccion1 AS campo4',
                'core_terceros.telefono1 AS campo5',
                'compras_clases_proveedores.descripcion AS campo6',
                'compras_proveedores.liquida_impuestos AS campo7',
                'compras_condiciones_pago.descripcion AS campo8',
                'compras_proveedores.estado AS campo9',
                'compras_proveedores.id AS campo10'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("core_terceros.razon_social", "LIKE", "%$search%")
            ->orWhere("core_terceros.direccion1", "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere("compras_clases_proveedores.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_proveedores.liquida_impuestos", "LIKE", "%$search%")
            ->orWhere("compras_condiciones_pago.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_proveedores.estado", "LIKE", "%$search%")
            ->orderBy('compras_proveedores.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Proveedor::leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_proveedores.core_tercero_id')->leftJoin('compras_clases_proveedores', 'compras_clases_proveedores.id', '=', 'compras_proveedores.clase_proveedor_id')->leftJoin('compras_condiciones_pago', 'compras_condiciones_pago.id', '=', 'compras_proveedores.condicion_pago_id')
            ->select(
                'core_terceros.numero_identificacion AS IDENTIFICACIÓN',
                'core_terceros.descripcion AS TERCERO',
                'core_terceros.razon_social AS RAZÓN_SOCIAL',
                'core_terceros.direccion1 AS DIRECCIÓN',
                'core_terceros.telefono1 AS TELÉFONO',
                'compras_clases_proveedores.descripcion AS CLASE_DE_PROVEEDOR',
                'compras_proveedores.liquida_impuestos AS LIQUIDA_IMPUESTOS',
                'compras_condiciones_pago.descripcion AS CONDICIÓN_DE_PAGO',
                'compras_proveedores.estado AS ESTADO'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("core_terceros.direccion1", "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere("compras_clases_proveedores.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_proveedores.liquida_impuestos", "LIKE", "%$search%")
            ->orWhere("compras_condiciones_pago.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_proveedores.estado", "LIKE", "%$search%")
            ->orderBy('compras_proveedores.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PROVEEDORES";
    }

    public static function opciones_campo_select()
    {
        $opciones = Proveedor::leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_proveedores.core_tercero_id')->where('compras_proveedores.estado', 'Activo')
            ->select(
                'compras_proveedores.id',
                'compras_proveedores.codigo',
                'core_terceros.descripcion')
            ->orderby('core_terceros.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {

            $codigo = '';
            if ( $opcion->codigo != null && $opcion->codigo!= '' ) {
                $codigo = ' (' . $opcion->codigo . ')';
            }
            $vec[$opcion->id] = $opcion->descripcion . $codigo;
        }

        return $vec;
    }

    public static function get_cuenta_por_pagar($proveedor_id)
    {
        $clase_proveedor_id = Proveedor::where('id', $proveedor_id)->value('clase_proveedor_id');

        $cta_x_pagar_id = ClaseProveedor::where('id', $clase_proveedor_id)->value('cta_x_pagar_id');

        if (is_null($cta_x_pagar_id) || !ContabCuenta::where('id', (int)$cta_x_pagar_id)->exists()) {
            $cta_x_pagar_id = config('configuracion.cta_por_pagar_default');
        }

        if (is_null($cta_x_pagar_id) || !ContabCuenta::where('id', (int)$cta_x_pagar_id)->exists()) {
            throw new \Exception('No existe una cuenta por pagar válida para el proveedor seleccionado. Verifique la cuenta CxP en la clase de proveedor o en la configuración general.');
        }

        return $cta_x_pagar_id;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"compras_doc_encabezados",
                                    "llave_foranea":"proveedor_id",
                                    "mensaje":"Proveedor tiene documentos de Compras asociados."
                                },
                            "1":{
                                    "tabla":"compras_movimientos",
                                    "llave_foranea":"proveedor_id",
                                    "mensaje":"Proveedor tiene movimientos de Compras asociados."
                                }
                        }';

        return (new CrudService())->validar_eliminacion_un_registro( $id, $tablas_relacionadas);
    }
}
