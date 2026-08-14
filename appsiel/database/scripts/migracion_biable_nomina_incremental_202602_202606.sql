/* ============================================================================
   Migracion incremental de nomina Biable -> Appsiel
   Periodo: 2026-02-01 a 2026-06-30
   Destino: u883985631_colegiobilingu
   Compatible con MySQL 5.7

   Requiere estas tablas de snapshot en la base destino:
     mig_biable_nmresumen_202602_202606
     mig_biable_contratos_202602_202606
     mig_biable_nmempleados_202602_202606
     mig_biable_conceptos_202602_202606
     mig_biable_cargos_202602_202606

   La carga es idempotente: reemplaza exclusivamente los documentos creados por
   "Migracion" que coinciden con los periodos del snapshot. No modifica enero.
   ============================================================================ */

USE `u883985631_colegiobilingu`;
SET NAMES utf8mb4;

SET @CORE_EMPRESA_ID := 1;
SET @CORE_TIPO_TRANSACCION_NOMINA_ID := 14;
SET @FECHA_DESDE := '2026-02-01';
SET @FECHA_HASTA := '2026-06-30';
SET @ESPERADO_PERIODOS := 14;
SET @ESPERADO_MOVIMIENTOS := 3763;
SET @ESPERADO_DEVENGOS := 2455407196.00;
SET @ESPERADO_DEDUCCIONES := 472578569.00;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_nomina_movimientos;
CREATE TEMPORARY TABLE tmp_mig_nomina_movimientos AS
SELECT
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(r.ID_TERC),'.',''),'-',''),' ',''),',',''), '') AS documento,
  STR_TO_DATE(r.FECHA_INICIAL, '%Y%m%d') AS fecha_inicial,
  STR_TO_DATE(r.FECHA_FINAL, '%Y%m%d') AS fecha_final,
  TRIM(r.ID_CONTRATO) AS contrato_biable,
  TRIM(r.ID_CPTO) AS concepto_biable,
  CAST(r.ID_IND_DEV_DED AS UNSIGNED) AS naturaleza_movimiento,
  CAST(r.NMMOV_VALOR AS DECIMAL(15,2)) AS valor,
  CAST(COALESCE(r.NMMOV_HORAS,0) AS DECIMAL(10,2)) AS horas,
  TRIM(r.ID_TIPO_DOC) AS tipo_documento_biable,
  TRIM(r.TIPO_NOMINA) AS tipo_nomina,
  TRIM(r.CO_MOV_DESC) AS detalle
FROM mig_biable_nmresumen_202602_202606 r
WHERE STR_TO_DATE(r.FECHA_FINAL, '%Y%m%d') BETWEEN @FECHA_DESDE AND @FECHA_HASTA;

ALTER TABLE tmp_mig_nomina_movimientos
  ADD INDEX ix_tmp_mig_contrato (contrato_biable),
  ADD INDEX ix_tmp_mig_concepto (concepto_biable),
  ADD INDEX ix_tmp_mig_periodo (fecha_inicial, fecha_final, tipo_documento_biable, tipo_nomina);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_nomina_periodos;
CREATE TEMPORARY TABLE tmp_mig_nomina_periodos AS
SELECT DISTINCT
  tipo_documento_biable,
  tipo_nomina,
  CASE tipo_documento_biable
    WHEN 'LC' THEN 121
    WHEN 'LS' THEN 122
    WHEN 'LV' THEN 123
    WHEN 'PL' THEN 124
    WHEN 'BN' THEN 125
    ELSE 21
  END AS core_tipo_doc_app_id,
  CASE tipo_documento_biable
    WHEN 'LC' THEN 'liquidacion_contrato'
    WHEN 'LS' THEN 'liquidacion_semestral'
    WHEN 'LV' THEN 'liquidacion_vacaciones'
    WHEN 'PL' THEN 'prima_legal'
    WHEN 'BN' THEN 'bonificacion_nomina'
    ELSE 'nomina_mensual'
  END AS tipo_liquidacion,
  fecha_inicial,
  fecha_final
