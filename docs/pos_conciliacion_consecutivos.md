# Conciliación de consecutivos POS con Artisan

El comando `pos:conciliar-consecutivos` utiliza la conexión predeterminada **que Laravel tenga cargada al ejecutarse**, incluida la configuración cacheada. Muestra el nombre de la base al comenzar. No tiene credenciales ni nombres de tenant incorporados y no carga una conexión de auditoría externa.

## Uso

Desde el directorio `appsiel`, generar un diagnóstico sin cambiar datos:

```bash
php artisan pos:conciliar-consecutivos
```

Para aplicar, detener temporalmente la facturación, los workers y las integraciones que puedan escribir documentos o abonos. Ejecutar:

```bash
php artisan pos:conciliar-consecutivos --ejecutar
```

`--ejecutar` autoriza la transacción completa, también en producción; no solicita una confirmación interactiva adicional. Se vuelve a calcular el plan y se revalidan los datos: no aplica ciegamente un archivo del diagnóstico anterior. Los bloqueos no sustituyen detener los escritores históricos que no respeten la reserva atómica del contador.

El respaldo se crea en un subdirectorio nuevo de `storage/app/conciliaciones-pos/`. Para elegir una ubicación privada fuera del proyecto:

```bash
php artisan pos:conciliar-consecutivos --ejecutar --respaldo=/ruta/privada/conciliacion-pos-20261113-01
```

El directorio indicado debe ser nuevo. Se protege con permisos 0700 y sus archivos con 0600. Debe existir espacio suficiente para respaldar las filas y el esquema consultados. El respaldo contiene datos del negocio; conservarlo en almacenamiento privado.

Después de una conciliación exitosa, ejecutar las migraciones pendientes mediante el procedimiento habitual de despliegue; por ejemplo, una vez revisadas:

```bash
php artisan migrate --force
```

El comando de conciliación no ejecuta migraciones, no activa mantenimiento, no despliega código ni modifica índices. La restricción única POS y la reserva atómica de consecutivos son las que previenen nuevos duplicados; deben quedar activadas antes de reanudar la facturación.

## Qué concilia

- Detecta todos los grupos duplicados por empresa, transacción, tipo de documento y consecutivo del histórico POS.
- Conserva el número en la primera factura vigente por ID. Las anuladas se ordenan después de las contabilizadas; si todas están anuladas, conserva el número la de menor ID.
- Propone los siguientes números inmediatos para las demás facturas del grupo. Deben estar libres en esa empresa/tipo, incluidos movimientos y referencias de cartera, y no superar el contador existente. No busca números alternativos, no modifica el contador ni utiliza un destino dos veces.
- Asigna movimientos mediante empresa, transacción, tipo, número, usuario, fecha y tercero; corrobora PDV/cajero, remisión y cliente donde existen. Rechaza cualquier movimiento que corresponda a más de un encabezado. No decide mediante el importe o la hora de creación.
- Coteja productos, cantidades e importes entre detalles, movimientos POS, ventas y líneas contables de productos; verifica equilibrio contable, recaudos, ajustes al peso, cartera y abonos.
- Renumera encabezados y sus movimientos POS, ventas, tesorería, contabilidad y cartera. En abonos cambia la referencia de la factura, manteniendo la numeración propia del recibo.
- Puede actualizar referencias textuales con el prefijo documental (por ejemplo, `POS 28859`) en filas cuyo dueño ya se identificó. Para inventario exige que el ID de la remisión coincida con la relación del encabezado POS. Conserva el número propio de inventario.
- Guarda el plan antes de escribir; bloquea los contadores en orden estable y las filas consultadas; revalida esquema, destinos y contenido completo. Aplica todo en una transacción y comprueba que solo cambiaron las columnas previstas. Verifica nuevamente desde una transacción nueva después del commit.

## Cuándo se detiene

Todo el conjunto debe poder conciliarse. Si un grupo falla, **no se aplica ninguno**. Se devuelve código 1 y se guarda el diagnóstico disponible en `failure.json`.

Requieren revisión manual: destinos ocupados, contador ausente/ambiguo/atrasado, facturas contabilizadas sin movimientos, diferencias de importes o productos, estados y formas de pago no soportados, movimientos ambiguos, tablas necesarias ausentes, referencias numéricas adicionales o textos sin dueño inequívoco. El estado `Anulado` permite ausencia de movimientos, pero no elimina controles sobre los movimientos y abonos que existan.

Se examinan las tablas base del esquema, sus columnas con `consecutivo` y los campos de texto de descripción, detalle, referencia, documento, observación y concepto. Los textos con números coincidentes que no puedan interpretarse con certeza también bloquean el proceso; no se ignoran automáticamente. No interpreta otras convenciones de referencias personalizadas, binarios ni documentos externos a la base. Deben revisarse por separado.

La ejecución en producción admite MySQL/MariaDB, exige InnoDB en las tablas de negocio soportadas y rechaza bases con triggers, cuyos efectos requieren revisión previa. SQLite se admite para las pruebas aisladas.

## Evidencia y recuperación

- `plan.json`: base, esquema, consultas, filas previas y modificaciones propuestas.
- `rollback-plan.json`: filas antes/después invertidas; es evidencia para una restauración revisada, no un ejecutor automático.
- `manifest.json`: SHA-256 del plan y del plan inverso, escritos antes de modificar datos.
- `validated.json`: las comprobaciones pasaron antes de solicitar el commit. Este archivo **no demuestra** que se confirmó.
- `result.json`: `DRY_RUN` o `COMMITTED`.
- `verified.json`: comprobación posterior al commit, con cero grupos duplicados.
- `failure.json`: motivo y evidencia disponible. Si indica `COMMITTED`, los datos ya se confirmaron aunque haya fallado el informe posterior. Si indica `UNKNOWN`, falló la respuesta al commit y se debe consultar el estado real antes de repetir o restaurar.

Ante fallos anteriores al commit se revierte la transacción. No restaurar indiscriminadamente un respaldo: primero cotejar las filas actuales para no sobrescribir operaciones posteriores.

Código de salida 0 significa diagnóstico válido, conciliación verificada o ausencia de duplicados. Una nueva ejecución después de conciliar no renumera de nuevo las facturas.

## Pruebas aisladas

Sin cargar Laravel ni `.env`:

```bash
vendor/bin/phpunit --no-configuration tests/VentasPos/PosDuplicateReconciliationServiceTest.php
```

La suite opcional en MySQL/MariaDB usa exclusivamente un socket Unix local y crea/elimina una base de pruebas por proceso (`appsiel_pos_reconciliation_test_<pid>`); nunca utiliza `.env`:

```bash
APPSIEL_POS_TEST_MYSQL_SOCKET=/ruta/al/mysql-test.sock \
  vendor/bin/phpunit --no-configuration tests/integration/PosDuplicateReconciliationMysqlTest.php
```

El usuario local predeterminado es `root`, con contraseña vacía. Se puede configurar mediante `APPSIEL_POS_TEST_MYSQL_USER` y `APPSIEL_POS_TEST_MYSQL_PASSWORD` para un servidor de pruebas. Sin socket, esta suite se omite. Las pruebas requieren PHP 7.1+ y PDO SQLite; la suite MySQL requiere además PDO MySQL. Son compatibles con PHPUnit 4 y 9.
