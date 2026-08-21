<?php

namespace App\Nomina\TransaccionesViaInterfaz;

use App\Core\Tercero;
use App\Nomina\NomConcepto;
use App\Nomina\NomContrato;
use App\Nomina\NomDocRegistro;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LibroExcel
{
    const COLUMNA_IDENTIFICACION = 'numero_identificacion';
    const COLUMNA_CONCEPTO = 'concepto_id';
    const COLUMNA_CANTIDAD_HORAS = 'cantidad_horas';
    const COLUMNA_VALOR = 'valor';

    protected $documento;
    protected $rutaArchivo;

    public function __construct($documento, $rutaArchivo)
    {
        $this->documento = $documento;
        $this->rutaArchivo = $rutaArchivo;
    }

    public static function columnasRequeridas()
    {
        return [
            self::COLUMNA_IDENTIFICACION,
            self::COLUMNA_CONCEPTO,
            self::COLUMNA_CANTIDAD_HORAS,
            self::COLUMNA_VALOR
        ];
    }

    public function validar()
    {
        try {
            $libro = IOFactory::load($this->rutaArchivo);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('No fue posible leer el libro. Verifique que sea un archivo Excel válido y que no esté protegido con contraseña.');
        }

        $hoja = $libro->getSheet(0);
        $columnas = $this->obtenerColumnas($hoja);
        $ultimaFila = (int) $hoja->getHighestDataRow();
        $lineas = [];
        $filasProcesadas = [];

        for ($numeroFila = 2; $numeroFila <= $ultimaFila; $numeroFila++) {
            $valores = [];
            foreach ($columnas as $nombre => $letra) {
                $valores[$nombre] = $hoja->getCell($letra . $numeroFila)->getCalculatedValue();
            }

            if ($this->filaEstaVacia($valores)) {
                continue;
            }

            $linea = $this->validarFila($valores, $numeroFila);
            $llave = $linea->numero_identificacion . '|' . $linea->concepto_id;

            if ($linea->numero_identificacion !== '' && $linea->concepto_id !== '' && isset($filasProcesadas[$llave])) {
                $linea->errores[] = 'La combinación empleado/concepto está repetida; ya aparece en la fila ' . $filasProcesadas[$llave] . '.';
            } else {
                $filasProcesadas[$llave] = $numeroFila;
            }

            $lineas[] = $linea;
        }

        $libro->disconnectWorksheets();

        if (empty($lineas)) {
            throw new InvalidArgumentException('El libro no contiene registros para procesar. La información debe comenzar en la fila 2.');
        }

        return $lineas;
    }

    protected function obtenerColumnas($hoja)
    {
        $ultimaColumna = $hoja->getHighestDataColumn();
        $encabezados = $hoja->rangeToArray('A1:' . $ultimaColumna . '1', null, true, false, true);
        $encabezados = isset($encabezados[1]) ? $encabezados[1] : [];
        $columnas = [];
        $noReconocidas = [];

        foreach ($encabezados as $letra => $encabezado) {
            $nombre = $this->normalizarEncabezado($encabezado);
            if ($nombre === '') {
                continue;
            }

            if (!in_array($nombre, self::columnasRequeridas(), true)) {
                $noReconocidas[] = (string) $encabezado;
                continue;
            }

            if (isset($columnas[$nombre])) {
                throw new InvalidArgumentException('La columna "' . $nombre . '" está repetida en la fila de encabezados.');
            }

            $columnas[$nombre] = $letra;
        }

        if (!empty($noReconocidas)) {
            throw new InvalidArgumentException('El libro contiene columnas no reconocidas: ' . implode(', ', $noReconocidas) . '.');
        }

        $faltantes = array_diff(self::columnasRequeridas(), array_keys($columnas));
        if (!empty($faltantes)) {
            throw new InvalidArgumentException('Faltan columnas obligatorias en la fila 1: ' . implode(', ', $faltantes) . '.');
        }

        return $columnas;
    }

    protected function normalizarEncabezado($encabezado)
    {
        return str_slug(trim((string) $encabezado), '_');
    }

    protected function filaEstaVacia(array $valores)
    {
        foreach ($valores as $valor) {
            if (!is_null($valor) && trim((string) $valor) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function validarFila(array $valores, $numeroFila)
    {
        $identificacion = $this->normalizarIdentificacion($valores[self::COLUMNA_IDENTIFICACION]);
        $conceptoId = $this->normalizarEntero($valores[self::COLUMNA_CONCEPTO]);
        $errores = [];

        if ($identificacion === '') {
            $errores[] = 'El número de identificación es obligatorio.';
        }

        if ($conceptoId === '') {
            $errores[] = 'El concepto_id debe ser un número entero positivo.';
        }

        $cantidadHoras = $this->normalizarNumero($valores[self::COLUMNA_CANTIDAD_HORAS], 'cantidad_horas', $errores);
        $valor = $this->normalizarNumero($valores[self::COLUMNA_VALOR], 'valor', $errores);

        if ($cantidadHoras < 0 || $valor < 0) {
            $errores[] = 'La cantidad de horas y el valor no pueden ser negativos.';
        } elseif (($cantidadHoras + $valor) <= 0) {
            $errores[] = 'Debe indicar una cantidad de horas o un valor mayor que cero.';
        }

        $tercero = $this->validarTercero($identificacion, $errores);
        $contrato = $this->validarContrato($tercero, $errores);
        $concepto = $this->validarConcepto($conceptoId, $contrato, $errores);

        return (object) [
            'numero_fila' => $numeroFila,
            'numero_identificacion' => $identificacion,
            'concepto_id' => $conceptoId,
            'tercero' => $tercero,
            'contrato' => $contrato,
            'concepto' => $concepto,
            'cantidad_horas' => $cantidadHoras,
            'valor' => $valor,
            'errores' => array_values(array_unique($errores))
        ];
    }

    protected function normalizarIdentificacion($valor)
    {
        if (is_float($valor) && floor($valor) == $valor) {
            return sprintf('%.0f', $valor);
        }

        return trim((string) $valor);
    }

    protected function normalizarEntero($valor)
    {
        if ($valor === null || trim((string) $valor) === '' || !is_numeric($valor)) {
            return '';
        }

        $numero = (float) $valor;
        if ($numero <= 0 || floor($numero) != $numero) {
            return '';
        }

        return (int) $numero;
    }

    protected function normalizarNumero($valor, $columna, array &$errores)
    {
        if ($valor === null || trim((string) $valor) === '') {
            return 0;
        }

        if (!is_numeric($valor)) {
            $errores[] = 'La columna ' . $columna . ' debe contener un número sin símbolos de moneda ni separadores de miles.';
            return 0;
        }

        return (float) $valor;
    }

    protected function validarTercero($identificacion, array &$errores)
    {
        if ($identificacion === '') {
            return $this->terceroVacio($identificacion);
        }

        $terceros = Tercero::where('numero_identificacion', $identificacion)
            ->where('core_empresa_id', $this->documento->core_empresa_id)
            ->get();

        if ($terceros->count() === 0) {
            $errores[] = 'No existe un empleado con esta identificación en la empresa del documento.';
            return $this->terceroVacio($identificacion);
        }

        if ($terceros->count() > 1) {
            $errores[] = 'La identificación está repetida en los terceros de la empresa.';
            return $this->terceroVacio($identificacion);
        }

        return $terceros->first();
    }

    protected function validarContrato($tercero, array &$errores)
    {
        if ((int) $tercero->id === 0) {
            return $this->contratoVacio();
        }

        $lapso = $this->documento->lapso();
        $contratos = NomContrato::where('core_tercero_id', $tercero->id)
            ->where('estado', 'Activo')
            ->where('fecha_ingreso', '<=', $lapso->fecha_final)
            ->where(function ($query) use ($lapso) {
                $query->whereNull('contrato_hasta')
                    ->orWhere('contrato_hasta', '')
                    ->orWhere('contrato_hasta', '0000-00-00')
                    ->orWhere('contrato_hasta', '>=', $lapso->fecha_inicial);
            })
            ->with('cargo')
            ->get();

        if ($contratos->count() === 0) {
            $errores[] = 'El empleado no tiene un contrato activo y vigente durante el período del documento.';
            return $this->contratoVacio();
        }

        if ($contratos->count() > 1) {
            $errores[] = 'El empleado tiene más de un contrato activo y vigente para el período del documento.';
            return $this->contratoVacio();
        }

        $contrato = $contratos->first();
        if (is_null($contrato->cargo)) {
            $errores[] = 'El contrato no tiene un cargo asociado.';
        }

        return $contrato;
    }

    protected function validarConcepto($conceptoId, $contrato, array &$errores)
    {
        if ($conceptoId === '') {
            return $this->conceptoVacio();
        }

        $concepto = NomConcepto::find($conceptoId);
        if (is_null($concepto)) {
            $errores[] = 'El concepto indicado no existe.';
            return $this->conceptoVacio();
        }

        if ($concepto->estado !== 'Activo') {
            $errores[] = 'El concepto no está activo.';
        }

        if ((int) $concepto->modo_liquidacion_id !== 2) {
            $errores[] = 'El concepto no tiene modo de liquidación Manual.';
        }

        if ((int) $contrato->id > 0 && NomDocRegistro::where([
            'nom_doc_encabezado_id' => $this->documento->id,
            'nom_contrato_id' => $contrato->id,
            'nom_concepto_id' => $concepto->id
        ])->exists()) {
            $errores[] = 'El concepto ya está liquidado para este empleado en el documento.';
        }

        return $concepto;
    }

    protected function terceroVacio($identificacion)
    {
        return (object) [
            'id' => 0,
            'descripcion' => 'Empleado no identificado',
            'numero_identificacion' => $identificacion
        ];
    }

    protected function contratoVacio()
    {
        return (object) [
            'id' => 0,
            'cargo' => (object) ['descripcion' => 'Sin contrato válido']
        ];
    }

    protected function conceptoVacio()
    {
        return (object) [
            'id' => 0,
            'descripcion' => 'Concepto no identificado',
            'naturaleza' => ''
        ];
    }
}