FROM tmp_mig_nomina_movimientos
WHERE fecha_inicial IS NOT NULL AND fecha_final IS NOT NULL;

ALTER TABLE tmp_mig_nomina_periodos
  ADD UNIQUE KEY uq_tmp_mig_periodo
    (tipo_documento_biable, tipo_nomina, fecha_inicial, fecha_final);

/* El cliente mysql aborta ante SIGNAL y cierra la conexion, haciendo rollback. */
SET @validacion := (
  SELECT IF(COUNT(*) = @ESPERADO_MOVIMIENTOS, 'DO 0',
    CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Snapshot invalido: se esperaban ",
           @ESPERADO_MOVIMIENTOS, " movimientos y se encontraron ", COUNT(*), "'"))
  FROM tmp_mig_nomina_movimientos
);
PREPARE validar FROM @validacion; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @validacion := (
  SELECT IF(COUNT(*) = @ESPERADO_PERIODOS, 'DO 0',
    CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Snapshot invalido: se esperaban ",
           @ESPERADO_PERIODOS, " periodos y se encontraron ", COUNT(*), "'"))
  FROM tmp_mig_nomina_periodos
);
PREPARE validar FROM @validacion; EXECUTE validar; DEALLOCATE PREPARE validar;

/* --------------------------------------------------------------------------
   Terceros/empleados nuevos requeridos por febrero-junio
   -------------------------------------------------------------------------- */
SET @nuevo_tercero_id := (SELECT COALESCE(MAX(id),0) FROM core_terceros);
INSERT INTO core_terceros
(id, descripcion, core_empresa_id, imagen, tipo, razon_social, nombre1, otros_nombres,
 apellido1, apellido2, id_tipo_documento_id, numero_identificacion,
 digito_verificacion, direccion1, direccion2, barrio, codigo_ciudad, codigo_postal,
 telefono1, telefono2, email, pagina_web, estado, user_id, contab_anticipo_cta_id,
 contab_cartera_cta_id, contab_cxp_cta_id, tax_level_code, creado_por,
 modificado_por, created_at, updated_at)
SELECT
  (@nuevo_tercero_id := @nuevo_tercero_id + 1),
  e.nombre_completo, @CORE_EMPRESA_ID, '', 'Persona', e.nombre_completo,
  e.nombres, '', e.apellido1, e.apellido2, 1, e.numero_identificacion,
  0, '', '', '', 0, 0, '', e.telefono2, '', '', e.estado,
  1, 0, 0, 0, NULL, 'Migracion', 'Migracion', NOW(), NOW()
