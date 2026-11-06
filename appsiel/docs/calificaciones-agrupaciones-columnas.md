# Agrupaciones de columnas con actividades independientes

## Diseño

Este modo separa dos responsabilidades:

- `sga_agrupaciones_calificacion`: empresa, año, periodo opcional, título agrupador, porcentaje total del grupo y estado.
- `sga_agrupacion_columnas`: columna C1…C15, label y porcentaje interno propio. El peso efectivo es **porcentaje del grupo × porcentaje interno / suma de porcentajes internos del grupo**. Los porcentajes internos son positivos (hasta 100) y se normalizan; no tienen que sumar 100. Para expresar un reparto 20%/80%, ingrese 20 y 80. La migración adicional asigna 1 a las columnas existentes, conservando su reparto igualitario.
- `sga_calificaciones_encabezados`: actividad del periodo, curso y asignatura, con fecha y descripción. Conserva una copia del título, label y porcentaje y el identificador de la columna de configuración.

Ejemplo: «APRENDER A CONOCER» tiene 30% y asocia C1–C4: 7,5% cada una; «APRENDER A HACER», 50% entre C5–C8: 12,5%; y «APRENDER A SER», 20% entre C9–C10: 10%. Total: 100%. La división conserva su precisión durante el cálculo; únicamente se redondea la definitiva al final. Un grupo de 100% con tres columnas da exactamente el mismo peso a las tres, sin asignar un remanente distinto a la última columna.

## Instalación y activación

1. Ejecutar `php artisan migrate --force` desde `appsiel` en la base académica. La migración solo actúa si existe `sga_calificaciones_encabezados`; no crea estructuras académicas en otras bases.
2. En Calificaciones → Catálogos → Agrupaciones de columnas crear los grupos del año e indicar su porcentaje total. El periodo vacío significa que se comparten en todos los periodos de ese año. Un conjunto con periodo propio reemplaza completamente al anual; no se mezclan conjuntos.
3. Abrir cada grupo con «Consultar» (`show` del CRUD genérico). Asociar cada columna con su label y porcentaje interno. El porcentaje por columna se calcula automáticamente y se redistribuye si se asocian o desasocian columnas antes de usar el grupo. El mismo formulario actualiza una columna ya asociada al grupo.
4. Cambiar los grupos de Borrador a Activo cuando estén listos. Los porcentajes de los grupos activos del mismo alcance deben sumar 100%; cada grupo activo debe tener al menos una columna.
5. En Configuración de Calificaciones activar **Usar agrupaciones de columnas con actividades independientes** (`calificaciones.usar_agrupaciones_columnas_calificaciones = Si`). Tiene prioridad sobre `manejar_encabezados_por_anio_lectivo_en_calificaciones`.

La nueva opción está desactivada por defecto. Al desactivarla se conservan los modos estándar, anual y por periodo anteriores. No se migran encabezados ni se recalculan notas por instalar la migración o activar el modo. Para un año/periodo con notas previas debe configurarse el conjunto equivalente antes de usar este modo: los nuevos guardados y cálculos utilizarán sus porcentajes. Configure también los años que se quieran consultar mediante este modo.

## Operación y validaciones

- En la tabla se muestran las columnas configuradas y las agrupaciones en orden de creación (ID), cada una en un bloque, y dentro de cada grupo sus columnas en orden numérico C1…C15. Se conservan los identificadores de las columnas y las notas asociadas.
- Los botones siguen abriendo la modal. Esta solicita fecha y descripción, y muestra el porcentaje como información. El servidor ignora pesos, títulos y labels enviados desde el navegador.
- Una columna solo puede pertenecer a una agrupación del mismo año/periodo y empresa; los porcentajes de grupo son positivos y el conjunto debe sumar exactamente 100%. Un conjunto incompleto bloquea la carga y el cálculo con un mensaje, sin usar silenciosamente otro modo.
- Eliminar una actividad no elimina su columna ni cambia el porcentaje.
- Desde el primer uso en una actividad o una nota, las agrupaciones quedan bloqueadas para conservar la estructura histórica. El bloqueo persiste aunque se elimine la actividad. Para una estructura distinta use otro periodo o año.
- Los reportes y la definitiva usan la misma configuración que la tabla, incluso si aún no se ha creado una actividad para una columna.
- Los permisos administrativos se asignan inicialmente a SuperAdmin y Administrador. El permiso `cali_agrupaciones_columnas` permite delegar la administración del catálogo.

## Verificación

`vendor/bin/phpunit tests/Calificaciones/AgrupacionesColumnasServiceTest.php`

Las pruebas usan SQLite en memoria e incluyen la compatibilidad con los modos anteriores, registro idempotente del catálogo, asociaciones en `show`, prioridades, aislamiento por empresa, validación de pesos, persistencia de actividades, protección frente a un ID ajeno y conservación de pesos al eliminar la actividad.

## Columnas calificadas y ponderación progresiva

Una columna participa si al menos un estudiante del mismo colegio, año, periodo, curso y asignatura tiene una nota distinta de cero. El sistema ya representa una casilla sin nota con cero; esta regla conserva esa convención.

El porcentaje del grupo se reparte proporcionalmente **entre sus columnas con notas**, usando sus porcentajes internos. Por ejemplo: grupo 30%, columnas con pesos 20/30/50; si solo las dos primeras tienen notas, reciben 12% y 18% de la definitiva. Un grupo sin ninguna columna calificada se excluye; los porcentajes de los grupos restantes se normalizan al 100%. Para un estudiante que aún tenga vacía una columna activa, esa casilla aporta cero: el conjunto de columnas se determina para la clase completa, no individualmente.

Guardar la primera nota de una columna o borrar su última nota recalcula las definitivas del alcance completo y actualiza la tabla del navegador. También se consideran las notas guardadas de estudiantes que no aparezcan en la lista de matrículas activas. Las notas de otros colegios, años, periodos, cursos o asignaturas no activan columnas de esta clase. Los modos anteriores conservan su cálculo.
