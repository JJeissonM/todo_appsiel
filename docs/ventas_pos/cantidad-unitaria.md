# Cantidad oculta en create/edit de factura POS

Activar `ventas_pos.ocultar_cantidad_lineas_factura = 1` en la configuración de la instancia (`appsiel/config/ventas_pos.php`). Valor predeterminado: `0`.

Los archivos de configuración de instancia están excluidos de Git. El proveedor de la aplicación registra el valor predeterminado cuando la clave no existe. Para desplegar, agregar esta clave al archivo de configuración de cada instancia que requiera usarla; no reemplazar sus demás configuraciones.

Con la opción activa:

- La columna de cantidad y la cantidad de la cinta de búsqueda se ocultan. Sus campos y posiciones se conservan para serialización, inventario y cálculo de impuestos.
- Las líneas nuevas ingresadas por código, referencia, lupa, lector de barras y selección táctil/cinta usan una unidad. Los códigos que incluyen peso también usan una unidad; el listener de balanza queda desactivado.
- La búsqueda manual y la lupa enfocan precio unitario. Se conservan los permisos existentes de edición de precio y descuento.
- Las líneas ya guardadas al editar, y las procedentes de pedidos, conservan sus cantidades e importes originales. No se transforman cantidades históricas en una unidad.
- El cálculo de bolsas, propinas, datáfono, redondeo y el envío electrónico conservan sus rutas actuales; la cantidad sigue incluida en el detalle enviado.

Con `0` se mantiene el ingreso habitual de cantidades y lectura de peso.

Validación: `appsiel/tests/js/pos_unit_quantity_test.js` ejercita las rutas de selección con ambas configuraciones. Complementar con pruebas de `PosInvoiceRoundingTest`, `PosInvoiceChargesServiceTest`, `ElectronicInvoiceAtomicStoreTest` y `pos_invoice_surcharges_test.js`.