FROM (
  SELECT
    CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(n.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED) AS numero_identificacion,
    MIN(TRIM(n.NOMBRE_COMPLETO)) AS nombre_completo,
    MIN(TRIM(n.NOMBRES)) AS nombres,
    MIN(TRIM(n.APELLIDO1)) AS apellido1,
    MIN(TRIM(n.APELLIDO2)) AS apellido2,
    MIN(TRIM(n.TELEFONO_2)) AS telefono2,
    IF(MAX(n.ESTADO_CONTRATO) IN ('1','A'), 'Activo', 'Inactivo') AS estado
  FROM mig_biable_nmempleados_202602_202606 n
  WHERE NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(n.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
  GROUP BY CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(n.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
) e
JOIN (SELECT DISTINCT CAST(documento AS UNSIGNED) AS numero_identificacion
      FROM tmp_mig_nomina_movimientos WHERE documento REGEXP '^[0-9]+$') m
  ON m.numero_identificacion = e.numero_identificacion
LEFT JOIN core_terceros t
  ON t.numero_identificacion = e.numero_identificacion
WHERE t.id IS NULL;

/* Conceptos nuevos presentes en el periodo (actualmente el 738). */
SET @nuevo_concepto_id := (SELECT COALESCE(MAX(id),0) FROM nom_conceptos);
INSERT INTO nom_conceptos
(id, descripcion, modo_liquidacion_id, naturaleza, porcentaje_sobre_basico, valor_fijo,
 abreviatura, forma_parte_basico, nom_agrupacion_id, cpto_dian_id, estado,
 created_at, updated_at)
SELECT
  (@nuevo_concepto_id := @nuevo_concepto_id + 1), c.descripcion,
  2,
  IF(c.naturaleza_biable = '2', 'Deduccion', 'Devengo'),
  0, 0, c.codigo, 0, 1,
  COALESCE((SELECT MIN(d.id) FROM nom_elect_cat_cptos_dian d
            WHERE d.codigo = IF(c.naturaleza_biable = '2','OTRA_DEDUCCION','OTRO_CONCEPTO')), 0),
  'Activo', NOW(), NOW()
FROM (
  SELECT TRIM(CODIGO) AS codigo, MIN(TRIM(DESCRIPCION)) AS descripcion,
         MIN(TRIM(NATURALEZA)) AS naturaleza_biable
  FROM mig_biable_conceptos_202602_202606
  GROUP BY TRIM(CODIGO)
) c
JOIN (SELECT DISTINCT concepto_biable FROM tmp_mig_nomina_movimientos) m
  ON m.concepto_biable = c.codigo
LEFT JOIN nom_conceptos nc ON nc.abreviatura = c.codigo
WHERE c.codigo <> '' AND nc.id IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_conceptos;
CREATE TEMPORARY TABLE tmp_mig_conceptos (
  concepto_biable VARCHAR(20) NOT NULL PRIMARY KEY,
  nom_concepto_id INT UNSIGNED NOT NULL
);
INSERT INTO tmp_mig_conceptos
SELECT m.concepto_biable, MIN(nc.id)
FROM (SELECT DISTINCT concepto_biable FROM tmp_mig_nomina_movimientos) m
JOIN nom_conceptos nc ON nc.abreviatura = m.concepto_biable
GROUP BY m.concepto_biable;

/* --------------------------------------------------------------------------
   Contratos nuevos y mapa contrato Biable -> Appsiel
   -------------------------------------------------------------------------- */
DROP TEMPORARY TABLE IF EXISTS tmp_mig_contratos_fuente;
CREATE TEMPORARY TABLE tmp_mig_contratos_fuente AS
SELECT DISTINCT
  TRIM(c.CODIGO) AS contrato_biable,
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(c.ID_TERC),'.',''),'-',''),' ',''),',',''), '') AS documento,
  COALESCE(STR_TO_DATE(NULLIF(TRIM(c.FECHA_INGRESO),''),'%Y%m%d'), '1900-01-01') AS fecha_ingreso,
  CASE
    WHEN c.FECHA_RETIRO IS NULL OR TRIM(c.FECHA_RETIRO) IN ('','00000000') THEN '2999-12-31'
    ELSE COALESCE(STR_TO_DATE(c.FECHA_RETIRO,'%Y%m%d'),'2999-12-31')
  END AS contrato_hasta,
  CAST(COALESCE(c.SALARIO,0) AS DECIMAL(15,2)) AS salario,
  IF(c.IND_SAL_INT='1',1,0) AS salario_integral,
  IF(c.IND_AUX='1',1,0) AS liquida_auxilio,
  TRIM(c.ID_CARGO) AS cargo_biable,
  TRIM(c.EPS) AS eps_biable,
  TRIM(c.PENSION) AS pension_biable,
  TRIM(c.ARP) AS arl_biable,
  TRIM(c.CAJA) AS caja_biable
FROM mig_biable_contratos_202602_202606 c
JOIN (SELECT DISTINCT contrato_biable, documento FROM tmp_mig_nomina_movimientos) m
  ON m.contrato_biable = TRIM(c.CODIGO)
 AND m.documento = NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(c.ID_TERC),'.',''),'-',''),' ',''),',',''), '');

ALTER TABLE tmp_mig_contratos_fuente
  ADD UNIQUE KEY uq_tmp_mig_contrato (contrato_biable),
  ADD INDEX ix_tmp_mig_contrato_documento (documento);

