/* 
Tabla `seg_usuarios_sistema` tiene campo temporal `id_user_fin` que se debe eliminar
Tabla `tb_centrocostos` tiene campo temporal `id_centro_fin` que se debe eliminar
Tabla `far_centrocosto_area` tiene campo temporal `id_x_sede` que se debe eliminar
*/
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `cronhis`.`seg_usuarios_sistema`
	(`id_user_fin`,`login`,`clave`,`id_rol`,`id_tipo_doc`,`num_documento`,`apellido1`,`apellido2`,`nombre1`,`nombre2`,`email`,`fec_creacion`,`estado`)
SELECT 
	`id_usuario`,`login`,`clave`,`id_rol`,4 AS `doc`, `documento`,`apellido1`,`apellido2`,`nombre1`,`nombre2`,`correo`,`fec_reg`,`estado`
FROM `bd_contablersc`.`seg_usuarios`
WHERE `id_usuario` <> 1 AND `bd_contablersc`.`seg_usuarios`.`documento` NOT IN (SELECT `num_documento` FROM `cronhis`.`seg_usuarios_sistema`);

UPDATE `cronhis`.`seg_usuarios_sistema`
JOIN `bd_contablersc`.`seg_usuarios` ON (`cronhis`.`seg_usuarios_sistema`.`num_documento` = `bd_contablersc`.`seg_usuarios`.`documento`)
SET `cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `bd_contablersc`.`seg_usuarios`.`id_usuario`
WHERE `cronhis`.`seg_usuarios_sistema`.`id_user_fin` IS NULL;

INSERT  INTO `seg_modulos`(`id_modulo`,`nom_modulo`,`fec_mensaje`,`fec_caduca`,`estado`) 
VALUES (50,'Almacén',NULL,NULL,1),(51,'Nómina',NULL,NULL,1),(52,'Terceros',NULL,NULL,1),(53,'Contratación',NULL,NULL,1),(54,'Presupuesto',NULL,NULL,1),(55,'Contabilidad',NULL,NULL,1),(56,'Tesorería',NULL,NULL,1),(57,'Activos fijos',NULL,NULL,1);

INSERT  INTO `seg_permisos_modulos`
	(`id_usuario`,`id_modulo`) 
SELECT 
	`seg_usuarios_sistema`.`id_usuario`,
	 CASE
        WHEN `seg_permisos_modulos`.`id_modulo` = 1 THEN 51
		WHEN `seg_permisos_modulos`.`id_modulo` = 2 THEN 52
		WHEN `seg_permisos_modulos`.`id_modulo` = 3 THEN 53
		WHEN `seg_permisos_modulos`.`id_modulo` = 4 THEN 54
		WHEN `seg_permisos_modulos`.`id_modulo` = 5 THEN 55
		WHEN `seg_permisos_modulos`.`id_modulo` = 6 THEN 56	
		WHEN `seg_permisos_modulos`.`id_modulo` = 7 THEN 50
		WHEN `seg_permisos_modulos`.`id_modulo` = 8 THEN 57
        ELSE NULL
    END AS `id_modulo`
FROM `cronhis`.`seg_usuarios_sistema`
	INNER JOIN `bd_contablersc`.`seg_permisos_modulos`
		ON (`cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `bd_contablersc`.`seg_permisos_modulos`.`id_usuario`);

INSERT INTO `cronhis`.`nom_epss`
	(`id_eps`,`id_tercero_api`,`nombre_eps`,`nit`,`digito_verific`,`telefono`,`correo`,`fec_reg`,`fec_act`)
SELECT
	`id_eps`,`id_tercero_api`,`nombre_eps`,`nit`,`digito_verific`,`telefono`,`correo`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_epss`;

INSERT INTO `cronhis`.`nom_arl`
	(`id_arl`,`id_tercero_api`,`nit_arl`,`dig_ver`,`nombre_arl`,`telefono`,`correo`,`fec_reg`,`fec_act`)
SELECT
	`id_arl`,`id_tercero_api`,`nit_arl`,`dig_ver`,`nombre_arl`,`telefono`,`correo`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_arl`;

INSERT INTO `cronhis`.`nom_afp`
	(`id_afp`,`id_tercero_api`,`nit_afp`,`dig_verf`,`nombre_afp`,`telefono`,`correo`,`fec_reg`,`fec_act`)
SELECT 
	`id_afp`,`id_tercero_api`,`nit_afp`,`dig_verf`,`nombre_afp`,`telefono`,`correo`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_afp`;

INSERT INTO `cronhis`.`nom_fondo_censan`
	(`id_fc`,`id_tercero_api`,`nit_fc`,`dig_verf`,`nombre_fc`,`telefono`,`correo`,`fec_reg`,`fec_act`)
SELECT
	`id_fc`,`id_tercero_api`,`nit_fc`,`dig_verf`,`nombre_fc`,`telefono`,`correo`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_fondo_censan`;

INSERT INTO `cronhis`.`nom_cargo_empleado`
	(`id_cargo`,`codigo`,`descripcion_carg`)
SELECT 
	`id_cargo`,NULL AS `codigo`,`descripcion_carg`
FROM `bd_contablersc`.`seg_cargo_empleado`;

ALTER TABLE `nom_estado` AUTO_INCREMENT = 1;
INSERT  INTO `nom_estado`(`id_est_emp`,`desc_est`) VALUES (0,'inactivo'),(1,'activo');

INSERT INTO `cronhis`.`tb_bancos`
	(`id_banco`,`id_tercero_api`,`nit_banco`,`dig_ver`,`cod_banco`,`nom_banco`,`estado`,`fec_reg`,`fec_act`)
SELECT
	`id_banco`,`id_tercero_api`,`nit_banco`,`dig_ver`,`cod_banco`,`nom_banco`,`estado`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_bancos`;

INSERT INTO`cronhis`.`tb_tipo_cta`
	(`id_tipo_cta`,`tipo_cta`)
SELECT
	`id_tipo_cta`,`tipo_cta`
FROM `bd_contablersc`.`seg_tipo_cta`;

INSERT INTO`cronhis`.`nom_tipo_contrato`
	(`id_tip_contrato`,`codigo`,`codigo_netc`,`descripcion`)
SELECT	
	`id_tip_contrato`,`codigo`,`codigo_netc`,`descripcion`
FROM `bd_contablersc`.`seg_tipo_contrato`;

INSERT INTO`cronhis`.`tb_tipos_documento`
	(`id_tipodoc`,`codigo`,`codigo_ne`,`descripcion`)
SELECT
	`id_tipodoc`,`codigo`,`codigo_ne`,`descripcion`
FROM `bd_contablersc`.`seg_tipos_documento`;

INSERT INTO`cronhis`.`nom_subtipo_empl`
	(`id_sub_emp`,`codigo`,`descripcion`)
SELECT
	`id_sub_emp`,`codigo`,`descripcion`
FROM `bd_contablersc`.`seg_subtipo_empl`;

INSERT INTO`cronhis`.`nom_tipo_empleado`
	(`id_tip_empl`,`codigo`,`descripcion`)
SELECT
	`id_tip_empl`,`codigo`,`descripcion`
FROM `bd_contablersc`.`seg_tipo_empleado`;

INSERT INTO `cronhis`.`nom_empleado`
	(`id_empleado`,`prefijo`,`sede_emp`,`tipo_empleado`,`subtipo_empleado`,`alto_riesgo_pension`,`tipo_contrato`,`tipo_doc`,`no_documento`,`pais_exp`,`dpto_exp`,`city_exp`,`fec_exp`,`pais_nac`,`dpto_nac`,`city_nac`,`fec_nac`,`genero`,`apellido1`,`apellido2`,`nombre2`,`nombre1`,`fech_inicio`,`fec_retiro`,`salario_integral`,`correo`,`telefono`,`cargo`,`tipo_cargo`,`sub_alimentacion`,`representacion`,`pais`,`departamento`,`municipio`,`direccion`,`id_banco`,`tipo_cta`,`cuenta_bancaria`,`estado`,`fec_reg`,`fec_actu`)
SELECT 
	`id_empleado`,`prefijo`,`sede_emp`,`tipo_empleado`,`subtipo_empleado`,`alto_riesgo_pension`,`tipo_contrato`,`tipo_doc`,`no_documento`,`pais_exp`,`dpto_exp`,`city_exp`,`fec_exp`,`pais_nac`,`dpto_nac`,`city_nac`,`fec_nac`,`genero`,`apellido1`,`apellido2`,`nombre2`,`nombre1`,`fech_inicio`,`fec_retiro`,`salario_integral`,`correo`,`telefono`,`cargo`,`tipo_cargo`,`sub_alimentacion`,`representacion`,`pais`,`departamento`,`municipio`,`direccion`,`id_banco`,`tipo_cta`,`cuenta_bancaria`,`estado`,`fec_reg`,`fec_actu`
FROM `bd_contablersc`.`seg_empleado`;

INSERT INTO `cronhis`.`nom_incremento_salario`
	(`id_inc`,`porcentaje`,`vigencia`,`fecha`,`estado`,`fec_reg`,`id_user_reg`,`fec_act`,`id_user_act`)             
SELECT
	`id_inc`,`porcentaje`,`vigencia`,`fecha`,`estado`,`fec_reg`,`id_user_reg`,`fec_act`,`id_user_act`
FROM `bd_contablersc`.`seg_incremento_salario`;

INSERT INTO `cronhis`.`nom_salarios_basico`
	(`id_salario`,`id_empleado`,`vigencia`,`salario_basico`,`fec_reg`,`fec_act`,`id_inc`)
SELECT
	`id_salario`,`id_empleado`,`vigencia`,`salario_basico`,`fec_reg`,`fec_act`,`id_inc`
FROM `bd_contablersc`.`seg_salarios_basico`;

INSERT INTO `cronhis`.`nom_novedades_eps`
	(`id_novedad`,`id_empleado`,`id_eps`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`)
SELECT 
	`id_novedad`,`id_empleado`,`id_eps`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_novedades_eps`;

INSERT INTO`cronhis`.`nom_riesgos_laboral`
	(`id_rlab`,`clase`,`riesgo`,`cotizacion`)
SELECT
	`id_rlab`,`clase`,`riesgo`,`cotizacion`
FROM `bd_contablersc`.`seg_riesgos_laboral`;

INSERT INTO `cronhis`.`nom_novedades_arl`
	(`id_novarl`,`id_empleado`,`id_arl`,`id_riesgo`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`)
SELECT
	`id_novarl`,`id_empleado`,`id_arl`,`id_riesgo`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_novedades_arl`;

INSERT INTO `cronhis`.`nom_novedades_afp`
	(`id_novafp`,`id_empleado`,`id_afp`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`)
SELECT
	`id_novafp`,`id_empleado`,`id_afp`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_novedades_afp`;

INSERT INTO `cronhis`.`nom_novedades_fc`
	(`id_novfc`,`id_empleado`,`id_fc`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`)
SELECT
	`id_novfc`,`id_empleado`,`id_fc`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_novedades_fc`;

INSERT INTO `cronhis`.`nom_libranzas`
	(`id_libranza`,`id_banco`,`id_empleado`,`estado`,`descripcion_lib`,`valor_total`,`cuotas`,`val_mes`,`porcentaje`,`fecha_inicio`,`fecha_fin`,`fec_reg`,`fec_act`)
SELECT
	`id_libranza`,`id_banco`,`id_empleado`,`estado`,`descripcion_lib`,`valor_total`,`cuotas`,`val_mes`,`porcentaje`,`fecha_inicio`,`fecha_fin`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_libranzas`;

INSERT INTO `cronhis`.`nom_juzgados`
	(`id_juzgado`,`id_tercero_api`,`nit`,`dig_verf`,`nom_juzgado`,`departamento`,`municipio`,`direcccion`,`correo`,`telefono`,`fec_reg`,`fec_act`)
SELECT 
	`id_juzgado`,`id_tercero_api`,`nit`,`dig_verf`,`nom_juzgado`,`departamento`,`municipio`,`direcccion`,`correo`,`telefono`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_juzgados`;

INSERT INTO`cronhis`.`nom_tipo_embargo`
	(`id_tipo_emb`,`tipo`,`porcentaje`)
SELECT
	`id_tipo_emb`,`tipo`,`porcentaje`
FROM `bd_contablersc`.`seg_tipo_embargo`;

INSERT INTO `cronhis`.`nom_embargos`
	(`id_embargo`,`id_juzgado`,`id_empleado`,`tipo_embargo`,`valor_total`,`dcto_max`,`valor_mes`,`porcentaje`,`fec_inicio`,`fec_fin`,`estado`,`fec_reg`,`fec_act`)
SELECT
	`id_embargo`,`id_juzgado`,`id_empleado`,`tipo_embargo`,`valor_total`,`dcto_max`,`valor_mes`,`porcentaje`,`fec_inicio`,`fec_fin`,`estado`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_embargos`;

INSERT INTO`cronhis`.`nom_tipo_incapacidad`
	(`id_tipo`,`codigo`,`tipo`)
SELECT
	`id_tipo`,`codigo`,`tipo`
FROM `bd_contablersc`.`seg_tipo_incapacidad`;

INSERT INTO `cronhis`.`nom_incapacidad`
	(`id_incapacidad`,`id_empleado`,`id_tipo`,`fec_inicio`,`fec_fin`,`can_dias`,`categoria`,`fec_reg`,`fec_act`)
SELECT 
	`id_incapacidad`,`id_empleado`,`id_tipo`,`fec_inicio`,`fec_fin`,`can_dias`,`categoria`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_incapacidad`;

INSERT INTO `cronhis`.`nom_vacaciones`
	(`id_vac`,`id_empleado`,`anticipo`,`fec_inicial`,`fec_inicio`,`fec_fin`,`dias_inactivo`,`dias_habiles`,`corte`,`dias_liquidar`,`estado`,`fec_reg`,`fec_act`)
SELECT 
	`id_vac`,`id_empleado`,`anticipo`,`fec_inicial`,`fec_inicio`,`fec_fin`,`dias_inactivo`,`dias_habiles`,`corte`,`dias_liquidar`,`estado`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_vacaciones`;

INSERT INTO`cronhis`.`nom_tipo_horaex`
	(`id_he`,`codigo`,`desc_he`,`factor`)
SELECT
	`id_he`,`codigo`,`desc_he`,`factor`
FROM `bd_contablersc`.`seg_tipo_horaex`;

INSERT INTO `cronhis`.`nom_horas_ex_trab`
	(`id_he_trab`,`id_empleado`,`id_he`,`fec_inicio`,`fec_fin`,`hora_inicio`,`hora_fin`,`cantidad_he`,`tipo`,`fec_reg`,`fec_actu`)
SELECT
	`id_he_trab`,`id_empleado`,`id_he`,`fec_inicio`,`fec_fin`,`hora_inicio`,`hora_fin`,`cantidad_he`,`tipo`,`fec_reg`,`fec_actu`
FROM `bd_contablersc`.`seg_horas_ex_trab`;

