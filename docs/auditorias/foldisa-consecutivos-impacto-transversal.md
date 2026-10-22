# Consecutivos: impacto transversal y conflictos de transacciones

Revisión del 09-10-2026. Análisis estático del repositorio y consultas de lectura en `u883985631_foldisa`, con `START TRANSACTION READ ONLY` y `ROLLBACK`. No se hicieron cambios funcionales ni pruebas que tomaran bloqueos en producción.

## Conclusión

Es viable reservar consecutivos con bloqueo, pero agregar únicamente `lockForUpdate()` a `TipoDocApp::get_consecutivo_actual()` no resuelve todos los caminos. Hay asignaciones sin transacción explícita, operaciones que toman varios contadores y reservas implementadas directamente fuera de la clase compartida.

El incremento actual (`UPDATE ... consecutivo_actual + 1`) ya toma bloqueo exclusivo en InnoDB. En una transacción activa ese bloqueo se conserva hasta finalizar la transacción externa. El cambio propuesto adelanta el bloqueo para incluir la lectura; no introduce desde cero la serialización del contador. Actualmente una solicitud puede esperar al incrementar después de haber calculado un número obsoleto.

No se demostró un ciclo concreto de deadlock mediante ejecución ni se midió su frecuencia. Se identificaron condiciones que requieren diseño y pruebas. El código desplegado, middleware, configuración efectiva de cada petición y logs del servidor no se inspeccionaron.

## Evidencia de producción

- Las 12 tablas consultadas de contadores, encabezados y movimientos de ventas, inventarios, compras, tesorería, contabilidad y cartera usan InnoDB.
- `core_consecutivos_documentos` tiene PK `id` e índice no único sobre `core_documento_app_id`. No tiene índice único compuesto empresa/documento. No se encontraron filas duplicadas por esa pareja.
- `EXPLAIN` para empresa 1/documento 18 usa el índice por documento y estima una fila. No hay evidencia de un recorrido completo para ese caso. El índice compuesto único mejora la identidad de la reserva y protege su creación inicial; el alcance físico del bloqueo depende del plan, índices y aislamiento.
- La sesión de diagnóstico reportó `READ-COMMITTED`, autocommit ON, `innodb_lock_wait_timeout=50` e `innodb_rollback_on_timeout=OFF`. Son valores de esta conexión, no una medición de todas las sesiones de la aplicación.

## Caminos revisados

| Camino | Recursos y transacción observados | Implicación |
| --- | --- | --- |
| POS: `FacturaPosController::store` | Transacción manual; pedido(s), contador POS, recetas/desarmes cuando aplican, remisión, movimientos y contabilidad | El contador POS se conserva hasta el final. Dos PDV con la misma serie esperan; también comparten contadores de inventario |
| POS electrónica: `FacturaElectronicaController::store` | Transacción externa; pedido(s), POS y remisión; conversión electrónica; anticipos/abonos cuando aplican | Puede mantener varios contadores simultáneamente. La conversión abre otra transacción en la misma conexión y no libera el bloqueo al confirmar su nivel interno |
| Conversión electrónica: `DocumentHeaderService::convert_to_electronic_invoice` | Bloquea encabezado POS y luego contador FE; modifica movimientos | Ya usa reserva bloqueante directa. Debe integrarse con el manejo común, incluida creación inicial y errores anidados |
| Compras: `CompraController::store` | Transacción; entrada de almacén antes de factura de compra | Contador de inventario antes del de compras. No se encontró en esta revisión un camino demostrado que tome exactamente esa misma pareja en orden contrario |
| Ventas: `VentaController::store` | Remisión antes de venta; sin transacción explícita en este método | Agregar bloqueo a la lectura sin abarcar la reserva completa no garantiza unicidad ni rollback del conjunto |
| Inventarios: `InventarioController::store`, `InvDocumentsService::store_document` | Recetas y documento; sin transacción explícita en los métodos citados | Pueden recibir una transacción del llamador o ejecutarse sin ella; no se puede asumir siempre que exista |
| Recaudos/Pagos: `RecaudoCxcController::store`, `PagoCxpController::store` | Transacción; contador de tesorería antes de abonos y medios de pago; chequera cuando aplica | Contención con otras operaciones de la misma serie y bloqueos adicionales de cheques/cartera |
| Cruces CxC: `DocCruceController::store` | Transacción; nota contable opcional, encabezado de cruce, cartera bloqueada | Comparte recursos de contabilidad y cartera; requiere ordenar y probar los bloqueos |
| Hotel: `HotelService` | Pedido bloqueado, reserva POS, acumulación, anticipos; conversión posterior cuando aplica | La reserva POS comparte serie con caja habitual. Tiene contexto transaccional, pero usa el patrón leer/incrementar directamente |
| Nómina automática: `PagoAutomaticoNominaService` | Transacción; documentos/contratos/cartera; reserva directa bloqueante de pago | Comparte contador de pagos con tesorería y usa un camino distinto de reserva |
| CRUD genérico y duplicación contable | `ModeloController::store`, `System/ModelController::store`, `ContabilidadController::duplicar_documento` | No tienen transacción explícita en los métodos revisados; necesitan migración de la reserva |

