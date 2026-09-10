/* ==========================================================================
   Normalizacion de clases de proveedores segun movimiento contable Biable

   Alcance:
     - Proveedores con movimientos en cuentas de pasivo (clase 2).
     - Corrige tanto la clase generica como clases antiguas no concordantes.

   Seleccion cuando existen varias cuentas:
     1. Cuenta con movimiento acreedor.
     2. Mayor credito acumulado.
     3. Mayor movimiento absoluto acumulado.
     4. Movimiento mas reciente.
     5. Menor codigo contable como desempate final.

   Escritura permanente exclusiva:
     - compras_clases_proveedores (crea la clase si falta)
     - compras_proveedores (actualiza clase_proveedor_id)

   Idempotencia: solo actualiza proveedores cuya cuenta-clase no concuerda.
   ========================================================================= */

USE `u883985631_colegiobilingu`;
SET NAMES utf8mb4;

SET @EMPRESA_ID := 1;
SET @CLASE_GENERICA_ID := 1;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_proveedor_cuentas_pasivo;
CREATE TEMPORARY TABLE tmp_mig_proveedor_cuentas_pasivo AS
SELECT p.id AS proveedor_id,p.core_tercero_id,c.id AS contab_cuenta_id,
       c.codigo,c.descripcion,
       SUM(CASE WHEN m.valor_credito<0 THEN -m.valor_credito ELSE 0 END) creditos,
       SUM(ABS(m.valor_debito)+ABS(m.valor_credito)) movimiento_total,
       MAX(m.fecha) ultima_fecha
FROM compras_proveedores p
JOIN contab_movimientos m
  ON m.core_empresa_id=@EMPRESA_ID
 AND m.core_tercero_id=p.core_tercero_id
JOIN contab_cuentas c ON c.id=m.contab_cuenta_id
WHERE m.creado_por='Migracion Biable Contabilidad 2026'
  AND c.codigo LIKE '2%'
  AND (m.valor_debito<>0 OR m.valor_credito<>0)
GROUP BY p.id,p.core_tercero_id,c.id,c.codigo,c.descripcion;
ALTER TABLE tmp_mig_proveedor_cuentas_pasivo
  ADD PRIMARY KEY(proveedor_id,contab_cuenta_id),
  ADD INDEX ix_tmp_mig_prov_cuenta(contab_cuenta_id);

/* MySQL 5.7 no permite reabrir dos veces la misma tabla temporal. */
DROP TEMPORARY TABLE IF EXISTS tmp_mig_proveedor_cuentas_comparacion;
CREATE TEMPORARY TABLE tmp_mig_proveedor_cuentas_comparacion
LIKE tmp_mig_proveedor_cuentas_pasivo;
INSERT INTO tmp_mig_proveedor_cuentas_comparacion
SELECT * FROM tmp_mig_proveedor_cuentas_pasivo;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_proveedor_cuenta_elegida;
CREATE TEMPORARY TABLE tmp_mig_proveedor_cuenta_elegida AS
SELECT a.proveedor_id,a.core_tercero_id,a.contab_cuenta_id,a.codigo,
       a.descripcion,a.creditos,a.movimiento_total,a.ultima_fecha
FROM tmp_mig_proveedor_cuentas_pasivo a
LEFT JOIN tmp_mig_proveedor_cuentas_comparacion b
  ON b.proveedor_id=a.proveedor_id
 AND (
      (b.creditos>0)>(a.creditos>0)
   OR ((b.creditos>0)=(a.creditos>0) AND b.creditos>a.creditos)
   OR ((b.creditos>0)=(a.creditos>0) AND b.creditos=a.creditos
       AND b.movimiento_total>a.movimiento_total)
   OR ((b.creditos>0)=(a.creditos>0) AND b.creditos=a.creditos
       AND b.movimiento_total=a.movimiento_total AND b.ultima_fecha>a.ultima_fecha)
   OR ((b.creditos>0)=(a.creditos>0) AND b.creditos=a.creditos
       AND b.movimiento_total=a.movimiento_total AND b.ultima_fecha=a.ultima_fecha
       AND b.codigo<a.codigo)
 )
WHERE b.proveedor_id IS NULL;
ALTER TABLE tmp_mig_proveedor_cuenta_elegida
  ADD PRIMARY KEY(proveedor_id),ADD INDEX ix_tmp_mig_cuenta_elegida(contab_cuenta_id);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_cuentas_clase_faltante;
CREATE TEMPORARY TABLE tmp_mig_cuentas_clase_faltante AS
SELECT e.contab_cuenta_id,MIN(e.codigo) codigo,MIN(e.descripcion) descripcion
FROM tmp_mig_proveedor_cuenta_elegida e
LEFT JOIN compras_clases_proveedores cp
  ON cp.cta_x_pagar_id=e.contab_cuenta_id
WHERE cp.id IS NULL
GROUP BY e.contab_cuenta_id;
ALTER TABLE tmp_mig_cuentas_clase_faltante ADD PRIMARY KEY(contab_cuenta_id);