SET @nuevo_contrato_id := (SELECT COALESCE(MAX(id),0) FROM nom_contratos);
INSERT INTO nom_contratos
(id, core_tercero_id, clase_contrato, cargo_id, grupo_empleado_id,
 clase_riesgo_laboral_id, horas_laborales, sueldo, salario_integral, fecha_ingreso,
 contrato_hasta, entidad_salud_id, entidad_pension_id, entidad_arl_id, estado,
 created_at, updated_at, liquida_subsidio_transporte, planilla_pila_id,
 es_pasante_sena, entidad_cesantias_id, entidad_caja_compensacion_id,
 genera_planilla_integrada, tipo_cotizante, turno_default_id,
 fingerprint_reader_id, dias_laborados_mes, excluir_documentos_nomina_electronica)
SELECT
  (@nuevo_contrato_id := @nuevo_contrato_id + 1), t.id, 'MIGRADO',
  COALESCE((SELECT MIN(car.id)
            FROM mig_biable_cargos_202602_202606 bc
            JOIN nom_cargos car ON car.descripcion = TRIM(bc.DESCRIPCION)
            WHERE TRIM(bc.CODIGO) = c.cargo_biable), 1),
  1, 1, 0, c.salario, c.salario_integral, c.fecha_ingreso, c.contrato_hasta,
  COALESCE((SELECT MIN(ne.id) FROM nom_entidades ne
            WHERE ne.codigo_nacional=c.eps_biable AND ne.tipo_entidad='EPS'),0),
  COALESCE((SELECT MIN(ne.id) FROM nom_entidades ne
            WHERE ne.codigo_nacional=c.pension_biable AND ne.tipo_entidad='AFP'),0),
  COALESCE((SELECT MIN(ne.id) FROM nom_entidades ne
            WHERE ne.codigo_nacional=c.arl_biable AND ne.tipo_entidad='ARP'),0),
  IF(c.contrato_hasta='2999-12-31','Activo','Inactivo'),
  NOW(), NOW(), c.liquida_auxilio, 0, 0, 0,
  COALESCE((SELECT MIN(ne.id) FROM nom_entidades ne
            WHERE ne.codigo_nacional=c.caja_biable AND ne.tipo_entidad='CCF'),0),
  0, '', NULL, 0, 30, 0
FROM tmp_mig_contratos_fuente c
JOIN (SELECT numero_identificacion, MIN(id) AS id
      FROM core_terceros GROUP BY numero_identificacion) t
  ON t.numero_identificacion = CAST(c.documento AS UNSIGNED)
LEFT JOIN nom_contratos nc
  ON nc.core_tercero_id=t.id
 AND nc.fecha_ingreso=c.fecha_ingreso
 AND nc.sueldo=c.salario
WHERE c.documento REGEXP '^[0-9]+$'
  AND nc.id IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_contratos;
CREATE TEMPORARY TABLE tmp_mig_contratos (
  contrato_biable VARCHAR(30) NOT NULL PRIMARY KEY,
  nom_contrato_id INT UNSIGNED NOT NULL,
  core_tercero_id INT UNSIGNED NOT NULL
);
INSERT INTO tmp_mig_contratos
SELECT c.contrato_biable, MIN(nc.id), MIN(nc.core_tercero_id)
FROM tmp_mig_contratos_fuente c
JOIN (SELECT numero_identificacion, MIN(id) AS id
      FROM core_terceros GROUP BY numero_identificacion) t
  ON t.numero_identificacion=CAST(c.documento AS UNSIGNED)
JOIN nom_contratos nc
  ON nc.core_tercero_id=t.id
 AND nc.fecha_ingreso=c.fecha_ingreso
 AND nc.sueldo=c.salario
GROUP BY c.contrato_biable;

