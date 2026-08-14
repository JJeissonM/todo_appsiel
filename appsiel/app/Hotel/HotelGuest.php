<?php

namespace App\Hotel;

use App\Core\Ciudad;
use App\Core\ModeloEavValor;
use App\Core\Tercero;
use App\Sistema\Campo;
use App\Sistema\Modelo;
use App\Ventas\Cliente;
use App\Ventas\Services\CustomerServices;
use Illuminate\Support\Facades\Input;

class HotelGuest extends Cliente
{
    const FIELD_FECHA_NACIMIENTO = 'hotel_guest_fecha_nacimiento';
    const FIELD_NACIONALIDAD = 'hotel_guest_nacionalidad';
    const FIELD_PROCEDENCIA = 'hotel_guest_procedencia';
    const FIELD_DESTINO = 'hotel_guest_destino';
    const FIELD_OCUPACION = 'hotel_guest_ocupacion';

    protected $table = 'vtas_clientes';

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Identificacion', 'Huesped', 'Fecha nacimiento', 'Nacionalidad', 'Procedencia', 'Destino', 'Ocupación', 'Creado por');

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    public $vistas = '';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($guest) {
            if (empty($guest->core_tercero_id)) {
                self::prepareGuestForGenericCrud($guest);
            }
        });
    }

    public function validar_datos_creacion($request, $controller)
    {
        $this->validateGuestData($request, $controller);
    }

    public function validar_datos_actualizacion($request, $controller, $id)
    {
        $this->validateGuestData($request, $controller, $id);
    }

    private function validateGuestData($request, $controller, $id = null)
    {
        self::mergeProcedenciaAsCodigoCiudad($request);

        $controller->validate($request, array(
            'id_tipo_documento_id' => 'required',
            'numero_identificacion' => 'required',
            'descripcion' => 'required',
            'codigo_ciudad' => 'required',
        ), array(
            'id_tipo_documento_id.required' => 'Debe seleccionar el tipo de documento.',
            'numero_identificacion.required' => 'Debe ingresar el numero de identificacion.',
            'descripcion.required' => 'Debe ingresar el nombre completo o establecimiento.',
            'codigo_ciudad.required' => 'Debe seleccionar la ciudad o una procedencia valida.',
        ));

        $validator = \Validator::make($request->all(), array());
        $validator->after(function ($validator) use ($request, $id) {
            $numeroIdentificacion = trim((string)$request->numero_identificacion);
            if ($numeroIdentificacion == '') {
                return;
            }

            $terceroQuery = Tercero::where('numero_identificacion', $numeroIdentificacion);

            if (!is_null($id)) {
                $guest = self::find($id);
                if (!is_null($guest) && !empty($guest->core_tercero_id)) {
                    $terceroQuery->where('id', '<>', $guest->core_tercero_id);
                }
            }

            if ($terceroQuery->count() > 0) {
                $validator->errors()->add('numero_identificacion', 'Ya existe un tercero o huesped con ese numero de identificacion.');
            }
        });

        $controller->validateWith($validator, $request);
    }

    private static function prepareGuestForGenericCrud($guest)
    {
        $datos = (new CustomerServices())->preparar_datos(self::getInputWithProcedenciaCity(Input::all()));

        $tercero = new Tercero;
        $tercero->fill($datos);
        $tercero->save();

        $guest->core_tercero_id = $tercero->id;

        foreach ($guest->getFillable() as $field) {
            if ($field == 'core_tercero_id') {
                continue;
            }

            if ((is_null($guest->$field) || $guest->$field === '') && isset($datos[$field])) {
                $guest->$field = $datos[$field];
            }
        }
    }

    public function store_adicional($datos, $registro)
    {
        $this->storeEavValues($datos, $registro->id);
        return null;
    }

    public function update_adicional($datos, $id)
    {
        $datos = self::getInputWithProcedenciaCity($datos);

        $guest = self::find($id);
        if (!is_null($guest)) {
            $this->updateTercero($guest, $datos);
        }

        $this->storeEavValues($datos, $id);
        return null;
    }

    public function get_campos_adicionales_create($lista_campos)
    {
        return parent::get_campos_adicionales_create($lista_campos);
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $lista_campos = parent::get_campos_adicionales_edit($lista_campos, $registro);
        $lista_campos = $this->setTerceroValues($lista_campos, $registro);

        return $this->setHotelEavEditValues($lista_campos, $registro);
    }

    public function show_adicional($lista_campos, $registro)
    {
        $lista_campos = $this->setTerceroValues($lista_campos, $registro);

        return $this->prepareShowFields($lista_campos, $registro);
    }

    public function hotelAutocompleteLabel()
    {
        $tercero = $this->tercero;
        if (is_null($tercero)) {
            return '';
        }

        return trim($tercero->numero_identificacion . ' - ' . $tercero->descripcion);
    }

    private function setTerceroValues($lista_campos, $registro)
    {
        $tercero = $registro->tercero;
        if (is_null($tercero)) {
            return $lista_campos;
        }

        $terceroFields = array('tipo', 'razon_social', 'nombre1', 'otros_nombres', 'apellido1', 'apellido2', 'descripcion', 'id_tipo_documento_id', 'numero_identificacion', 'digito_verificacion', 'direccion1', 'codigo_ciudad', 'telefono1', 'email', 'estado');

        foreach ($lista_campos as $key => $campo) {
            if (!isset($campo['name']) || !in_array($campo['name'], $terceroFields)) {
                continue;
            }

            $value = $tercero->{$campo['name']};
            $lista_campos[$key]['value'] = $value;
            $lista_campos[$key]['show_value'] = $value;
        }

        return $lista_campos;
    }

    private function setHotelEavEditValues($lista_campos, $registro)
    {
        foreach ($lista_campos as $key => $campo) {
            if (!isset($campo['name']) || strpos($campo['name'], 'core_campo_id-') === false) {
                continue;
            }

            $fieldId = isset($campo['id']) ? (int)$campo['id'] : 0;
            if ($fieldId == 0) {
                $fieldId = (int)str_replace('core_campo_id-', '', $campo['name']);
            }

            if ($fieldId == 0) {
                continue;
            }

            $value = self::getEavValue($registro->id, $fieldId);
            $lista_campos[$key]['name'] = 'core_campo_id-' . $fieldId;
            $lista_campos[$key]['value'] = $value;
            $lista_campos[$key]['show_value'] = $value;
        }

        return $lista_campos;
    }

    private static function mergeProcedenciaAsCodigoCiudad($request)
    {
        $datos = self::getInputWithProcedenciaCity($request->all());

        if (isset($datos['codigo_ciudad']) && !empty($datos['codigo_ciudad'])) {
            $request->merge(array('codigo_ciudad' => $datos['codigo_ciudad']));
        }
    }

    private static function getInputWithProcedenciaCity($datos)
    {
        if (!is_array($datos)) {
            return $datos;
        }

        if (isset($datos['codigo_ciudad']) && trim($datos['codigo_ciudad']) != '') {
            return $datos;
        }

        $procedencia = self::getProcedenciaValueFromInput($datos);
        if ($procedencia != '' && self::isValidCityId($procedencia)) {
            $datos['codigo_ciudad'] = $procedencia;
        }

        return $datos;
    }

    private static function getProcedenciaValueFromInput($datos)
    {
        $fieldIds = self::hotelFieldIds();
        $fieldId = isset($fieldIds[self::FIELD_PROCEDENCIA]) ? (int)$fieldIds[self::FIELD_PROCEDENCIA] : 0;

        if ($fieldId > 0) {
            $key = 'core_campo_id-' . $fieldId;
            if (isset($datos[$key]) && !is_array($datos[$key]) && trim($datos[$key]) != '') {
                return trim($datos[$key]);
            }
        }

        if (isset($datos[self::FIELD_PROCEDENCIA]) && !is_array($datos[self::FIELD_PROCEDENCIA]) && trim($datos[self::FIELD_PROCEDENCIA]) != '') {
            return trim($datos[self::FIELD_PROCEDENCIA]);
        }

        return '';
    }

    private static function isValidCityId($cityId)
    {
        return Ciudad::where('id', $cityId)->count() > 0;
    }

    private function prepareShowFields($lista_campos, $registro)
    {
        $fields = array();
        $allowedNames = array(
            'tipo',
            'id_tipo_documento_id',
            'numero_identificacion',
            'descripcion',
            'direccion1',
            'codigo_ciudad',
            'telefono1',
            'email',
            'estado',
        );

        foreach ($lista_campos as $campo) {
            if (!isset($campo['name'])) {
                continue;
            }

            if ($campo['name'] == 'core_campo_id-ID') {
                $campo = $this->prepareHotelEavShowField($campo, $registro);
                if (!is_null($campo)) {
                    $fields[] = $campo;
                }

                continue;
            }

            if (!in_array($campo['name'], $allowedNames)) {
                continue;
            }

            $campo['show_value'] = $this->getGuestShowValue($campo);
            $fields[] = $campo;
        }

        $fields[] = [
            "id" => 7752,
            "descripcion" => "Creado por",
            "tipo" => "bsText",
            "name" => "creado_por",
            "opciones" => " ",
            "value" => $registro->tercero->creado_por,
            "atributos" => "{'class':'form-control'}",
            "definicion" => "",
            "requerido" => 1,
            "editable" => 1,
            "unico" => 0,
            "created_at" => "2020-10-06 23:46:22",
            "updated_at" => "2026-08-03 06:34:25",
            "pivot" =>  [],
            "show_value" => $registro->tercero->creado_por,
        ];

        $fields[] = [
            "id" => 7753,
            "descripcion" => "Estado",
            "tipo" => "bsText",
            "name" => "estado",
            "opciones" => " ",
            "value" => $registro->estado,
            "atributos" => "{'class':'form-control'}",
            "definicion" => "",
            "requerido" => 1,
            "editable" => 1,
            "unico" => 0,
            "created_at" => "2020-10-06 23:46:22",
            "updated_at" => "2026-08-03 06:34:25",
            "pivot" =>  [],
            "show_value" => $registro->estado,
        ];

        //dd($fields);

        return $fields;
    }

    private function prepareHotelEavShowField($campo, $registro)
    {
        $definition = $this->hotelEavDefinitionByLabel($campo['descripcion']);
        if (is_null($definition)) {
            return null;
        }

        $value = self::getEavValue($registro->id, $campo['id']);
        $campo['value'] = $value;
        $campo['show_value'] = $this->getHotelEavShowValue($definition, $value);

        return $campo;
    }

    private function hotelEavDefinitionByLabel($label)
    {
        foreach (self::hotelEavFields() as $fieldName => $definition) {
            if ($definition['label'] == $label) {
                $definition['name'] = $fieldName;
                return $definition;
            }
        }

        return null;
    }

    private function getGuestShowValue($campo)
    {
        $value = isset($campo['value']) ? $campo['value'] : '';

        switch ($campo['name']) {
            case 'id_tipo_documento_id':
                return $this->getTableDescription('core_tipos_docs_id', $value);
            case 'codigo_ciudad':
                return $this->getCityDescription($value);
            default:
                return $this->cleanShowValue($value);
        }
    }

    private function getHotelEavShowValue($definition, $value)
    {
        if ($value === '' || is_null($value)) {
            return '';
        }

        switch ($definition['name']) {
            case self::FIELD_NACIONALIDAD:
                return $this->getCountryGentilicio($value);
            case self::FIELD_PROCEDENCIA:
            case self::FIELD_DESTINO:
                return $this->getLocationDescription($value);
            default:
                return $this->cleanShowValue($value);
        }
    }

    private function getCountryGentilicio($paisId)
    {
        $pais = \DB::table('core_paises')->where('id', $paisId)->first();
        if (is_null($pais)) {
            return '';
        }

        if (isset($pais->gentilicio) && trim($pais->gentilicio) != '') {
            return $pais->gentilicio;
        }

        return isset($pais->descripcion) ? $pais->descripcion : '';
    }

    private function getLocationDescription($value)
    {
        $city = $this->getCityDescription($value);
        if ($city != '') {
            return $city;
        }

        return $this->getTableDescription('core_paises', $value);
    }

    private function getCityDescription($cityId)
    {
        $city = \DB::table('core_ciudades')
            ->leftJoin('core_departamentos', 'core_departamentos.id', '=', 'core_ciudades.core_departamento_id')
            ->select('core_ciudades.descripcion as ciudad', 'core_departamentos.descripcion as departamento')
            ->where('core_ciudades.id', $cityId)
            ->first();

        if (is_null($city)) {
            return '';
        }

        if (!empty($city->departamento)) {
            return $city->ciudad . ', ' . $city->departamento;
        }

        return $city->ciudad;
    }

    private function getTableDescription($table, $id)
    {
        if ($id === '' || is_null($id)) {
            return '';
        }

        $row = \DB::table($table)->where('id', $id)->first();
        if (is_null($row)) {
            return '';
        }

        if (isset($row->descripcion)) {
            return $row->descripcion;
        }

        return $this->cleanShowValue($id);
    }

    private function cleanShowValue($value)
    {
        if (is_null($value) || $value === 'null') {
            return '';
        }

        return $value;
    }

    private function storeEavValues($datos, $clienteId)
    {
        $modelId = self::hotelGuestModelId();
        if ($modelId == 0) {
            return;
        }

        foreach ($datos as $key => $value) {
            if (strpos($key, 'core_campo_id-') === false) {
                continue;
            }

            $fieldId = (int)str_replace('core_campo_id-', '', $key);
            if ($fieldId == 0) {
                continue;
            }

            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $row = ModeloEavValor::where('modelo_padre_id', $modelId)
                ->where('registro_modelo_padre_id', $clienteId)
                ->where('modelo_entidad_id', 0)
                ->where('core_campo_id', $fieldId)
                ->first();

            if (is_null($row)) {
                if ($value === '') {
                    continue;
                }

                ModeloEavValor::create(array(
                    'modelo_padre_id' => $modelId,
                    'registro_modelo_padre_id' => $clienteId,
                    'modelo_entidad_id' => 0,
                    'core_campo_id' => $fieldId,
                    'valor' => $value,
                ));
            } else {
                $row->valor = $value;
                $row->save();
            }
        }
    }

    private function updateTercero($guest, $datos)
    {
        $tercero = $guest->tercero;
        if (is_null($tercero)) {
            return;
        }

        $datos = (new CustomerServices())->preparar_datos($datos);
        $tercero->fill($datos);
        $tercero->save();
    }

    public static function hotelEavFields()
    {
        return array(
            self::FIELD_FECHA_NACIMIENTO => array('label' => 'Fecha de nacimiento', 'type' => 'fecha'),
            self::FIELD_NACIONALIDAD => array('label' => 'Nacionalidad', 'type' => 'select'),
            self::FIELD_PROCEDENCIA => array('label' => 'Procedencia', 'type' => 'select'),
            self::FIELD_DESTINO => array('label' => 'Destino', 'type' => 'select'),
            self::FIELD_OCUPACION => array('label' => 'Ocupación', 'type' => 'bsText'),
        );
    }

    public static function hotelGuestModelId()
    {
        return (int)Modelo::where('name_space', 'App\\Hotel\\HotelGuest')->value('id');
    }

    public static function hotelFieldIds()
    {
        $ids = array();
        foreach (self::hotelEavFields() as $fieldName => $definition) {
            $campo = self::findHotelEavField($fieldName, $definition);
            $ids[$fieldName] = !is_null($campo) ? (int)$campo->id : 0;
        }

        return $ids;
    }

    private static function findHotelEavField($legacyName, $definition)
    {
        $campo = Campo::where('name', 'core_campo_id-ID')
            ->where('descripcion', $definition['label'])
            ->first();

        if (!is_null($campo)) {
            return $campo;
        }

        return Campo::where('name', $legacyName)->first();
    }

    public static function getEavValue($clienteId, $fieldId)
    {
        $modelId = self::hotelGuestModelId();
        if ($modelId == 0 || $fieldId == 0) {
            return '';
        }

        return (string)ModeloEavValor::where('modelo_padre_id', $modelId)
            ->where('registro_modelo_padre_id', $clienteId)
            ->where('modelo_entidad_id', 0)
            ->where('core_campo_id', $fieldId)
            ->value('valor');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $ids = self::hotelFieldIds();
        $modelId = self::hotelGuestModelId();

        $query = self::hotelGuestQuery($ids, $modelId)
            ->select(
                'core_terceros.numero_identificacion AS campo1',
                'core_terceros.descripcion AS campo2',
                'fecha_nacimiento.valor AS campo3',
                \DB::raw("COALESCE(NULLIF(pais_nacionalidad.gentilicio, ''), NULLIF(pais_nacionalidad.descripcion, ''), nacionalidad.valor, '') AS campo4"),
                \DB::raw("COALESCE(NULLIF(CONCAT(COALESCE(ciudad_procedencia.descripcion,''), IF(depto_procedencia.descripcion IS NULL OR depto_procedencia.descripcion = '', '', CONCAT(', ', depto_procedencia.descripcion))), ''), NULLIF(pais_procedencia.descripcion, ''), NULLIF(CONCAT(COALESCE(ciudad_tercero.descripcion,''), IF(depto_tercero.descripcion IS NULL OR depto_tercero.descripcion = '', '', CONCAT(', ', depto_tercero.descripcion))), ''), procedencia.valor, '') AS campo5"),
                \DB::raw("COALESCE(NULLIF(CONCAT(COALESCE(ciudad_destino.descripcion,''), IF(depto_destino.descripcion IS NULL OR depto_destino.descripcion = '', '', CONCAT(', ', depto_destino.descripcion))), ''), pais_destino.descripcion, '') AS campo6"),
                \DB::raw("COALESCE(NULLIF(ocupacion.valor, ''), NULLIF(ocupacion_legacy.valor, ''), '') AS campo7"),
                'core_terceros.creado_por AS campo8',
                'vtas_clientes.id AS campo9'
            );

        self::applySearch($query, $search);

        return $query->orderBy('vtas_clientes.created_at', 'DESC')->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $ids = self::hotelFieldIds();
        $modelId = self::hotelGuestModelId();

        $query = self::hotelGuestQuery($ids, $modelId)
            ->select(
                'core_terceros.numero_identificacion AS IDENTIFICACION',
                'core_terceros.descripcion AS HUESPED',
                'fecha_nacimiento.valor AS FECHA_NACIMIENTO',
                \DB::raw("COALESCE(NULLIF(pais_nacionalidad.gentilicio, ''), NULLIF(pais_nacionalidad.descripcion, ''), nacionalidad.valor, '') AS NACIONALIDAD"),
                \DB::raw("COALESCE(NULLIF(CONCAT(COALESCE(ciudad_procedencia.descripcion,''), IF(depto_procedencia.descripcion IS NULL OR depto_procedencia.descripcion = '', '', CONCAT(', ', depto_procedencia.descripcion))), ''), NULLIF(pais_procedencia.descripcion, ''), NULLIF(CONCAT(COALESCE(ciudad_tercero.descripcion,''), IF(depto_tercero.descripcion IS NULL OR depto_tercero.descripcion = '', '', CONCAT(', ', depto_tercero.descripcion))), ''), procedencia.valor, '') AS PROCEDENCIA"),
                \DB::raw("COALESCE(NULLIF(CONCAT(COALESCE(ciudad_destino.descripcion,''), IF(depto_destino.descripcion IS NULL OR depto_destino.descripcion = '', '', CONCAT(', ', depto_destino.descripcion))), ''), pais_destino.descripcion, '') AS DESTINO"),
                \DB::raw("COALESCE(NULLIF(ocupacion.valor, ''), NULLIF(ocupacion_legacy.valor, ''), '') AS OCUPACION"),
                'core_terceros.creado_por AS CREADO_POR'
            );

        self::applySearch($query, $search);

        $string = $query->orderBy('core_terceros.descripcion')->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    private static function hotelGuestQuery($ids, $modelId)
    {
        return self::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('core_eav_valores as fecha_nacimiento', function ($join) use ($ids, $modelId) {
                $join->on('fecha_nacimiento.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('fecha_nacimiento.modelo_padre_id', '=', $modelId)
                    ->where('fecha_nacimiento.modelo_entidad_id', '=', 0)
                    ->where('fecha_nacimiento.core_campo_id', '=', isset($ids[self::FIELD_FECHA_NACIMIENTO]) ? $ids[self::FIELD_FECHA_NACIMIENTO] : 0);
            })
            ->leftJoin('core_eav_valores as nacionalidad', function ($join) use ($ids, $modelId) {
                $join->on('nacionalidad.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('nacionalidad.modelo_padre_id', '=', $modelId)
                    ->where('nacionalidad.modelo_entidad_id', '=', 0)
                    ->where('nacionalidad.core_campo_id', '=', isset($ids[self::FIELD_NACIONALIDAD]) ? $ids[self::FIELD_NACIONALIDAD] : 0);
            })
            ->leftJoin('core_eav_valores as procedencia', function ($join) use ($ids, $modelId) {
                $join->on('procedencia.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('procedencia.modelo_padre_id', '=', $modelId)
                    ->where('procedencia.modelo_entidad_id', '=', 0)
                    ->where('procedencia.core_campo_id', '=', isset($ids[self::FIELD_PROCEDENCIA]) ? $ids[self::FIELD_PROCEDENCIA] : 0);
            })
            ->leftJoin('core_eav_valores as destino', function ($join) use ($ids, $modelId) {
                $join->on('destino.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('destino.modelo_padre_id', '=', $modelId)
                    ->where('destino.modelo_entidad_id', '=', 0)
                    ->where('destino.core_campo_id', '=', isset($ids[self::FIELD_DESTINO]) ? $ids[self::FIELD_DESTINO] : 0);
            })
            ->leftJoin('core_eav_valores as ocupacion', function ($join) use ($ids, $modelId) {
                $join->on('ocupacion.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('ocupacion.modelo_padre_id', '=', $modelId)
                    ->where('ocupacion.modelo_entidad_id', '=', 0)
                    ->where('ocupacion.core_campo_id', '=', isset($ids[self::FIELD_OCUPACION]) ? $ids[self::FIELD_OCUPACION] : 0);
            })
            ->leftJoin('core_eav_valores as ocupacion_legacy', function ($join) use ($modelId) {
                $join->on('ocupacion_legacy.registro_modelo_padre_id', '=', 'vtas_clientes.id')
                    ->where('ocupacion_legacy.modelo_padre_id', '=', $modelId)
                    ->where('ocupacion_legacy.modelo_entidad_id', '=', 0)
                    ->where('ocupacion_legacy.core_campo_id', '=', 7);
            })
            ->leftJoin('core_paises as pais_nacionalidad', 'pais_nacionalidad.id', '=', 'nacionalidad.valor')
            ->leftJoin('core_ciudades as ciudad_tercero', 'ciudad_tercero.id', '=', 'core_terceros.codigo_ciudad')
            ->leftJoin('core_departamentos as depto_tercero', 'depto_tercero.id', '=', 'ciudad_tercero.core_departamento_id')
            ->leftJoin('core_ciudades as ciudad_procedencia', 'ciudad_procedencia.id', '=', 'procedencia.valor')
            ->leftJoin('core_departamentos as depto_procedencia', 'depto_procedencia.id', '=', 'ciudad_procedencia.core_departamento_id')
            ->leftJoin('core_paises as pais_procedencia', 'pais_procedencia.id', '=', 'procedencia.valor')
            ->leftJoin('core_ciudades as ciudad_destino', 'ciudad_destino.id', '=', 'destino.valor')
            ->leftJoin('core_departamentos as depto_destino', 'depto_destino.id', '=', 'ciudad_destino.core_departamento_id')
            ->leftJoin('core_paises as pais_destino', 'pais_destino.id', '=', 'destino.valor');
    }

    private static function applySearch($query, $search)
    {
        if ($search == '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('core_terceros.numero_identificacion', 'LIKE', "%$search%")
                ->orWhere('core_terceros.descripcion', 'LIKE', "%$search%")
                ->orWhere('fecha_nacimiento.valor', 'LIKE', "%$search%")
                ->orWhere('nacionalidad.valor', 'LIKE', "%$search%")
                ->orWhere('pais_nacionalidad.gentilicio', 'LIKE', "%$search%")
                ->orWhere('procedencia.valor', 'LIKE', "%$search%")
                ->orWhere('ciudad_procedencia.descripcion', 'LIKE', "%$search%")
                ->orWhere('depto_procedencia.descripcion', 'LIKE', "%$search%")
                ->orWhere('pais_procedencia.descripcion', 'LIKE', "%$search%")
                ->orWhere('destino.valor', 'LIKE', "%$search%")
                ->orWhere('ciudad_destino.descripcion', 'LIKE', "%$search%")
                ->orWhere('depto_destino.descripcion', 'LIKE', "%$search%")
                ->orWhere('pais_destino.descripcion', 'LIKE', "%$search%")
                ->orWhere('ocupacion.valor', 'LIKE', "%$search%")
                ->orWhere('ocupacion_legacy.valor', 'LIKE', "%$search%")
                ->orWhere('ciudad_tercero.descripcion', 'LIKE', "%$search%")
                ->orWhere('depto_tercero.descripcion', 'LIKE', "%$search%")
                ->orWhere('core_terceros.creado_por', 'LIKE', "%$search%");
        });
    }
}
