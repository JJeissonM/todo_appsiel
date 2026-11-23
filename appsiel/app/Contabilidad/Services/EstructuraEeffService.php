<?php

namespace App\Contabilidad\Services;

class EstructuraEeffService
{
    public $totales_clases = [];

    public function generar($movimientos, $grupos, $clases, $detallar)
    {
        $catalogo = [];
        foreach ($grupos as $grupo) {
            $catalogo[$grupo->id] = $grupo;
        }
        $arbol = [];
        $this->totales_clases = array_fill(0, 7, 0);
        foreach ($movimientos as $movimiento) {
            if (empty($movimiento->cuenta_codigo)) {
                throw new \RuntimeException('Hay movimientos con cuentas inexistentes en el catalogo.');
            }
            $clase = (int)$movimiento->cuenta_clase_id;
            $id = (int)$movimiento->cuenta_grupo_hijo_id;
            $ruta = [];
            while ($id) {
                if (isset($ruta[$id]) || !isset($catalogo[$id]) || (int)$catalogo[$id]->contab_cuenta_clase_id !== $clase) {
                    throw new \RuntimeException('Jerarquia de grupos invalida para la cuenta ' . $movimiento->cuenta_codigo . '. Revise padres inexistentes, ciclos o grupos de otra clase.');
                }
                $ruta[$id] = $catalogo[$id];
                $id = (int)$catalogo[$id]->grupo_padre_id;
            }
            if (empty($ruta)) {
                throw new \RuntimeException('La cuenta ' . $movimiento->cuenta_codigo . ' no tiene grupo contable.');
            }
            $centavos = (int)round($movimiento->valor_saldo * 100);
            if ($centavos === 0) {
                continue;
            }
            if (!isset($arbol[$clase])) {
                $arbol[$clase] = ['descripcion'=>strtoupper($movimiento->cuenta_clase_descripcion), 'valor'=>0, 'hijos'=>[]];
            }
            $nodo = &$arbol[$clase];
            $nodo['valor'] += $centavos;
            foreach (array_reverse($ruta, true) as $grupo) {
                if (!isset($nodo['hijos'][$grupo->id])) {
                    $nodo['hijos'][$grupo->id] = ['descripcion'=>$grupo->descripcion, 'valor'=>0, 'hijos'=>[], 'cuentas'=>[]];
                }
                $nodo = &$nodo['hijos'][$grupo->id];
                $nodo['valor'] += $centavos;
            }
            if ($detallar) {
                $nodo['cuentas'][] = (object)['descripcion'=>$movimiento->cuenta_codigo . ' ' . $movimiento->cuenta_descripcion, 'valor'=>$centavos / 100];
            }
            unset($nodo);
        }
        $filas = [];
        foreach ($clases as $clase) {
            if (!isset($arbol[$clase])) {
                continue;
            }
            $nodo = $arbol[$clase];
            $this->totales_clases[$clase] = $nodo['valor'] / 100;
            $filas[] = $this->fila('datos_clase_cuenta', $nodo['descripcion'], $nodo['valor'] / 100, 0);
            foreach ($nodo['hijos'] as $hijo) {
                $this->agregarGrupo($filas, $hijo, 1);
            }
        }
        return $filas;
    }

    private function agregarGrupo(&$filas, $nodo, $nivel)
    {
        $filas[] = $this->fila($nivel === 1 ? 'datos_grupo_padre' : 'datos_grupo_hijo', $nodo['descripcion'], $nodo['valor'] / 100, $nivel);
        foreach ($nodo['cuentas'] as $cuenta) {
            $filas[] = $this->fila('datos_cuenta', $cuenta->descripcion, $cuenta->valor, $nivel + 1);
        }
        foreach ($nodo['hijos'] as $hijo) {
            $this->agregarGrupo($filas, $hijo, $nivel + 1);
        }
    }

    private function fila($campo, $descripcion, $valor, $nivel)
    {
        $fila = (object)['datos_clase_cuenta'=>0, 'datos_grupo_padre'=>0, 'datos_grupo_hijo'=>0, 'datos_cuenta'=>0, 'nivel'=>$nivel];
        $fila->$campo = (object)['descripcion'=>$descripcion, 'valor'=>$valor];
        return $fila;
    }
}
