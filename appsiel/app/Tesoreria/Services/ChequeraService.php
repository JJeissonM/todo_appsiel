<?php

namespace App\Tesoreria\Services;

use App\Tesoreria\TesoChequera;
use App\Tesoreria\ControlCheque;
use Illuminate\Support\Facades\DB;

class ChequeraService
{
    public function create(array $data)
    {
        $data['numero_inicial'] = (int)$data['numero_inicial'];
        $data['numero_final'] = (int)$data['numero_final'];

        if (!isset($data['consecutivo_actual']) || (int)$data['consecutivo_actual'] === 0) {
            $data['consecutivo_actual'] = $data['numero_inicial'];
        } else {
            $data['consecutivo_actual'] = (int)$data['consecutivo_actual'];
        }

        $this->validar_rango($data['numero_inicial'], $data['numero_final'], $data['consecutivo_actual']);

        return TesoChequera::create($data);
    }

    public function get_chequera_activa($teso_cuenta_bancaria_id)
    {
        return TesoChequera::where('teso_cuenta_bancaria_id', (int)$teso_cuenta_bancaria_id)
            ->where('estado', 'Activo')
            ->whereColumn('consecutivo_actual', '<=', 'numero_final')
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function get_consecutivo($teso_cuenta_bancaria_id)
    {
        $chequera = $this->get_chequera_activa($teso_cuenta_bancaria_id);

        if (is_null($chequera)) {
            throw new \Exception('No hay una chequera activa para la cuenta bancaria seleccionada.');
        }

        return (int)$chequera->consecutivo_actual;
    }

    /**
     * Reserva un cheque dentro de la transacción que almacena el documento.
     * El número esperado evita emitir silenciosamente un cheque distinto al
     * que el usuario confirmó en pantalla cuando hay concurrencia.
     */
    public function reservar_consecutivo($teso_chequera_id, $teso_cuenta_bancaria_id, $numero_esperado = null)
    {
        $chequera = TesoChequera::where('id', (int)$teso_chequera_id)
            ->lockForUpdate()
            ->first();

        if (is_null($chequera)) {
            throw new \Exception('La chequera seleccionada no existe.');
        }

        if ((int)$chequera->teso_cuenta_bancaria_id !== (int)$teso_cuenta_bancaria_id) {
            throw new \Exception('La chequera no pertenece a la cuenta bancaria seleccionada.');
        }

        $numero = (int)$chequera->consecutivo_actual;
        if ($numero < (int)$chequera->numero_inicial || $numero > (int)$chequera->numero_final) {
            throw new \Exception('No se puede generar el cheque: el consecutivo actual supera el número final de la chequera.');
        }

        if ($chequera->estado !== 'Activo') {
            throw new \Exception('La chequera seleccionada no está activa.');
        }

        if (!is_null($numero_esperado) && (int)$numero_esperado !== $numero) {
            throw new \Exception('El consecutivo de la chequera cambió. Actualice la selección y vuelva a guardar el documento.');
        }

        $chequera->consecutivo_actual = $numero + 1;
        if ((int)$chequera->consecutivo_actual > (int)$chequera->numero_final) {
            $chequera->estado = 'Agotada';
        }
        $chequera->save();

        return $numero;
    }

    public function get_disponibles($teso_cuenta_bancaria_id)
    {
        return TesoChequera::where('teso_cuenta_bancaria_id', (int)$teso_cuenta_bancaria_id)
            ->where('estado', 'Activo')
            ->whereColumn('consecutivo_actual', '<=', 'numero_final')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function actualizar_consecutivo($teso_chequera_id, $nuevo_consecutivo = null)
    {
        $chequera = TesoChequera::find((int)$teso_chequera_id);

        if (is_null($chequera)) {
            throw new \Exception('Chequera no encontrada.');
        }

        $consecutivo = (int)$chequera->consecutivo_actual + 1;
        if (!is_null($nuevo_consecutivo)) {
            $consecutivo = (int)$nuevo_consecutivo;
            if ($consecutivo < (int)$chequera->consecutivo_actual) {
                throw new \Exception('El consecutivo de una chequera no puede retroceder.');
            }
        }

        if ($consecutivo > (int)$chequera->numero_final) {
            $chequera->consecutivo_actual = (int)$chequera->numero_final + 1;
            $chequera->estado = 'Agotada';
            $chequera->save();
            return $chequera;
        }

        if ($consecutivo < (int)$chequera->numero_inicial) {
            throw new \Exception('El consecutivo no puede ser menor al número inicial de la chequera.');
        }

        $chequera->consecutivo_actual = $consecutivo;
        $chequera->save();

        return $chequera;
    }

    public function actualizar_consecutivo_por_numero($teso_cuenta_bancaria_id, $numero_cheque)
    {
        $numero_cheque = (int)$numero_cheque;

        $chequera = TesoChequera::where('teso_cuenta_bancaria_id', (int)$teso_cuenta_bancaria_id)
            ->where('estado', 'Activo')
            ->where('numero_inicial', '<=', $numero_cheque)
            ->where('numero_final', '>=', $numero_cheque)
            ->orderBy('id', 'ASC')
            ->first();

        if (is_null($chequera)) {
            throw new \Exception('El número de cheque no pertenece a una chequera activa de la cuenta bancaria seleccionada.');
        }

        if ($numero_cheque < (int)$chequera->consecutivo_actual) {
            throw new \Exception('El número de cheque es menor al consecutivo actual de la chequera.');
        }

        return $this->actualizar_consecutivo($chequera->id, $numero_cheque + 1);
    }

    public function validar_rango($numero_inicial, $numero_final, $consecutivo_actual)
    {
        if ((int)$numero_inicial <= 0 || (int)$numero_final <= 0) {
            throw new \Exception('Los números de la chequera deben ser mayores a cero.');
        }

        if ((int)$numero_final < (int)$numero_inicial) {
            throw new \Exception('El número final no puede ser menor al número inicial.');
        }

        if ((int)$consecutivo_actual < (int)$numero_inicial || (int)$consecutivo_actual > (int)$numero_final + 1) {
            throw new \Exception('El consecutivo actual está fuera del rango de la chequera.');
        }
    }

    public function validar_edicion(TesoChequera $chequera, $numero_inicial, $numero_final, $consecutivo_actual)
    {
        $this->validar_rango($numero_inicial, $numero_final, $consecutivo_actual);

        $usados = ControlCheque::where('teso_chequera_id', $chequera->id)
            ->select(
                DB::raw('MIN(CAST(numero_cheque AS UNSIGNED)) AS minimo'),
                DB::raw('MAX(CAST(numero_cheque AS UNSIGNED)) AS maximo')
            )
            ->first();

        if (!is_null($usados) && !is_null($usados->minimo)) {
            if ((int)$numero_inicial > (int)$usados->minimo || (int)$numero_final < (int)$usados->maximo) {
                throw new \Exception('El nuevo rango no puede excluir cheques que ya fueron emitidos.');
            }
            if ((int)$consecutivo_actual <= (int)$usados->maximo) {
                throw new \Exception('El consecutivo actual debe ser mayor al último cheque emitido.');
            }
        }

        if ((int)$consecutivo_actual < (int)$chequera->consecutivo_actual) {
            throw new \Exception('El consecutivo de una chequera no puede retroceder.');
        }
    }

    public function existe_solapamiento($teso_cuenta_bancaria_id, $numero_inicial, $numero_final, $ignorar_id = 0)
    {
        return TesoChequera::where('teso_cuenta_bancaria_id', (int)$teso_cuenta_bancaria_id)
            ->where('id', '<>', (int)$ignorar_id)
            ->where(function ($query) use ($numero_inicial, $numero_final) {
                $query->whereBetween('numero_inicial', [(int)$numero_inicial, (int)$numero_final])
                    ->orWhereBetween('numero_final', [(int)$numero_inicial, (int)$numero_final])
                    ->orWhere(function ($query2) use ($numero_inicial, $numero_final) {
                        $query2->where('numero_inicial', '<=', (int)$numero_inicial)
                            ->where('numero_final', '>=', (int)$numero_final);
                    });
            })
            ->exists();
    }
}