/* Ningun movimiento puede perderse por falta de tercero, contrato o concepto. */
SET @faltantes := (
  SELECT COUNT(*)
  FROM tmp_mig_nomina_movimientos m
  LEFT JOIN tmp_mig_contratos c ON c.contrato_biable=m.contrato_biable
  LEFT JOIN tmp_mig_conceptos cp ON cp.concepto_biable=m.concepto_biable
  WHERE m.documento IS NULL OR m.documento NOT REGEXP '^[0-9]+$'
     OR c.nom_contrato_id IS NULL OR cp.nom_concepto_id IS NULL
);
SET @validacion := IF(@faltantes=0, 'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hay ", @faltantes,
         " movimientos sin tercero, contrato o concepto'"));
PREPARE validar FROM @validacion; EXECUTE validar; DEALLOCATE PREPARE validar;

/* --------------------------------------------------------------------------
   Reemplazo idempotente de los documentos del rango
   -------------------------------------------------------------------------- */
DROP TEMPORARY TABLE IF EXISTS tmp_mig_encabezados_anteriores;
CREATE TEMPORARY TABLE tmp_mig_encabezados_anteriores (
  id INT UNSIGNED NOT NULL PRIMARY KEY
);
INSERT INTO tmp_mig_encabezados_anteriores
SELECT DISTINCT e.id
FROM nom_doc_encabezados e
JOIN tmp_mig_nomina_periodos p
  ON e.fecha=p.fecha_final
 AND e.core_tipo_doc_app_id=p.core_tipo_doc_app_id
 AND e.tipo_liquidacion=p.tipo_liquidacion
 AND e.descripcion LIKE CONCAT('%',DATE_FORMAT(p.fecha_inicial,'%Y-%m-%d'),
                               ' a ',DATE_FORMAT(p.fecha_final,'%Y-%m-%d'))
WHERE e.core_empresa_id=@CORE_EMPRESA_ID
  AND e.core_tipo_transaccion_id=@CORE_TIPO_TRANSACCION_NOMINA_ID
  AND e.creado_por='Migracion';

DROP TEMPORARY TABLE IF EXISTS tmp_mig_encabezados;
CREATE TEMPORARY TABLE tmp_mig_encabezados (
  nom_doc_encabezado_id INT UNSIGNED NOT NULL,
  tipo_documento_biable VARCHAR(20) NOT NULL,
  tipo_nomina VARCHAR(20) NOT NULL,
  fecha_inicial DATE NOT NULL,
  fecha_final DATE NOT NULL,
  UNIQUE KEY uq_tmp_mig_encabezado
    (tipo_documento_biable, tipo_nomina, fecha_inicial, fecha_final),
  INDEX ix_tmp_mig_encabezado_id (nom_doc_encabezado_id)
);

/* Desde este punto solo hay DML sobre tablas InnoDB: el reemplazo es atomico. */
START TRANSACTION;

DELETE ed
FROM nom_empleados_del_documento ed
JOIN tmp_mig_encabezados_anteriores x ON x.id=ed.nom_doc_encabezado_id;

DELETE r
FROM nom_doc_registros r
JOIN tmp_mig_encabezados_anteriores x ON x.id=r.nom_doc_encabezado_id;

DELETE e
FROM nom_doc_encabezados e
JOIN tmp_mig_encabezados_anteriores x ON x.id=e.id;

SET @base := (SELECT IFNULL(MAX(consecutivo),0)
              FROM nom_doc_encabezados
              WHERE core_empresa_id=@CORE_EMPRESA_ID);
SET @i := 0;
SET @nuevo_encabezado_id := (SELECT COALESCE(MAX(id),0) FROM nom_doc_encabezados);

INSERT INTO nom_doc_encabezados
(id, core_tipo_transaccion_id, core_tipo_doc_app_id, consecutivo, fecha,
 core_empresa_id, descripcion, tiempo_a_liquidar, total_devengos,
 total_deducciones, presupuesto, estado, creado_por, modificado_por,
 created_at, updated_at, tipo_liquidacion)