SET @proveedores_seleccionados :=
  (SELECT COUNT(*) FROM tmp_mig_proveedor_cuenta_elegida);
SET @clases_faltantes :=
  (SELECT COUNT(*) FROM tmp_mig_cuentas_clase_faltante);
SET @genericos_antes := (SELECT COUNT(*) FROM compras_proveedores
  WHERE clase_proveedor_id=@CLASE_GENERICA_ID);
SET @proveedores_por_actualizar := (SELECT COUNT(*)
  FROM tmp_mig_proveedor_cuenta_elegida e
  JOIN compras_proveedores p ON p.id=e.proveedor_id
  LEFT JOIN compras_clases_proveedores cp ON cp.id=p.clase_proveedor_id
  WHERE COALESCE(cp.cta_x_pagar_id,0)<>e.contab_cuenta_id);

/* El proveedor indicado debe resolverse a Salarios por pagar. */
SET @actual := (SELECT COUNT(*) FROM tmp_mig_proveedor_cuenta_elegida
  WHERE proveedor_id=6199 AND codigo='250501');
SET @sql_validar := IF(@actual=1,'DO 0',
  "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Proveedor 6199 no resolvio a cuenta 250501'");
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM tmp_mig_proveedor_cuenta_elegida e
  LEFT JOIN contab_cuentas c ON c.id=e.contab_cuenta_id
  WHERE c.id IS NULL OR c.codigo NOT LIKE '2%');
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuentas elegidas invalidas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

START TRANSACTION;

INSERT INTO compras_clases_proveedores
(descripcion,cta_x_pagar_id,cta_anticipo_id,clase_padre_id,estado,
 created_at,updated_at)
SELECT LEFT(CONCAT('Biable ',c.codigo,' - ',c.descripcion),255),
       c.contab_cuenta_id,0,@CLASE_GENERICA_ID,'Activo',NOW(),NOW()
FROM tmp_mig_cuentas_clase_faltante c;

UPDATE compras_proveedores p
JOIN tmp_mig_proveedor_cuenta_elegida e ON e.proveedor_id=p.id
LEFT JOIN compras_clases_proveedores actual ON actual.id=p.clase_proveedor_id
JOIN (SELECT cta_x_pagar_id,MIN(id) clase_proveedor_id
      FROM compras_clases_proveedores GROUP BY cta_x_pagar_id) cp
  ON cp.cta_x_pagar_id=e.contab_cuenta_id
SET p.clase_proveedor_id=cp.clase_proveedor_id,p.updated_at=NOW()
WHERE COALESCE(actual.cta_x_pagar_id,0)<>e.contab_cuenta_id;
SET @proveedores_actualizados := ROW_COUNT();

/* Validar cobertura exacta y concordancia cuenta-clase. */
SET @actual := (SELECT COUNT(*) FROM tmp_mig_proveedor_cuenta_elegida e
  LEFT JOIN compras_proveedores p ON p.id=e.proveedor_id
  LEFT JOIN compras_clases_proveedores cp ON cp.id=p.clase_proveedor_id
  WHERE p.id IS NULL OR p.clase_proveedor_id=@CLASE_GENERICA_ID
     OR cp.cta_x_pagar_id<>e.contab_cuenta_id);
SET @sql_validar := IF(@actual=0
  AND @proveedores_actualizados=@proveedores_por_actualizar,
  'DO 0',"SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignacion de clases incompleta'");
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(DISTINCT p.id)
  FROM compras_proveedores p
  JOIN contab_movimientos m ON m.core_tercero_id=p.core_tercero_id
   AND m.core_empresa_id=@EMPRESA_ID
  JOIN contab_cuentas c ON c.id=m.contab_cuenta_id
  WHERE p.clase_proveedor_id=@CLASE_GENERICA_ID
    AND m.creado_por='Migracion Biable Contabilidad 2026'
    AND c.codigo LIKE '2%'
    AND (m.valor_debito<>0 OR m.valor_credito<>0));
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Proveedores resolubles aun genericos: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

COMMIT;

SELECT @clases_faltantes clases_creadas,
       @proveedores_actualizados proveedores_actualizados,
       (SELECT COUNT(*) FROM compras_proveedores
        WHERE clase_proveedor_id=@CLASE_GENERICA_ID) proveedores_genericos_restantes,
       @proveedores_seleccionados proveedores_con_movimiento_pasivo;

SELECT p.id proveedor_id,t.numero_identificacion,t.descripcion,
       cp.id clase_proveedor_id,c.codigo cuenta_x_pagar,c.descripcion cuenta_descripcion
FROM compras_proveedores p
JOIN core_terceros t ON t.id=p.core_tercero_id
JOIN compras_clases_proveedores cp ON cp.id=p.clase_proveedor_id
JOIN contab_cuentas c ON c.id=cp.cta_x_pagar_id
WHERE p.id=6199;