INSERT INTO `cronhis`.`nom_retroactivos`
	(`id_retroactivo`,`fec_inicio`,`fec_final`,`meses`,`id_incremento`,`observaciones`,`estado`,`vigencia`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT 
	`id_retroactivo`,`fec_inicio`,`fec_final`,`meses`,`id_incremento`,`observaciones`,`estado`,`vigencia`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`
FROM `bd_contablersc`.`seg_retroactivos`;

INSERT INTO `cronhis`.`nom_nominas`
	(`id_nomina`,`descripcion`,`mes`,`vigencia`,`tipo`,`estado`,`planilla`,`id_incremento`,`fec_reg`,`id_user_reg`,`fec_act`)
SELECT
	`id_nomina`,`descripcion`,`mes`,`vigencia`,`tipo`,`estado`,`planilla`,`id_incremento`,`fec_reg`,`id_user_reg`,`fec_act`
FROM `bd_contablersc`.`seg_nominas`
WHERE `id_nomina` > 0;

INSERT INTO `cronhis`.`nom_nominas`
	(`descripcion`,`mes`,`vigencia`,`tipo`,`estado`,`planilla`,`id_incremento`,`fec_reg`,`id_user_reg`,`fec_act`)
VALUES('INICIAL',NULL,NULL,'N',5,5,NULL,NULL,25,NULL);

UPDATE `cronhis`.`nom_nominas` SET `id_nomina` = 0 WHERE `descripcion` = 'INICIAL';

ALTER TABLE `nom_nominas` AUTO_INCREMENT = 1;

INSERT INTO `cronhis`.`nom_liq_bsp`
	(`id_bonificaciones`,`id_empleado`,`val_bsp`,`id_user_reg`,`mes`,`anio`,`fec_reg`,`id_nomina`)
SELECT 
	`id_bonificaciones`,`id_empleado`,`val_bsp`,`id_user_reg`,`mes`,`anio`,`fec_reg`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_bsp`;

INSERT INTO `cronhis`.`nom_liq_cesantias`
	(`id_liq_cesan`,`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`corte`,`anio`,`salbase`,`gasrep`,`auxt`,`auxali`,`promHorExt`,`bspant`,`primserant`,`primavacant`,`primanavant`,`diasToCes`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
	`id_liq_cesan`,`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`corte`,`anio`,`salbase`,`gasrep`,`auxt`,`auxali`,`promHorExt`,`bspant`,`primserant`,`primavacant`,`primanavant`,`diasToCes`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_cesantias`;

INSERT INTO  `cronhis`.`nom_liq_compesatorio`
    (`id_compensa`,`id_empleado`,`val_compensa`,`dias`,`estado`,`fec_reg`,`id_nomina`)
SELECT
    `id_compensa`,`id_empleado`,`val_compensa`,`dias`,`estado`,`fec_reg`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_compesatorio`;

INSERT INTO  `cronhis`.`nom_liq_dias_lab`
    (`id_diatrab`,`id_empleado`,`id_contrato`,`cant_dias`,`mes`,`anio`,`liq_vac`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_diatrab`,`id_empleado`,`id_contrato`,`cant_dias`,`mes`,`anio`,`liq_vac`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_dias_lab`;

INSERT INTO  `cronhis`.`nom_liq_dlab_auxt`
    (`id_liq_dlab_auxt`,`id_empleado`,`dias_liq`,`val_liq_dias`,`val_liq_auxt`,`aux_alim`,`g_representa`,`horas_ext`,`mes_liq`,`anio_liq`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_dlab_auxt`,`id_empleado`,`dias_liq`,`val_liq_dias`,`val_liq_auxt`,`aux_alim`,`g_representa`,`horas_ext`,`mes_liq`,`anio_liq`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_dlab_auxt`;

INSERT INTO  `cronhis`.`nom_liq_embargo`
    (`id_liq_embargo`,`id_embargo`,`val_mes_embargo`,`mes_embargo`,`anio_embargo`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_embargo`,`id_embargo`,`val_mes_embargo`,`mes_embargo`,`anio_embargo`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_embargo`;

INSERT INTO  `cronhis`.`nom_liq_empleado`
    (`id_liq`,`id_empleado`,`corte`,`no_resolucion`,`fec_inicio`,`fec_fin`,`sal_base`,`vigencia`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
    `id_liq`,`id_empleado`,`corte`,`no_resolucion`,`fec_inicio`,`fec_fin`,`sal_base`,`vigencia`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`
FROM `bd_contablersc`.`seg_liq_empleado`;

INSERT INTO  `cronhis`.`nom_liq_horex`
    (`id_liq_he`,`id_he_lab`,`val_liq`,`mes_he`,`anio_he`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_he`,`id_he_lab`,`val_liq`,`mes_he`,`anio_he`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_horex`;

INSERT INTO  `cronhis`.`nom_liq_incap`
    (`id_liq_incap`,`id_incapacidad`,`id_eps`,`id_arl`,`fec_inicio`,`fec_fin`,`dias_liq`,`pago_empresa`,`pago_eps`,`pago_arl`,`mes`,`anios`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_incap`,`id_incapacidad`,`id_eps`,`id_arl`,`fec_inicio`,`fec_fin`,`dias_liq`,`pago_empresa`,`pago_eps`,`pago_arl`,`mes`,`anios`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_incap`;

INSERT INTO  `cronhis`.`nom_liq_libranza`
    (`id_lid_lib`,`id_libranza`,`val_mes_lib`,`mes_lib`,`anio_lib`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_lid_lib`,`id_libranza`,`val_mes_lib`,`mes_lib`,`anio_lib`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_libranza`;

INSERT INTO  `cronhis`.`nom_liq_parafiscales`
    (`id_liq_pfis`,`id_empleado`,`val_sena`,`val_icbf`,`val_comfam`,`mes_pfis`,`anio_pfis`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_pfis`,`id_empleado`,`val_sena`,`val_icbf`,`val_comfam`,`mes_pfis`,`anio_pfis`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_parafiscales`;

INSERT INTO  `cronhis`.`nom_liq_prestaciones_sociales`
    (`id_liqpresoc`,`id_empleado`,`id_contrato`,`val_vacacion`,`val_cesantia`,`val_interes_cesantia`,`val_prima`,`val_prima_vac`,`val_prima_nav`,`val_bonifica_recrea`,`mes_prestaciones`,`anio_prestaciones`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liqpresoc`,`id_empleado`,`id_contrato`,`val_vacacion`,`val_cesantia`,`val_interes_cesantia`,`val_prima`,`val_prima_vac`,`val_prima_nav`,`val_bonifica_recrea`,`mes_prestaciones`,`anio_prestaciones`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_prestaciones_sociales`;

INSERT INTO  `cronhis`.`nom_liq_prima`
    (`id_liq_prima`,`id_empleado`,`cant_dias`,`val_liq_ps`,`val_liq_pns`,`periodo`,`corte`,`anio`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_prima`,`id_empleado`,`cant_dias`,`val_liq_ps`,`val_liq_pns`,`periodo`,`corte`,`anio`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_prima`;

INSERT INTO  `cronhis`.`nom_liq_prima_nav`
    (`id_liq_privac`,`id_empleado`,`cant_dias`,`val_liq_pv`,`val_liq_pnv`,`periodo`,`corte`,`anio`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_privac`,`id_empleado`,`cant_dias`,`val_liq_pv`,`val_liq_pnv`,`periodo`,`corte`,`anio`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_prima_nav`;

INSERT INTO  `cronhis`.`nom_liq_salario`
    (`id_sal_liq`,`id_empleado`,`forma_pago`,`metodo_pago`,`val_liq`,`mes`,`anio`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_sal_liq`,`id_empleado`,`forma_pago`,`metodo_pago`,`val_liq`,`mes`,`anio`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_salario`;

INSERT INTO`cronhis`.`nom_metodo_pago`
	(`id_metodo_pago`,`codigo`,`metodo`)
SELECT
	`id_metodo_pago`,`codigo`,`metodo`
FROM `bd_contablersc`.`seg_metodo_pago`;

INSERT INTO  `cronhis`.`nom_liq_segsocial_empdo`
    (`id_liq_empdo`,`id_empleado`,`id_eps`,`id_arl`,`id_afp`,`aporte_salud_emp`,`aporte_pension_emp`,`aporte_solidaridad_pensional`,`porcentaje_ps`,`aporte_salud_empresa`,`aporte_pension_empresa`,`aporte_rieslab`,`mes`,`anio`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_empdo`,`id_empleado`,`id_eps`,`id_arl`,`id_afp`,`aporte_salud_emp`,`aporte_pension_emp`,`aporte_solidaridad_pensional`,`porcentaje_ps`,`aporte_salud_empresa`,`aporte_pension_empresa`,`aporte_rieslab`,`mes`,`anio`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_segsocial_empdo`;

INSERT INTO  `cronhis`.`nom_liq_vac`
    (`id_liq_vac`,`id_vac`,`id_contrato`,`fec_inicio`,`fec_fin`,`dias_liqs`,`val_liq`,`val_diavac`,`val_bsp`,`val_prima_vac`,`val_bon_recrea`,`mes_vac`,`anio_vac`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_vac`,`id_vac`,`id_contrato`,`fec_inicio`,`fec_fin`,`dias_liqs`,`val_liq`,`val_diavac`,`val_bsp`,`val_prima_vac`,`val_bon_recrea`,`mes_vac`,`anio_vac`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `bd_contablersc`.`seg_liq_vac`;

INSERT INTO`cronhis`.`nom_conceptosxvigencia`
	(`id_concp`,`concepto`)
SELECT
	`id_concp`,`concepto`
FROM `bd_contablersc`.`seg_conceptosxvigencia`;

INSERT INTO`cronhis`.`tb_vigencias`
	(`id_vigencia`,`anio`,`registros`,`ven_fecha`,`estado`,`id_empresa`)
SELECT
	`id_vigencia`,`anio`,`registros`,`ven_fecha`,`estado`,1 AS `id_empresa`
FROM `bd_contablersc`.`con_vigencias`;

INSERT INTO `cronhis`.`nom_valxvigencia`
	(`id_valxvig`,`id_vigencia`,`id_concepto`,`valor`,`fec_reg`,`fec_act`)
SELECT
	`id_valxvig`,`id_vigencia`,`id_concepto`,`valor`,`fec_reg`,`fec_act`
FROM `bd_contablersc`.`seg_valxvigencia`;

INSERT INTO `cronhis`.`nom_soporte_ne`
	(`id_soporte`,`id_empleado`,`shash`,`referencia`,`mes`,`anio`,`id_user_reg`,`fec_reg`)
SELECT
	`id_soporte`,`id_empleado`,`shash`,`referencia`,`mes`,`anio`,`id_user_reg`,`fec_reg`
FROM `bd_contablersc`.`seg_soporte_ne`;

INSERT INTO`cronhis`.`tb_terceros`
	(`nom_tercero`,`nit_tercero`,`dir_tercero`,`tel_tercero`,`id_municipio`,`email`,`id_tercero_api`,`estado`,`tipo_doc`,`fec_inicio`,`genero`)
SELECT
    TRIM(
        REPLACE(
            REPLACE(
                CONCAT_WS(
                    ' ',
                    TRIM(`terceros`.`seg_terceros`.`nombre1`),
                    TRIM(`terceros`.`seg_terceros`.`nombre2`),
                    TRIM(`terceros`.`seg_terceros`.`apellido1`),
                    TRIM(`terceros`.`seg_terceros`.`apellido2`),
                    TRIM(`terceros`.`seg_terceros`.`razon_social`)
                ),
                '.',
                ''
            ),
            '-',
            ''
        )
    ) AS `nombre`,
    `terceros`.`seg_terceros`.`cc_nit`,
    `terceros`.`seg_terceros`.`direccion`,
    `terceros`.`seg_terceros`.`telefono`,
    `terceros`.`seg_terceros`.`municipio`,
    `terceros`.`seg_terceros`.`correo`,
    `terceros`.`seg_terceros`.`id_tercero`,
    `bd_contablersc`.`seg_terceros`.`estado`,
    `bd_contablersc`.`seg_terceros`.`tipo_doc`,
    `bd_contablersc`.`seg_terceros`.`fec_inicio`,
    `terceros`.`seg_terceros`.`genero`
FROM
    `bd_contablersc`.`seg_terceros`
    INNER JOIN `terceros`.`seg_terceros` 
        ON (`bd_contablersc`.`seg_terceros`.`id_tercero_api` = `terceros`.`seg_terceros`.`id_tercero`)
WHERE `bd_contablersc`.`seg_terceros`.`no_doc` NOT IN (SELECT `nit_tercero` FROM`cronhis`.`tb_terceros` WHERE nit_tercero NOT LIKE '%-%');

INSERT INTO `cronhis`.`tb_tipo_tercero`
            (`id_tipo`,`descripcion`)
SELECT 
	`id_tipo`,`descripcion`
FROM `bd_contablersc`.`seg_tipo_tercero`;

INSERT INTO `cronhis`.`tb_rel_tercero`
	(`id_relacion`,`id_tercero_api`,`id_tipo_tercero`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_relacion`,`id_tercero_api`,`id_tipo_tercero`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`
FROM `bd_contablersc`.`rel_tipo_tercero`;

INSERT INTO `cronhis`.`ctt_modalidad`
	(`id_modalidad`,`modalidad`,`id_user_reg`,`fec_reg`)
SELECT 
	`id_modalidad`,`modalidad`,`id_user_reg`,`fec_reg`
FROM `bd_contablersc`.`seg_modalidad_contrata`;

INSERT INTO`cronhis`.`pto_tipo`
	(`id_tipo`,`nombre`)
SELECT
	`id_pto_tipo`,`nombre`
FROM `bd_contablersc`.`seg_pto_tipo`;

INSERT INTO `cronhis`.`pto_presupuestos`
	(`id_pto`,`id_tipo`,`id_vigencia`,`nombre`,`descripcion`,`estado`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT 
	`id_pto_presupuestos`,`id_pto_tipo`,
	CASE
		WHEN `vigencia` = 2023 THEN 7
		WHEN `vigencia` = 2024 THEN 8
	END,`nombre`,`descripcion`,0 AS `est`,`id_user_reg`,`fec_reg`,`id_usuer_act`,`fec_act`
FROM `bd_contablersc`.`seg_pto_presupuestos`;

INSERT INTO`cronhis`.`pto_tipo_recurso`
	(`id_pto_tipo`,`nombre_tipo`)
SELECT
	`id_pto_tiporecurso`,`nombre_tipo`
FROM `bd_contablersc`.`seg_pto_tiporecursos`;

INSERT INTO`cronhis`.`pto_tipo_rubro`(`id_trubro`,`nombre`,`id_pto`) 
	VALUES (1,'Disponibilidad inicial',1),(2,'Ingresos corrientes',1),(3,'Recursos de capital',1),(4,'Funcionamiento',2),(5,'Operación',2),(6,'Inversión',2),(7,'Servico de la Deuda',2);

INSERT INTO `cronhis`.`pto_cargue`
	(`id_cargue`,`id_pto`,`id_tipo_recurso`,`cod_pptal`,`nom_rubro`,`tipo_dato`,`valor_aprobado`,`tipo_pto`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_pto_cargue`,`id_pto_presupuestos`,1 AS `recurso`,`cod_pptal`,`nom_rubro`,`tipo_dato`,`ppto_aprob`,`id_tipo_recurso`,`id_user_reg`,`fec_reg`,`id_usuer_act`,`fec_act`
FROM `bd_contablersc`.`seg_pto_cargue`;

INSERT INTO`cronhis`.`pto_actos_admin`
        (`id_acto`,`nombre`)
SELECT
	`id_pto_actos`,`acto`
FROM `bd_contablersc`.`seg_pto_actos_admin`;

INSERT INTO`cronhis`.`pto_tipo_mvto`
	(`id_tmvto`,`codigo`,`nombre`,`filtro`) 
VALUES (1,'TRA','TRASLADO INGRESO','1'),(2,'ADI','ADICION','0'),(3,'RED','REDUCCION','0'),(4,'APL','APLAZAMIENTO ','2'),(5,'DPL','DESAPLAZAMIENTO','2'),(6,'TRA','TRASLADO GASTO','2');

INSERT INTO `cronhis`.`pto_mod` 
    (`id_pto_mod`, `id_pto`, `id_tipo_mod`, `id_tipo_acto`, `numero_acto`, `fecha`, `id_manu`, `objeto`, `estado`, `id_user_reg`, `fecha_reg`, `id_user_act`, `fecha_act`)
SELECT
    `id_pto_doc`,
    `id_pto_presupuestos`,
    CASE
        WHEN `tipo_doc` = 'TRA' THEN 6
        WHEN `tipo_doc` = 'ADI' THEN 2
        WHEN `tipo_doc` = 'RED' THEN 3
        ELSE NULL
    END AS `id_tipo_mod`,
    `tipo_mod`,
    `id_manu`,
    `fecha`,
    `id_manu`,
    `objeto`,
    CASE
        WHEN `seg_pto_documento`.`estado` = 0 THEN 2
        WHEN `seg_pto_documento`.`estado` = 5 THEN 0
        ELSE 1
    END AS `estado`,
    `cronhis`.`seg_usuarios_sistema`.`id_usuario`,
    `fec_reg`,
    `id_user_act`,
    `fec_act`
FROM `bd_contablersc`.`seg_pto_documento`
LEFT JOIN `cronhis`.`seg_usuarios_sistema`
    ON (`cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `bd_contablersc`.`seg_pto_documento`.`id_user_reg`)
WHERE `tipo_doc` = 'ADI' OR `tipo_doc` = 'RED' OR `tipo_doc` = 'TRA';

INSERT INTO `cronhis`.`pto_mod_detalle`
	(`id_pto_mod`,`id_cargue`,`valor_deb`,`valor_cred`)
SELECT
	`cronhis`.`pto_mod`.`id_pto_mod`
	, `cronhis`.`pto_cargue`.`id_cargue`
	, CASE
		WHEN `bd_contablersc`.`seg_pto_mvto`.`mov` = 0 THEN `bd_contablersc`.`seg_pto_mvto`.`valor`
		ELSE 0
	  END AS `debito`
	, CASE
		WHEN `bd_contablersc`.`seg_pto_mvto`.`mov` = 1 THEN `bd_contablersc`.`seg_pto_mvto`.`valor`
		ELSE 0
	  END AS `credito`
FROM `cronhis`.`pto_mod`
INNER JOIN  `bd_contablersc`.`seg_pto_mvto`
	ON(`cronhis`.`pto_mod`.`id_pto_mod` = `bd_contablersc`.`seg_pto_mvto`.`id_pto_doc`)
INNER JOIN `cronhis`.`pto_cargue`
	ON(`cronhis`.`pto_cargue`.`id_pto` = `cronhis`.`pto_mod`.`id_pto`)
WHERE `bd_contablersc`.`seg_pto_mvto`.`rubro` = `cronhis`.`pto_cargue`.`cod_pptal`;

INSERT INTO `cronhis`.`pto_cdp`
	(`id_pto_cdp`,`id_pto`,`fecha`,`id_manu`,`objeto`,`num_solicitud`,`estado`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`)
SELECT
	`id_pto_doc`,`id_pto_presupuestos`,`fecha`,`id_manu`,`objeto`,`num_solicitud`,
	CASE
		WHEN `seg_pto_documento`.`estado` = 0 THEN 2
		WHEN `seg_pto_documento`.`estado` = 5 THEN 0
		ELSE 1
	END AS `estado`,`cronhis`.`seg_usuarios_sistema`.`id_usuario`, `fec_reg`,`id_user_reg`,`fec_act`
FROM `bd_contablersc`.`seg_pto_documento`
LEFT JOIN `cronhis`.`seg_usuarios_sistema`
    ON (`cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `bd_contablersc`.`seg_pto_documento`.`id_user_reg`)
WHERE `tipo_doc` = 'CDP' OR `tipo_doc` = 'LDP';

INSERT INTO `cronhis`.`pto_cdp_detalle`
	(`id_pto_cdp`,`id_rubro`,`valor`,`valor_liberado`)
	SELECT 
		`id_pto_cdp`
		, `id_cargue`
		, SUM(`valor`) AS `valor`
		, SUM(`liberado`) AS `liberado`
	FROM 
		(SELECT
			`cronhis`.`pto_cdp`.`id_pto_cdp`
			, `cronhis`.`pto_cargue`.`id_cargue`
			, CASE
				WHEN `bd_contablersc`.`seg_pto_mvto`.`tipo_mov` = 'CDP' THEN `bd_contablersc`.`seg_pto_mvto`.`valor`
				ELSE 0
			END AS `valor`
			, CASE
				WHEN `bd_contablersc`.`seg_pto_mvto`.`tipo_mov` = 'LCD' THEN `bd_contablersc`.`seg_pto_mvto`.`valor` *(-1)
				ELSE 0
			END AS `liberado`
		FROM `cronhis`.`pto_cdp`
		INNER JOIN  `bd_contablersc`.`seg_pto_mvto`
			ON(`cronhis`.`pto_cdp`.`id_pto_cdp` = `bd_contablersc`.`seg_pto_mvto`.`id_pto_doc`)
		INNER JOIN `cronhis`.`pto_cargue`
			ON(`cronhis`.`pto_cargue`.`id_pto` = `cronhis`.`pto_cdp`.`id_pto`)
	WHERE `bd_contablersc`.`seg_pto_mvto`.`rubro` = `cronhis`.`pto_cargue`.`cod_pptal`) AS `taux`
GROUP BY `taux`.`id_pto_cdp`, `taux`.`id_cargue`;

INSERT INTO `cronhis`.`pto_crp`
	(`id_pto_crp`,`id_pto`,`id_cdp`,`fecha`,`id_manu`,`id_tercero_api`,`objeto`,`num_contrato`,`estado`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`)
SELECT
	`id_pto_doc`,`id_pto_presupuestos`,
	CASE
		WHEN `seg_pto_documento`.`id_auto` = 0 THEN NULL
		ELSE `seg_pto_documento`.`id_auto`
	END AS `id_auto`,`fecha`,`id_manu`,`id_tercero`,`objeto`,`num_contrato`,
	CASE
		WHEN `seg_pto_documento`.`estado` = 0 THEN 2
		WHEN `seg_pto_documento`.`estado` = 5 THEN 0
		ELSE 1
	END AS `estado`,`cronhis`.`seg_usuarios_sistema`.`id_usuario`, `fec_reg`,`cronhis`.`seg_usuarios_sistema`.`id_usuario`,`fec_act`
FROM `bd_contablersc`.`seg_pto_documento`
LEFT JOIN `cronhis`.`seg_usuarios_sistema`
    ON (`cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `bd_contablersc`.`seg_pto_documento`.`id_user_reg`)
WHERE `tipo_doc` = 'CRP' OR `tipo_doc` = 'LRP';

INSERT INTO `cronhis`.`pto_crp_detalle`
         (`id_pto_crp`,`id_pto_cdp_det`,`id_tercero_api`,`valor`,`valor_liberado`)
SELECT
	`tb`.`id_pto_doc`
	, `tb`.`id_pto_cdp_det`
	, `tb`.`id_tercero_api`
	, SUM(`tb`.`valor`) AS `valor` 
	, SUM(`tb`.`liberado`) AS `liberado`
FROM 
	(SELECT
		`t1`.`id_pto_doc`
		, `t3`.`id_pto_cdp_det`
		, `t1`.`id_tercero_api`
		, CASE
			WHEN `t1`.`tipo_mov` = 'CRP' THEN `t1`.`valor`
			ELSE 0
			END AS `valor`
		, CASE
			WHEN `t1`.`tipo_mov` = 'LRP' THEN `t1`.`valor` *(-1)
			ELSE 0
			END AS `liberado`
	FROM 	
		(SELECT 
			`seg_pto_mvto`.`id_pto_doc`
			, `seg_pto_documento`.`id_pto_presupuestos`
			, `seg_pto_mvto`.`tipo_mov`
			, `seg_pto_mvto`.`id_tercero_api`
			, `seg_pto_mvto`.`rubro`
			, `seg_pto_mvto`.`valor`
			, `seg_pto_mvto`.`id_auto_dep`
		FROM `bd_contablersc`.`seg_pto_mvto` 
		INNER JOIN `bd_contablersc`.`seg_pto_documento`
			ON(`seg_pto_documento`.`id_pto_doc` = `seg_pto_mvto`.`id_pto_doc`)
			
		WHERE `bd_contablersc`.`seg_pto_mvto`.`tipo_mov` = 'CRP' OR `bd_contablersc`.`seg_pto_mvto`.`tipo_mov` = 'LRP') AS `t1`
	LEFT JOIN
		(SELECT 
			`pto_cargue`.`id_cargue`
			, `pto_cargue`.`id_pto`
			, `pto_cargue`.`cod_pptal`
		FROM `cronhis`.`pto_cargue`) AS `t2` 
		ON(`t1`.`id_pto_presupuestos` = `t2`.`id_pto` AND `t1`.`rubro` = `t2`.`cod_pptal`)
	LEFT JOIN 
		(SELECT
			`pto_cdp_detalle`.`id_pto_cdp_det`
			, `pto_cdp_detalle`.`id_pto_cdp`
			,`pto_cdp_detalle`.`id_rubro`
		FROM `cronhis`.`pto_cdp_detalle`) AS `t3`
		ON(`t1`.`id_auto_dep` = `t3`.`id_pto_cdp` AND `t2`.`id_cargue` = `t3`.`id_rubro`)) AS `tb`
GROUP BY `id_pto_doc`,`id_pto_cdp_det`,`id_tercero_api`;

INSERT  INTO`cronhis`.`tes_referencia`
	(`id_referencia`,`numero`,`estado`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`) 
VALUES (1,1,0,1,'2024-05-09 14:20:10',NULL,NULL);

INSERT  INTO`cronhis`.`ctb_fuente`
	(`id_doc_fuente`,`cod`,`nombre`,`contab`,`tesor`,`estado`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`) 
VALUES (1,'NCON','NOTA DE CONTABILIDAD',1,0,0,NULL,NULL,NULL,NULL),(2,'GRUR','GESTION RECURSOS DE USO RESTRINGIDOS',1,0,1,NULL,NULL,NULL,NULL),(3,'NCXP','CUENTAS POR PAGAR',1,0,1,NULL,NULL,NULL,NULL),(4,'CEVA','COMPROBANTE DE EGRESO',0,1,1,NULL,NULL,NULL,NULL),(5,'CNOM','CAUSACION DE NOMINA',1,0,1,NULL,NULL,NULL,NULL),(6,'CING','COMPROBANTE DE INGRESO',0,2,1,NULL,NULL,NULL,NULL),(7,'CNCR','NOTA CREDITO',0,2,1,NULL,NULL,NULL,NULL),(8,'CNDB','NOTA DEBITO',0,1,1,NULL,NULL,NULL,NULL),(9,'CICP','RECIBO DE CAJA',0,2,1,NULL,NULL,NULL,NULL),(10,'CTTR','TRASLADO BANCARIO',0,3,1,NULL,NULL,NULL,NULL),(11,'CTCB','CONSIGNACION BANCARIA',0,2,1,NULL,NULL,NULL,NULL),(12,'CIVA','COMPROBANTE DE INGRESO VIGENCIA ANTERIOR',0,2,1,NULL,NULL,NULL,NULL),(13,'CMCN','CONSTITUCION CAJA MENOR',0,4,1,NULL,NULL,NULL,NULL),(14,'CMMT','MOVIMIENTOS DE CAJA MENOR',0,4,1,NULL,NULL,NULL,NULL),(15,'CMLG','LEGALIZACION DE CAJA MENOR',0,4,1,NULL,NULL,NULL,NULL),(16,'CTDI','DISPONIBILIDAD INICIAL',0,2,1,NULL,NULL,NULL,NULL),(17,'NCTC','TRASLADO DE COSTOS',1,0,0,NULL,NULL,NULL,NULL),(18,'NCAC','AJUSTES CONTABLES',1,0,0,NULL,NULL,NULL,NULL),(19,'NCNB','NOTAS BANCARIAS',1,0,0,NULL,NULL,NULL,NULL),(20,'NCTA','COSTOS DE ALMACEN',1,0,0,NULL,NULL,NULL,NULL),(21,'CDP','CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL',0,0,0,NULL,NULL,NULL,NULL),(22,'CRP','CERTIFICADO DE REGISTRO PRESUPUESTAL',0,0,0,NULL,NULL,NULL,NULL),(23,'MOD','MODIFICACIONES',0,0,0,NULL,NULL,NULL,NULL);

INSERT INTO `cronhis`.`ctb_pgcp`
	(`id_pgcp`,`fecha`,`cuenta`,`nombre`,`tipo_dato`,`estado`,`id_user_reg`,`fec_reg`,`id_usuer_act`,`fec_act`)
SELECT
	`id_pgcp`,`fecha`,`cuenta`,`nombre`,`tipo_dato`
	, CASE
		WHEN `seg_ctb_pgcp`.`estado` =  0 THEN 1
		ELSE 0
	END AS `estado`
	,`cronhis`.`seg_usuarios_sistema`.`id_usuario`,`fec_reg`,`cronhis`.`seg_usuarios_sistema`.`id_usuario`,`fec_act`
FROM `bd_contablersc`.`seg_ctb_pgcp`
LEFT JOIN `cronhis`.`seg_usuarios_sistema`
	ON(`bd_contablersc`.`seg_ctb_pgcp`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`);

INSERT  INTO`cronhis`.`ctb_referencia`
	(`id_ctb_referencia`,`id_ctb_fuente`,`id_cuenta`,`nombre`,`accion`,`estado`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`) 
VALUES (1,7,1585,'RENDIMIENTOS FINANCIEROS',1,1,NULL,NULL,NULL,NULL),(2,8,1798,'COMISIONES BANCARIAS',0,1,NULL,NULL,NULL,NULL),(3,8,1760,'GRAVAMENES MOVIMIENTOS FINANCIEROS',0,1,NULL,NULL,NULL,NULL),(4,8,2925,'RETENCION EN LA FUENTE BANCARIA',0,1,NULL,NULL,NULL,NULL),(5,8,2900,'IVA BANCARIO',0,1,NULL,NULL,NULL,NULL),(6,6,188,'RECAUDO FACTURACION IDENTIFICADA',1,1,NULL,NULL,NULL,NULL),(7,6,589,'RECAUDO TERCERO SIN IDENTIFICAR',1,1,NULL,NULL,NULL,NULL),(8,11,5,'CONSIGNACION CAJA',1,1,NULL,NULL,NULL,NULL),(9,9,189,'ARQUEO DE CAJA',1,1,NULL,NULL,NULL,NULL),(10,7,1629,'AJUSTE AL PESO CREDITO',1,1,NULL,NULL,NULL,NULL),(11,8,1807,'AJUSTE AL PESO DEBITO',0,1,NULL,NULL,NULL,NULL),(12,15,7,'COMPROBANTE LEGALIZACION CAJA MENOR',2,1,NULL,NULL,NULL,NULL),(13,7,2922,'RENDIMIENTOS FINANCIEROS PIC',1,1,NULL,NULL,NULL,NULL);

INSERT INTO `cronhis`.`ctb_doc`
	(`id_ctb_doc`,`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_ref`,`id_ref_ctb`,`id_crp`,`id_tercero`,`fecha`,`detalle`,`id_nomina`,`estado`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`)
SELECT
	`id_ctb_doc`
	, `tt`.`vigencia`
	, `tt`.`tipo_doc`
	, `tt`.`id_manu`
	, 1 AS `idrefctb`
	, `tt`.`id_ref`
	, `tt`.`crp`
	, `tt`.`id_tercero`
	, `tt`.`fecha`
	, `tt`.`detalle`
	, `tt`.`id_nomina`
	, `tt`.`estado`
	, `tt`.`id_usuario`
	, `tt`.`fec_reg`
	, `tt`.`user_act`
	, `tt`.`fec_act`
FROM (SELECT
	`seg_ctb_doc`.`id_ctb_doc`
	, CASE
		WHEN `vigencia` = 2023 THEN 7
		WHEN `vigencia` = 2024 THEN 8
		WHEN `vigencia` = NULL THEN 7
	END AS `vigencia`
	, CASE
		WHEN `seg_ctb_doc`.`tipo_doc` = 'NCON' THEN 1
		WHEN `seg_ctb_doc`.`tipo_doc` = 'GRUR' THEN 2
		WHEN `seg_ctb_doc`.`tipo_doc` = 'NCXP' THEN 3
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CEVA' THEN 4
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CNOM' THEN 5
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CING' THEN 6
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CNCR' THEN 7
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CNDB' THEN 8
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CICP' THEN 9
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CTTR' THEN 10
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CTCB' THEN 11
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CIVA' THEN 12
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CMCN' THEN 13
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CMMT' THEN 14
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CMLG' THEN 15
		WHEN `seg_ctb_doc`.`tipo_doc` = 'CTDI' THEN 16
		WHEN `seg_ctb_doc`.`tipo_doc` = 'NCTC' THEN 17
		WHEN `seg_ctb_doc`.`tipo_doc` = 'NCAC' THEN 18
		WHEN `seg_ctb_doc`.`tipo_doc` = 'NCNB' THEN 19
		WHEN `seg_ctb_doc`.`tipo_doc` = 'NCTA' THEN 20
	END AS `tipo_doc`,`id_manu`,1 AS `idrefctb`
	, CASE
		WHEN `seg_ctb_doc`.`id_ref` = 0 THEN NULL
		ELSE `seg_ctb_doc`.`id_ref`
	END AS `id_ref`
	, CASE
		WHEN `taux`.`id_crp` = 0 THEN NULL
		WHEN `taux`.`id_crp` = 1904 THEN NULL
		WHEN `taux`.`id_crp` = 4786 THEN NULL
		WHEN `taux`.`id_crp` = 4787 THEN NULL
		WHEN `taux`.`id_crp` = 4788 THEN NULL
		WHEN `taux`.`id_crp` = 4789 THEN NULL
		WHEN `taux`.`id_crp` = 4790 THEN NULL
		WHEN `taux`.`id_crp` = 4792 THEN NULL
		WHEN `taux`.`id_crp` = 4797 THEN NULL
		WHEN `taux`.`id_crp` = 4798 THEN NULL
		WHEN `taux`.`id_crp` = 4799 THEN NULL
		WHEN `taux`.`id_crp` = 4800 THEN NULL
		WHEN `taux`.`id_crp` = 4801 THEN NULL		
		ELSE `taux`.`id_crp`
	END AS `crp`,`id_tercero`,`fecha`,`detalle`,`id_nomina`
	, CASE
		WHEN `seg_ctb_doc`.`estado` = 0 THEN 2
		WHEN `seg_ctb_doc`.`estado` = 5 THEN 0
		ELSE 1
	END AS `estado`,`cronhis`.`seg_usuarios_sistema`.`id_usuario`,`fec_reg`,`cronhis`.`seg_usuarios_sistema`.`id_usuario` AS `user_act`,`fec_act`
FROM `bd_contablersc`.`seg_ctb_doc`
LEFT JOIN `cronhis`.`seg_usuarios_sistema`
	ON(`bd_contablersc`.`seg_ctb_doc`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
LEFT JOIN 
	(SELECT 
		`id_ctb_doc`,`id_crp` 
	FROM 
		(SELECT `id_ctb_doc`,`id_crp` 
		FROM `bd_contablersc`.`seg_ctb_libaux`
		ORDER BY `id_crp` DESC) AS `t1`
	GROUP BY `id_ctb_doc`) AS `taux`
	ON(`seg_ctb_doc`.`id_ctb_doc` = `taux`.`id_ctb_doc`)) AS `tt`;

UPDATE `cronhis`.`ctb_doc` SET `id_tercero` = NULL WHERE `id_tercero` = 0;

INSERT INTO `cronhis`.`ctb_libaux`
	(`id_ctb_libaux`,`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`)
SELECT
	`id_ctb_libaux`,`id_ctb_doc`,`id_tercero`,`cronhis`.`ctb_pgcp`.`id_pgcp`,`debito`,`credito`,`cronhis`.`seg_usuarios_sistema`.`id_usuario`,`seg_ctb_libaux`.`fec_reg`,`cronhis`.`seg_usuarios_sistema`.`id_usuario`,`seg_ctb_libaux`.`fec_act`
FROM `bd_contablersc`.`seg_ctb_libaux`
LEFT JOIN `cronhis`.`seg_usuarios_sistema`
	ON(`bd_contablersc`.`seg_ctb_libaux`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
LEFT JOIN `cronhis`.`ctb_pgcp`
	ON (`cronhis`.`ctb_pgcp`.`cuenta` = `bd_contablersc`.`seg_ctb_libaux`.`cuenta`);

INSERT INTO`cronhis`.`fin_maestro_doc`
	(`id_maestro`,`id_modulo`,`id_doc_fte`,`version_doc`,`fecha_doc`,`estado`,`control_doc`)
SELECT 
	`seg_fin_maestro_doc`.`id_maestro`, 
	CASE
		WHEN `seg_fin_maestro_doc`.`tipo_doc` = 'CDP' THEN '54'
		WHEN `seg_fin_maestro_doc`.`tipo_doc` = 'NCXP' THEN '55'
		WHEN `seg_fin_maestro_doc`.`tipo_doc` = 'CNOM' THEN '55'
		ELSE NULL
	END AS `modulo`,
	CASE
		WHEN `seg_fin_maestro_doc`.`tipo_doc` = 'CDP' THEN '21'
		WHEN `seg_fin_maestro_doc`.`tipo_doc` = 'NCXP' THEN '3'
		WHEN `seg_fin_maestro_doc`.`tipo_doc` = 'CNOM' THEN '5'
		ELSE NULL
	END AS `id_fte`,
	`seg_fin_maestro_doc`.`version_doc`,
	`seg_fin_maestro_doc`.`fecha_doc`,
	1 AS `estado`,
	1 AS `control_doc`
FROM `bd_contablersc`.`seg_fin_maestro_doc`;

INSERT  INTO`cronhis`.`ctb_tipo_doc`
	(`id_ctb_tipodoc`,`tipo`) 
VALUES (1,'FACTURA'),(2,'CUENTA DE COBRO'),(3,'DOCUMENTO EQUIVALENTE'),(4,'CAJA MENOR'),(5,'RESOLUCION'),(6,'OTROS');

INSERT INTO `cronhis`.`ctb_factura`
	(`id_cta_factura`,`id_ctb_doc`,`id_tipo_doc`,`num_doc`,`fecha_fact`,`fecha_ven`,`valor_pago`,`valor_iva`,`valor_base`,`detalle`,`id_user_reg`,`fec_rec`,`id_user_act`,`fec_act`)       
SELECT
	`id_cta_factura`,`id_ctb_doc`,`tipo_doc`,`num_doc`,`fecha_fact`,`fecha_ven`,`valor_pago`,`valor_iva`,`valor_base`,`detalle`,`id_user_reg`,`fec_rec`,`id_user_act`,`fec_act`
FROM `bd_contablersc`.`ctb_factura`;

INSERT INTO `cronhis`.`pto_cop_detalle`
	(`id_ctb_doc`,`id_pto_crp_det`,`id_tercero_api`,`valor`,`valor_liberado`)
SELECT
	`taux`.`id_ctb_doc`
	, `t2`.`id_pto_crp_det`
	, `taux`.`id_tercero_api`
	, `taux`.`valor`
	, 0 AS `liberado`
FROM	
	(SELECT
		`seg_pto_mvto`.`id_pto_doc`,`seg_pto_mvto`.`id_ctb_doc`,IFNULL(`seg_pto_mvto`.`id_tercero_api`,0) AS `id_tercero_api`,`seg_pto_mvto`.`rubro`
		,CASE
			WHEN `seg_pto_mvto`.`estado` = 0 THEN 2
			WHEN `seg_pto_mvto`.`estado` = 5 THEN 0
			ELSE 1
		END AS `estado`,SUM(`seg_pto_mvto`.`valor`) AS `valor`,`seg_pto_documento`.`id_pto_presupuestos`
	FROM `bd_contablersc`.`seg_pto_mvto`
	INNER JOIN `bd_contablersc`.`seg_pto_documento`
		ON(`bd_contablersc`.`seg_pto_mvto`.`id_pto_doc` = `bd_contablersc`.`seg_pto_documento`.`id_pto_doc`)
	WHERE `seg_pto_mvto`.`tipo_mov` = 'COP'
	GROUP BY `seg_pto_mvto`.`id_pto_doc`,`seg_pto_mvto`.`id_ctb_doc`,`seg_pto_mvto`.`id_tercero_api`,`seg_pto_mvto`.`rubro`) AS `taux`
	LEFT JOIN 
		(SELECT 
			`pto_cargue`.`id_cargue`
			, `pto_cargue`.`id_pto`
			, `pto_cargue`.`cod_pptal`
		FROM `cronhis`.`pto_cargue`) AS `t1`
		ON(`taux`.`id_pto_presupuestos` = `t1`.`id_pto` AND `taux`.`rubro` = `t1`.`cod_pptal`)
	LEFT JOIN 
		(SELECT
			`pto_cargue`.`id_cargue`
			, `pto_crp_detalle`.`id_pto_crp_det`
			, `pto_crp_detalle`.`id_pto_crp`
			, IFNULL(`pto_crp_detalle`.`id_tercero_api`,0) AS `id_tercero_api`
		FROM
			`cronhis`.`pto_crp_detalle`
		INNER JOIN `cronhis`.`pto_cdp_detalle` 
			ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
		INNER JOIN `cronhis`.`pto_cargue` 
			ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)) AS `t2`
		ON(`t1`.`id_cargue` = `t2`.`id_cargue` 
			AND `t2`.`id_pto_crp` = `taux`.`id_pto_doc` 
			AND `t2`.`id_tercero_api` = `taux`.`id_tercero_api`);

INSERT INTO `cronhis`.`tb_centrocostos`
	(`id_centro_fin`,`nom_centro`,`es_clinico`)
SELECT `id_centro`,`descripcion`, 0 AS `clinico` 
FROM `bd_contablersc`.`seg_centros_costo`
WHERE `seg_centros_costo`.`id_centro` IN (0,3,9,11,12,13,14);

UPDATE `cronhis`.`tb_centrocostos` SET `id_centro_fin` = 1 WHERE `id_centro`= 7;
UPDATE `cronhis`.`tb_centrocostos` SET `id_centro_fin` = 2 WHERE `id_centro`= 2;
UPDATE `cronhis`.`tb_centrocostos` SET `id_centro_fin` = 4 WHERE `id_centro`= 3;
UPDATE `cronhis`.`tb_centrocostos` SET `id_centro_fin` = 5 WHERE `id_centro`= 6;
UPDATE `cronhis`.`tb_centrocostos` SET `id_centro_fin` = 6 WHERE `id_centro`= 20;
UPDATE `cronhis`.`tb_centrocostos` SET `id_centro_fin` = 7 WHERE `id_centro`= 5;
UPDATE `cronhis`.`tb_centrocostos` SET `id_centro_fin` = 8 WHERE `id_centro`= 1;
UPDATE `cronhis`.`tb_centrocostos` SET `id_centro_fin` = 10 WHERE `id_centro`= 8;

INSERT  INTO`cronhis`.`far_area_tipo`(`id_tipo`,`nom_tipo`) VALUES (0,''),(1,'Consultorio'),(2,'Sala Clínicas'),(3,'Oficina');


INSERT INTO `cronhis`.`far_centrocosto_area`
	(`nom_area`,`id_centrocosto`,`id_x_sede`,`id_tipo_area`,`id_responsable`,`id_sede`)
SELECT
	`tb_centrocostos`.`nom_centro`,
	CASE 
		WHEN `tb_centrocostos`.`id_centro` IS NULL THEN 0
		ELSE `tb_centrocostos`.`id_centro`
	END AS `id_centro`,
	`seg_centro_costo_x_sede`.`id_x_sede`,0 AS `tipo`, 1 AS `responsable`
	,CASE
		WHEN `seg_centro_costo_x_sede`.`id_sede` = 1 THEN 1
		WHEN `seg_centro_costo_x_sede`.`id_sede` = 2 THEN 2
		WHEN `seg_centro_costo_x_sede`.`id_sede` = 3 THEN 2
		ELSE 2
	END AS `id_sede`
FROM `bd_contablersc`.`seg_centro_costo_x_sede`
LEFT JOIN `cronhis`.`tb_centrocostos`
	ON(`bd_contablersc`.`seg_centro_costo_x_sede`.`id_centro_c` = `cronhis`.`tb_centrocostos`.`id_centro_fin`);

INSERT INTO`cronhis`.`ctb_causa_costos`
	(`id`,`id_ctb_doc`,`id_area_cc`,`id_cc`,`valor`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`,`estado`)
SELECT
	`id`,`id_ctb_doc`,94 AS `ids`,`id_cc`,`valor`,`seg_usuarios_sistema`.`id_usuario`,`fecha_reg`,`seg_usuarios_sistema`.`id_usuario`,`fecha_act`
	, CASE
        WHEN `seg_ctb_causa_costos`.`estado` = 0 THEN 2
        WHEN `seg_ctb_causa_costos`.`estado` = 5 THEN 0
        ELSE 1
    END AS `estado`
FROM `bd_contablersc`.`seg_ctb_causa_costos`
LEFT JOIN`cronhis`.`seg_usuarios_sistema`
    ON (`cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `bd_contablersc`.`seg_ctb_causa_costos`.`id_user_reg`);

INSERT INTO `cronhis`.`ctb_causa_costos`
	(`id`,`id_ctb_doc`,`id_area_cc`,`id_cc`,`valor`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`,`estado`)
SELECT
	`id`,`id_ctb_doc`,94 AS `ids`,`id_cc`,`valor`,`seg_usuarios_sistema`.`id_usuario`,`fecha_reg`,`seg_usuarios_sistema`.`id_usuario`,`fecha_act`
	, CASE
        WHEN `seg_ctb_causa_costos`.`estado` = 0 THEN 2
        WHEN `seg_ctb_causa_costos`.`estado` = 5 THEN 0
        ELSE 1
    END AS `estado`
FROM `bd_contablersc`.`seg_ctb_causa_costos`
LEFT JOIN `cronhis`.`seg_usuarios_sistema`
    ON (`cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `bd_contablersc`.`seg_ctb_causa_costos`.`id_user_reg`);

INSERT  INTO`cronhis`.`ctb_retencion_tipo`
	(`id_retencion_tipo`,`tipo`,`id_tercero`,`estado`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`) 
VALUES (1,'Retención en la fuente',680,1,NULL,NULL,1,'2024-10-17 17:31:11'),(2,'Retención de IVA',680,1,NULL,NULL,1,'2024-10-17 17:31:09'),(3,'Retención de ICA',680,1,NULL,NULL,1,'2024-10-17 17:31:09'),(4,'Sobretasa bomberil',903,1,NULL,NULL,1,'2024-10-17 17:31:08'),(5,'Estampillas',903,1,NULL,NULL,1,'2024-10-17 17:31:07'),(6,'Otras retenciones',903,1,NULL,NULL,1,'2024-10-17 17:31:07'),(7,'Seguridad social',903,1,NULL,NULL,1,'2024-10-17 17:31:06');

INSERT  INTO`cronhis`.`ctb_retenciones`
	(`id_retencion`,`id_retencion_tipo`,`nombre_retencion`,`id_cuenta`,`estado`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`) 
VALUES (1,1,'Compras generales (no declarantes) 3.5%',NULL,1,NULL,NULL,1,'2024-10-17 18:35:12'),(2,2,'Responsables de impuestos a la ventas compras 15%',NULL,1,NULL,NULL,1,'2024-10-17 18:35:14'),(3,2,'A no residentes por servicios prestados',167,1,NULL,NULL,1,'2024-10-17 18:35:56'),(4,3,'Retencion por ICA 6 por mil',NULL,1,NULL,NULL,1,'2024-10-17 18:35:15'),(5,3,'Retencion por ICA 10 por mil',NULL,1,NULL,NULL,1,'2024-10-17 18:35:16'),(6,4,'Sobretasa bomberil 6%',NULL,1,NULL,NULL,1,'2024-10-17 18:35:16'),(9,4,'Sobretasa bomberil 10%',NULL,1,NULL,NULL,1,'2024-10-17 18:35:17'),(10,6,'Papeleria',NULL,1,NULL,NULL,1,'2024-10-17 18:36:02'),(11,3,'Retencion por ICA 1 por mil',NULL,0,NULL,NULL,NULL,NULL),(12,3,'Retencion por ICA 2 por mil',NULL,0,NULL,NULL,NULL,NULL),(13,3,'Retencion por ICA 3 por mil',NULL,0,NULL,NULL,NULL,NULL),(14,3,'Retencion por ICA 4 por mil',NULL,0,NULL,NULL,NULL,NULL),(15,3,'Retencion por ICA 5 por mil',NULL,0,NULL,NULL,NULL,NULL),(16,3,'Retencion por ICA 7 por mil',NULL,0,NULL,NULL,NULL,NULL),(17,3,'Retencion por ICA 8 por mil',NULL,0,NULL,NULL,NULL,NULL),(18,3,'Retencion por ICA 9 por mil',NULL,0,NULL,NULL,NULL,NULL),(19,4,'Sobretasa bomberil 1%',NULL,0,NULL,NULL,NULL,NULL),(20,4,'Sobretasa bomberil 2%',NULL,0,NULL,NULL,NULL,NULL),(21,4,'Sobretasa bomberil 3%',NULL,0,NULL,NULL,NULL,NULL),(22,4,'Sobretasa bomberil 4%',NULL,0,NULL,NULL,NULL,NULL),(23,4,'Sobretasa bomberil 5%',NULL,0,NULL,NULL,NULL,NULL),(24,4,'Sobretasa bomberil 7%',NULL,0,NULL,NULL,NULL,NULL),(25,4,'Sobretasa bomberil 8%',NULL,0,NULL,NULL,NULL,NULL),(26,4,'Sobretasa bomberil 9%',NULL,0,NULL,NULL,NULL,NULL),(27,1,'Arrendamiento de bienes muebles 4%',NULL,0,NULL,NULL,NULL,NULL),(28,1,'Compras de combustibles derivados del petróleo 0.10%',NULL,0,NULL,NULL,NULL,NULL),(29,1,'Compras generales (declarantes renta) 2.5%',NULL,0,NULL,NULL,NULL,NULL),(31,1,'Contratos de consultoría de obras públicas 2%',NULL,0,NULL,NULL,NULL,NULL),(32,1,'Diseño de página web y consultoría en programas de informática a obligados a declarar renta 3.5% ',NULL,0,NULL,NULL,NULL,NULL),(33,1,'Honorarios y comisiones (no declarantes renta) 10%',NULL,0,NULL,NULL,NULL,NULL),(34,1,'Honorarios y comisiones (personas jurídicas) 11%',NULL,0,NULL,NULL,NULL,NULL),(35,1,'Salarios y demás rentas de trabajo (Tabla art. 383 ET)',NULL,0,NULL,NULL,NULL,NULL),(36,1,'Servicios de licenciamiento o derecho de uso de software 3.5%',NULL,0,NULL,NULL,NULL,NULL),(37,1,'Servicios de transporte nacional de pasajeros por vía terrestre 3.5%',NULL,0,NULL,NULL,NULL,NULL),(38,1,'Servicios generales (declarantes renta) 4%',NULL,0,NULL,NULL,NULL,NULL),(39,1,'Servicios generales (no declarantes renta) 6%',NULL,0,NULL,NULL,NULL,NULL),(40,2,'Responsables de impuestos a la ventas servicios 15%',NULL,0,NULL,NULL,NULL,NULL),(41,3,'Retencion por ICA 2.5 por mil',NULL,0,NULL,NULL,NULL,NULL),(42,3,'Retencion por ICA 3.5 por mil',NULL,0,NULL,NULL,NULL,NULL),(43,3,'Retencion por ICA 12 por mil',NULL,0,NULL,NULL,NULL,NULL),(44,3,'Retencion por ICA 15 por mil',NULL,0,NULL,NULL,NULL,NULL),(45,4,'Sobretasa bomberil 11%',NULL,0,NULL,NULL,NULL,NULL),(46,4,'Sobretasa bomberil 16%',NULL,0,NULL,NULL,NULL,NULL),(47,4,'Sobretasa bomberil 15%',NULL,0,NULL,NULL,NULL,NULL),(48,4,'Sobretasa bomberil 20%',NULL,0,NULL,NULL,NULL,NULL),(49,5,'Tasa prodeporte y recreacion',NULL,0,NULL,NULL,NULL,NULL),(50,1,'Servicios prestados por empresas de vigilancia y aseo (sobre AIU)',NULL,0,NULL,NULL,NULL,NULL),(51,1,'Servicios integrales de salud prestados por IPS 2%',NULL,0,NULL,NULL,NULL,NULL),(52,2,'Responsables de impuestos a la ventas compras IVA 5%',NULL,0,NULL,NULL,NULL,NULL),(53,1,'Servicios de transporte de carga 1%',NULL,0,NULL,NULL,NULL,NULL),(54,7,'Aporte a salud',NULL,0,NULL,NULL,NULL,NULL),(55,7,'Aporte pensión',NULL,0,NULL,NULL,NULL,NULL),(56,7,'Fondo de solidaridad pensional',NULL,0,NULL,NULL,NULL,NULL);

INSERT INTO `cronhis`.`ctb_retencion_rango`
	(`id_rango`,`id_vigencia`,`id_retencion`,`valor_base`,`valor_tope`,`tarifa`,`estado`)
SELECT
	`id_rango`,`id_vigencia`,`id_retencion`,`valor_base`,`valor_tope`,`tarifa`,1 AS`estado`
FROM `bd_contablersc`.`seg_ctb_retencion_rango`
INNER JOIN `tb_vigencias`
	ON (`tb_vigencias`.`anio` = `seg_ctb_retencion_rango`.`vigencia`);

INSERT INTO `cronhis`.`ctb_causa_retencion`
	(`id_causa_retencion`,`id_ctb_doc`,`id_rango`,`valor_base`,`tarifa`,`valor_retencion`,`id_terceroapi`)
SELECT
	`id_causa_retencion`,`id_ctb_doc`,`seg_ctb_retencion_rango`.`id_rango`,`seg_ctb_causa_retencion`.`valor_base`,`seg_ctb_causa_retencion`.`tarifa`,`seg_ctb_causa_retencion`.`valor_retencion`,`id_terceroapi`
FROM `bd_contablersc`.`seg_ctb_causa_retencion`
LEFT JOIN `bd_contablersc`.`seg_ctb_retencion_rango`
	ON(`seg_ctb_retencion_rango`.`id_retencion` = `seg_ctb_causa_retencion`.`id_retencion`);

INSERT INTO`cronhis`.`ctb_doc` (`id_ctb_doc`) VALUES (12462);

INSERT INTO `cronhis`.`pto_pag_detalle`
            (`id_ctb_doc`,`id_pto_cop_det`,`valor`,`valor_liberado`,`id_tercero_api`)
SELECT
	`taux`.`id_ctb_cop`
	, `t2`.`id_pto_cop_det`
	, `taux`.`valor`
	, 0 AS `liberado`
	, `taux`.`id_tercero_api`
FROM	
	(SELECT
		`seg_pto_mvto`.`id_ctb_cop`,`seg_pto_mvto`.`id_ctb_doc`,IFNULL(`seg_pto_mvto`.`id_tercero_api`,0) AS `id_tercero_api`,`seg_pto_mvto`.`rubro`
		,CASE
			WHEN `seg_pto_mvto`.`estado` = 0 THEN 2
			WHEN `seg_pto_mvto`.`estado` = 5 THEN 0
			ELSE 1
		END AS `estado`,SUM(`seg_pto_mvto`.`valor`) AS `valor`,`seg_pto_documento`.`id_pto_presupuestos`
	FROM `bd_contablersc`.`seg_pto_mvto`
	INNER JOIN `bd_contablersc`.`seg_pto_documento`
		ON(`bd_contablersc`.`seg_pto_mvto`.`id_pto_doc` = `bd_contablersc`.`seg_pto_documento`.`id_pto_doc`)
	WHERE `seg_pto_mvto`.`tipo_mov` = 'PAG'
	GROUP BY `seg_pto_mvto`.`id_pto_doc`,`seg_pto_mvto`.`id_ctb_doc`,`seg_pto_mvto`.`id_tercero_api`,`seg_pto_mvto`.`rubro`) AS `taux`
	LEFT JOIN 
		(SELECT 
			`pto_cargue`.`id_cargue`
			, `pto_cargue`.`id_pto`
			, `pto_cargue`.`cod_pptal`
		FROM `cronhis`.`pto_cargue`) AS `t1`
		ON(`taux`.`id_pto_presupuestos` = `t1`.`id_pto` AND `taux`.`rubro` = `t1`.`cod_pptal`)
	LEFT JOIN 
		(SELECT
			`pto_cop_detalle`.`id_ctb_doc`
			, `pto_cop_detalle`.`id_pto_cop_det`
			, `pto_cop_detalle`.`id_pto_crp_det`
			, `pto_cargue`.`id_cargue`
			, IFNULL(`pto_cop_detalle`.`id_tercero_api`,0) AS `id_tercero_api`
		FROM
				`cronhis`.`pto_cop_detalle`
		INNER JOIN `cronhis`.`pto_crp_detalle` 
			ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
		INNER JOIN `cronhis`.`pto_cdp_detalle` 
			ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
		INNER JOIN `cronhis`.`pto_cargue` 
			ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)) AS `t2`
		ON(`t1`.`id_cargue` = `t2`.`id_cargue` 
			AND `t2`.`id_ctb_doc` = `taux`.`id_ctb_cop` 
			AND `t2`.`id_tercero_api` = `taux`.`id_tercero_api`)
WHERE `taux`.`id_ctb_cop` <> 0;

INSERT  INTO`cronhis`.`tes_tipo_cuenta`(`id_tipo_cuenta`,`tipo_cuenta`) VALUES (1,'Ahorros'),(2,'Corriente'),(3,'Uso restringido');

INSERT INTO `cronhis`.`tes_cuentas`
	(`id_tes_cuenta`,`id_banco`,`id_tipo_cuenta`,`id_cuenta`,`nombre`,`numero`,`estado`,`id_user_reg`,`fecha_reg`)
SELECT
	`seg_tes_cuentas`.`id_tes_cuenta`,`seg_tes_cuentas`.`id_banco`,`seg_tes_cuentas`.`id_tipo_cuenta`,`ctb_pgcp`.`id_pgcp`,`seg_tes_cuentas`.`nombre`,`seg_tes_cuentas`.`numero`
	,CASE
		WHEN `seg_tes_cuentas`.`estado` = 1 THEN 0
		WHEN `seg_tes_cuentas`.`estado` = 0 THEN 1
		ELSE 0
	END AS `estado`
	,`seg_tes_cuentas`.`id_user_reg`,`seg_tes_cuentas`.`fecha_reg`
FROM `bd_contablersc`.`seg_tes_cuentas`
LEFT JOIN `cronhis`.`ctb_pgcp`
	ON(`seg_tes_cuentas`.`cta_contable` = `ctb_pgcp`.`cuenta`);

INSERT  INTO`cronhis`.`tes_forma_pago`
	(`id_forma_pago`,`forma_pago`) 
VALUES (1,'Trasferencia'),(2,'Cheque'),(3,'Nota debito'),(4,'Nota credito'),(5,'Efectivo');

INSERT INTO `cronhis`.`tes_detalle_pago`
	(`id_detalle_pago`,`id_ctb_doc`,`id_tes_cuenta`,`id_forma_pago`,`documento`,`valor`,`id_user_reg`,`fecha_reg`)
SELECT
	`id_detalle_pago`,`id_ctb_doc`,`id_tes_cuenta`,`id_forma_pago`,`documento`,`valor`,`seg_usuarios_sistema`.`id_usuario`,`fecha_reg`
FROM `bd_contablersc`.`seg_tes_detalle_pago`
INNER JOIN `cronhis`.`seg_usuarios_sistema`
	ON (`seg_tes_detalle_pago`.`id_user_reg` = `seg_usuarios_sistema`.`id_user_fin`);

INSERT  INTO`cronhis`.`tb_tipo_compra`(`id_tipo`,`tipo_compra`) VALUES (1,'BIENES'),(2,'SERVICIOS'),(3,'PROYECTOS');

INSERT INTO `cronhis`.`tb_tipo_contratacion`
	(`id_tipo`,`id_tipo_compra`,`tipo_contrato`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
VALUES
(1,2,"PRESTACION DE SERVICIOS",NULL,NULL,NULL,NULL),
(2,2,"OTROS SERVICIOS",NULL,NULL,NULL,NULL);


INSERT  INTO `cronhis`.`tb_tipo_bien_servicio`
	(`id_tipo_b_s`,`id_tipo_cotrato`,`filtro_adq`,`tipo_bn_sv`,`cta_contable`,`objeto_definido`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`) 
VALUES (1,1,0,'PROFESIONAL 3',NULL,'DFD',1,'2023-01-03 15:39:00',1,'2024-01-05 14:23:54'),(2,1,0,'PROFESIONAL 1',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(3,1,0,'BACHILLER 2',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(4,1,0,'TECNICO 3',NULL,'TECNICO 3',1,'2023-01-03 15:39:00',1,'2024-01-05 11:35:03'),(5,1,0,'PROFESIONAL ESPECIALIZADO 2',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(6,1,0,'TECNOLOGO 1',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(8,1,0,'TECNICO 1',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(9,1,0,'TECNICO 2',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(10,1,0,'TECNOLOGO 2',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(11,1,0,'BACHILLER 3',NULL,'CONTRATO',1,'2023-01-03 15:39:00',1,'2023-01-04 07:09:55'),(12,1,0,'MEDICO GENERAL CONSULTA',NULL,'MC',1,'2023-01-03 15:39:00',1,'2024-01-05 11:35:41'),(13,1,0,'PROFESIONAL SALUD 1',NULL,'R',1,'2023-01-03 15:39:00',1,'2024-01-05 14:17:14'),(14,1,0,'HIGIENISTA EN SALUD ORAL',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(15,1,0,'REGENTE  DE  FARMACIA',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(16,1,0,'AUXILIAR DE FAMACIA',NULL,NULL,1,'2023-01-03 15:39:00',1,NULL),(17,1,0,'TECNICO 1 SALUD',NULL,'CONTRATO DE PRESTACION DE SERVICIOS',1,'2023-01-04 07:09:07',1,NULL),(18,1,0,'TECNICO 2 SALUD',NULL,'CONTRATO DE PRESTACION DE SERVICIOS',1,'2023-01-04 07:09:31',1,NULL),(19,1,0,'TECNOLOGO 1 SALUD',NULL,'CONTRATO',1,'2023-01-04 07:17:29',1,NULL),(20,1,0,'AUXILIARES AREA DE LA SALUD',NULL,'CONTRATO DE PRESTACION DE SERVICIOS',1,'2023-01-05 09:40:21',1,NULL),(21,1,0,'SERVICIOS A TODO COSTO',NULL,'CONTRATAR A TODO COSTO LOS SERVICIOS',1,'2023-01-10 09:21:04',1,NULL),(22,1,0,'VIGILANCIA Y SEGURIDAD PRIVADA',NULL,'PRESTAR EL SERVICIO DE VIGILANCIA Y SEGURIDAD PRIVADA PARA EL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',1,'2023-01-11 10:37:50',1,NULL),(23,1,0,'OTROS',NULL,'PRESTACIÓN DE SERVICIOS OTROS',1,'2023-01-13 16:40:45',1,NULL),(41,1,0,'BACHILLER 1',NULL,'SA',1,'2023-03-02 17:44:40',1,'2024-01-05 11:35:20'),(66,1,0,'SERVICIOS DE IMPRESION ',NULL,'“CONTRATAR LOS SERVICIOS DE IMPRESIÓN, INCLUIDO EL ALQUILER DE LOS EQUIPOS, CON EL SUMINISTRO DE TONER, REPUESTOS PARA EL MANTENIMIENTO PREVENTIVO Y CORRECTIVO PARA EL NORMAL FUNCIONAMIENTO DE LOS EQUIPOS EN LAS DIFERENTES AREAS DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',12,'2023-06-01 10:03:35',12,NULL),(67,1,0,'SERVICIOS DE IMPRESION ',NULL,'“CONTRATAR LOS SERVICIOS DE IMPRESIÓN, INCLUIDO EL ALQUILER DE LOS EQUIPOS, CON EL SUMINISTRO DE TONER, REPUESTOS PARA EL MANTENIMIENTO PREVENTIVO Y CORRECTIVO PARA EL NORMAL FUNCIONAMIENTO DE LOS EQUIPOS EN LAS DIFERENTES AREAS DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.”.',12,'2023-06-01 16:00:05',12,NULL),(72,1,0,'MANTENIMIENTO PREVENTIVO Y CORRECTIVO UPS',NULL,'PRESTACIÓN DE SERVICIOS DEL PROCESO DE MANTENIMIENTO PREVENTIVO Y CORRECTIVO A TODO COSTO INCLUIDOS REPUESTOS ORIGINALES NUEVOS Y MANO DE OBRA DE LAS UPS DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',12,'2023-08-16 09:03:00',12,NULL),(73,1,0,'SERVICIO DE ALQUILER DE DIGITALIZADOR CR 15-X PARA EL  EQUIPO DE RAYOS X.',NULL,'PRESTAR EL SERVICIO DE ALQUILER UN DE UN EQUIPO DE DIGITALIZACIÓN CR 15-X PARA EL ÁREA DE RAYOS X DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO ESE.',12,'2023-08-16 14:55:34',12,NULL),(74,1,0,'MANTENIMIENTO RED DE OXIGENO ',NULL,'MANTENIMIENTO CORRECTIVO Y SUMINISTRO DE REPUESTOS PARA LA RED DE OXÍGENO MEDICINAL DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',12,'2023-08-16 15:28:59',12,'2024-03-06 09:58:52'),(77,1,0,'MANTENIMIENTO RED ELECTRICA',NULL,'MANTENIMIENTO Y ADECUACIÓN DE INFRAESTRUCTURA DE LA RED ELÉCTRICA DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E',12,'2023-08-28 10:57:53',12,NULL),(78,1,0,'MANTENIMIENTO RED ELECTRICA',NULL,'MANTENIMIENTO Y ADECUACIÓN DE INFRAESTRUCTURA DE LA RED ELÉCTRICA DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E',12,'2023-08-28 10:58:48',12,NULL),(81,1,0,'BIENESTAR INSTITUCIONAL',NULL,'FORTALECER LAS RELACIONES PERSONALES Y EFICACIA LABORAL PARA EL MEJORAMIENTO\r\nDEL CLIMA ORGANIZACIONAL EN EL MARCO DEL PROGRAMA DE BIENESTAR SOCIAL DEL\r\nHOSPITAL DE AGUAZUL JUAN HERNANDO URREGO ESE.',14,'2023-10-04 10:18:06',14,NULL),(83,1,0,'UPS',NULL,'CONTRATAR EL MANTENIMIENTO PREVENTIVO Y CORRECTIVO INTEGRAL DE LAS UPS AL SERVICIO DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E. A TODO COSTO, INCLUIDOS REPUESTOS ORIGINALES, NUEVOS Y MANO DE OBRA. ',12,'2023-10-26 10:08:34',12,NULL),(84,1,0,'MANTENIMIENTO DE EXTINTORES ',NULL,'MANTENIMIENTO DE EXTINTORES ',12,'2023-11-17 15:01:54',12,NULL),(89,1,0,'PROFESIONAL ESPECIALIZADO 1',NULL,'PROFESIONAL ESPECIALIZADO 1',1,'2024-01-05 11:31:35',1,NULL),(90,1,0,'PROFESIONAL 2',NULL,'PROFESIONAL 1',1,'2024-01-05 11:31:56',1,'2024-01-05 14:24:30'),(91,1,0,'TECNOLOGO 3',NULL,'TECNOLOGO 3',1,'2024-01-05 11:32:20',1,NULL),(92,1,0,'AUX URGENCIAS 4',NULL,'AUX URGENCIAS 4',1,'2024-01-05 11:32:43',1,NULL),(93,1,0,'AUX URGENCIAS 5',NULL,'AUX URGENCIAS 5',1,'2024-01-05 11:33:20',1,NULL),(94,1,0,'MEDICO GENERAL URG',NULL,'MEDICO GENERAL URG',1,'2024-01-05 11:33:49',1,NULL),(95,1,0,'PROFESIONAL SALUD 2',NULL,'PROFESIONAL SALUD 2',1,'2024-01-05 11:34:21',1,NULL),(96,1,0,'PROFESIONAL SALUD 3',NULL,'PROFESIONAL SALUD 3',1,'2024-01-05 11:34:37',1,NULL),(97,1,0,'BACHILLER 4',NULL,'BACHILLER 4',1,'2024-01-05 13:56:13',1,NULL),(98,1,0,'BACHILLER 5',NULL,'BACHILLER 5',1,'2024-01-05 13:56:28',1,NULL),(99,1,0,'TECNICO EN MANTENIMIENTO ',NULL,'PRESTAR SERVICIOS DE APOYO A LA GESTIÓN COMO AUXILIAR DE MANTENIMIENTO EN EL ÁREA DE AMBIENTE FÍSICO Y TECNOLÓGICO DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',16,'2024-01-05 14:30:55',16,NULL),(101,1,0,'ASESOR 2',NULL,'ASESOR 2',1,'2024-01-09 14:04:59',1,NULL),(103,1,0,'MANTENIMIENTO EQUIPO BIOMEDICO ',NULL,'PRESTAR EL SERVICIO DE MANTENIMIENTO PREVENTIVO Y CORRECTIVO Y SUMINISTRO DE REPUESTOS A TODO COSTO DE EQUIPOS BIOMÉDICOS DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',16,'2024-01-29 08:04:21',16,NULL),(105,1,0,'MANTENIMIENTO DE AIRES ACONDICIONADOS ',NULL,'SUMINISTRO E INSTALACIÓN Y MANTENIMIENTO DE AIRES ACONDICIONADOS, NEVERAS, REFRIGERADORES, DISPENSADORES DE AGUA A TODO COSTO DEL HOSPITAL JUAN HERNANDO URREGO E.S.E.',16,'2024-02-05 09:03:44',16,NULL),(106,1,0,'MANTENIMIENTO DE AIRES ACONDICIONADOS ',NULL,'SUMINISTRO E INSTALACIÓN Y MANTENIMIENTO DE AIRES ACONDICIONADOS, NEVERAS, REFRIGERADORES, DISPENSADORES DE AGUA A TODO COSTO DEL HOSPITAL JUAN HERNANDO URREGO E.S.E.',16,'2024-02-05 09:07:52',16,NULL),(107,1,0,'RECOLECCION DE RESIDUOS PELIGROSOS ',NULL,'PRESTAR EL SERVICIO DE RECOLECCIÓN, TRANSPORTE, TRATAMIENTO Y DISPOSICIÓN FINAL DE RESIDUOS HOSPITALARIOS QUE GENERAL EL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',16,'2024-02-06 18:06:17',16,NULL),(108,1,0,'MANTENMIENTO DE VEHICULOS ',NULL,'MANTENIMIENTO PREVENTIVO Y CORRECTIVO CON SU RESPECTIVO SUMINISTRO DE REPUESTOS SEGÚN LA NECESIDAD DE LA ACTIVIDAD REALIZADA A LOS VEHÍCULOS DE PROPIEDAD DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',16,'2024-02-16 10:03:00',16,'2024-02-16 10:08:51'),(110,1,0,'MANTENIMIENTO DE PLANTAS ELECTRICAS Y ELECTROBOMBAS  ',NULL,'MANTENIMIENTO PREVENTIVO Y CORRECTIVO DE LAS PLANTAS ELECTRICAS Y ELECTROBOMBAS, UBICADAS EN LAS INSTALACIONES DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E',16,'2024-02-19 10:19:33',16,NULL),(116,1,0,'PROFESIONAL SALUD  07 (APS)',NULL,'PROFESIONAL SALUD  07 (APS)',1,'2024-03-08 15:39:00',1,NULL),(117,1,0,'PROFESIONAL SALUD  06 (APS)',NULL,'PROFESIONAL SALUD  06 (APS)',1,'2024-03-08 15:39:00',1,NULL),(118,1,0,'PROFESIONAL SALUD  05 (APS)',NULL,'PROFESIONAL SALUD  05 (APS)',1,'2024-03-08 15:39:00',1,NULL),(119,1,0,'PROFESIONAL SALUD  04 (APS)',NULL,'PROFESIONAL SALUD  04 (APS)',1,'2024-03-08 15:39:00',1,NULL),(120,1,0,'PROFESIONAL SALUD  03 (APS)',NULL,'PROFESIONAL SALUD  03 (APS)',1,'2024-03-08 15:39:00',1,NULL),(121,1,0,'PROFESIONAL SALUD  02 (APS)',NULL,'PROFESIONAL SALUD  02 (APS)',1,'2024-03-08 15:39:00',1,NULL),(122,1,0,'PROFESIONAL SALUD  01 (APS)',NULL,'PROFESIONAL SALUD  01 (APS)',1,'2024-03-08 15:39:00',1,NULL),(123,1,0,'PROFESIONAL 03 (APS)',NULL,'PROFESIONAL 03 (APS)',1,'2024-03-08 15:39:00',1,NULL),(124,1,0,'PROFESIONAL 02 (APS)',NULL,'PROFESIONAL 02 (APS)',1,'2024-03-08 15:39:00',1,NULL),(125,1,0,'PROFESIONAL 01 (APS)',NULL,'PROFESIONAL 01 (APS)',1,'2024-03-08 15:39:00',1,NULL),(126,1,0,'TÉCNICO 02 (APS)',NULL,'TÉCNICO 02 (APS)',1,'2024-03-08 15:39:00',1,NULL),(127,1,0,'TÉCNICO 01 (APS)',NULL,'TÉCNICO 01 (APS)',1,'2024-03-08 15:39:00',1,NULL),(128,1,0,'AUX. ÁREA DE LA SALUD 01 (APS)',NULL,'AUX. ÁREA DE LA SALUD 01 (APS)',1,'2024-03-08 15:39:00',1,NULL),(131,1,0,'PRESTAR SERVICIO DE TRANSPORTE APS ',NULL,'PRESTAR SERVICIO DE TRANSPORTE ESPECIAL TERRESTRE A TODO COSTO, PARA LA EJECUCIÓN DEL PROGRAMA DE ATENCIÓN PRIMARIA EN SALUD (APS) DESARROLLADO POR EL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',16,'2024-03-21 09:58:17',16,NULL),(132,1,0,'ENVIO Y RECEPCION DE MENSAJERIA ',NULL,'CONTRATAR LA PRESTACIÓN DEL SERVICIO DE ENVÍO DE CORRESPONDENCIA QUE EXPIDE EL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E. A LAS DIFERENTES ENTIDADES DEL ORDEN DEPARTAMENTAL Y NACIONAL ',16,'2024-04-08 09:57:01',16,NULL),(134,1,0,'AUXILIAR DE ARCHIVO ',NULL,'PRESTAR LOS SERVICIOS COMO AUXILIAR EN ARCHIVO, EN EL PROCESO GESTION DOCUMENTAL DEL HOSPITAL DE AGUAZUL JUAN HERNANDO ESE.',16,'2024-05-22 15:28:25',16,NULL),(136,1,0,'PROFESIONAL SALUD  01 (PIC)',NULL,'PSICOPEDAGOGO CON SEIS MESES DE EXPERINCIA',1,'2024-06-05 14:13:18',1,NULL),(137,1,0,'PROFESIONAL SALUD  02 (PIC)',NULL,'PROFESIONAL O TECNOLOGO CERTIFICADO MANIPULACIÓN DE ALIMENTOS ',1,'2024-06-05 14:13:46',1,NULL),(138,1,0,'PROFESIONAL SALUD  03 (PIC)',NULL,'NUTRICIONISTA CON UN AÑO DE EXPERINCIA',1,'2024-06-05 14:14:13',1,NULL),(139,1,0,'PROFESIONAL SALUD  04 (PIC)',NULL,'MEDICO GENERAL',1,'2024-06-05 14:14:34',1,NULL),(140,1,0,'PROFESIONAL SALUD  05 (PIC)',NULL,'FONOAUDIOLOGO CON UN AÑO DE EXPERINCIA',1,'2024-06-05 14:14:53',1,NULL),(141,1,0,'PROFESIONAL SALUD  06 (PIC)',NULL,'MEDICO ESPECIALISTA GINECOLOGÍA',1,'2024-06-05 14:15:16',1,NULL),(142,1,0,'PROFESIONAL SALUD  07 (PIC)',NULL,'PSICOLOGAS O PSICOPEDAGOGO (ZOE)',1,'2024-06-05 14:15:40',1,NULL),(143,1,0,'TÉCNICO 01 (PIC)',NULL,'TECNICO EN SEGURIDAD VÍAL',1,'2024-06-05 14:16:48',1,NULL),(144,1,0,'TÉCNICO 02 (PIC)',NULL,'TECNICO DE SALUD PUBLICA',1,'2024-06-05 14:17:07',1,NULL),(145,1,0,'AUX. ÁREA DE LA SALUD 01 (PIC)',NULL,'AUXILIAR DE ENFERMERIA EXPERIENCIA EN ACCIDENTES Y MANEJO DE EXTINTORES',1,'2024-06-05 14:17:29',1,NULL),(146,1,0,'AUX. ÁREA DE LA SALUD 02 (PIC)',NULL,'AUXILIARES',1,'2024-06-05 14:17:47',1,NULL),(147,1,0,'BACHILLER.ÁREA DE LA SALUD 01 (PIC)',NULL,'BACHILLER COMO INTERPRETE EN AMBIENTE EDUCATIVO',1,'2024-06-05 14:19:18',1,NULL),(148,1,0,'PROFESIONAL 01 (PIC)',NULL,'COORDINADOR PIC',1,'2024-06-05 14:34:11',1,NULL),(150,1,0,'SOPORTE TECNICO, MANTENIMINETO Y ACTUALIZACION DE SOFTWARE',NULL,'PRESTACIÓN DE SERVICIOS DE SOPORTE TÉCNICO, ACTUALIZACIÓN DEL SOFTWARE Y MANTENIMIENTO AL SISTEMA DE INFORMACIÓN CRONHIS, CON LICENCIA DE USO PARA EL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E. \r\n\r\n',3,'2024-06-19 08:23:46',3,NULL),(151,1,0,'PROFESIONAL SALUD  01 (DISCAPACIDAD)',NULL,'MEDICO',1,'2024-07-05 16:04:07',1,NULL),(152,1,0,'PROFESIONAL SALUD  01 (PAPSIVI)',NULL,'PROFESIONAL EN PSICOLOGO O  TRABAJO SOCIAL',1,'2024-07-15 18:41:26',1,NULL),(153,1,0,'BACHILLER  01 (PAPSIVI)',NULL,'TERMINACIÓN Y APROBACIÓN DE NOVENO BACHILLERATO.',1,'2024-07-15 18:41:57',1,NULL),(154,1,0,'PROFESIONAL SALUD 02 (PAPSIVI)',NULL,'PROFESIONAL EN PSICOLOGÍA Y/O TRABAJO SOCIAL Y/O DESARROLLO FAMILIAR',1,'2024-07-15 18:42:23',1,NULL),(155,1,0,'PRESTACION DE SERVICOS ',NULL,'REALIZAR APOYO A LA GESTIÓN PARA LOS PROCESO ADMINISTRATIVOS DE LA OFICINA DE GESTIÓN DEL AMBIENTE FISICO Y TECNOLOGICO.',16,'2024-07-22 15:23:52',16,NULL),(156,1,0,'BACHILLER AUXILIAR ',NULL,'REALIZAR APOYO A LA GESTION PARA LOS PROCESO ADMNISITRATIVOS DE LA OFICINA DE GESTION DEL AMBIENTE FISICO Y TECNOLOGICO.',16,'2024-07-22 15:28:00',16,NULL),(157,3,1,'ELEMENTOS DE CONSUMO',NULL,'SUMINISTRO DE ELEMENTOS DE CONSUMO SOLICITADOS DESDE ALMACÉN',1,'2024-08-22 11:23:21',NULL,NULL),(158,3,2,'ACTIVOS FIJOS',NULL,'SUMINISTRO DE ELEMENTOS DE CONSUMO SOLICITADOS DESDE ALMACÉN',1,'2024-08-22 11:24:10',NULL,NULL);

INSERT  INTO `cronhis`.`ctt_bien_servicio`
	(`id_b_s`,`id_tipo_bn_sv`,`bien_servicio`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`) 
VALUES (1,1,'PROFESIONAL DE APOYO',1,'2023-01-03 15:39:00',1,NULL),(2,90,'PROFESIONAL GESTION CONTRACTUAL',1,'2023-01-03 15:39:00',1,NULL),(3,1,'ABOGADO DEFENSA JUDICIAL',1,'2023-01-03 15:39:00',1,NULL),(4,2,'PROFESIONAL DE APOYO',1,'2023-01-03 15:39:00',1,NULL),(5,3,'AUXILIAR EN GESTION DOCUMENTAL',1,'2023-01-03 15:39:00',1,NULL),(6,90,'PROFESIONAL DE APOYO OFICINA GESTION DEL AMBIENTE FISICO Y TECNOLOGICO',1,'2023-01-03 15:39:00',1,NULL),(7,4,'TECNICO EN MANTENIMIENTO',1,'2023-01-03 15:39:00',1,NULL),(8,1,'REVISOR FISCAL',1,'2023-01-03 15:39:00',1,NULL),(9,1,'CONTADOR PUBLICO',1,'2023-01-03 15:39:00',1,NULL),(10,5,'ABOGADO CARTERA',1,'2023-01-03 15:39:00',1,NULL),(11,6,'APOYO CARTERA',1,'2023-01-03 15:39:00',1,NULL),(12,90,'TALENTO HUMANO',1,'2023-01-03 15:39:00',1,'2023-01-04 07:12:27'),(13,8,'APOYO TALENTO HUMANO',1,'2023-01-03 15:39:00',1,NULL),(14,90,'APOYO FINANCIERO',1,'2023-01-03 15:39:00',1,NULL),(15,90,'INGENIERO DE SISTEMAS',1,'2023-01-03 15:39:00',1,NULL),(16,8,'TECNICO SISTEMAS',1,'2023-01-03 15:39:00',1,NULL),(17,9,'TECNICO GLOSAS',1,'2023-01-03 15:39:00',1,NULL),(18,10,'AUDITORIA URGENCIAS',1,'2023-01-03 15:39:00',1,NULL),(19,9,'ARMADOR DE CUENTAS',1,'2023-01-03 15:39:00',1,NULL),(20,9,'AUDITORIA CONSULTA EXTERNA',1,'2023-01-03 15:39:00',1,NULL),(21,11,'FACTURADOR URGENCIAS',1,'2023-01-03 15:39:00',1,NULL),(22,3,'FACTURADOR CONSULTA EXTERNA',1,'2023-01-03 15:39:00',1,NULL),(23,3,'DEVOLUCIONES',1,'2023-01-03 15:39:00',1,NULL),(24,3,'FACTURADOR CALL CENTER',1,'2023-01-03 15:39:00',1,NULL),(25,9,'CORDINADOR FACTURACION ',1,'2023-01-03 15:39:00',1,NULL),(26,1,'PROFESIONAL GESTION CALIDAD',1,'2023-01-03 15:39:00',1,NULL),(27,90,'PROFESIONAL SIAU',1,'2023-01-03 15:39:00',1,NULL),(28,90,'PROFESIONAL MIPG',1,'2023-01-03 15:39:00',1,'2023-01-04 07:12:09'),(29,90,'PROFESIONAL SGSST',1,'2023-01-03 15:39:00',1,'2023-01-04 07:12:18'),(30,8,'APOYO PRENSA Y COMUNICACIONES',1,'2023-01-03 15:39:00',1,NULL),(31,94,'URGENCIAS E INTERNACIÓN',1,'2023-01-03 15:39:00',1,'2024-01-13 10:19:34'),(32,12,'CONSULTA EXTERNA',1,'2023-01-03 15:39:00',1,NULL),(33,13,'COORDINACIÓN MÉDICA',1,'2023-01-03 15:39:00',1,NULL),(34,13,'COORDINADOR URGENCIAS E INTERNACIÓN',1,'2023-01-03 15:39:00',1,NULL),(35,13,'GINECOLOGÍA Y GINECOBSTETRICIA',1,'2023-01-03 15:39:00',1,NULL),(36,13,'ESPECIALIZADOS EN PEDIATRÍA',1,'2023-01-03 15:39:00',1,NULL),(37,13,'PROFESIONALES COMO  OPTÓMETRA',1,'2023-01-03 15:39:00',1,NULL),(38,13,'ESPECIALIZADOS EN MEDICINA LABORAL Y DEL TRABAJO ',1,'2023-01-03 15:39:00',1,NULL),(39,13,'ESPECIALIZADOS EN OTORRINOLARINGOLOGIA',1,'2023-01-03 15:39:00',1,NULL),(40,13,'PSICOLOGÍA',1,'2023-01-03 15:39:00',1,NULL),(41,13,'NUTRICIONISTA Y  DIETÉTICA',1,'2023-01-03 15:39:00',1,NULL),(42,13,'BACTERIÓLOGÍA',1,'2023-01-03 15:39:00',1,NULL),(43,13,'BACTERIÓLOGÍA ESPECIALIZADA',1,'2023-01-03 15:39:00',1,NULL),(44,13,'ODONTÓLOGÍA',1,'2023-01-03 15:39:00',1,NULL),(45,14,'HIGIENISTA EN SALUD ORAL',1,'2023-01-03 15:39:00',1,NULL),(46,13,'ENFERMERÍA  EN LA COORDINACIÓN DEL ÁREA DE PYP',1,'2023-01-03 15:39:00',1,NULL),(47,13,'ENFERMERÍA PARA  EL ÁREA DE PYP',1,'2023-01-03 15:39:00',1,NULL),(48,15,'REGENTE  DE  FARMACIA',1,'2023-01-03 15:39:00',1,NULL),(49,16,'TÉCNICO EN SERVICIOS FARMACÉUTICOS',1,'2023-01-03 15:39:00',1,NULL),(50,19,'TECNÓLOGO EN IMÁGENES  DIAGNOSTICAS',1,'2023-01-03 15:39:00',1,'2023-01-04 07:17:50'),(51,13,'PROFESIONALES EN LA PROYECCIÓN DE INFORMES E INDICADORES',1,'2023-01-03 15:39:00',1,NULL),(52,9,'TÉCNICO EN LA ELABORACIÓN DE BASES DE DATOS',1,'2023-01-03 15:39:00',1,NULL),(53,13,'PROFESIONALES PARA LA CONSOLIDACIÓN DE INFORMACIÓN E INDICADORES DE VACUNACIÓN COVID-19.  ',1,'2023-01-03 15:39:00',1,'2024-01-26 10:55:40'),(54,13,'TRABAJADOR SOCIAL',1,'2023-01-03 15:39:00',1,NULL),(55,8,'TÉCNICO EN ESTERILIZACIÓN ',1,'2023-01-03 15:39:00',1,NULL),(56,13,'SEGURIDAD DEL PACIENTE ',1,'2023-01-03 15:39:00',1,NULL),(57,13,'FISIOTERAPEUTA',1,'2023-01-03 15:39:00',1,NULL),(58,13,'FONOAUDIOLOGIA',1,'2023-01-03 15:39:00',1,NULL),(59,13,'TERAPIA OCUPACIONAL',1,'2023-01-03 15:39:00',1,NULL),(60,13,'TERAPEUTA RESPIRATORIA',1,'2023-01-03 15:39:00',1,NULL),(61,1,'ABOGADO PARA BRINDAR APOYO JURÍDICO ',1,'2023-01-03 15:39:00',1,NULL),(62,6,'APOYO A LA GESTIÓN EN LOS TEMAS JURÍDICOS ',1,'2023-01-03 15:39:00',1,NULL),(63,12,'MEDICO GENERAL CONSULTA EXTERNA',1,'2023-01-04 07:22:12',1,'2024-01-13 10:20:06'),(64,20,'AUXILIAR AREA VACUNACION',1,'2023-01-05 09:41:22',1,NULL),(65,20,'AUXILIAR URGENCIAS E INTERNACIÓN',1,'2023-01-05 09:58:47',1,NULL),(66,20,'AUXILIAR CONSULTA EXTERNA-PYP -TERAPIAS',1,'2023-01-05 09:59:08',1,NULL),(67,20,'AUXILIAR DE LABORATORIO CLINICO',1,'2023-01-05 09:59:32',1,NULL),(68,20,'AUXILIAR DE ODONTOLOGIA',1,'2023-01-05 09:59:53',1,NULL),(69,13,'ENFERMERA URGENCIAS E INTERNACIÓN',1,'2023-01-05 10:17:08',1,NULL),(70,21,'LECTURA DE ELECTROCARDIOGRAMAS Y DE IMÁGENES DIAGNOSTICAS',1,'2023-01-10 09:22:26',1,NULL),(71,21,'PROCESAMIENTO, LECTURA Y REPORTE DE LAS MUESTRAS DE CITOLOGÍAS',1,'2023-01-10 09:22:26',1,NULL),(72,21,'PROCESAMIENTO DE MUESTRAS DE LABORATORIO CLÍNICO',1,'2023-01-10 09:22:26',1,NULL),(73,22,'VIGILANCIA Y SEGURIDAD PRIVADA',1,'2023-01-11 10:38:12',1,NULL),(74,23,'OTRO',1,'2023-01-13 16:42:36',1,NULL),(505,41,'PROMOTOR COMUNITARIO PAPSIVI',1,'2023-03-02 17:45:21',1,NULL),(3627,66,'SERVICIOS DE IMPRESION ',12,'2023-06-01 16:01:09',12,NULL),(3634,1,'COORDINADOR PIC',1,'2023-06-15 17:48:42',1,NULL),(3764,72,'SUMINISTRO E INSTALACIÓN DE BATERÍA SECA DE LIBRE MANTENIMIENTO DE REFERENCIA 12V7.5AH/20HR ',12,'2023-08-16 09:18:13',12,NULL),(3765,72,'SUMINISTRO DE BATERÍA SECA DE LIBRE MANTENIMIENTO DE REFERENCIA 12V18AH/20HR CON VÁLVULA V.R.L.A',12,'2023-08-16 09:18:13',12,NULL),(3766,72,'SUMINISTRO CABLE UTP CAT. 6*305 METROS TIPO INTERIOR',12,'2023-08-16 09:18:13',12,NULL),(3767,72,'SUMINISTRO CONECTOR PLUG PARA DATOS RJ 45',12,'2023-08-16 09:18:13',12,NULL),(3768,72,'SUMINISTRO TESTER PROBADOR CABLE LAN, TESTEA RJ45 TIA 568A/B ',12,'2023-08-16 09:18:13',12,NULL),(3769,72,'MANTENIMIENTO PREVENTIVO Y PREDICTIVO OVERALL A LAS UPS’S DE 12, 10,10 Y 5KVA',12,'2023-08-16 09:18:13',12,NULL),(3770,72,'MANTENIMIENTO PREVENTIVO DE UPS HASTA DE 1500W',12,'2023-08-16 09:18:13',12,NULL),(3771,73,'SERVICIO DE ALQUILER DE DIGITALIZADOR CR 15-X PARA EL  EQUIPO DE RAYOS X.',12,'2023-08-16 14:56:34',12,NULL),(3773,74,'MANTENIMIENTO MANIFOLD OXIGENO DE 2X1 LOX: DESENSAMBLE UNIDADES DE REGULACIÓN, LIMPIEZA, CAMBIO KIT EMPAQUES INTERNOS, VERIFICACIÓN CALIBRACIÓN MANÓMETROS Y/O CAMBIO, ENSAMBLE, VERIFICACIÓN FUGAS Y PRUEBAS DE FUNCIONAMIENTO, PINTURA.',12,'2023-08-16 15:36:41',12,NULL),(3774,74,'MANTENIMIENTO MANIFOLD OXIGENO DE 1X8 GASEOSO: DESENSAMBLE UNIDADES DE REGULACIÓN, LIMPIEZA, CAMBIO KIT EMPAQUES INTERNOS, VERIFICACIÓN CALIBRACIÓN MANÓMETROS Y/O CAMBIO, ENSAMBLE, VERIFICACIÓN FUGAS Y PRUEBAS DE FUNCIONAMIENTO, PINTURA.',12,'2023-08-16 15:36:41',12,NULL),(3775,74,'MANTENIMIENTO CAJAS DE CORTE DE 3 GASES: VERIFICACIÓN Y MANTENIMIENTO VÁLVULAS, LIMPIEZA INTERNA Y EXTERNA, VERIFICACIÓN CALIBRACIÓN Y/O CAMBIO MANÓMETROS, PINTURA, ETIQUETADO.',12,'2023-08-16 15:36:41',12,NULL),(3776,74,'MANTENIMIENTO TOMAS CHEMETRON OXIGENO: LIMPIEZA Y DESINFECCIÓN, CAMBIO EMPAQUES INTERNOS, VERIFICACIÓN DE FUGAS Y FUNCIONAMIENTO, PINTURA, ETIQUETADO.',12,'2023-08-16 15:36:41',12,NULL),(3777,74,'MANTENIMIENTO DE ALARMA ÁREA: DESENSAMBLE ALARMA, LIMPIEZA TARJETA ELECTRÓNICA, VERIFICACIÓN ALIMENTACIÓN ELÉCTRICA, INSPECCIÓN DE PRESOSTATOS, CALIBRACIÓN Y PRUEBAS DE FUNCIONAMIENTO, ETIQUETADO.',12,'2023-08-16 15:36:41',12,NULL),(3778,74,'SUMINISTRO DE MANÓMETROS 0-100PSI',12,'2023-08-16 15:36:41',12,NULL),(3779,74,'SUMINISTRO DE ETIQUETAS Y AVISOS ',12,'2023-08-16 15:36:41',12,NULL),(3780,74,'SUMINISTRO ESCUDO TOMA CHEMETRON OXIGENO.',12,'2023-08-16 15:36:41',12,NULL),(3781,74,'SERVICIO TÉCNICO',12,'2023-08-16 15:36:41',12,NULL),(3834,78,'MANTENIMIENTO PREVENTIVO Y CORRECTIVO CON SUS RESPECTIVOS REPUESTOS',12,'2023-08-28 11:00:29',12,NULL),(3932,81,'BIENESTAR INSTITUCIONAL',14,'2023-10-04 10:23:10',14,NULL),(3933,81,'BIENESTAR INSTITUCIONAL',14,'2023-10-04 10:25:24',14,NULL),(3934,81,'BIENESTAR INSTITUCIONAL',14,'2023-10-04 10:25:58',14,NULL),(4037,1,'ARQUITECTO ',1,'2023-10-25 17:48:06',1,NULL),(4038,83,'CONTRATAR EL MANTENIMIENTO PREVENTIVO Y CORRECTIVO INTEGRAL DE LAS UPS AL SERVICIO DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E. A TODO COSTO, INCLUIDOS REPUESTOS ORIGINALES, NUEVOS Y MANO DE OBRA. ',12,'2023-10-26 10:13:13',12,NULL),(4091,84,'EXTINTORES ',12,'2023-11-17 15:02:26',12,NULL),(4220,95,'NUTRICIONISTA',1,'2024-01-05 11:40:01',1,NULL),(4221,96,'OPTOMETRA',1,'2024-01-05 11:40:25',1,NULL),(4222,99,'PRESTAR SERVICIOS DE APOYO A LA GESTIÓN COMO AUXILIAR DE MANTENIMIENTO EN EL ÁREA DE AMBIENTE FÍSICO Y TECNOLÓGICO DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',16,'2024-01-05 14:31:32',16,NULL),(4223,90,'PROFESIONAL DE APOYO',1,'2024-01-09 14:04:34',1,NULL),(4224,101,'ABOGADO CARTERA ',1,'2024-01-09 14:05:17',1,NULL),(4225,4,'CORDINADOR FACTURACION',3,'2024-01-10 16:56:55',3,NULL),(4226,4,'TECNICO SISTEMAS',3,'2024-01-10 16:57:17',3,NULL),(4227,4,'ARMADOR DE CUENTAS',3,'2024-01-10 16:57:35',3,NULL),(4228,4,'AUDITORIA CONSULTA EXTERNA',3,'2024-01-10 16:57:53',3,NULL),(4229,4,'AUDITORIA CONSULTA URGENCIAS',3,'2024-01-10 16:58:11',3,NULL),(4230,4,'TECNICO GLOSAS Y DEVOLUIONES',3,'2024-01-10 16:58:40',3,NULL),(4231,13,'ENFERMERO JEFE IAMII',17,'2024-01-12 11:53:02',17,NULL),(4232,8,'APOYO A LA GESTION AREA JURIDICA',11,'2024-01-15 07:50:30',11,NULL),(4234,92,'FACTURADOR URGENCIAS',3,'2024-01-22 10:30:16',3,NULL),(4235,97,'FACTURADOR URGENCIAS',3,'2024-01-22 10:31:03',3,NULL),(4236,98,'FACTURADOR URGENCIAS',3,'2024-01-22 10:31:17',3,NULL),(4237,94,'MEDICO GENERAL URGENCIAS Y CONSULTA EXTERNA.',17,'2024-01-22 16:11:24',17,NULL),(4240,103,'MANTENIMIENTO DE LOS EQUIPOS BIOMÉDICOS ',16,'2024-01-29 08:20:55',16,NULL),(4242,13,'EAPB (ENTIDADES ADMINISTRADORAS DE PLANES DE BENEFICIOS DE SALUD) SANITAS. ',17,'2024-02-01 08:25:31',17,NULL),(4243,105,'MANTENIMIENTO DE AIRES ACONDICIONADOS ',16,'2024-02-05 09:05:24',16,NULL),(4244,106,'MANTENIMIENTO DE AIRES ACONDICIONADOS ',16,'2024-02-05 09:08:18',16,NULL),(4245,4,'TÉCNICO JURÍDICA',11,'2024-02-05 14:37:23',11,'2024-02-05 14:59:49'),(4246,107,'PRESTAR EL SERVICIO DE RECOLECCIÓN, TRANSPORTE, TRATAMIENTO Y DISPOSICIÓN FINAL DE RESIDUOS HOSPITALARIOS QUE GENERAL EL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',16,'2024-02-06 18:06:37',16,NULL),(4250,108,'MANTENIMIENTO DE VEHICULOS ',16,'2024-02-16 10:03:27',16,NULL),(4252,110,'MANTENIMIENTO PREVENTIVO Y CORRECTIVO DE LAS PLANTAS ELECTRICAS Y ELECTROBOMBAS, UBICADAS EN LAS INSTALACIONES DEL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E',16,'2024-02-19 10:20:15',16,NULL),(4267,74,'MANTENIMIENTO RED DE OXIGENO ',16,'2024-03-06 09:59:33',16,NULL),(4307,116,'MEDICOS   RURAL',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4308,117,'MEDICOS  URBANO',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4309,118,'PROFESIONAL EN ENFERMERIA   RURAL',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4310,119,'PROFESIONAL EN ENFERMERIA  URBANO',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4311,120,'APOYO A LA SUPERVICIÓN',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4312,121,'ODONTOLOGÍA  URBANO',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4313,121,'TERAPEUTA  URBANO',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4314,121,'NUTIRCIONISTA ',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4315,121,'ODONTOLOGÍA   RURAL',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4316,121,'PSICOLOGÍA   RURAL',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4317,121,'TERAPEUTA   RURAL',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4318,122,'PSICOLOGÍA  URBANO',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4319,123,'COORDINADOR  RURAL',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4320,124,'COORDINADOR  URBANO',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4321,125,'INGENIERO DE SISTEMAS',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4322,126,'APOYO ADMINISTRATIVO 1',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4323,127,'APOYO ADMINISTRATIVO 2',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4324,128,'AUXILIARES DE ENFERMERÍA  URBANO',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4325,128,'AUXILIARES DE ENFERMERÍA   RURAL',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4326,128,'AUXILIAR EN SALUD ORAL URBANO',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4327,128,'AUXILIAR EN SALUD ORAL RURAL',1,'2024-03-08 12:14:53',1,'2024-03-15 10:33:41'),(4423,131,'PRESTAR SERVICIO DE TRANSPORTE ESPECIAL TERRESTRE A TODO COSTO, PARA LA EJECUCIÓN DEL PROGRAMA DE ATENCIÓN PRIMARIA EN SALUD (APS) DESARROLLADO POR EL HOSPITAL DE AGUAZUL JUAN HERNANDO URREGO E.S.E.',16,'2024-03-21 10:04:40',16,NULL),(4468,132,'MENSAJERIA ',16,'2024-04-08 09:59:18',16,NULL),(4537,8,'TECNICO GLOSAS',3,'2024-04-26 10:29:15',3,NULL),(4595,134,'PRESTAR LOS SERVICIOS COMO AUXILIAR EN ARCHIVO, EN EL PROCESO GESTION DOCUMENTAL DEL HOSPITAL DE AGUAZUL JUAN HERNANDO ESE.',16,'2024-05-22 15:29:45',16,NULL),(4611,147,'BACHILLER COMO INTERPRETE EN AMBIENTE EDUCATIVO',1,'2024-06-05 14:22:21',1,NULL),(4612,145,'AUXILIAR DE ENFERMERIA EXPERIENCIA EN ACCIDENTES Y MANEJO DE EXTINTORES',1,'2024-06-05 14:26:25',1,NULL),(4613,146,'AUXILIARES',1,'2024-06-05 14:26:57',1,NULL),(4614,136,'PSICOPEDAGOGO CON SEIS MESES DE EXPERINCIA',1,'2024-06-05 14:27:34',1,NULL),(4615,137,'PROFESIONAL O TECNOLOGO CERTIFICADO MANIPULACIÓN DE ALIMENTOS',1,'2024-06-05 14:28:16',1,NULL),(4616,137,'MEDICO VETERINARIO',1,'2024-06-05 14:28:16',1,NULL),(4617,138,'NUTRICIONISTA CON UN AÑO DE EXPERINCIA',1,'2024-06-05 14:28:38',1,NULL),(4618,139,'MEDICO GENERAL',1,'2024-06-05 14:29:02',1,NULL),(4619,140,'PSICOLOGO O PEDAGOGO CON UN AÑO DE EXPERINCIA',1,'2024-06-05 14:29:28',1,NULL),(4620,140,'FONOAUDIOLOGO CON UN AÑO DE EXPERINCIA',1,'2024-06-05 14:29:55',1,NULL),(4621,141,'MEDICO ESPECIALISTA GINECOLOGÍA',1,'2024-06-05 14:30:15',1,NULL),(4622,142,'PSICOLOGAS O PSICOPEDAGOGO (ZOE)',1,'2024-06-05 14:31:01',1,NULL),(4623,142,'JEFE DE ENFERMERIA',1,'2024-06-05 14:31:01',1,NULL),(4624,143,'TECNICO EN SEGURIDAD VÍAL',1,'2024-06-05 14:32:10',1,NULL),(4625,144,'TECNICO DE SALUD PUBLICA',1,'2024-06-05 14:32:38',1,NULL),(4626,148,'COORDINADOR PIC',1,'2024-06-05 14:35:32',1,NULL),(4721,150,'SOPORTE TECNICO, MANTENIMINETO Y ACTUALIZACION DE SOFTWARE',1,'2024-06-19 08:43:50',1,NULL),(4784,151,'MEDICO GENERAL',1,'2024-07-05 16:04:39',1,NULL),(4785,152,'PROFESIONAL EN PSICOLOGO O  TRABAJO SOCIAL',1,'2024-07-15 18:43:54',1,NULL),(4786,154,'PROFESIONAL EN PSICOLOGÍA Y/O TRABAJO SOCIAL Y/O DESARROLLO FAMILIAR',1,'2024-07-15 18:45:45',1,NULL),(4787,153,'TERMINACIÓN Y APROBACIÓN DE NOVENO BACHILLERATO.',1,'2024-07-15 18:46:50',1,NULL),(4800,9,'REALIZAR APOYO A LA GESTIÓN PARA LOS PROCESO ADMINISTRATIVOS DE LA OFICINA DE GESTIÓN DEL AMBIENTE FISICO Y TECNOLOGICO.',16,'2024-07-22 15:25:03',16,NULL),(4801,156,'REALIZAR APOYO A LA GESTION PARA LOS PROCESO ADMINISTRATIVOS DE LA OFICINA DE GESTIÓN DEL AMBIENTE FISICO Y TECNOLOGICO.',16,'2024-07-22 15:28:27',16,NULL);

INSERT INTO `cronhis`.`ctt_bien_servicio`
	(`id_b_s`,`id_tipo_bn_sv`,`bien_servicio`)
SELECT 
	`id_b_s`,
	CASE 
		WHEN `bd_contablersc`.`seg_bien_servicio`.`id_tipo_bn_sv` = 157 THEN 99
		WHEN `bd_contablersc`.`seg_bien_servicio`.`id_tipo_bn_sv` = 174 THEN 99
		WHEN `bd_contablersc`.`seg_bien_servicio`.`id_tipo_bn_sv` = 159 THEN 20
		WHEN `bd_contablersc`.`seg_bien_servicio`.`id_tipo_bn_sv` = 162 THEN 134
		WHEN `bd_contablersc`.`seg_bien_servicio`.`id_tipo_bn_sv` = 165 THEN 20
		ELSE 99
	END AS `id_tipo_bn_sv`,`bien_servicio` 
FROM `bd_contablersc`.`seg_bien_servicio` WHERE `id_b_s` IN (4807,4811,4812,4832,4832,4842,5022);

INSERT INTO `cronhis`.`ctt_estado_adq`
	(`id`,`descripcion`)
SELECT
	`id`,`descripcion`
FROM `bd_contablersc`.`seg_estado_adq`;

INSERT  INTO`cronhis`.`seg_permisos_modulos`(`id_usuario`,`id_modulo`) VALUES (1,50),(1,51),(1,52),(1,53),(1,54),(1,55),(1,56),(1,57);

INSERT  INTO`cronhis`.`tb_area_c`
	(`id_area`,`area`,`filtro_adq`) 
VALUES (1,'JURIDICA',0),(2,'GESTION DEL AMBIENTE FISICO Y TECNOLOGICO',0),(3,'ADMINISTRATIVA Y FINANCIERA',0),(4,'PLANEACION',0),(5,'OFICINA DE GESTIÓN ÁREA DE LA SALUD',0),(6,'ALMACÉN',1),(7,'ACTIVOS FIJOS',2);

INSERT INTO `cronhis`.`ctt_adquisiciones`
	(`id_adquisicion`,`id_modalidad`,`id_empresa`,`id_sede`,`id_area`,`id_cdp`,`fecha_adquisicion`,`val_contrato`,`vigencia`,`id_tipo_bn_sv`,`obligaciones`,`objeto`,`id_tercero`,`estado`,`id_cont_api`,`id_supervision`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_adquisicion`,`id_modalidad`,1 AS `id_empresa`,`id_sede`,`id_area`
	,CASE
		WHEN `seg_adquisiciones`.`id_cdp` = 1 THEN NULL
		ELSE `seg_adquisiciones`.`id_cdp`
	END AS `id_cdp`
	,`fecha_adquisicion`,`val_contrato`,`vigencia`,`id_tipo_bn_sv`
	,`obligaciones`,`seg_adquisiciones`.`objeto`,`seg_terceros`.`id_tercero_api`,`seg_adquisiciones`.`estado`,`id_cont_api`,`id_supervision`,`seg_usuarios_sistema`.`id_usuario`,`seg_adquisiciones`.`fec_reg`,`seg_usuarios_sistema`.`id_usuario` AS `act`,`seg_adquisiciones`.`fec_act`
FROM `bd_contablersc`.`seg_adquisiciones`
INNER JOIN `cronhis`.`tb_tipo_bien_servicio`
	ON(`cronhis`.`tb_tipo_bien_servicio`.`id_tipo_b_s` = `bd_contablersc`.`seg_adquisiciones`.`id_tipo_bn_sv`)
INNER JOIN `cronhis`.`seg_usuarios_sistema`
	ON (`bd_contablersc`.`seg_adquisiciones`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
INNER JOIN `bd_contablersc`.`seg_terceros`
	ON(`bd_contablersc`.`seg_terceros`.`id_tercero` = `bd_contablersc`.`seg_adquisiciones`.`id_tercero`)
LEFT JOIN `cronhis`.`pto_cdp`
	ON(`bd_contablersc`.`seg_adquisiciones`.`id_cdp` = `cronhis`.`pto_cdp`.`id_pto_cdp`);

INSERT INTO `cronhis`.`ctt_adquisicion_detalles`
	(`id_detalle_adq`,`id_adquisicion`,`id_bn_sv`,`cantidad`,`val_estimado_unid`,`id_user_reg`,`fec_reg`)
SELECT
	`id_detalle_adq`,`seg_detalle_adquisicion`.`id_adquisicion`,`id_bn_sv`,`cantidad`,`val_estimado_unid`
	,`seg_usuarios_sistema`.`id_usuario`,`seg_detalle_adquisicion`.`fec_reg`
FROM `bd_contablersc`.`seg_detalle_adquisicion`
INNER JOIN `cronhis`.`ctt_adquisiciones`
	ON(`bd_contablersc`.`seg_detalle_adquisicion`.`id_adquisicion` = `cronhis`.`ctt_adquisiciones`.`id_adquisicion`)
INNER JOIN `cronhis`.`seg_usuarios_sistema`
	ON (`bd_contablersc`.`seg_detalle_adquisicion`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`);

INSERT INTO `cronhis`.`ctt_destino_contrato`
	(`id_destino`,`id_adquisicion`,`id_area_cc`,`horas_mes`,`id_user_reg`,`fec_reg`)
SELECT
	`seg_destino_contrato`.`id_destino`
	,`seg_destino_contrato`.`id_adquisicion`
	,`far_centrocosto_area`.`id_area`
	,`seg_destino_contrato`.`horas_mes`
	,`seg_usuarios_sistema`.`id_usuario`
	,`seg_destino_contrato`.`fec_reg`
FROM `bd_contablersc`.`seg_destino_contrato`
INNER JOIN `cronhis`.`ctt_adquisiciones`
	ON(`cronhis`.`ctt_adquisiciones`.`id_adquisicion` = `bd_contablersc`.`seg_destino_contrato`.`id_adquisicion`)
INNER JOIN `cronhis`.`seg_usuarios_sistema`
	ON (`bd_contablersc`.`seg_destino_contrato`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
LEFT JOIN `cronhis`.`far_centrocosto_area`
	ON(`bd_contablersc`.`seg_destino_contrato`.`id_centro_costo` = `cronhis`.`far_centrocosto_area`.`id_x_sede`);

INSERT  INTO `cronhis`.`tb_forma_pago_compras`
	(`id_form_pago`,`descripcion`,`fec_reg`) 
VALUES (1,'PAGO PARCIAL','2022-01-21 15:56:33'),(2,'PAGO FINAL','2022-01-21 15:56:42'),(3,'ANTICIPO','2022-01-21 15:56:52');

INSERT INTO `cronhis`.`ctt_estudios_previos`
	(`id_est_prev`,`id_compra`,`fec_ini_ejec`,`fec_fin_ejec`,`val_contrata`,`id_forma_pago`,`id_supervisor`,`necesidad`,`act_especificas`,`prod_entrega`
	,`obligaciones`,`forma_pago`,`num_ds`,`requisitos`,`garantia`,`describe_valor`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_est_prev`,`id_compra`,`fec_ini_ejec`,`fec_fin_ejec`,`val_contrata`,`id_forma_pago`,`id_supervisor`,`necesidad`,`act_especificas`,`prod_entrega`
	,`seg_estudios_previos`.`obligaciones`,`forma_pago`,`num_ds`,`requisitos`,`garantia`,`describe_valor`,`seg_usuarios_sistema`.`id_usuario`
	,`seg_estudios_previos`.`fec_reg`,`seg_usuarios_sistema`.`id_usuario` AS `user_act`,`seg_estudios_previos`.`fec_act`
FROM `bd_contablersc`.`seg_estudios_previos`
INNER JOIN `cronhis`.`seg_usuarios_sistema`
	ON(`cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `bd_contablersc`.`seg_estudios_previos`.`id_user_reg`)
INNER JOIN `cronhis`.`ctt_adquisiciones`
	ON(`bd_contablersc`.`seg_estudios_previos`.`id_compra` = `cronhis`.`ctt_adquisiciones`.`id_adquisicion`);

INSERT INTO `cronhis`.`ctt_contratos`
	(`id_contrato_compra`,`id_compra`,`fec_ini`,`fec_fin`,`val_contrato`,`id_forma_pago`,`id_supervisor`,`id_secop`,`num_contrato`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_contrato_compra`,`id_compra`,`fec_ini`,`fec_fin`,`seg_contrato_compra`.`val_contrato`,`id_forma_pago`,`id_supervisor`,`id_secop`,`num_contrato`
	,`seg_usuarios_sistema`.`id_usuario`,`seg_contrato_compra`.`fec_reg`,`seg_usuarios_sistema`.`id_usuario` AS `user_act`,`seg_contrato_compra`.`fec_act`
FROM `bd_contablersc`.`seg_contrato_compra`
INNER JOIN `cronhis`.`seg_usuarios_sistema`
	ON(`bd_contablersc`.`seg_contrato_compra`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
INNER JOIN `cronhis`.`ctt_adquisiciones`
	ON(`bd_contablersc`.`seg_contrato_compra`.`id_compra` = `cronhis`.`ctt_adquisiciones`.`id_adquisicion`);

UPDATE `bd_contablersc`.`seg_novedad_contrato_adi_pror` SET `id_adq` = NULL WHERE `id_adq` = 0;

INSERT  INTO `cronhis`.`ctt_tipo_novedad`
	(`id_novedad`,`descripcion`) 
VALUES (1,'ADICIÓN'),(2,'PRORROGA'),(3,'ADICIÓN Y PRORROGA '),(4,'CESIÓN'),(5,'SUSPENSIÓN'),(6,'REINICIO'),(7,'TERMINACIÓN'),(8,'LIQUIDACIÓN');

INSERT INTO `cronhis`.`ctt_novedad_adicion_prorroga`
	(`id_nov_con`,`id_tip_nov`,`id_adq`,`val_adicion`,`fec_adcion`,`id_cdp`,`fec_ini_prorroga`,`fec_fin_prorroga`,`observacion`,`id_user_reg`,`fec_reg`)
SELECT
	`id_nov_con`,`id_tip_nov`,`id_adq`,`val_adicion`,`fec_adcion`
	,CASE
		WHEN `cdp` = 0 THEN NULL
		ELSE `cdp`
	END AS `cdp`,`fec_ini_prorroga`,`fec_fin_prorroga`,`observacion`
	,`seg_usuarios_sistema`.`id_usuario`,`seg_novedad_contrato_adi_pror`.`fec_reg`
FROM `bd_contablersc`.`seg_novedad_contrato_adi_pror`
INNER JOIN `cronhis`.`seg_usuarios_sistema`
	ON(`bd_contablersc`.`seg_novedad_contrato_adi_pror`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
INNER JOIN `cronhis`.`ctt_contratos`
	ON (`bd_contablersc`.`seg_novedad_contrato_adi_pror`.`id_adq` = `cronhis`.`ctt_contratos`.`id_contrato_compra`);

INSERT INTO `cronhis`.`ctt_novedad_cesion`
	(`id_cesion`,`id_adq`,`id_tipo_nov`,`id_tercero`,`fec_cesion`,`observacion`,`id_user_reg`,`fec_reg`)
SELECT
	 `id_cesion`,`id_adq`,`id_tipo_nov`,`id_tercero`,`fec_cesion`,`observacion`,`seg_usuarios_sistema`.`id_usuario`,`seg_novedad_contrato_cesion`.`fec_reg`
FROM `bd_contablersc`.`seg_novedad_contrato_cesion`
INNER JOIN `cronhis`.`seg_usuarios_sistema`
	ON(`bd_contablersc`.`seg_novedad_contrato_cesion`.`id_user_reg` = `cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
INNER JOIN `cronhis`.`ctt_contratos`
	ON (`bd_contablersc`.`seg_novedad_contrato_cesion`.`id_adq` = `cronhis`.`ctt_contratos`.`id_contrato_compra`);

INSERT INTO `cronhis`.`nom_causacion`
	(`id_causacion`,`centro_costo`,`id_tipo`,`cuenta`,`detalle`)
SELECT
	`id_causacion`,`centro_costo`,`id_tipo`, `id_pgcp` AS`cuenta`,`detalle`
FROM `bd_contablersc`.`seg_causacion_nomina`
LEFT JOIN `cronhis`.`ctb_pgcp`
	ON (`bd_contablersc`.`seg_causacion_nomina`.`cuenta` = `cronhis`.`ctb_pgcp`.`cuenta`);

INSERT  INTO `cronhis`.`nom_tipo_rubro`
	(`id_rubro`,`nombre`,`fec_act`) 
VALUES (1,'Sueldos','2023-01-28 21:52:11'),(2,'Horas extras y festivos','2023-01-28 21:52:11'),(3,'Gastos de representación','2023-01-28 21:52:11'),(4,'Bonificaciones por recreacion','2023-01-28 21:52:11'),(5,'Bonificaciones por servicios','2023-01-28 21:52:11'),(6,'Auxilio de transporte','2023-01-28 21:52:11'),(7,'Subsidio de alimentación','2023-01-28 21:52:11'),(8,'Incapacidades','2023-01-28 21:52:11'),(9,'Indemnizaciones','2023-01-28 21:52:11'),(10,'Licencias','2023-01-28 21:52:11'),(11,'Aportes a cajas de compensación familiar','2023-01-28 21:52:11'),(12,'Cotizaciones a seguridad social en salud','2023-01-28 21:52:11'),(13,'Cotizaciones a riesgos laborales','2023-01-28 21:52:11'),(14,'Cotizaciones a pension','2023-01-28 21:52:11'),(15,'Aportes al ICBF','2023-01-28 21:52:11'),(16,'Aportes al SENA','2023-01-28 21:52:11'),(17,'Vacaciones','2023-01-28 21:52:11'),(18,'Cesantías','2023-01-28 21:52:11'),(19,'Intereses a las cesantías','2023-01-28 21:52:11'),(20,'Prima de vacaciones','2023-01-28 21:52:11'),(21,'Prima de navidad','2023-01-28 21:52:11'),(22,'Prima de servicios','2023-01-28 21:52:11'),(23,'Viaticos','2023-01-28 21:52:11'),(24,NULL,'2023-01-29 16:26:03'),(25,NULL,'2023-01-29 16:26:24'),(26,'Sindicatos','2023-01-29 16:26:41'),(27,'Fondos de empleados','2023-01-29 16:26:48'),(28,'Libranzas','2023-01-29 16:26:54'),(29,'Embargos judiciales','2023-01-29 16:27:00'),(30,'Retencion en la fuente','2023-01-29 16:27:05'),(32,'Incapacidad Empresa','2023-07-04 15:41:14');

INSERT INTO `cronhis`.`nom_rel_rubro`
	(`id_relacion`,`id_tipo`,`r_admin`,`r_operativo`,`id_vigencia`,`fec_reg`)
SELECT
	`t1`.`id_relacion`
	, `t1`.`id_tipo`
	, `t1`.`id_cargue` AS `r_admin`
	, `t2`.`id_cargue` AS `r_operativo`
	, CASE
		WHEN `t1`.`vigencia` = 2023 THEN 7
		WHEN `t1`.`vigencia` = 2024 THEN 8
		ELSE NULL
	END AS `vigencia`
	, `t1`.`fec_reg`
FROM
	(SELECT
		`id_relacion`,`id_tipo`,`id_cargue`,`vigencia`, `seg_rel_rubro_nomina`.`fec_reg`
	FROM `bd_contablersc`.`seg_rel_rubro_nomina`
	LEFT JOIN `cronhis`.`pto_cargue`
		ON(`cronhis`.`pto_cargue`.`cod_pptal` = `bd_contablersc`.`seg_rel_rubro_nomina`.`r_admin` 
		AND (`cronhis`.`pto_cargue`.`id_pto` = 4 OR `cronhis`.`pto_cargue`.`id_pto` = 5))) AS `t1`
	LEFT JOIN 
	(SELECT
		`id_relacion`,`id_cargue`
	FROM `bd_contablersc`.`seg_rel_rubro_nomina`
	LEFT JOIN `cronhis`.`pto_cargue`
		ON(`cronhis`.`pto_cargue`.`cod_pptal` = `bd_contablersc`.`seg_rel_rubro_nomina`.`r_operativo` 
		AND (`cronhis`.`pto_cargue`.`id_pto` = 4 OR `cronhis`.`pto_cargue`.`id_pto` = 5))) AS `t2`
	ON (`t1`.`id_relacion` = `t2`.`id_relacion`)
ORDER BY `t1`.`id_relacion` ASC;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;