SELECT
  (@nuevo_encabezado_id := @nuevo_encabezado_id + 1),
  @CORE_TIPO_TRANSACCION_NOMINA_ID,
  p.core_tipo_doc_app_id,
  (@base + (@i := @i + 1)),
  p.fecha_final,
  @CORE_EMPRESA_ID,
  CONCAT(CASE p.tipo_liquidacion
      WHEN 'liquidacion_contrato' THEN 'Migracion liquidacion contrato '
      WHEN 'liquidacion_semestral' THEN 'Migracion liquidacion semestral '
      WHEN 'liquidacion_vacaciones' THEN 'Migracion liquidacion vacaciones '
      WHEN 'prima_legal' THEN 'Migracion prima legal '
      WHEN 'bonificacion_nomina' THEN 'Migracion bonificaciones nomina '
      ELSE 'Migracion nomina mensual '
    END, DATE_FORMAT(p.fecha_inicial,'%Y-%m-%d'), ' a ',
         DATE_FORMAT(p.fecha_final,'%Y-%m-%d')),
  DATEDIFF(p.fecha_final,p.fecha_inicial)+1,
  0, 0, 0, 'Activo', 'Migracion', 'Migracion', NOW(), NOW(),
  p.tipo_liquidacion
FROM tmp_mig_nomina_periodos p
ORDER BY p.fecha_inicial, p.fecha_final, p.core_tipo_doc_app_id, p.tipo_nomina;

INSERT INTO tmp_mig_encabezados
(nom_doc_encabezado_id, tipo_documento_biable, tipo_nomina,
 fecha_inicial, fecha_final)
SELECT
  e.id AS nom_doc_encabezado_id,
  p.tipo_documento_biable,
  p.tipo_nomina,
  p.fecha_inicial,
  p.fecha_final
FROM nom_doc_encabezados e
JOIN tmp_mig_nomina_periodos p
  ON e.fecha=p.fecha_final
 AND e.core_tipo_doc_app_id=p.core_tipo_doc_app_id
 AND e.tipo_liquidacion=p.tipo_liquidacion
 AND e.descripcion LIKE CONCAT('%',DATE_FORMAT(p.fecha_inicial,'%Y-%m-%d'),
                               ' a ',DATE_FORMAT(p.fecha_final,'%Y-%m-%d'))
WHERE e.core_empresa_id=@CORE_EMPRESA_ID
  AND e.core_tipo_transaccion_id=@CORE_TIPO_TRANSACCION_NOMINA_ID
  AND e.creado_por='Migracion';

SET @nuevo_registro_id := (SELECT COALESCE(MAX(id),0) FROM nom_doc_registros);
INSERT INTO nom_doc_registros
(id, nom_doc_encabezado_id, core_tercero_id, nom_contrato_id, fecha,
 core_empresa_id, detalle, nom_concepto_id, nom_cuota_id, nom_prestamo_id,
 novedad_tnl_id, orden_trabajo_id, cantidad_horas, porcentaje, valor_devengo,
 valor_deduccion, estado, creado_por, modificado_por, created_at, updated_at,
 codigo_referencia_tercero)
SELECT
  (@nuevo_registro_id := @nuevo_registro_id + 1),
  e.nom_doc_encabezado_id,
  c.core_tercero_id,
  c.nom_contrato_id,
  e.fecha_final,
  @CORE_EMPRESA_ID,
  COALESCE(m.detalle,''),
  cp.nom_concepto_id,
  0, 0, NULL, 0,
  COALESCE(m.horas,0),
  0,
  IF(m.naturaleza_movimiento=1,m.valor,0),
  IF(m.naturaleza_movimiento=2,ABS(m.valor),0),
  'Activo', 'Migracion', 'Migracion', NOW(), NOW(),
  IF(CAST(m.documento AS DECIMAL(20,0)) <= 4294967295,
     CAST(m.documento AS UNSIGNED), 0)
FROM tmp_mig_nomina_movimientos m
JOIN tmp_mig_encabezados e
  ON e.fecha_inicial=m.fecha_inicial
 AND e.fecha_final=m.fecha_final
 AND e.tipo_documento_biable=m.tipo_documento_biable
 AND e.tipo_nomina=m.tipo_nomina
JOIN tmp_mig_contratos c ON c.contrato_biable=m.contrato_biable
JOIN tmp_mig_conceptos cp ON cp.concepto_biable=m.concepto_biable
ORDER BY e.nom_doc_encabezado_id, c.nom_contrato_id, cp.nom_concepto_id;

