# Activación piloto de turnos operativos

## Alcance y precondiciones

El piloto debe limitarse a una empresa y un PDV. No usar inicialmente una regla
global de empresa. No hacer backfill ni activar Compras, CxP, CxC, Contabilidad,
Producción, Nómina o restaurante autónomo.

1. Identificar empresa, PDV, caja, responsables y ventana de prueba.
2. Ejecutar `php artisan turnos:diagnosticar-piloto EMPRESA_ID pdv PDV_ID --dias=7`.
3. Resolver advertencias: PDV tradicional abierto, modos mixtos no deliberados y
   movimientos recientes sin FK en rutas que participarán en el piloto.
4. Confirmar que no exista apertura/cierre tradicional pendiente ni un turno
   operativo abierto incompatible.
5. Guardar la salida JSON del diagnóstico como evidencia previa.

## Activación controlada

1. Configurar por `TurnoConfigurationService` el contexto exacto `pdv:PDV_ID`.
2. Activar sólo los módulos que participarán. Para POS compuesto revisar al menos
   Ventas POS, Ventas, Inventarios, Tesorería y Facturación Electrónica; añadir Hotel
   sólo si el PDV ejecutará esos flujos.
3. Revisar y aceptar deliberadamente cualquier advertencia de grupo mixto.
4. Abrir el primer turno con usuario identificado, fecha operativa y saldo inicial.
5. Ejecutar una venta controlada y verificar FK en documento, inventario, tesorería
   y FE. Si aplica hotel, ejecutar cargo, recaudo y factura como eventos separados.
6. Confirmar que no existan registros nuevos sin FK mediante el diagnóstico JSON.
7. Cerrar el turno, generar arqueo por su FK y comparar ventas, inventario, caja y
   eventos. No combinar rangos de otros turnos de la misma fecha.
8. Revisar en orden `APERTURA` y `CIERRE`, con usuario, estados y timestamps. Probar
   auditoría o reapertura sólo con autorización y motivo.
9. Revisar el log por `turnos.assignment_failed` y resolver cualquier ocurrencia
   antes de ampliar el alcance.

## Retorno a TRADICIONAL

1. Detener nuevas operaciones del PDV.
2. Cerrar el turno abierto y completar o cancelar de forma controlada procesos en
   vuelo. Un `TurnoEnvelope` ya persistido podrá terminar conservando su turno.
3. Confirmar con el diagnóstico que no existe turno abierto.
4. Cambiar la configuración mediante `TurnoConfigurationService`; una eliminación o
   desactivación con turno abierto será rechazada.
5. Ejecutar apertura, cierre y reportes tradicionales de comprobación.
6. Conservar turnos, eventos y FK históricas; no borrar ni convertir registros.

## Riesgos y deuda que permanecen

- Herramientas administrativas de SQL pueden eludir eventos Eloquent y deben estar
  restringidas durante el piloto.
- Ventas estándar, documentos de Tesorería e Inventario sin columna PDV requieren
  `TurnoContext` cuando formen parte del alcance exacto del piloto. El diagnóstico
  los reporta a nivel empresa porque no puede atribuirlos con certeza a un PDV.
- Permanecen reportes históricos de Tesorería y POS por fecha/hora,
  `CashRegisterShiftService::getDayRange` para modo tradicional y el parche
  `TesoMovimiento::sincronizarCreatedAtConUltimoCierre` exclusivo de ese modo.
- Contabilidad, CxC/CxP y módulos pendientes no persisten todavía una identidad de
  turno propia. No deben declararse `TURNOS`.
- Nómina automática usa un encabezado de Tesorería sin contexto PDV. Una activación
  exacta del piloto no lo incluye; evitar reglas globales de Tesorería hasta separar
  formalmente ese flujo.
- Los reintentos de ajustes sobre entidades nuevas deben enviar una clave de
  idempotencia estable; sin ella el núcleo los rechaza.

## Evidencia mínima de aceptación

- Diagnóstico previo y posterior en JSON.
- IDs del turno, venta, movimientos de inventario/tesorería, FE y arqueo.
- Conteo cero de nuevos registros sin FK en las fuentes del contexto.
- Línea de tiempo de eventos sin duplicados.
- Resultado de pruebas focales y conciliación firmada por el responsable piloto.
