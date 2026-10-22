# Libro auxiliar por terceros

Reporte de consulta para una empresa y un año. Fuente: `contab_movimientos`, usando la conexión Laravel configurada. No realiza INSERT, UPDATE, DELETE ni ALTER sobre la información contable, ni requiere migraciones.

## Ejecución

En Appsiel, abrir **Auxiliar por cuenta → Auxiliar por terceros: mensual y anual**, o la ruta `/contab_auxiliar_terceros`. El año predeterminado es 2025. La empresa es la activa de la sesión; el servidor rechaza una empresa diferente. Los filtros opcionales se combinan: tercero (identificación y nombre), cuenta (código y nombre) y rango inclusivo de códigos completos de cuenta. No se excluyen cuentas o terceros inactivos del historial.

Para una exportación grande conviene ejecutar desde consola, evitando los tiempos máximos del servidor HTTP:

```bash
cd appsiel
php artisan contabilidad:auxiliar-terceros 1 --ano=2025
```

En el entorno Docker de este proyecto:

```bash
docker exec -w /var/www/html/appsiel appsiel_10_app \
  php artisan contabilidad:auxiliar-terceros 1 --ano=2025
```

Opciones: `--tercero=ID`, `--cuenta=ID`, `--cuenta_desde=110505`, `--cuenta_hasta=139999`, `--salida=/ruta/a/carpeta_nueva`. La carpeta de salida no debe existir. La consola imprime la ubicación del ZIP y el acta de validación, sin credenciales. La ejecución manual usa la empresa indicada por el operador; la restricción de empresa de sesión aplica a la interfaz web.

## Entrega Excel

El ZIP contiene uno o más `.xlsx`, `LEAME.txt` y `validaciones.json`. Cada Excel tiene exactamente dos hojas:

- `AUXILIAR_TERCEROS_MENSUAL`: 44 columnas, con identificación, tercero, código, cuenta, saldo inicial, tres columnas por mes y tres columnas anuales.
- `AUXILIAR_TERCEROS_DETALLE`: movimientos del año, saldo corrido por tercero y cuenta e identificadores de trazabilidad. Incluye fecha, documento, consecutivo, identificación, tercero, cuenta, concepto, débitos y créditos, además de ID de movimiento, IDs de tercero/cuenta/transacción/tipo de documento, documento soporte y estado.

Los enlaces de los importes mensuales llevan al primer movimiento de ese mes. Los enlaces anuales llevan al primer movimiento del año del tercero y cuenta. Para verificar un saldo, considerar también el saldo inicial y los movimientos anteriores. El detalle permite filtrar por tercero, cuenta y fecha, y está ordenado por IDs de tercero/cuenta, fecha e ID de movimiento; los movimientos del mismo día tienen orden determinista.

Excel admite 1.048.576 filas por hoja, incluida la cabecera. La empresa 1 tiene 1.124.114 movimientos de 2025, de modo que no caben en una única hoja. El reporte divide automáticamente en partes disjuntas por tercero y cuenta, manteniendo cada combinación completa y su consolidado anual en el mismo archivo. No se duplican saldos entre partes. Una combinación individual que supere el límite se rechaza explícitamente; necesitaría otro formato de entrega. No se truncan registros.

Las identificaciones y los códigos se escriben como texto, incluidos ceros iniciales y cadenas largas. Los conceptos y nombres no se interpretan como fórmulas. Los importes son celdas numéricas con presentación de dos decimales; no se redondean movimientos antes de sumar.

## Campos y reglas verificadas

`contab_movimientos`: `core_empresa_id`, `fecha` (DATE), `core_tercero_id`, `contab_cuenta_id`, `core_tipo_transaccion_id`, `core_tipo_doc_app_id`, `consecutivo`, `documento_soporte`, `detalle_operacion`, `valor_debito`, `valor_credito`, `valor_saldo`, `estado`, `id`. Los importes son DOUBLE, no DECIMAL.

Identificación y nombre se obtienen de `core_terceros`; código y descripción, de `contab_cuentas`; prefijo documental, de `core_tipos_docs_apps`. Se verificó que `id` es clave primaria en los tres catálogos. Los datos descriptivos son los actuales; no existe reconstrucción del nombre histórico en este reporte.

Appsiel almacena habitualmente el crédito negativo (`ContabMovimiento::contabilizar_linea_registro` guarda el crédito multiplicado por -1). La exportación presenta `-valor_credito`, conservando las reversiones; no usa `ABS`. El saldo conserva el signo registrado y no se invierte por naturaleza de cuenta. Las variaciones se acumulan separadamente del saldo inicial, evitando sumar repetidamente valores pequeños a saldos históricos muy grandes. Diciembre y saldo final usan el mismo valor; el XML numérico conserva la precisión del DOUBLE sin truncarlo a 15 cifras significativas.

