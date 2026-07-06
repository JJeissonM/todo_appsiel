<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class HotelApplicationSeeder extends Seeder
{
    private $appId = 0;
    private $modelIds = array();

    public function run()
    {
        $this->appId = $this->seedApplication();
        $this->modelIds = $this->seedModels();
        $this->seedFields();
        $this->seedPermissions();
        $this->seedReports();
        $this->forgetPermissionCache();
    }

    private function seedApplication()
    {
        if (!Schema::hasTable('sys_aplicaciones')) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $appQuery = DB::table('sys_aplicaciones')->whereIn('descripcion', array('Gestion Hotelera', 'Gestión Hotelera'));
        if (Schema::hasColumn('sys_aplicaciones', 'app')) {
            $appQuery->orWhere('app', 'hotel')->orWhere('app', 'hotel/stays');
        }
        $app = $appQuery->first();

        $data = array(
            'ambito' => 'Core',
            'descripcion' => 'Gestión Hotelera',
            'app' => 'hotel',
            'definicion' => 'Modulo inicial para administrar habitaciones, estadias, huespedes, pedidos hoteleros y facturacion.',
            'tipo_precio' => 'Gratis',
            'precio' => 0,
            'orden' => 60,
            'nombre_imagen' => 'gestion_hotelera.png',
            'mostrar_en_pag_web' => 0,
            'estado' => 'Activo',
            'updated_at' => $now,
        );

        $data = $this->onlyExistingColumns('sys_aplicaciones', $data);

        if ($app) {
            DB::table('sys_aplicaciones')->where('id', $app->id)->update($data);
            return (int)$app->id;
        }

        $data['created_at'] = $now;
        return (int)DB::table('sys_aplicaciones')->insertGetId($data);
    }

    private function seedModels()
    {
        if (!Schema::hasTable('sys_modelos')) {
            return array();
        }

        $models = array(
            'rooms' => array('Habitaciones', 'hotel_rooms', 'App\\Hotel\\HotelRoom', 'web/create', 'web/id_fila/edit', 'web/id_fila'),
            'stays' => array('Estadias', 'hotel_stays', 'App\\Hotel\\HotelStay', 'web/create', 'web/id_fila/edit', 'web/id_fila'),
            'reservations' => array('Reservas hoteleras', 'hotel_reservations', 'App\\Hotel\\HotelReservation', 'web/create', 'web/id_fila/edit', 'web/id_fila'),
            'hotel_guests' => array('Huespedes hoteleros', 'vtas_clientes', 'App\\Hotel\\HotelGuest', 'web/create', 'web/id_fila/edit', 'web/id_fila'),
            'guests' => array('Huespedes', 'hotel_stay_guests', 'App\\Hotel\\HotelStayGuest', 'web/create', 'web/id_fila/edit', 'web/id_fila'),
            'orders' => array('Pedidos hoteleros', 'hotel_order_headers', 'App\\Hotel\\HotelOrderHeader', 'web/create', 'web/id_fila/edit', 'hotel/orders/id_fila'),
            'lines' => array('Lineas de pedidos hoteleros', 'hotel_order_lines', 'App\\Hotel\\HotelOrderLine', 'web/create', 'web/id_fila/edit', 'web/id_fila'),
        );

        $ids = array();
        foreach ($models as $key => $model) {
            $ids[$key] = $this->seedModel($model[0], $model[1], $model[2], $model[3], $model[4], $model[5]);
        }

        $ids['services'] = $this->getExistingModelId('App\\Inventarios\\Servicio');
        if ($ids['services'] == 0) {
            $ids['services'] = $this->seedModel('Servicios hoteleros', 'inv_productos', 'App\\Inventarios\\Servicio', 'web/create', 'web/id_fila/edit', 'web/id_fila');
        }

        $ids['clients'] = $this->getExistingModelId('App\\Ventas\\Cliente');
        $ids['sales_invoices'] = $this->getExistingModelId('App\\Ventas\\VtasDocEncabezado');

        return $ids;
    }

    private function getExistingModelId($namespace)
    {
        if (!Schema::hasTable('sys_modelos')) {
            return 0;
        }

        return (int)DB::table('sys_modelos')->where('name_space', $namespace)->value('id');
    }

    private function seedModel($description, $table, $namespace, $createUrl, $editUrl, $showUrl)
    {
        $now = date('Y-m-d H:i:s');
        $row = DB::table('sys_modelos')->where('name_space', $namespace)->first();

        $data = array(
            'descripcion' => $description,
            'modelo' => $table,
            'name_space' => $namespace,
            'modelo_relacionado' => '',
            'url_crear' => $createUrl,
            'url_edit' => $editUrl,
            'url_print' => '',
            'url_ver' => $showUrl,
            'enlaces' => '',
            'url_estado' => '',
            'url_eliminar' => '',
            'controller_complementario' => '',
            'url_form_create' => '',
            'home_miga_pan' => 'hotel,Gestión Hotelera',
            'ruta_storage_imagen' => '',
            'ruta_storage_archivo_adjunto' => '',
            'updated_at' => $now,
        );

        $data = $this->onlyExistingColumns('sys_modelos', $data);

        if ($row) {
            DB::table('sys_modelos')->where('id', $row->id)->update($data);
            return (int)$row->id;
        }

        $data['created_at'] = $now;
        return (int)DB::table('sys_modelos')->insertGetId($data);
    }

    private function seedFields()
    {
        if (!Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $roomTypes = '{"SENCILLA":"SENCILLA","DOBLE":"DOBLE","TRIPLE":"TRIPLE","FAMILIAR":"FAMILIAR","SUITE":"SUITE"}';
        $roomStatuses = '{"DISPONIBLE":"DISPONIBLE","RESERVADA":"RESERVADA","OCUPADA":"OCUPADA","LIMPIEZA":"LIMPIEZA","MANTENIMIENTO":"MANTENIMIENTO","BLOQUEADA":"BLOQUEADA"}';
        $stayStatuses = '{"ACTIVA":"ACTIVA","CERRADA":"CERRADA","ANULADA":"ANULADA"}';
        $reservationStatuses = '{"ACTIVA":"ACTIVA","CUMPLIDA":"CUMPLIDA","ANULADA":"ANULADA"}';
        $orderStatuses = '{"ABIERTO":"ABIERTO","FACTURADO":"FACTURADO","ANULADO":"ANULADO"}';
        $invoiceTypes = '{"":"Sin factura","STANDARD":"STANDARD","POS":"POS"}';
        $sourceTypes = '{"ROOM":"ROOM","PRODUCT":"PRODUCT","SERVICE":"SERVICE","MANUAL":"MANUAL"}';
        $yesNo = '{"1":"Si","0":"No"}';
        $textAttrs = '{"class":"form-control"}';
        $comboAttrs = '{"class":"combobox"}';

        $this->seedModelFields('rooms', array(
            $this->field(1, 'Numero', 'bsText', 'room_number', '', 'null', $textAttrs, 1),
            $this->field(2, 'Tipo', 'select', 'room_type', $roomTypes, 'SENCILLA', '', 1),
            $this->field(3, 'Producto/servicio', 'select', 'inv_producto_id', 'model_App\\Inventarios\\InvProducto', 'null', $comboAttrs, 1),
            $this->field(4, 'Piso', 'bsText', 'floor', '', 'null', $textAttrs, 0),
            $this->field(5, 'Capacidad', 'bsText', 'capacity', '', '1', $textAttrs, 1),
            $this->field(6, 'Estado', 'select', 'status', $roomStatuses, 'DISPONIBLE', '', 1),
            $this->field(7, 'Descripcion', 'bsTextArea', 'description', '', 'null', $textAttrs, 0),
            $this->field(8, 'Activa', 'select', 'is_active', $yesNo, '1', '', 1),
        ));

        $this->seedModelFields('stays', array(
            $this->field(1, 'Cliente principal', 'cliente_autocomplete', 'main_cliente_id', '', 'null', $textAttrs, 1),
            $this->field(2, 'Habitacion', 'select', 'room_id', 'model_App\\Hotel\\HotelRoom', 'null', $comboAttrs, 1),
            $this->field(3, 'Check-in', 'fecha_hora', 'check_in_at', '', 'null', $textAttrs, 1),
            $this->field(4, 'Salida esperada', 'fecha_hora', 'expected_check_out_at', '', 'null', $textAttrs, 0),
            $this->field(5, 'Check-out', 'fecha_hora', 'check_out_at', '', 'null', $textAttrs, 0),
            $this->field(6, 'Adultos', 'bsText', 'adults_count', '', '1', $textAttrs, 1),
            $this->field(7, 'Niños', 'bsText', 'children_count', '', '0', $textAttrs, 0),
            $this->field(8, 'Estado', 'select', 'status', $stayStatuses, 'ACTIVA', $comboAttrs, 1),
            $this->field(9, 'Notas', 'bsTextArea', 'notes', '', 'null', $textAttrs, 0),
        ));

        $this->seedModelFields('reservations', array(
            $this->field(1, 'Cliente', 'cliente_autocomplete', 'cliente_id', '', 'null', $textAttrs, 1),
            $this->field(2, 'Habitacion', 'select', 'room_id', 'model_App\\Hotel\\HotelRoom', 'null', $comboAttrs, 1),
            $this->field(3, 'Fecha desde', 'fecha', 'reserved_from', '', 'null', $textAttrs, 1),
            $this->field(4, 'Fecha hasta', 'fecha', 'reserved_until', '', 'null', $textAttrs, 1),
            $this->field(5, 'Estado', 'select', 'status', $reservationStatuses, 'ACTIVA', $comboAttrs, 1),
            $this->field(6, 'Notas', 'bsTextArea', 'notes', '', 'null', $textAttrs, 0),
        ));

        $this->seedModelFields('guests', array(
            $this->field(1, 'Estadia', 'select', 'stay_id', 'model_App\\Hotel\\HotelStay', 'null', $comboAttrs, 1),
            $this->field(2, 'Cliente', 'select', 'cliente_id', 'model_App\\Ventas\\Cliente', 'null', $comboAttrs, 1),
            $this->field(3, 'Principal', 'select', 'is_main_guest', $yesNo, '0', $comboAttrs, 1),
            $this->field(4, 'Parentezco', 'bsText', 'relationship', '', 'null', $textAttrs, 0),
        ));

        $this->seedModelFields('orders', array(
            $this->field(1, 'Estadia', 'select', 'stay_id', 'model_App\\Hotel\\HotelStay', 'null', $comboAttrs, 1),
            $this->field(2, 'Cliente', 'select', 'cliente_id', 'model_App\\Ventas\\Cliente', 'null', $comboAttrs, 1),
            $this->field(3, 'Numero documento', 'bsText', 'document_number', '', 'null', $textAttrs, 0),
            $this->field(4, 'Fecha pedido', 'bsText', 'order_date', '', 'null', $textAttrs, 1),
            $this->field(5, 'Estado', 'select', 'status', $orderStatuses, 'ABIERTO', '', 1),
            $this->field(6, 'Tipo factura', 'select', 'invoice_type', $invoiceTypes, 'null', '', 0),
            $this->field(7, 'Factura estandar', 'bsText', 'sales_doc_id', '', 'null', $textAttrs, 0),
            $this->field(8, 'Factura POS', 'bsText', 'pos_doc_id', '', 'null', $textAttrs, 0),
            $this->field(9, 'Notas', 'bsTextArea', 'notes', '', 'null', $textAttrs, 0),
        ));

        $this->seedModelFields('lines', array(
            $this->field(1, 'Pedido hotelero', 'select', 'hotel_order_id', 'model_App\\Hotel\\HotelOrderHeader', 'null', $comboAttrs, 1),
            $this->field(2, 'Producto', 'select', 'producto_id', 'model_App\\Inventarios\\InvProducto', 'null', $comboAttrs, 1),
            $this->field(3, 'Habitacion', 'select', 'room_id', 'model_App\\Hotel\\HotelRoom', 'null', $comboAttrs, 0),
            $this->field(4, 'Descripcion', 'bsText', 'description', '', 'null', $textAttrs, 0),
            $this->field(5, 'Cantidad', 'bsText', 'quantity', '', '1', $textAttrs, 1),
            $this->field(6, 'Precio unitario', 'bsText', 'unit_price', '', '0', $textAttrs, 1),
            $this->field(7, 'Descuento', 'bsText', 'discount', '', '0', $textAttrs, 0),
            $this->field(8, 'Impuesto', 'bsText', 'tax_value', '', '0', $textAttrs, 0),
            $this->field(9, 'Tipo origen', 'select', 'source_type', $sourceTypes, 'MANUAL', '', 1),
            $this->field(10, 'Origen ID', 'bsText', 'source_id', '', 'null', $textAttrs, 0),
        ));

        $this->seedHotelGuestFields($textAttrs, $comboAttrs);
    }

    private function seedHotelGuestFields($textAttrs, $comboAttrs)
    {
        if (!isset($this->modelIds['hotel_guests']) || $this->modelIds['hotel_guests'] == 0) {
            return;
        }

        $copied = $this->copyModelFields('clients', 'hotel_guests');
        if ($copied == 0) {
            $this->seedModelFields('hotel_guests', array(
                $this->field(1, 'Tipo', 'select', 'tipo', '{"Persona natural":"Persona natural","Persona juridica":"Persona juridica","Interno":"Interno"}', 'Persona natural', $comboAttrs, 1),
                $this->field(2, 'Tipo documento', 'select', 'id_tipo_documento_id', 'table_core_tipos_docs_id', '13', $comboAttrs, 1),
                $this->field(3, 'Numero identificacion', 'bsText', 'numero_identificacion', '', 'null', $textAttrs, 1),
                $this->field(4, 'Nombre completo o Establecimiento', 'bsText', 'descripcion', '', 'null', $textAttrs, 1),
                $this->field(5, 'Direccion', 'bsText', 'direccion1', '', 'null', $textAttrs, 0),
                $this->field(6, 'Ciudad', 'select', 'codigo_ciudad', 'model_App\\Core\\Ciudad', '16920001', $comboAttrs, 1),
                $this->field(7, 'Cel/Tel', 'bsText', 'telefono1', '', 'null', $textAttrs, 0),
                $this->field(8, 'Email', 'bsText', 'email', '', 'null', $textAttrs, 0),
                $this->field(9, 'Clase de cliente', 'select', 'clase_cliente_id', 'model_App\\Ventas\\ClaseCliente', '1', $comboAttrs, 1),
                $this->field(10, 'Bodega', 'select', 'inv_bodega_id', 'model_App\\Inventarios\\InvBodega', '1', $comboAttrs, 0),
                $this->field(11, 'Estado', 'select', 'estado', '{"Activo":"Activo","Inactivo":"Inactivo"}', 'Activo', $comboAttrs, 1),
            ));
        }

        $this->getOrCreateField('Fecha de nacimiento', 'fecha', 'hotel_guest_fecha_nacimiento', '', 'null', $textAttrs, 0);
        $this->getOrCreateField('Nacionalidad', 'bsText', 'hotel_guest_nacionalidad', '', 'null', $textAttrs, 0);
        $this->getOrCreateField('Procedencia', 'bsText', 'hotel_guest_procedencia', '', 'null', $textAttrs, 0);
        $this->getOrCreateField('Destino', 'bsText', 'hotel_guest_destino', '', 'null', $textAttrs, 0);
    }

    private function copyModelFields($sourceModelKey, $targetModelKey)
    {
        if (!isset($this->modelIds[$sourceModelKey]) || !isset($this->modelIds[$targetModelKey])) {
            return 0;
        }

        $sourceModelId = (int)$this->modelIds[$sourceModelKey];
        $targetModelId = (int)$this->modelIds[$targetModelKey];
        if ($sourceModelId == 0 || $targetModelId == 0) {
            return 0;
        }

        $relations = DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $sourceModelId)
            ->orderBy('orden')
            ->get();

        $copied = 0;
        foreach ($relations as $relation) {
            $this->attachModelField($targetModelId, $relation->core_campo_id, $relation->orden);
            $copied++;
        }

        return $copied;
    }

    private function seedModelFields($modelKey, $fields)
    {
        if (!isset($this->modelIds[$modelKey]) || $this->modelIds[$modelKey] == 0) {
            return;
        }

        $modelId = (int)$this->modelIds[$modelKey];
        foreach ($fields as $field) {
            $order = $field['orden'];
            unset($field['orden']);

            $fieldId = $this->getOrCreateModelField($modelId, $field);
            $this->attachModelField($modelId, $fieldId, $order);
        }
    }

    private function getOrCreateModelField($modelId, $field)
    {
        $fieldId = (int)DB::table('sys_modelo_tiene_campos')
            ->join('sys_campos', 'sys_campos.id', '=', 'sys_modelo_tiene_campos.core_campo_id')
            ->where('sys_modelo_tiene_campos.core_modelo_id', $modelId)
            ->where('sys_campos.name', $field['name'])
            ->value('sys_campos.id');

        if ($fieldId == 0) {
            $fieldId = (int)DB::table('sys_campos')
                ->where('name', $field['name'])
                ->where('descripcion', $field['descripcion'])
                ->value('id');
        }

        $now = date('Y-m-d H:i:s');
        $field['updated_at'] = $now;
        $field = $this->onlyExistingColumns('sys_campos', $field);

        if ($fieldId > 0) {
            DB::table('sys_campos')->where('id', $fieldId)->update($field);
            return $fieldId;
        }

        $field['created_at'] = $now;
        $field = $this->onlyExistingColumns('sys_campos', $field);
        return (int)DB::table('sys_campos')->insertGetId($field);
    }

    private function attachModelField($modelId, $fieldId, $order)
    {
        if ($fieldId == 0) {
            return;
        }

        $relation = DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $modelId)
            ->where('core_campo_id', $fieldId);

        if ($relation->exists()) {
            $relation->update(array('orden' => $order));
            return;
        }

        DB::table('sys_modelo_tiene_campos')->insert(array(
            'core_modelo_id' => $modelId,
            'core_campo_id' => $fieldId,
            'orden' => $order,
        ));
    }

    private function field($order, $description, $type, $name, $options, $value, $attributes, $required)
    {
        return array(
            'orden' => $order,
            'descripcion' => $description,
            'tipo' => $type,
            'name' => $name,
            'opciones' => $options,
            'value' => $value,
            'atributos' => $attributes,
            'definicion' => '',
            'requerido' => $required,
            'editable' => 1,
            'unico' => 0,
        );
    }

    private function seedPermissions()
    {
        if ($this->appId == 0 || !Schema::hasTable('permissions')) {
            return;
        }

        $parentId = $this->upsertPermission(array(
            'name' => 'Gestión Hotelera',
            'descripcion' => 'Gestión Hotelera',
            'url' => 'hotel',
            'modelo_id' => isset($this->modelIds['stays']) ? $this->modelIds['stays'] : 0,
            'parent' => 0,
            'orden' => 1,
            'enabled' => 0,
            'fa_icon' => 'building',
        ));

        $transactionsParentId = $this->upsertPermission(array(
            'name' => 'hotel.transacciones',
            'descripcion' => 'Transacciones',
            'url' => 'web',
            'modelo_id' => isset($this->modelIds['stays']) ? $this->modelIds['stays'] : 0,
            'parent' => 0,
            'orden' => 1,
            'enabled' => 1,
            'fa_icon' => 'exchange',
        ));

        $catalogParentId = $this->upsertPermission(array(
            'name' => 'hotel.catalogos',
            'descripcion' => 'Catalogos',
            'url' => 'web',
            'modelo_id' => isset($this->modelIds['rooms']) ? $this->modelIds['rooms'] : 0,
            'parent' => 0,
            'orden' => 2,
            'enabled' => 1,
            'fa_icon' => 'list',
        ));

        // hotel/stays/check-in
        $permissions = array(
            array('hotel.dashboard', 'Panel hotelero', 'hotel', 'stays', $transactionsParentId, 0, 0, 'building'),
            array('hotel.checkin', 'Estadías', 'web', 'stays', $transactionsParentId, 1, 1, 'sign-in'),
            array('hotel.checkout', 'Check-Out', 'hotel/stays/active', 'stays', $transactionsParentId, 2, 0, 'sign-out'),
            array('hotel.reservas', 'Reservas', 'web', 'reservations', $transactionsParentId, 3, 1, 'calendar'),
            array('hotel.facturas', 'Pedidos', 'web', 'orders', $transactionsParentId, 4, 1, 'file-text'),
            array('hotel.rooms', 'Habitaciones', 'web', 'rooms', $catalogParentId, 1, 1, 'bed'),
            array('hotel.services', 'Servicios', 'web', 'services', $catalogParentId, 2, 1, 'cubes'),
            array('hotel.guests', 'Huespedes', 'web', 'hotel_guests', $catalogParentId, 3, 1, 'users'),
            array('hotel.stays.catalog', 'Estadias', 'web', 'stays', $catalogParentId, 10, 0, 'calendar'),
            array('hotel.stay_guests', 'Huespedes por estadia', 'web', 'guests', $catalogParentId, 11, 0, 'users'),
            array('hotel.orders.catalog', 'Pedidos hoteleros', 'web', 'orders', $transactionsParentId, 12, 0, 'shopping-cart'),
            array('hotel.order_lines', 'Lineas de pedidos', 'web', 'lines', $transactionsParentId, 13, 0, 'list-alt'),
            array('hotel.stays.active', 'Estadias activas', 'hotel/stays/active', 'stays', $transactionsParentId, 14, 0, 'check'),
            array('hotel.stays', 'Estadias operativas', 'hotel/stays', 'stays', $transactionsParentId, 15, 0, 'calendar'),
            array('hotel.invoices.standard', 'Generar factura estandar hotelera', 'hotel/orders/id_fila/generate-standard-invoice', 'orders', $transactionsParentId, 16, 0, 'file-text'),
            array('hotel.invoices.pos', 'Generar factura POS hotelera', 'hotel/orders/id_fila/generate-pos-invoice', 'orders', $transactionsParentId, 17, 0, 'print'),
        );

        $permissionIds = array($parentId, $transactionsParentId, $catalogParentId);
        foreach ($permissions as $permission) {
            $permissionIds[] = $this->upsertPermission(array(
                'name' => $permission[0],
                'descripcion' => $permission[1],
                'url' => $permission[2],
                'modelo_id' => isset($this->modelIds[$permission[3]]) ? $this->modelIds[$permission[3]] : 0,
                'parent' => $permission[4],
                'orden' => $permission[5],
                'enabled' => $permission[6],
                'fa_icon' => $permission[7],
            ));
        }

        $this->grantPermissionsToAdminRoles($permissionIds);
    }

    private function upsertPermission($data)
    {
        $now = date('Y-m-d H:i:s');
        $permissionId = DB::table('permissions')->where('name', $data['name'])->value('id');
        if (!$permissionId && $data['name'] == 'Gestión Hotelera') {
            $permissionId = DB::table('permissions')->where('name', 'Gestión Hotelera')->value('id');
        }

        $data = array_merge(array(
            'core_app_id' => $this->appId,
            'updated_at' => $now,
        ), $data);

        if (Schema::hasColumn('permissions', 'guard_name')) {
            $data['guard_name'] = 'web';
        }

        $data = $this->onlyExistingColumns('permissions', $data);

        if ($permissionId) {
            DB::table('permissions')->where('id', $permissionId)->update($data);
            return (int)$permissionId;
        }

        $data['created_at'] = $now;
        return (int)DB::table('permissions')->insertGetId($data);
    }

    private function grantPermissionsToAdminRoles($permissionIds)
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $roles = DB::table('roles')->whereIn('name', array('SuperAdmin', 'Administrador'))->get();

        foreach ($roles as $role) {
            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('role_has_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $data = array(
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                );

                if (Schema::hasColumn('role_has_permissions', 'orden')) {
                    $data['orden'] = 0;
                }

                DB::table('role_has_permissions')->insert($data);
            }
        }
    }

    private function seedReports()
    {
        if ($this->appId == 0 || !Schema::hasTable('sys_reportes')) {
            return;
        }

        $roomsReportId = $this->upsertReport('Listado de habitaciones hoteleras', 'hotel/reports/rooms');
        $staysReportId = $this->upsertReport('Listado de estadias hoteleras', 'hotel/reports/stays');
        $migrationReportId = $this->upsertReport('Migracion hotelera', 'hotel/reports/migration');

        if ($staysReportId && Schema::hasTable('sys_campos') && Schema::hasTable('sys_reporte_tiene_campos')) {
            $fechaDesdeId = $this->getOrCreateField('Fecha desde', 'date', 'fecha_desde', '', 'null', '{"class":"form-control"}', 0);
            $fechaHastaId = $this->getOrCreateField('Fecha hasta', 'date', 'fecha_hasta', '', 'null', '{"class":"form-control"}', 0);
            $this->attachReportField($staysReportId, $fechaDesdeId, 1);
            $this->attachReportField($staysReportId, $fechaHastaId, 2);
        }

        if ($migrationReportId && Schema::hasTable('sys_campos') && Schema::hasTable('sys_reporte_tiene_campos')) {
            $codigoHotelId = $this->getOrCreateField('Codigo hotel', 'bsText', 'codigo_hotel', '', 'null', '{"class":"form-control"}', 0);
            $fechaDesdeId = $this->getOrCreateField('Fecha desde', 'date', 'fecha_desde', '', 'null', '{"class":"form-control"}', 0);
            $fechaHastaId = $this->getOrCreateField('Fecha hasta', 'date', 'fecha_hasta', '', 'null', '{"class":"form-control"}', 0);
            $tipoMovimientoId = $this->getOrCreateField('Tipo movimiento', 'select', 'tipo_movimiento', '{"E":"Entrada","S":"Salida"}', 'E', '{"class":"form-control"}', 0);
            $procedenciaId = $this->getOrCreateField('Lugar de procedencia', 'bsText', 'lugar_procedencia', '', 'null', '{"class":"form-control"}', 0);
            $destinoId = $this->getOrCreateField('Lugar de destino', 'bsText', 'lugar_destino', '', 'null', '{"class":"form-control"}', 0);
            $this->attachReportField($migrationReportId, $codigoHotelId, 1);
            $this->attachReportField($migrationReportId, $fechaDesdeId, 2);
            $this->attachReportField($migrationReportId, $fechaHastaId, 3);
            $this->attachReportField($migrationReportId, $tipoMovimientoId, 4);
            $this->attachReportField($migrationReportId, $procedenciaId, 5);
            $this->attachReportField($migrationReportId, $destinoId, 6);
        }

        if ($roomsReportId) {
            return;
        }
    }

    private function upsertReport($description, $url)
    {
        $now = date('Y-m-d H:i:s');
        $reportId = DB::table('sys_reportes')->where('url_form_action', $url)->value('id');

        $data = array(
            'descripcion' => $description,
            'core_app_id' => $this->appId,
            'url_form_action' => $url,
            'estado' => 'Activo',
            'updated_at' => $now,
        );

        $data = $this->onlyExistingColumns('sys_reportes', $data);

        if ($reportId) {
            DB::table('sys_reportes')->where('id', $reportId)->update($data);
            return (int)$reportId;
        }

        $data['created_at'] = $now;
        return (int)DB::table('sys_reportes')->insertGetId($data);
    }

    private function getOrCreateField($description, $type, $name, $options, $value, $attributes, $required)
    {
        $fieldId = DB::table('sys_campos')
            ->where('name', $name)
            ->where('tipo', $type)
            ->value('id');

        if ($fieldId) {
            return (int)$fieldId;
        }

        $now = date('Y-m-d H:i:s');
        $data = array(
            'descripcion' => $description,
            'tipo' => $type,
            'name' => $name,
            'opciones' => $options,
            'value' => $value,
            'atributos' => $attributes,
            'definicion' => '',
            'requerido' => $required,
            'editable' => 1,
            'unico' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        );

        $data = $this->onlyExistingColumns('sys_campos', $data);
        return (int)DB::table('sys_campos')->insertGetId($data);
    }

    private function attachReportField($reportId, $fieldId, $order)
    {
        if ($fieldId == 0) {
            return;
        }

        $exists = DB::table('sys_reporte_tiene_campos')
            ->where('core_reporte_id', $reportId)
            ->where('core_campo_id', $fieldId)
            ->exists();

        if ($exists) {
            DB::table('sys_reporte_tiene_campos')
                ->where('core_reporte_id', $reportId)
                ->where('core_campo_id', $fieldId)
                ->update(array('orden' => $order));
            return;
        }

        DB::table('sys_reporte_tiene_campos')->insert(array(
            'core_reporte_id' => $reportId,
            'core_campo_id' => $fieldId,
            'orden' => $order,
        ));
    }

    private function onlyExistingColumns($table, $data)
    {
        foreach (array_keys($data) as $column) {
            if (!Schema::hasColumn($table, $column)) {
                unset($data[$column]);
            }
        }

        return $data;
    }

    private function forgetPermissionCache()
    {
        if (class_exists('Spatie\\Permission\\PermissionRegistrar')) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
