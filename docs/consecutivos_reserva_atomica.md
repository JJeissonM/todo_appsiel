# Reserva atómica de consecutivos

Implementación del 09-10-2026, posterior a la saneación de POS en Foldisa.

## Uso y alcance

Los escritores del contador compartido ahora utilizan:

```php
$numero = TipoDocApp::reservar_consecutivo($empresaId, $tipoDocAppId);
```

La clave del contador continúa siendo **empresa + tipo de documento**. La transacción no es parte de esa clave. Se migraron los constructores genéricos de encabezados y las asignaciones de hotel, inventarios, tesorería, contabilidad, CxC, propiedad horizontal, nómina, conversiones electrónicas y DATAICO. Los movimientos que heredan la identidad de una factura siguen usando su número; no hacen una reserva adicional.

`get_consecutivo_actual` es una consulta de vista previa y devuelve cero si no hay contador. No crea filas ni incrementa. DATAICO reserva solamente cuando se solicita almacenar. `aumentar_consecutivo` rechaza explícitamente el patrón antiguo con `LogicException`; los escritores del repositorio ya no lo invocan. Scripts externos que lo utilicen deben migrar a la reserva y utilizar el valor retornado.

Las secuencias independientes de inscripciones, historias clínicas, contratos o chequeras no forman parte de `core_consecutivos_documentos` y no se modificaron.

## Transacciones y creación de encabezados

`DocumentSequenceService` identifica y bloquea la fila del contador, incrementa una sola vez y retorna ese valor. Rechaza contadores ambiguos, IDs inválidos y agotamiento del rango entero. Para la primera creación también toma el bloqueo del tipo de documento y vuelve a comprobar el contador; la unicidad de empresa/documento protege la creación concurrente. Ese bloqueo adicional se usa únicamente cuando no existe la fila del contador.

`DocumentSequenceTransaction` participa directamente en una transacción existente. No abre un savepoint adicional ni reintenta una reserva que forma parte de una operación mayor. El llamador debe confirmar o revertir la operación completa; no debe capturar un conflicto y continuar con una transacción incompleta.

Sin una transacción externa, el servicio abre una transacción breve y puede reintentar hasta tres veces los errores MySQL/MariaDB 1205 y 1213. Cada intento revierte sus escrituras. Si el motor ya revirtió la transacción, descarta la conexión administrada para evitar el contador de anidación obsoleto de Laravel 5.2. No reintenta errores de validación, restricciones únicas de documentos ni otros errores funcionales.

Los tres constructores de encabezados reservan **antes del INSERT**, en la misma transacción que la inserción. El CRUD genérico también reserva antes de crear los documentos numerados. Esto elimina los encabezados provisionales con consecutivo cero y permite que los eventos de creación reciban el número final. Las creaciones genéricas sin consecutivo mantienen su camino anterior.

Algunos caminos antiguos reservan un número y después realizan el resto del trabajo sin una transacción de negocio completa. La reserva es atómica, pero una falla posterior puede dejar un salto. Esta implementación no convierte todos esos procesos en una única transacción ni reintenta procesos completos con efectos externos. Dentro de una transacción externa, el rollback sí revierte la reserva.

## Migraciones y activación

| Migración | Unicidad |
| --- | --- |
| `2026_10_09_000001_make_document_counters_unique.php` | `core_consecutivos_documentos(core_empresa_id, core_documento_app_id)` |
| `2026_10_09_000002_make_pos_document_identity_unique.php` | `vtas_pos_doc_encabezados(core_empresa_id, core_tipo_transaccion_id, core_tipo_doc_app_id, consecutivo)` |

Las migraciones son repetibles y permiten retirar sus índices con `down`. Si existen duplicados históricos, rechazan la instalación con un error de conciliación; no fusionan contadores ni renumeran documentos. La restricción documental se agrega a POS, cuyo histórico se saneó. Los demás módulos usan la reserva compartida; no se agregan restricciones únicas a movimientos, donde una factura tiene varias filas, ni se imponen índices a históricos de otros encabezados sin conciliarlos primero.

Para activar en un tenant, publicar conjuntamente todos los escritores actualizados y ejecutar estas dos migraciones mediante el procedimiento de despliegue. Evitar que queden procesos con la versión anterior facturando durante el cambio. Revisar las migraciones pendientes antes de ejecutar una migración global. No cambiar la clave del contador para incluir la transacción: eso alteraría las series actuales.

**Esta tarea implementó y probó el código en el repositorio; no desplegó la aplicación ni ejecutó estas migraciones en producción.**

## Validación

- PHP 7.3 y componentes Illuminate v5.2.45, correspondientes al framework del proyecto.
- PHPUnit: 12 pruebas y 37 aserciones en SQLite en memoria, sin cargar la aplicación ni su `.env`: reservas, independencia, vistas previas, rollback externo, tres constructores, rechazo de INSERT duplicado, unicidad de cuatro campos, migraciones, errores funcionales, agotamiento y reintentos.
- Integración InnoDB en MySQL 5.7.44 local, con READ COMMITTED y REPEATABLE READ: 12 procesos concurrentes crean 120 encabezados y un solo contador nuevo; compañías y tipos distintos avanzan independientemente; una serie bloqueada no detiene otra; un deadlock real se recupera con la operación completa y sin filas parciales ni números repetidos. Cada ejecución termina con 244 encabezados distribuidos en tres series consecutivas.
- La integración valida el motor InnoDB, pero no es una prueba de carga de toda la aplicación ni una medición en MariaDB 11.8 de producción.

Las pruebas se ejecutaron con dependencias aisladas porque este checkout no tiene `vendor`. No se utilizaron credenciales ni tablas de producción para las pruebas.

La prueba de integración está en `appsiel/tests/integration/document_sequence_concurrency.php`. Requiere una base descartable previamente creada cuyo nombre empiece por `appsiel_sequence_test_`. Se conecta únicamente mediante las variables `SEQUENCE_TEST_DATABASE`, `SEQUENCE_TEST_HOST`, `SEQUENCE_TEST_PORT`, `SEQUENCE_TEST_USERNAME` y `SEQUENCE_TEST_PASSWORD`; no carga `.env`. Reinicia sus tres tablas de prueba en esa base. `SEQUENCE_TEST_ISOLATION` admite `READ COMMITTED` (por defecto) o `REPEATABLE READ`.
