# ReteICA en compras (25 y 48)

ReteICA solo se habilita cuando `compras.maneja_retenciones_fuente` está explícitamente activa. Con la configuración desactivada, se ocultan los controles y el servidor rechaza nuevas aplicaciones de ReteICA, aunque se envíe un ID manualmente. Las retenciones históricas siguen disponibles para consulta y anulación.

El formulario de creación permite agregar, confirmar, editar y eliminar ReteICA. El importe se recalcula al cambiar productos, cantidades o descuentos. Antes de guardar se debe confirmar o eliminar la edición pendiente.

La base es la suma neta sin IVA de las líneas de compra. El servidor calcula el valor desde las líneas persistidas, sin aceptar un importe ni una tarifa enviados por el navegador. Las tasas de `contab_retenciones` permanecen en porcentaje: **4,14 por mil = 0,414 %**. Una base de $4.500.000 produce $18.630 de retención a esa tarifa.

## Instalación y configuración

1. Aplicar `2026_09_10_000001_add_reteica_to_compras_doc_encabezados.php`. Laravel 5.2 acepta una **carpeta** en `migrate --path`; con `php artisan migrate` se ejecutan todas las migraciones pendientes.
2. Ejecutar `composer dump-autoload --no-scripts` y `php artisan db:seed --class=ComprasReteicaValledupar2026Seeder`.
3. En **Configuración → Contabilidad**, seleccionar la **categoría de retención ReteICA** (`categoria_reteica_id`) y el **tercero recaudador municipal** (`tercero_reteica_id`). Ambos tienen valor predeterminado 0. El archivo local `config/contabilidad.php` está excluido de Git en este proyecto; guardar el formulario persiste las nuevas claves sin exigir copiar una configuración de otra instalación.
4. Asignar en el catálogo de retenciones la **cuenta de compras** de cada tarifa que se utilizará. El seeder deja cuentas en 0 porque dependen del plan contable de la empresa. No sobrescribe tarifas ni cuentas preexistentes al repetirse.

## Tarifas de referencia de Valledupar

El seeder incluye 3, 5, 6, 7, 8, 10, 10,62, 11, 11,04 y 14 por mil. Fuente: [Acuerdo 027 del 23 de diciembre de 2024, artículo 12, páginas 10–12](https://concejodevalledupar.gov.co/wp-content/uploads/2024/03/Acuerdo-027-de-2024.pdf). Se revisó también el [Acuerdo 011 del 31 de agosto de 2025](https://concejodevalledupar.gov.co/wp-content/uploads/2025/02/ACUERDO-N%C2%B0011-DEL-31-DE-AGOSTO-DE-2025.pdf), que modifica facilidades de pago y no reemplaza esa tabla.

Son opciones del catálogo para seleccionar según la actividad aplicable, no una asignación automática por actividad o régimen del proveedor. La selección manual también determina la procedencia de la retención; el formulario no decide exenciones o cuantías mínimas municipales. El 4,14 por mil de la referencia visual se utiliza como prueba matemática; no se presenta como tarifa de Valledupar en el seeder.

## Contabilización y control

- Conserva en el encabezado la retención seleccionada, base, tasa e importe.
- Disminuye el pago al proveedor por ReteFuente y ReteICA; registra el crédito de ReteICA y su obligación por pagar al recaudador municipal configurado.
- Registra ReteICA en `contab_registros_retenciones` y en `compras_retenciones_liquidaciones`, con el código `reteica`, origen manual, documento y registro contable relacionado. No usa una línea ficticia ni un concepto de ReteFuente.
- La contabilización repetida del mismo documento no duplica la retención.
- Las consultas y los formatos impresos incluyen las retenciones activas y el total neto.
- La anulación se ejecuta en una transacción, valida abonos, notas crédito vigentes e inventario antes de modificarlo, elimina movimientos y marca las retenciones/liquidaciones como anuladas. Conserva los importes históricos del encabezado.
- El editor antiguo de importes y la recontabilización masiva no soportan recalcular las obligaciones de ReteICA: se impide utilizarlos en esas compras para no alterar sus saldos. Para modificar una compra con ReteICA ya contabilizada, se debe anular y registrar nuevamente. Los botones de edición solicitados están disponibles durante la creación.

## Verificación

PHP: `php vendor/bin/phpunit tests/ComprasReteicaTest.php` y `php vendor/bin/phpunit tests/ComprasTurnoPropagationTest.php`, desde `appsiel/`. Usan transacciones con rollback para los datos de prueba.

JavaScript: instalar `jsdom@26` y `jquery@3` en una carpeta temporal y ejecutar `NODE_PATH=/ruta/node_modules node appsiel/tests/js/compras_reteica_test.js` desde la raíz. Comprueba importe neto con ambas retenciones, cambios de base, confirmación, edición, eliminación y bloqueo de guardado pendiente. Esta prueba verifica el DOM, no el aspecto visual en un navegador.