El auxiliar existente (`get_movimiento_contable`, `get_saldo_inicial_v2`) no filtra por estado. La anulación en `ContabilidadController::contab_anular_documento` elimina los movimientos del documento y marca su encabezado/registros. Este reporte reproduce la consulta del libro existente: no añade filtros de estado ni joins con encabezados de distintos módulos que puedan excluir o duplicar movimientos. En el historial inspeccionado hasta 2025 no había movimientos con estado `Anulado`; sí había estados vacíos, Activo, Facturada, Pendiente, Sin enviar y Contabilizado - Sin enviar.

## Consulta principal

`AuxiliarTercerosService::summaryQuery()` construye con Query Builder una consulta equivalente a:

```sql
SELECT m.core_tercero_id, m.contab_cuenta_id,
       SUM(CASE WHEN m.fecha < :inicio THEN m.valor_saldo ELSE 0 END) AS inicial,
       SUM(CASE WHEN m.fecha >= :enero AND m.fecha < :febrero
                THEN m.valor_debito ELSE 0 END) AS d1,
       SUM(CASE WHEN m.fecha >= :enero AND m.fecha < :febrero
                THEN m.valor_credito ELSE 0 END) AS c1,
       SUM(CASE WHEN m.fecha >= :enero AND m.fecha < :febrero
                THEN m.valor_saldo ELSE 0 END) AS s1,
       -- La misma agregación para febrero a diciembre.
       SUM(CASE WHEN m.fecha >= :inicio THEN m.valor_debito ELSE 0 END) AS debito,
       SUM(CASE WHEN m.fecha >= :inicio THEN m.valor_credito ELSE 0 END) AS credito,
       SUM(CASE WHEN m.fecha >= :inicio THEN m.valor_saldo ELSE 0 END) AS saldo
FROM contab_movimientos m
WHERE m.core_empresa_id = :empresa AND m.fecha < :fin_exclusivo
GROUP BY m.core_tercero_id, m.contab_cuenta_id
ORDER BY m.core_tercero_id, m.contab_cuenta_id;
```

Para 2025: inicio `2025-01-01`, fin exclusivo `2026-01-01`. Todos los valores de filtro se enlazan como parámetros. Los filtros de tercero y cuenta se aplican a la misma consulta base para historial y detalle. El rango utiliza un `WHERE IN (SELECT id FROM contab_cuentas WHERE codigo ...)`, evitando multiplicar movimientos. La agregación principal no tiene joins. Los nombres se obtienen por lotes de IDs y el detalle sólo tiene un LEFT JOIN al tipo documental por clave primaria.

Saldo inicial por tercero y cuenta = suma de `valor_saldo` de **todo** el historial anterior al primero de enero. También se generan filas para combinaciones que sólo tienen historia y carecen de movimientos en el año.

Saldo del mes = saldo inicial + suma de `valor_saldo` desde enero hasta el cierre de ese mes. Saldo final = inicial + saldo del año; se valida contra diciembre. Con datos consistentes equivale a inicial + débitos - créditos presentados. Cuando el origen presenta diferencias, prevalece `valor_saldo`, igual que en el auxiliar existente, y se registra la advertencia.

## Rendimiento y consistencia

Las agregaciones se realizan en SQL. PHP conserva agregados por tercero/cuenta y metadatos, nunca todos los movimientos. El detalle se lee con un cursor PDO sin buffer MySQL y se escribe incrementalmente con XMLWriter; el XLSX se comprime desde archivos temporales en disco. No se crean millones de objetos de celdas de PHPExcel/PhpSpreadsheet. La memoria crece con las combinaciones, no con el número de movimientos.

Resumen, catálogos y detalle usan la misma transacción de lectura con aislamiento REPEATABLE READ en MySQL. La transacción se cierra una vez leído y conciliado el cursor, antes de comprimir los archivos. La conexión recupera su modo de buffer al finalizar, incluso ante errores. No se usa una transacción para modificar datos. El archivo sólo se entrega si pasan las conciliaciones. Los archivos web se generan en directorios privados y se eliminan después de descargar; las ejecuciones de consola conservan su entrega.

Se necesita espacio temporal para XML sin comprimir: la ejecución de 2025 utiliza aproximadamente 1,5 GB de XML antes de comprimir. No se añadieron índices ni se cambió el esquema. No se ha medido concurrencia de múltiples exportaciones simultáneas.

## Validaciones

