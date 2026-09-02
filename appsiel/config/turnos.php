<?php

return array(
    // Implementación inicial simplificada: una sola configuración y un solo
    // turno abierto para toda la empresa. El PDV se conserva únicamente como
    // dato descriptivo de la apertura histórica.
    'simple_company_mode' => true,

    'modules' => array(
        'ventas_pos' => array('integrated' => true),
        'ventas' => array('integrated' => true),
        'facturacion_electronica' => array('integrated' => true),
        'inventarios' => array('integrated' => true),
        'tesoreria' => array('integrated' => true),
        'hotel' => array('integrated' => true),
        'compras' => array('integrated' => false),
        'cxp' => array('integrated' => false),
        'cxc' => array('integrated' => false),
        'contabilidad' => array('integrated' => false),
        'produccion' => array('integrated' => false),
        'nomina' => array('integrated' => false),
    ),

    // Agregar un flujo futuro consiste en declararlo aquí, no en cambiar el núcleo.
    'operation_groups' => array(
        'venta_pos' => array(
            'modules' => array('ventas_pos', 'ventas', 'facturacion_electronica', 'inventarios', 'tesoreria'),
            'enforce_uniform_mode' => false,
        ),
        'operacion_hotelera' => array(
            'modules' => array('hotel', 'ventas', 'facturacion_electronica', 'inventarios', 'tesoreria'),
            'enforce_uniform_mode' => false,
        ),
        'compra' => array(
            'modules' => array('compras', 'inventarios', 'cxp'),
            'enforce_uniform_mode' => false,
        ),
        'produccion' => array(
            'modules' => array('produccion', 'inventarios'),
            'enforce_uniform_mode' => false,
        ),
    ),

    // Sólo estos modelos representan una nueva operación que puede recibir un
    // turno explícito desde el formulario. Los derivados técnicos conservan el
    // turno de su origen y por eso no aparecen aquí.
    'manual_assignment_models' => array(
        'App\\VentasPos\\FacturaPos' => 'ventas_pos',
        'App\\Ventas\\VtasDocEncabezado' => 'ventas',
        'App\\Ventas\\VtasPedido' => 'ventas',
        'App\\Ventas\\VtasCotizacion' => 'ventas',
        'App\\Ventas\\VtasFacturaMedica' => 'ventas',
        'App\\Ventas\\NotaCreditoDirecta' => 'ventas',
        'App\\Matriculas\\FacturaEstudiante' => 'ventas',
        'App\\FacturacionElectronica\\Factura' => 'facturacion_electronica',
        'App\\FacturacionElectronica\\FacturaContingencia' => 'facturacion_electronica',
        'App\\Inventarios\\InvDocEncabezado' => 'inventarios',
        'App\\Inventarios\\InvFisico' => 'inventarios',
        'App\\Inventarios\\InvMovimiento' => 'inventarios',
        'App\\Inventarios\\InvEntradaAlmacen' => 'inventarios',
        'App\\Inventarios\\InvSalidaAlmacen' => 'inventarios',
        'App\\Inventarios\\InvTransferenciaMercancia' => 'inventarios',
        'App\\Inventarios\\InvFabricacion' => 'inventarios',
        'App\\Inventarios\\InvAjuste' => 'inventarios',
        // La factura de compra origina una entrada de almacén. Mientras Compras
        // continúa como integración parcial, ambos documentos comparten el
        // alcance operativo de Inventarios y la misma FK de turno.
        'App\\Compras\\ComprasDocEncabezado' => 'inventarios',
        'App\\Tesoreria\\TesoDocEncabezadoRecaudo' => 'tesoreria',
        'App\\Tesoreria\\TesoDocEncabezadoPago' => 'tesoreria',
        'App\\Tesoreria\\TesoDocEncabezadoPagoCxp' => 'tesoreria',
        'App\\Tesoreria\\TesoDocEncabezadoRecaudoCxc' => 'tesoreria',
        'App\\Tesoreria\\TesoDocEncabezadoTraslado' => 'tesoreria',
        'App\\Tesoreria\\ComprobanteEgreso' => 'tesoreria',
        'App\\Tesoreria\\ReciboCaja' => 'tesoreria',
        'App\\Tesoreria\\TesoMovimiento' => 'tesoreria',
        'App\\Hotel\\HotelOrderHeader' => 'hotel',
    ),

    // Estos perfiles operativos reciben el turno vigente automáticamente. No
    // pueden quitarlo ni escoger otro desde formularios de transacciones.
    'turn_selection_locked_roles' => array(
        'Cajero PDV',
        'Cajero Canchas',
        'Cajero Domi',
    ),

    // Fuentes revisadas por el diagnóstico previo al piloto. Si una tabla no
    // conserva el contexto directamente, el conteo se reporta a nivel empresa.
    'diagnostic_sources' => array(
        array('module' => 'ventas_pos', 'label' => 'Ventas POS', 'table' => 'vtas_pos_doc_encabezados', 'company_column' => 'core_empresa_id', 'context_type' => 'pdv', 'context_column' => 'pdv_id'),
        array('module' => 'ventas', 'label' => 'Ventas estándar', 'table' => 'vtas_doc_encabezados', 'company_column' => 'core_empresa_id'),
        array('module' => 'tesoreria', 'label' => 'Documentos de tesorería', 'table' => 'teso_doc_encabezados', 'company_column' => 'core_empresa_id'),
        array('module' => 'tesoreria', 'label' => 'Movimientos de tesorería', 'table' => 'teso_movimientos', 'company_column' => 'core_empresa_id', 'context_type' => 'pdv', 'context_column' => 'pdv_id'),
        array('module' => 'inventarios', 'label' => 'Documentos de inventario', 'table' => 'inv_doc_encabezados', 'company_column' => 'core_empresa_id'),
        array('module' => 'inventarios', 'label' => 'Movimientos de inventario', 'table' => 'inv_movimientos', 'company_column' => 'core_empresa_id'),
        array('module' => 'inventarios', 'label' => 'Facturas de compras con efecto en inventario', 'table' => 'compras_doc_encabezados', 'company_column' => 'core_empresa_id'),
        array('module' => 'hotel', 'label' => 'Pedidos hoteleros', 'table' => 'hotel_order_headers', 'company_column' => 'empresa_id', 'context_type' => 'pdv', 'context_column' => 'pdv_id'),
        array('module' => 'hotel', 'label' => 'Cargos hoteleros', 'table' => 'hotel_order_lines', 'company_column' => 'empresa_id'),
    ),
);
