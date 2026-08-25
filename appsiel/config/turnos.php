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
);