En cada exportación, para **cada tercero, cuenta y mes**, se compara débito, crédito y variación de saldo del detalle leído contra los agregados SQL. También se compara el número de movimientos, la suma de débitos y créditos mensuales contra sus totales anuales y diciembre contra el saldo final. Se usa una tolerancia absoluta de medio centavo por la representación DOUBLE del origen. Una diferencia superior detiene la entrega.

Pruebas automatizadas en SQLite en memoria (sin modificar datos de la conexión configurada): saldo histórico, cuenta acreedora, cuenta sin movimientos del año, meses vacíos, límites de año, orden de movimientos del mismo día, aislamiento de empresa, filtros acumulativos, crédito reversado, tercero faltante, estado Pendiente, resultado vacío, partición de archivos, rechazo de resumen/detalle diferentes, año inválido y empresa no autorizada. También se prueba la descarga web con el usuario www-data y la eliminación de temporales privados. Se abre un XLSX de prueba con PhpSpreadsheet para verificar ambas hojas, importes e identificaciones como texto. El paquete de prueba contiene 11 pruebas y 60 aserciones.

```bash
docker exec -w /var/www/html/appsiel appsiel_10_app \
  php vendor/bin/phpunit tests/Contabilidad/AuxiliarTercerosTest.php
```

## Inconsistencias del origen observadas

Lecturas de la conexión configurada, empresa 1, historial anterior a `2026-01-01`:

| ID movimiento | Fecha | Débito | Crédito almacenado | Saldo almacenado |
| --- | --- | ---: | ---: | ---: |
| 137428 | 2019-11-07 | 79.832 | 0 | 798,32 |
| 1080175 | 2021-09-27 | 100.320 | -200.640 | 100.320 |
| 1080176 | 2021-09-27 | 200.640 | -100.320 | -100.320 |

Estos tres saldos no coinciden con débito + crédito almacenado. Son anteriores a 2025, afectan el saldo inicial y se conservan. Se encontraron además seis movimientos históricos sin tercero en el catálogo y doce créditos positivos en la inspección global del historial (empresas 1 y 24). Un crédito positivo puede ser una reversión y no se corrige automáticamente. No se encontraron cuentas ni tipos documentales faltantes para la empresa 1. Las cantidades de la exportación concreta y ejemplos de saldos inconsistentes quedan en `validaciones.json`.

## Archivos del desarrollo

- `appsiel/app/Contabilidad/Services/AuxiliarTercerosService.php`: consultas, conciliación y partición.
- `appsiel/app/Contabilidad/Exports/AuxiliarXlsxWriter.php`: escritura de XLSX en disco.
- `appsiel/app/Http/Controllers/Contabilidad/AuxiliarTercerosController.php`: formulario, validación de empresa y descarga.
- `appsiel/app/Console/Commands/GenerarAuxiliarTerceros.php`: ejecución desde consola.
- `appsiel/app/Console/Kernel.php`: registro del comando.
- `appsiel/app/Http/contab_routes.php`: rutas del reporte.
- `appsiel/resources/views/contabilidad/auxiliar_terceros.blade.php`: filtros.
- `appsiel/resources/views/contabilidad/auxiliar_por_cuenta.blade.php`: acceso al nuevo reporte.
- `appsiel/tests/Contabilidad/AuxiliarTercerosTest.php`: pruebas.
- `docs/auxiliar_terceros.md`: este documento.

## Ejecución verificada de 2025

Generada para empresa 1 usando la conexión configurada, el 16 de septiembre de 2026. Entrega local: `appsiel/storage/app/auxiliar_terceros/entrega_2025/auxiliar_terceros_1_2025.zip` (aproximadamente 84 MB; los reportes no se incorporan al repositorio).

| Parte | Filas de resumen | Movimientos de detalle |
| --- | ---: | ---: |
| 1 | 5.243 | 1.048.558 |
| 2 | 1.715 | 75.556 |
| Total | 6.958 | 1.124.114 |

Además de las conciliaciones del servicio, se leyeron íntegramente los XML de ambos XLSX y se verificó su estructura, cantidad de columnas y filas, sumas mensuales y anuales, igualdad exacta entre las celdas de diciembre y saldo final, y cierre del detalle contra el resumen. Una suma independiente con aritmética decimal sobre las celdas exportadas confirmó diferencias resumen/detalle inferiores a 0,0002 unidades monetarias, dentro de la tolerancia de medio centavo definida para el origen DOUBLE. Se comprobó también la integridad CRC del ZIP y su acta de validación.

Pasaron las 11 pruebas nuevas (60 aserciones), ejecutadas como www-data, y las 5 pruebas de regresión del auxiliar por cuenta (32 aserciones). Se verificaron las rutas web con middleware de autenticación y la compilación del formulario con catálogos de la empresa activa. No se realizó una prueba visual en navegador.