SET @orden_empleado := 0;
SET @documento_empleado := 0;
INSERT INTO nom_empleados_del_documento
(orden, nom_doc_encabezado_id, nom_contrato_id)
SELECT
  (@orden_empleado := IF(@documento_empleado=x.nom_doc_encabezado_id,
                         @orden_empleado+1,1)),
  (@documento_empleado := x.nom_doc_encabezado_id),
  x.nom_contrato_id
FROM (
  SELECT DISTINCT r.nom_doc_encabezado_id, r.nom_contrato_id
  FROM nom_doc_registros r
  JOIN tmp_mig_encabezados e ON e.nom_doc_encabezado_id=r.nom_doc_encabezado_id
  ORDER BY r.nom_doc_encabezado_id, r.nom_contrato_id
) x;

UPDATE nom_doc_encabezados e
JOIN (
  SELECT r.nom_doc_encabezado_id,
         SUM(r.valor_devengo) AS total_devengos,
         SUM(r.valor_deduccion) AS total_deducciones
  FROM nom_doc_registros r
  JOIN tmp_mig_encabezados x ON x.nom_doc_encabezado_id=r.nom_doc_encabezado_id
  GROUP BY r.nom_doc_encabezado_id
) t ON t.nom_doc_encabezado_id=e.id
SET e.total_devengos=t.total_devengos,
    e.total_deducciones=t.total_deducciones;

/* --------------------------------------------------------------------------
   Validaciones bloqueantes antes del COMMIT
   -------------------------------------------------------------------------- */
SET @actual_periodos := (SELECT COUNT(*) FROM tmp_mig_encabezados);
SET @actual_movimientos := (
  SELECT COUNT(*) FROM nom_doc_registros r
  JOIN tmp_mig_encabezados e ON e.nom_doc_encabezado_id=r.nom_doc_encabezado_id
);
SET @actual_devengos := (
  SELECT COALESCE(SUM(r.valor_devengo),0) FROM nom_doc_registros r
  JOIN tmp_mig_encabezados e ON e.nom_doc_encabezado_id=r.nom_doc_encabezado_id
);
SET @actual_deducciones := (
  SELECT COALESCE(SUM(r.valor_deduccion),0) FROM nom_doc_registros r
  JOIN tmp_mig_encabezados e ON e.nom_doc_encabezado_id=r.nom_doc_encabezado_id
);

SET @validacion := IF(@actual_periodos=@ESPERADO_PERIODOS, 'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validacion fallo: encabezados=",
         @actual_periodos, "'"));
PREPARE validar FROM @validacion; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @validacion := IF(@actual_movimientos=@ESPERADO_MOVIMIENTOS, 'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validacion fallo: movimientos=",
         @actual_movimientos, "'"));
PREPARE validar FROM @validacion; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @validacion := IF(@actual_devengos=@ESPERADO_DEVENGOS, 'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validacion fallo: devengos=",
         @actual_devengos, "'"));
PREPARE validar FROM @validacion; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @validacion := IF(@actual_deducciones=@ESPERADO_DEDUCCIONES, 'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validacion fallo: deducciones=",
         @actual_deducciones, "'"));
PREPARE validar FROM @validacion; EXECUTE validar; DEALLOCATE PREPARE validar;

COMMIT;

/* Resumen auditable de la ejecucion. */
SELECT
  DATE_FORMAT(e.fecha_final,'%Y-%m') AS mes,
  COUNT(DISTINCT e.nom_doc_encabezado_id) AS documentos,
  COUNT(r.id) AS movimientos,
  SUM(r.valor_devengo) AS devengos,
  SUM(r.valor_deduccion) AS deducciones
FROM tmp_mig_encabezados e
JOIN nom_doc_registros r ON r.nom_doc_encabezado_id=e.nom_doc_encabezado_id
GROUP BY DATE_FORMAT(e.fecha_final,'%Y-%m')
ORDER BY mes;