La búsqueda encontró `get_consecutivo_actual` en 23 archivos, incluyendo definiciones y lectores. No equivale a 23 flujos operativos distintos. Existen tres constructores genéricos de encabezados (`EncabezadoDocumentoTransaccion`, `Transactions/Services/DocumentsService`, `Transactions/TransactionDocumentHeader`) y una implementación adicional `Sistema/Services/AppDocType`, utilizada en documentos soporte DATAICO.

## Conflictos posibles

1. **Espera por la misma serie.** Es esperable y necesaria. El módulo no determina la independencia: la clave es empresa/tipo de documento. Movimientos de tesorería con la identidad de la factura no necesariamente reservan otro consecutivo de tesorería.
2. **Bloqueo inefectivo fuera de transacción.** Con autocommit, una lectura `FOR UPDATE` no protege la lectura y el incremento posteriores como una unidad. Envolver solo la lectura en una transacción corta tampoco basta si se incrementa después de cerrarla.
3. **Deadlock por orden contrario de recursos.** Si A retiene contador X y solicita Y, mientras B retiene Y y solicita X, hay ciclo. Los recursos pueden ser contadores, pedidos, cartera, chequeras o filas de costos. El orden debe analizarse por los IDs realmente configurados y no solamente por nombre del módulo. Los caminos revisados no bastan para afirmar que este ciclo ya ocurre.
4. **Timeout y cambios parciales.** Con `innodb_rollback_on_timeout=OFF`, un timeout revierte la sentencia, no automáticamente toda la transacción. Capturar el error y continuar puede confirmar una operación incompleta. Los caminos sin transacción pueden haber confirmado escrituras anteriores.
5. **Errores en transacciones anidadas.** `composer.lock` fija Laravel v5.2.45. Su `DB::transaction` no implementa reintentos de deadlock. Los niveles internos usan savepoints. Un deadlock que revierte toda la transacción en el motor puede causar errores al intentar volver a un savepoint; se debe probar que la causa original y el estado de la conexión se manejen correctamente, sin continuar ni reintentar solo el servicio interior.
6. **Creación inicial concurrente.** Dos reservas de una serie nueva necesitan una fila única empresa/documento y manejo de la colisión. No basta comprobar que el contador no existe y crearlo.
7. **Migración parcial.** Una lectura antigua no bloqueante puede calcular un número que otro camino protegido ya reservó. La protección no es completa mientras escritores de la misma serie sigan usando el patrón antiguo.

## Diseño recomendado y validación pendiente

- Crear una operación explícita `reservar_consecutivo(empresa, tipo)` que bloquee, incremente y devuelva el número. Conservar la lectura de contador como lectura, porque también se consulta para validar resoluciones.
- Usar la transacción externa cuando exista; cuando no exista, reservar en una transacción propia que abarque lectura e incremento. Esta reserva aislada puede dejar saltos si falla luego el documento; si se exige rollback conjunto, el flujo completo debe ser transaccional. Documentar esa decisión antes de implementarla.
- Agregar unicidad al contador por empresa/documento y resolver la creación inicial concurrente. Migrar todos los escritores que compartan una serie, incluidas reservas directas y `AppDocType`.
- Normalizar el orden de adquisición de recursos donde sea posible. Si se conocen varios contadores de antemano, bloquearlos en orden estable; esto no elimina por sí solo ciclos con cartera, pedidos o chequeras.
- Manejar 1205 y 1213 en el límite de la operación completa, con rollback y reintentos acotados e idempotencia. En Laravel 5.2 deben implementarse expresamente y validarse los errores anidados. No reintentar indiscriminadamente errores funcionales ni efectos externos.
- Medir duración y espera de las transacciones. Mantener llamadas HTTP, impresión y otros efectos externos fuera de los intentos transaccionales que puedan repetirse.
- Antes de desplegar, probar dos PDV con la misma serie; series distintas; POS con recetas junto a inventario; compra con entrada de almacén; conversión FE simultánea; pago manual junto a nómina automática; creación de contador nuevo; timeout y deadlock controlados; rollback sin movimientos huérfanos ni duplicados; y recuperación de conexiones con transacciones anidadas.

No se ejecutó una prueba de carga ni se prometen tiempos o ausencia absoluta de deadlocks. La modificación debe validarse en un entorno aislado con configuración equivalente a producción.

## Referencias del motor y framework

- [MariaDB: FOR UPDATE](https://mariadb.com/docs/server/reference/sql-statements/data-manipulation/selecting-data/for-update).
- [MariaDB: timeout y rollback de InnoDB](https://mariadb.com/docs/server/server-usage/storage-engines/innodb/innodb-system-variables#innodb_lock_wait_timeout).
- [Laravel v5.2.45: Connection y transacciones](https://github.com/laravel/framework/blob/v5.2.45/src/Illuminate/Database/Connection.php).
