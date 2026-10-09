# Validación de flujos tras la reserva atómica

Fecha: 2026-10-09. PHP 7.3.33, Laravel 5.2.45, PHPUnit 4.8.36 y MySQL 5.7.44.

Resultado: **110 pruebas, 539 aserciones; cero errores, fallos o pruebas omitidas**.

Se ejecutaron con SQLite en memoria y un bootstrap Laravel aislado que no carga `.env`, reemplaza todas las conexiones por SQLite y utiliza sesión y caché en memoria. Las dependencias se instalaron en un directorio temporal del contenedor; no se modificó el Composer del proyecto.

| Área | Cobertura ejecutada |
| --- | --- |
| POS y facturación electrónica | Guardado POS para clientes internos; rollback al rechazar conversión electrónica; pagos, cambio, comisión de datáfono, cargos, redondeo y fechas. El controlador electrónico utiliza servicios simulados en sus pruebas. |
| Inventarios | Preparación de líneas de recetas, selección de bodegas y costos de ingredientes. |
| Hotel | Checkout con facturas de contado/crédito, cartera, pedidos abiertos y separación por empresa. |
| Nómina | Fondo de solidaridad, cuotas, parametrización de prestaciones y días del documento soporte electrónico. |
| Contabilidad | Cierre, balance de débitos/créditos y rollback por error en líneas. Un caso guarda por HTTP el cierre real de 1.000 cuentas y 100.000 movimientos previos; genera 2.000 registros y 2.000 movimientos nuevos. |
| Consecutivos | Doce pruebas de reserva, tres fábricas, identidad, migraciones, errores y rollback. |
| Atomicidad entre módulos | Cinco escenarios con modelos reales de encabezados POS, inventarios, tesorería y contabilidad: confirmación conjunta y fallas después de cada encabezado. Se verifican rollback de encabezados y contadores y reutilización del número tras una falla. |

El fixture del cierre contable se actualizó para incluir el tipo de documento válido que necesita la nueva reserva. No se cambió código de negocio durante esta validación.

## Reproducción

Con las dependencias del proyecto instaladas, PHP compatible, `pdo_sqlite` y PHPUnit:

```bash
cd appsiel
vendor/bin/phpunit -c tests/consecutivos_flows.xml
```

La suite seleccionada usa `tests/integration/isolated_flow_bootstrap.php`, no el bootstrap normal de producción. Registra explícitamente la ruta de cierre contable que prueba y los proveedores Laravel necesarios; no valida todos los proveedores ni middleware del sistema.

También se ejecutó `tests/integration/document_sequence_concurrency.php` contra una base MySQL desechable, con `READ COMMITTED` y `REPEATABLE READ`. Ambos pasaron:

- Doce procesos concurrentes y 120 encabezados sin consecutivos duplicados.
- Empresas y tipos de documento concurrentes.
- Otra serie continúa mientras una serie compartida espera el bloqueo.
- Deadlock real de InnoDB y reintento completo del callback corto sin escrituras parciales.

La base `appsiel_sequence_test_20261009_flows` se eliminó al terminar.

## Alcance

Estas pruebas no escribieron en producción ni desplegaron los cambios. La prueba entre módulos verifica fábricas y persistencia de encabezados en modo tradicional, sin turnos; no ejecuta toda la venta con movimientos y asientos desde la interfaz. Las pruebas del controlador electrónico simulan conversión/envío y no llaman a proveedores externos. No se ejecutaron los casos de tesorería que dependen de usuarios, motivos y catálogos completos de una base previamente sembrada, ni todos los endpoints modificados. No se puede concluir que todo el sistema esté validado de extremo a extremo; sí que los escenarios descritos pasan con la implementación actual.
