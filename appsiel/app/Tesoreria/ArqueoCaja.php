<?php

namespace App\Tesoreria;

use App\Http\Controllers\Tesoreria\ArqueoCajaController;
use App\Sistema\Html\Boton;
use App\Sistema\TipoTransaccion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;

class ArqueoCaja extends Model
{
    protected $table = 'teso_arqueos_caja';

    protected $fillable = [
        'fecha', 'core_empresa_id', 'teso_caja_id', 'total_billetes', 'billetes_contados',
        'base', 'total_monedas', 'monedas_contadas', 'otros_saldos', 'detalle_otros_saldos', 'lbl_total_efectivo',
        'lbl_total_sistema', 'total_saldo', 'detalles_mov_entradas', 'total_mov_entradas', 'detalles_mov_salidas', 'total_mov_salidas', 'observaciones', 'estado', 'creado_por', 'modificado_por'
    ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Caja', 'Observaciones', 'Total saldo', 'Estado'];

    public function caja()
    {
        return $this->belongsTo('App\Tesoreria\TesoCaja','teso_caja_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return ArqueoCaja::leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_arqueos_caja.teso_caja_id')
            ->select(
                'teso_arqueos_caja.fecha AS campo1',
                'teso_cajas.descripcion AS campo2',
                'teso_arqueos_caja.observaciones AS campo3',
                'teso_arqueos_caja.total_saldo AS campo4',
                'teso_arqueos_caja.estado AS campo5',
                'teso_arqueos_caja.id AS campo6'
            )
            ->where("teso_arqueos_caja.fecha", "LIKE", "%$search%")
            ->orWhere("teso_cajas.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_arqueos_caja.observaciones", "LIKE", "%$search%")
            ->orWhere("teso_arqueos_caja.total_saldo", "LIKE", "%$search%")
            ->orWhere("teso_arqueos_caja.estado", "LIKE", "%$search%")
            ->orderBy('teso_arqueos_caja.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ArqueoCaja::leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_arqueos_caja.teso_caja_id')
            ->select(
                'teso_arqueos_caja.fecha AS FECHA',
                'teso_cajas.descripcion AS CAJA',
                'teso_arqueos_caja.observaciones AS OBSERVACIONES',
                'teso_arqueos_caja.total_saldo AS TOTAL_SALDO',
                'teso_arqueos_caja.estado AS ESTADO'
            )
            ->where("teso_arqueos_caja.fecha", "LIKE", "%$search%")
            ->orWhere("teso_cajas.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_arqueos_caja.observaciones", "LIKE", "%$search%")
            ->orWhere("teso_arqueos_caja.total_saldo", "LIKE", "%$search%")
            ->orWhere("teso_arqueos_caja.estado", "LIKE", "%$search%")
            ->orderBy('teso_arqueos_caja.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ARQUEOS DE CAJA";
    }

    public static function opciones_campo_select()
    {
        $opciones = ArqueoCaja::where('teso_arqueos_caja.estado', 'Activo')
            ->select('teso_arqueos_caja.id', 'teso_arqueos_caja.detalle')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->detalle;
        }

        return $vec;
    }

    public function store_adicional($datos, $arqueocaja)
    {
        //dd([$datos], [$arqueocaja]);
        $arqueocaja->billetes_contados = json_encode($datos['billetes']);
        $arqueocaja->monedas_contadas = json_encode($datos['monedas']);
        $arqueocaja->detalles_mov_entradas = $datos['movimientos_entradas'];
        $arqueocaja->detalles_mov_salidas = $datos['movimientos_salidas'];
        $arqueocaja->estado = 'ACTIVO';
        $result = $arqueocaja->save();
        if ($result) {
            return redirect('tesoreria/arqueo_caja/' . $arqueocaja->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $datos['url_id_modelo'])->with('flash_message', 'Registro CREADO correctamente.');
        } else {
            return redirect('tesoreria/arqueo_caja/' . $arqueocaja->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $datos['url_id_modelo'])->with('flash_message', 'Registro NO FUE CREADO correctamente.');
        }
    }

    public function update_adicional($datos, $doc_encabezado_id)
    {
        $arqueocaja = ArqueoCaja::find($doc_encabezado_id);

        $arqueocaja->billetes_contados = json_encode($datos['billetes']);
        $arqueocaja->monedas_contadas = json_encode($datos['monedas']);
        $arqueocaja->detalles_mov_entradas = $datos['movimientos_entradas'];
        $arqueocaja->detalles_mov_salidas = $datos['movimientos_salidas'];
        $arqueocaja->estado = 'ACTIVO';
        $result = $arqueocaja->save();

        return 'tesoreria/arqueo_caja/' . $arqueocaja->id . '?id=' . $datos['url_id'] . '&id_modelo=' . $datos['url_id_modelo'];
    }
}
