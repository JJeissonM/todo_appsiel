# Pruebas de estabilizacion POS (guardado, concurrencia y pestanas)

Fecha: 2026-03-16  
Ambiente sugerido: QA o produccion en horario controlado

## Preparacion
1. Validar que exista al menos un PDV activo con caja configurada.
2. Validar usuario cajero con permisos de facturacion POS.
3. Tener 2-3 productos activos con inventario.
4. Abrir consola de logs del servidor:
   - `tail -f storage/logs/laravel.log` (Linux)
   - `Get-Content storage/logs/laravel.log -Wait` (PowerShell)

## Datos de evidencia por caso
1. Usuario
2. PDV
3. Hora inicio
4. `request_id` mostrado en pantalla (si aplica)
5. Consecutivo final de factura
6. Resultado (OK/Fail)
7. Observaciones

## Caso 1: Guardado normal
1. Abrir POS create.
2. Agregar 2 productos.
3. Verificar total y medio de recaudo.
4. Guardar una sola vez.

Resultado esperado:
1. Se guarda una factura.
2. No queda spin bloqueado.
3. Respuesta imprime/flujo normal.
4. En logs aparece `POS_SAVE_SUCCESS` con `request_id`.

## Caso 2: Doble click / reintento rapido
1. En la misma factura, hacer click doble rapido en Guardar (o click + Enter).
2. Esperar respuesta.

Resultado esperado:
1. Solo se crea una factura.
2. Si llega segundo intento, aparece mensaje de recuperacion:
   - "Factura ya guardada..."
3. No se generan dos consecutivos por el mismo intento.
4. En logs debe aparecer:
   - `POS_SAVE_SUCCESS` (1 vez) y/o `POS_SAVE_REUSED_UNIQID` / `POS_SAVE_DUPLICATE_RECOVERED`.

## Caso 3: Timeout o red intermitente
1. Iniciar guardado de factura.
2. Simular latencia o corte corto de red durante el guardado.
3. Reintentar guardar.

Resultado esperado:
1. Si el servidor ya guardo, el reintento recupera factura existente.
2. El mensaje de error/advertencia muestra `Ref: <request_id>`.
3. Soporte puede buscar en logs por ese `request_id`.

Comando de busqueda sugerido:
- `rg "POS_SAVE_|<request_id>" storage/logs/laravel.log`

## Caso 4: 3-4 pestanas y pestana duplicada
1. Abrir POS en una pestana A.
2. Duplicar esa pestana (B).
3. Abrir una tercera desde menu (C) y una cuarta (D).
4. En cada pestana agregar productos distintos y guardar.

Resultado esperado:
1. Cada pestana guarda su factura sin bloquear las otras.
2. No hay cruce de `uniqid`/`draft_id` entre pestanas.
3. Si dos pestanas disparan mismo intento por duplicacion extrema, una guarda y la otra recupera.
4. No queda boton en estado pegado permanente.

## Criterios de aceptacion
1. 0 facturas duplicadas por el mismo intento de guardado.
2. 0 bloqueos permanentes del boton Guardar en los 4 casos.
3. 100% de incidentes con `request_id` rastreable en log.
4. Flujo de caja continua aun con multiples pestanas.

## Si un caso falla
1. Capturar hora exacta y usuario.
2. Guardar screenshot del mensaje (si hay `Ref:` mejor).
3. Extraer 2-3 minutos de log alrededor del evento.
4. Reportar con: escenario, pasos exactos, `request_id`, resultado observado.
