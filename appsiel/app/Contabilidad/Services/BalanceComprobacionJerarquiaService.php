<?php

namespace App\Contabilidad\Services;

class BalanceComprobacionJerarquiaService
{
    public function generar($movimientos, $grupos, $clases, $detallar)
    {
        $catalogo = [];
        foreach ($grupos as $grupo) {
            $catalogo[$grupo->id] = $grupo;
        }
        $nombres = [];
        foreach ($clases as $clase) {
            $nombres[$clase->id] = strtoupper($clase->descripcion);
        }
        // Redondear por cuenta, después de sumar los terceros, evita que activar
        // el detalle cambie los subtotales por acumulacion de fracciones de centavo.
        $cuentas = [];
        foreach ($movimientos as $movimiento) {
            $id = $movimiento->cuenta->id;
            if (!isset($cuentas[$id])) {
                $cuentas[$id] = clone $movimiento;
                $cuentas[$id]->saldo_inicial = 0;
                $cuentas[$id]->debitos = 0;
                $cuentas[$id]->creditos = 0;
                $cuentas[$id]->terceros = [];
            }
            $valoresTercero = [];
            foreach (['saldo_inicial', 'debitos', 'creditos'] as $campo) {
                $cuentas[$id]->$campo += $movimiento->$campo;
                $valoresTercero[$campo] = $movimiento->$campo * 100;
            }
            $valoresTercero['saldo_final'] = array_sum($valoresTercero);
            if ($detallar) {
                $tercero = $movimiento->tercero;
                $cuentas[$id]->terceros[] = [
                    'descripcion'=>$tercero ? trim($tercero->numero_identificacion.' '.$tercero->descripcion) : 'Sin tercero',
                    'valores'=>$valoresTercero
                ];
            }
        }
        $arbol = [];
        $total = $this->ceros();
        foreach ($cuentas as $movimiento) {
            $clase = (int)$movimiento->cuenta->clase_id;
            if (!isset($arbol[$clase])) {
                $arbol[$clase] = $this->nodo(isset($nombres[$clase]) ? $nombres[$clase] : 'SIN CLASE');
            }
            $ruta = [];
            $id = (int)$movimiento->grupo->id;
            if (!$id || !isset($catalogo[$id])) {
                // Las cuentas sin catalogacion siguen incluidas en los totales.
                $ruta['sin-grupo-'.$id] = $movimiento->grupo->descripcion;
            } else {
                while ($id) {
                    if (isset($ruta[$id]) || !isset($catalogo[$id]) || (int)$catalogo[$id]->contab_cuenta_clase_id !== $clase) {
                        throw new \RuntimeException('Jerarquia de grupos invalida para la cuenta '.$movimiento->cuenta->codigo.'. Revise sus grupos padres.');
                    }
                    $ruta[$id] = $catalogo[$id]->descripcion;
                    $id = (int)$catalogo[$id]->grupo_padre_id;
                }
                $ruta = array_reverse($ruta, true);
            }
            $valores = [];
            foreach (['saldo_inicial', 'debitos', 'creditos'] as $campo) {
                $valores[$campo] = (int)round($movimiento->$campo * 100);
            }
            $valores['saldo_final'] = array_sum($valores);
            $this->sumar($total, $valores);
            $nodo = &$arbol[$clase];
            $this->sumar($nodo['valores'], $valores);
            foreach ($ruta as $id => $descripcion) {
                if (!isset($nodo['hijos'][$id])) {
                    $nodo['hijos'][$id] = $this->nodo($descripcion);
                }
                $nodo = &$nodo['hijos'][$id];
                $this->sumar($nodo['valores'], $valores);
            }
            $cuenta = $movimiento->cuenta;
            if (!isset($nodo['cuentas'][$cuenta->id])) {
                $nodo['cuentas'][$cuenta->id] = $this->nodo($cuenta->codigo.' '.$cuenta->descripcion);
            }
            $this->sumar($nodo['cuentas'][$cuenta->id]['valores'], $valores);
            $nodo['cuentas'][$cuenta->id]['terceros'] = $movimiento->terceros;
            unset($nodo);
        }
        ksort($arbol, SORT_NUMERIC);
        $filas = [];
        foreach ($arbol as $nodo) {
            $filas[] = $this->fila($nodo, 'clase', 0);
            foreach ($nodo['hijos'] as $hijo) {
                $this->agregarGrupo($filas, $hijo, 1);
            }
        }
        return ['filas'=>$filas, 'totales'=>array_map(function ($v) { return $v / 100; }, $total)];
    }

    private function ceros()
    {
        return ['saldo_inicial'=>0, 'debitos'=>0, 'creditos'=>0, 'saldo_final'=>0];
    }

    private function nodo($descripcion)
    {
        return ['descripcion'=>$descripcion, 'valores'=>$this->ceros(), 'hijos'=>[], 'cuentas'=>[], 'terceros'=>[]];
    }

    private function sumar(&$destino, $valores)
    {
        foreach ($valores as $campo=>$valor) {
            $destino[$campo] += $valor;
        }
    }

    private function fila($nodo, $tipo, $nivel)
    {
        return (object)['descripcion'=>$nodo['descripcion'], 'tipo'=>$tipo, 'nivel'=>$nivel,
            'valores'=>array_map(function ($v) { return $v / 100; }, $nodo['valores'])];
    }

    private function agregarGrupo(&$filas, $nodo, $nivel)
    {
        $filas[] = $this->fila($nodo, 'grupo', $nivel);
        foreach ($nodo['cuentas'] as $cuenta) {
            $filas[] = $this->fila($cuenta, 'cuenta', $nivel + 1);
            usort($cuenta['terceros'], function ($a, $b) { return strcmp($a['descripcion'], $b['descripcion']); });
            foreach ($cuenta['terceros'] as $tercero) {
                $filas[] = $this->fila($tercero, 'tercero', $nivel + 2);
            }
        }
        foreach ($nodo['hijos'] as $hijo) {
            $this->agregarGrupo($filas, $hijo, $nivel + 1);
        }
    }
}
