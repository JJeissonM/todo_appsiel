<?php

namespace App\Tesoreria\Services;

use App\Tesoreria\ControlCheque;
use App\Tesoreria\TesoCuentaBancaria;
use Illuminate\Support\Facades\Auth;

class ChequePaymentService
{
    const MODALIDAD_CREADOS = 'cheques_creados';
    const MODALIDAD_CHEQUERA = 'usar_chequera';

    protected $chequeraService;

    public function __construct(ChequeraService $chequeraService = null)
    {
        $this->chequeraService = $chequeraService ?: new ChequeraService();
    }

    public static function modalidadConfigurada()
    {
        $modalidad = (string)config('tesoreria.modalidad_cheques_pago', self::MODALIDAD_CREADOS);

        return in_array($modalidad, [self::MODALIDAD_CREADOS, self::MODALIDAD_CHEQUERA], true)
            ? $modalidad
            : self::MODALIDAD_CREADOS;
    }

    public static function usaChequera()
    {
        return self::modalidadConfigurada() === self::MODALIDAD_CHEQUERA;
    }

    /**
     * Emite y relaciona un cheque físico de una chequera. Debe ejecutarse
     * dentro de la misma transacción de base de datos del pago.
     */
    public function emitirDesdeChequera($linea, $documento)
    {
        $cuentaId = isset($linea->teso_cuenta_bancaria_id_cheque)
            ? (int)$linea->teso_cuenta_bancaria_id_cheque
            : 0;
        $chequeraId = isset($linea->teso_chequera_id_cheque)
            ? (int)$linea->teso_chequera_id_cheque
            : 0;
        $cuenta = TesoCuentaBancaria::where('id', $cuentaId)
            ->where('core_empresa_id', (int)$documento->core_empresa_id)
            ->where('estado', 'Activo')
            ->first();

        if (is_null($cuenta)) {
            throw new \Exception('La cuenta bancaria seleccionada no existe, no está activa o pertenece a otra empresa.');
        }

        if (Auth::check() && !TesoCuentaBancaria::es_permitida_para_usuario($cuentaId)) {
            throw new \Exception('El usuario no tiene permiso para usar la cuenta bancaria seleccionada.');
        }

        if ($chequeraId <= 0) {
            throw new \Exception('Debe seleccionar una chequera.');
        }

        // El número nunca se acepta desde el formulario: bajo bloqueo de base de
        // datos se incrementa el último emitido (consecutivo_actual) y se reserva.
        $numero = $this->chequeraService->reservar_consecutivo($chequeraId, $cuentaId);
        $usuario = Auth::check() ? Auth::user()->email : (string)$documento->creado_por;

        $cheque = ControlCheque::create([
            'fuente' => 'propio',
            'modalidad' => self::MODALIDAD_CHEQUERA,
            'tercero_id' => (int)$documento->core_tercero_id,
            'fecha_emision' => $documento->fecha,
            'fecha_cobro' => $documento->fecha,
            'numero_cheque' => $numero,
            'referencia_cheque' => '',
            'entidad_financiera_id' => (int)$cuenta->entidad_financiera_id,
            'valor' => abs((float)$linea->valor_cheque),
            'detalle' => 'Pago con cheque ' . $numero,
            'creado_por' => $usuario,
            'modificado_por' => '',
            'core_tipo_transaccion_id_origen' => (int)$documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id_origen' => (int)$documento->core_tipo_doc_app_id,
            'consecutivo' => (int)$documento->consecutivo,
            'core_tipo_transaccion_id_consumo' => 0,
            'core_tipo_doc_app_id_consumo' => 0,
            'consecutivo_doc_consumo' => 0,
            'teso_caja_id' => 0,
            'teso_chequera_id' => $chequeraId,
            'teso_cuenta_bancaria_id' => $cuentaId,
            'teso_doc_encabezado_id' => (int)$documento->id,
            'tipo' => 'cheque_propio',
            'estado' => 'Emitido'
        ]);

        return ['cheque' => $cheque, 'cuenta' => $cuenta, 'numero' => $numero];
    }

    /**
     * Anula todos los cheques propios del documento y libera únicamente los
     * cheques de terceros consumidos. Un número físico emitido no se reutiliza.
     */
    public function anularDocumento($documento)
    {
        $usuario = Auth::check() ? Auth::user()->email : (string)$documento->modificado_por;

        ControlCheque::where('core_tipo_transaccion_id_consumo', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id_consumo', $documento->core_tipo_doc_app_id)
            ->where('consecutivo_doc_consumo', $documento->consecutivo)
            ->where('fuente', 'de_tercero')
            ->lockForUpdate()
            ->get()
            ->each(function ($cheque) use ($usuario) {
                $cheque->core_tipo_transaccion_id_consumo = 0;
                $cheque->core_tipo_doc_app_id_consumo = 0;
                $cheque->consecutivo_doc_consumo = 0;
                $cheque->estado = 'Recibido';
                $cheque->modificado_por = $usuario;
                $cheque->save();
            });

        ControlCheque::where(function ($query) use ($documento) {
                $query->where('teso_doc_encabezado_id', $documento->id)
                    ->orWhere(function ($or) use ($documento) {
                        $or->where('core_tipo_transaccion_id_origen', $documento->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id_origen', $documento->core_tipo_doc_app_id)
                            ->where('consecutivo', $documento->consecutivo);
                    });
            })
            ->where('fuente', 'propio')
            ->lockForUpdate()
            ->get()
            ->each(function ($cheque) use ($usuario) {
                $cheque->estado = 'Anulado';
                $cheque->modificado_por = $usuario;
                $cheque->save();
            });
    }
}
