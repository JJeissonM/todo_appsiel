<?php

return array(
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

    // Fuentes revisadas por el diagnóstico previo al piloto. Si una tabla no
    // conserva el contexto directamente, el conteo se reporta a nivel empresa.
    'diagnostic_sources' => array(
        array('module' => 'ventas_pos', 'label' => 'Ventas POS', 'table' => 'vtas_pos_doc_encabezados', 'company_column' => 'core_empresa_id', 'context_type' => 'pdv', 'context_column' => 'pdv_id'),
        array('module' => 'ventas', 'label' => 'Ventas estándar', 'table' => 'vtas_doc_encabezados', 'company_column' => 'core_empresa_id'),
        array('module' => 'tesoreria', 'label' => 'Documentos de tesorería', 'table' => 'teso_doc_encabezados', 'company_column' => 'core_empresa_id'),
        array('module' => 'tesoreria', 'label' => 'Movimientos de tesorería', 'table' => 'teso_movimientos', 'company_column' => 'core_empresa_id', 'context_type' => 'pdv', 'context_column' => 'pdv_id'),
        array('module' => 'inventarios', 'label' => 'Documentos de inventario', 'table' => 'inv_doc_encabezados', 'company_column' => 'core_empresa_id'),
        array('module' => 'inventarios', 'label' => 'Movimientos de inventario', 'table' => 'inv_movimientos', 'company_column' => 'core_empresa_id'),
        array('module' => 'hotel', 'label' => 'Pedidos hoteleros', 'table' => 'hotel_order_headers', 'company_column' => 'empresa_id', 'context_type' => 'pdv', 'context_column' => 'pdv_id'),
        array('module' => 'hotel', 'label' => 'Cargos hoteleros', 'table' => 'hotel_order_lines', 'company_column' => 'empresa_id'),
    ),
);
