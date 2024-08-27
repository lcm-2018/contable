/* 
Tabla `seg_usuarios_sistema` tiene campo temporal `id_user_fin` que se debe eliminar
Tabla `tb_centrocostos` tiene campo temporal `id_centro_fin` que se debe eliminar
Tabla `far_centrocosto_area` tiene campo temporal `id_x_sede` que se debe eliminar
*/
INSERT  INTO `seg_rol`
	(`id_rol`,`nom_rol`,`id_usr_crea`) 
VALUES (1,'ADMINISTRADOR',NULL),(2,'ADMISION Y CITAS',NULL),(3,'FACTURACION',NULL),(4,'MEDICOS',NULL),(5,'ENFERMERAS',NULL),(6,'LABORATORIO',NULL),(7,'BACTERIOLOGO',NULL),(8,'PSICOLOGO',NULL),(9,'ODONTOLOGOS',NULL),(10,'ESPECIALISTAS MEDICINA',NULL),(11,'FARMACIA',NULL),(12,'NUTRICIONISTA',NULL),(13,'POSCONSULTA',NULL),(14,'PYP',NULL);

INSERT  INTO `seg_rol` (`nom_rol`,`id_usr_crea`) VALUES ('NINGUNO',NULL);
UPDATE `seg_rol` SET `id_rol` = 0 WHERE `nom_rol` = 'NINGUNO';
ALTER TABLE `seg_rol` AUTO_INCREMENT = 1;

INSERT INTO `bd_cronhis`.`seg_usuarios_sistema`
	(`id_user_fin`,`login`,`clave`,`id_rol`,`id_tipo_doc`,`num_documento`,`apellido1`,`apellido2`,`nombre1`,`nombre2`,`email`,`fec_creacion`,`estado`)
SELECT 
	`id_usuario`,`login`,`clave`,`id_rol`,4 AS `doc`, `documento`,`apellido1`,`apellido2`,`nombre1`,`nombre2`,`correo`,`fec_reg`,`estado`
FROM `financiero`.`seg_usuarios`
WHERE `id_usuario` <> 1;

INSERT  INTO `seg_modulos`(`id_modulo`,`nom_modulo`,`fec_mensaje`,`fec_caduca`,`estado`) 
VALUES (10,'Administración',NULL,NULL,1),(11,'Admisiones',NULL,NULL,1),(12,'Facturación',NULL,NULL,1),(13,'Historia Clínica',NULL,NULL,1),(14,'Laboratorio',NULL,NULL,1),(15,'Procedimientos Especializados',NULL,NULL,1),(16,'Farmacia',NULL,NULL,1),(17,'Informes',NULL,NULL,1),(50,'Almacén',NULL,NULL,1),(51,'Nómina',NULL,NULL,1),(52,'Terceros',NULL,NULL,1),(53,'Contratación',NULL,NULL,1),(54,'Presupuesto',NULL,NULL,1),(55,'Contabilidad',NULL,NULL,1),(56,'Tesorería',NULL,NULL,1),(57,'Activos fijos',NULL,NULL,1);

INSERT  INTO `seg_permisos_modulos`(`id_per_mod`,`id_usuario`,`id_modulo`) 
VALUES (1,1,50),(2,1,51),(3,1,52),(4,1,53),(5,1,54),(6,1,55),(7,1,56),(8,1,57);

INSERT INTO `bd_cronhis`.`nom_epss`
	(`id_eps`,`id_tercero_api`,`nombre_eps`,`nit`,`digito_verific`,`telefono`,`correo`,`fec_reg`,`fec_act`)
SELECT
	`id_eps`,`id_tercero_api`,`nombre_eps`,`nit`,`digito_verific`,`telefono`,`correo`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_epss`;

INSERT INTO `bd_cronhis`.`nom_arl`
	(`id_arl`,`id_tercero_api`,`nit_arl`,`dig_ver`,`nombre_arl`,`telefono`,`correo`,`fec_reg`,`fec_act`)
SELECT
	`id_arl`,`id_tercero_api`,`nit_arl`,`dig_ver`,`nombre_arl`,`telefono`,`correo`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_arl`;

INSERT INTO `bd_cronhis`.`nom_afp`
	(`id_afp`,`id_tercero_api`,`nit_afp`,`dig_verf`,`nombre_afp`,`telefono`,`correo`,`fec_reg`,`fec_act`)
SELECT 
	`id_afp`,`id_tercero_api`,`nit_afp`,`dig_verf`,`nombre_afp`,`telefono`,`correo`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_afp`;

INSERT INTO `bd_cronhis`.`nom_fondo_censan`
	(`id_fc`,`id_tercero_api`,`nit_fc`,`dig_verf`,`nombre_fc`,`telefono`,`correo`,`fec_reg`,`fec_act`)
SELECT
	`id_fc`,`id_tercero_api`,`nit_fc`,`dig_verf`,`nombre_fc`,`telefono`,`correo`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_fondo_censan`;

INSERT INTO `bd_cronhis`.`nom_cargo_empleado`
	(`id_cargo`,`codigo`,`descripcion_carg`)
SELECT 
	`id_cargo`,`codigo`,`descripcion_carg`
FROM `financiero`.`seg_cargo_empleado`;

INSERT INTO `bd_cronhis`.`nom_empleado`
	(`id_empleado`,`prefijo`,`sede_emp`,`tipo_empleado`,`subtipo_empleado`,`alto_riesgo_pension`,`tipo_contrato`,`tipo_doc`,`no_documento`,`pais_exp`,`dpto_exp`,`city_exp`,`fec_exp`,`pais_nac`,`dpto_nac`,`city_nac`,`fec_nac`,`genero`,`apellido1`,`apellido2`,`nombre2`,`nombre1`,`fech_inicio`,`fec_retiro`,`salario_integral`,`correo`,`telefono`,`cargo`,`tipo_cargo`,`sub_alimentacion`,`representacion`,`pais`,`departamento`,`municipio`,`direccion`,`id_banco`,`tipo_cta`,`cuenta_bancaria`,`estado`,`fec_reg`,`fec_actu`)
SELECT 
	`id_empleado`,`prefijo`,`sede_emp`,`tipo_empleado`,`subtipo_empleado`,`alto_riesgo_pension`,`tipo_contrato`,`tipo_doc`,`no_documento`,`pais_exp`,`dpto_exp`,`city_exp`,`fec_exp`,`pais_nac`,`dpto_nac`,`city_nac`,`fec_nac`,`genero`,`apellido1`,`apellido2`,`nombre2`,`nombre1`,`fech_inicio`,`fec_retiro`,`salario_integral`,`correo`,`telefono`,`cargo`,`tipo_cargo`,`sub_alimentacion`,`representacion`,`pais`,`departamento`,`municipio`,`direccion`,`id_banco`,`tipo_cta`,`cuenta_bancaria`,`estado`,`fec_reg`,`fec_actu`
FROM `financiero`.`seg_empleado`;

INSERT INTO `bd_cronhis`.`nom_incremento_salario`
	(`id_inc`,`porcentaje`,`vigencia`,`fecha`,`estado`,`fec_reg`,`id_user_reg`,`fec_act`,`id_user_act`)             
SELECT
	`id_inc`,`porcentaje`,`vigencia`,`fecha`,`estado`,`fec_reg`,`id_user_reg`,`fec_act`,`id_user_act`
FROM `financiero`.`seg_incremento_salario`;

INSERT INTO `bd_cronhis`.`nom_salarios_basico`
	(`id_salario`,`id_empleado`,`vigencia`,`salario_basico`,`fec_reg`,`fec_act`,`id_inc`)
SELECT
	`id_salario`,`id_empleado`,`vigencia`,`salario_basico`,`fec_reg`,`fec_act`,`id_inc`
FROM `financiero`.`seg_salarios_basico`;

INSERT INTO `bd_cronhis`.`nom_novedades_eps`
	(`id_novedad`,`id_empleado`,`id_eps`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`)
SELECT 
	`id_novedad`,`id_empleado`,`id_eps`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_novedades_eps`;

INSERT INTO `bd_cronhis`.`nom_novedades_arl`
	(`id_novarl`,`id_empleado`,`id_arl`,`id_riesgo`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`)
SELECT
	`id_novarl`,`id_empleado`,`id_arl`,`id_riesgo`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_novedades_arl`;

INSERT INTO `bd_cronhis`.`nom_novedades_afp`
	(`id_novafp`,`id_empleado`,`id_afp`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`)
SELECT
	`id_novafp`,`id_empleado`,`id_afp`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_novedades_afp`;

INSERT INTO `bd_cronhis`.`nom_novedades_fc`
	(`id_novfc`,`id_empleado`,`id_fc`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`)
SELECT
	`id_novfc`,`id_empleado`,`id_fc`,`fec_afiliacion`,`fec_retiro`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_novedades_fc`;

INSERT INTO `bd_cronhis`.`nom_libranzas`
	(`id_libranza`,`id_banco`,`id_empleado`,`estado`,`descripcion_lib`,`valor_total`,`cuotas`,`val_mes`,`porcentaje`,`fecha_inicio`,`fecha_fin`,`fec_reg`,`fec_act`)
SELECT
	`id_libranza`,`id_banco`,`id_empleado`,`estado`,`descripcion_lib`,`valor_total`,`cuotas`,`val_mes`,`porcentaje`,`fecha_inicio`,`fecha_fin`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_libranzas`;

INSERT INTO `bd_cronhis`.`nom_juzgados`
	(`id_juzgado`,`id_tercero_api`,`nit`,`dig_verf`,`nom_juzgado`,`departamento`,`municipio`,`direcccion`,`correo`,`telefono`,`fec_reg`,`fec_act`)
SELECT 
	`id_juzgado`,`id_tercero_api`,`nit`,`dig_verf`,`nom_juzgado`,`departamento`,`municipio`,`direcccion`,`correo`,`telefono`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_juzgados`;

INSERT INTO `bd_cronhis`.`nom_embargos`
	(`id_embargo`,`id_juzgado`,`id_empleado`,`tipo_embargo`,`valor_total`,`dcto_max`,`valor_mes`,`porcentaje`,`fec_inicio`,`fec_fin`,`estado`,`fec_reg`,`fec_act`)
SELECT
	`id_embargo`,`id_juzgado`,`id_empleado`,`tipo_embargo`,`valor_total`,`dcto_max`,`valor_mes`,`porcentaje`,`fec_inicio`,`fec_fin`,`estado`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_embargos`;

INSERT INTO `bd_cronhis`.`nom_incapacidad`
	(`id_incapacidad`,`id_empleado`,`id_tipo`,`fec_inicio`,`fec_fin`,`can_dias`,`categoria`,`fec_reg`,`fec_act`)
SELECT 
	`id_incapacidad`,`id_empleado`,`id_tipo`,`fec_inicio`,`fec_fin`,`can_dias`,`categoria`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_incapacidad`;

INSERT INTO `bd_cronhis`.`nom_vacaciones`
	(`id_vac`,`id_empleado`,`anticipo`,`fec_inicial`,`fec_inicio`,`fec_fin`,`dias_inactivo`,`dias_habiles`,`corte`,`dias_liquidar`,`estado`,`fec_reg`,`fec_act`)
SELECT 
	`id_vac`,`id_empleado`,`anticipo`,`fec_inicial`,`fec_inicio`,`fec_fin`,`dias_inactivo`,`dias_habiles`,`corte`,`dias_liquidar`,`estado`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_vacaciones`;

INSERT INTO `bd_cronhis`.`nom_horas_ex_trab`
	(`id_he_trab`,`id_empleado`,`id_he`,`fec_inicio`,`fec_fin`,`hora_inicio`,`hora_fin`,`cantidad_he`,`tipo`,`fec_reg`,`fec_actu`)
SELECT
	`id_he_trab`,`id_empleado`,`id_he`,`fec_inicio`,`fec_fin`,`hora_inicio`,`hora_fin`,`cantidad_he`,`tipo`,`fec_reg`,`fec_actu`
FROM `financiero`.`seg_horas_ex_trab`;

INSERT INTO `bd_cronhis`.`nom_retroactivos`
	(`id_retroactivo`,`fec_inicio`,`fec_final`,`meses`,`id_incremento`,`observaciones`,`estado`,`vigencia`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT 
	`id_retroactivo`,`fec_inicio`,`fec_final`,`meses`,`id_incremento`,`observaciones`,`estado`,`vigencia`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`
FROM `financiero`.`seg_retroactivos`;

INSERT INTO `bd_cronhis`.`nom_nominas`
	(`id_nomina`,`descripcion`,`mes`,`vigencia`,`tipo`,`estado`,`planilla`,`id_incremento`,`fec_reg`,`id_user_reg`,`fec_act`)
SELECT
	`id_nomina`,`descripcion`,`mes`,`vigencia`,`tipo`,`estado`,`planilla`,`id_incremento`,`fec_reg`,`id_user_reg`,`fec_act`
FROM `financiero`.`seg_nominas`
WHERE `id_nomina` > 0;

INSERT INTO `bd_cronhis`.`nom_nominas`
	(`descripcion`,`mes`,`vigencia`,`tipo`,`estado`,`planilla`,`id_incremento`,`fec_reg`,`id_user_reg`,`fec_act`)
VALUES('INICIAL',NULL,NULL,'N',5,5,NULL,NULL,25,NULL);

UPDATE `bd_cronhis`.`nom_nominas` SET `id_nomina` = 0 WHERE `descripcion` = 'INICIAL';

ALTER TABLE `nom_nominas` AUTO_INCREMENT = 1;

INSERT INTO `bd_cronhis`.`nom_liq_bsp`
	(`id_bonificaciones`,`id_empleado`,`val_bsp`,`id_user_reg`,`mes`,`anio`,`fec_reg`,`id_nomina`)
SELECT 
	`id_bonificaciones`,`id_empleado`,`val_bsp`,`id_user_reg`,`mes`,`anio`,`fec_reg`,`id_nomina`
FROM `financiero`.`seg_liq_bsp`;

INSERT INTO `bd_cronhis`.`nom_liq_cesantias`
	(`id_liq_cesan`,`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`corte`,`anio`,`salbase`,`gasrep`,`auxt`,`auxali`,`promHorExt`,`bspant`,`primserant`,`primavacant`,`primanavant`,`diasToCes`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
	`id_liq_cesan`,`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`corte`,`anio`,`salbase`,`gasrep`,`auxt`,`auxali`,`promHorExt`,`bspant`,`primserant`,`primavacant`,`primanavant`,`diasToCes`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_cesantias`;



INSERT INTO  `bd_cronhis`.`nom_liq_compesatorio`
    (`id_compensa`,`id_empleado`,`val_compensa`,`dias`,`estado`,`fec_reg`,`id_nomina`)
SELECT
    `id_compensa`,`id_empleado`,`val_compensa`,`dias`,`estado`,`fec_reg`,`id_nomina`
FROM `financiero`.`seg_liq_compesatorio`;

INSERT INTO  `bd_cronhis`.`nom_liq_dias_lab`
    (`id_diatrab`,`id_empleado`,`id_contrato`,`cant_dias`,`mes`,`anio`,`liq_vac`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_diatrab`,`id_empleado`,`id_contrato`,`cant_dias`,`mes`,`anio`,`liq_vac`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_dias_lab`;

INSERT INTO  `bd_cronhis`.`nom_liq_dlab_auxt`
    (`id_liq_dlab_auxt`,`id_empleado`,`dias_liq`,`val_liq_dias`,`val_liq_auxt`,`aux_alim`,`g_representa`,`horas_ext`,`mes_liq`,`anio_liq`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_dlab_auxt`,`id_empleado`,`dias_liq`,`val_liq_dias`,`val_liq_auxt`,`aux_alim`,`g_representa`,`horas_ext`,`mes_liq`,`anio_liq`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_dlab_auxt`;

INSERT INTO  `bd_cronhis`.`nom_liq_embargo`
    (`id_liq_embargo`,`id_embargo`,`val_mes_embargo`,`mes_embargo`,`anio_embargo`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_embargo`,`id_embargo`,`val_mes_embargo`,`mes_embargo`,`anio_embargo`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_embargo`;

INSERT INTO  `bd_cronhis`.`nom_liq_empleado`
    (`id_liq`,`id_empleado`,`corte`,`no_resolucion`,`fec_inicio`,`fec_fin`,`sal_base`,`vigencia`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
    `id_liq`,`id_empleado`,`corte`,`no_resolucion`,`fec_inicio`,`fec_fin`,`sal_base`,`vigencia`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`
FROM `financiero`.`seg_liq_empleado`;

INSERT INTO  `bd_cronhis`.`nom_liq_horex`
    (`id_liq_he`,`id_he_lab`,`val_liq`,`mes_he`,`anio_he`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_he`,`id_he_lab`,`val_liq`,`mes_he`,`anio_he`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_horex`;

INSERT INTO  `bd_cronhis`.`nom_liq_incap`
    (`id_liq_incap`,`id_incapacidad`,`id_eps`,`id_arl`,`fec_inicio`,`fec_fin`,`dias_liq`,`pago_empresa`,`pago_eps`,`pago_arl`,`mes`,`anios`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_incap`,`id_incapacidad`,`id_eps`,`id_arl`,`fec_inicio`,`fec_fin`,`dias_liq`,`pago_empresa`,`pago_eps`,`pago_arl`,`mes`,`anios`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_incap`;

INSERT INTO  `bd_cronhis`.`nom_liq_libranza`
    (`id_lid_lib`,`id_libranza`,`val_mes_lib`,`mes_lib`,`anio_lib`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_lid_lib`,`id_libranza`,`val_mes_lib`,`mes_lib`,`anio_lib`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_libranza`;

INSERT INTO  `bd_cronhis`.`nom_liq_parafiscales`
    (`id_liq_pfis`,`id_empleado`,`val_sena`,`val_icbf`,`val_comfam`,`mes_pfis`,`anio_pfis`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_pfis`,`id_empleado`,`val_sena`,`val_icbf`,`val_comfam`,`mes_pfis`,`anio_pfis`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_parafiscales`;

INSERT INTO  `bd_cronhis`.`nom_liq_prestaciones_sociales`
    (`id_liqpresoc`,`id_empleado`,`id_contrato`,`val_vacacion`,`val_cesantia`,`val_interes_cesantia`,`val_prima`,`val_prima_vac`,`val_prima_nav`,`val_bonifica_recrea`,`mes_prestaciones`,`anio_prestaciones`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liqpresoc`,`id_empleado`,`id_contrato`,`val_vacacion`,`val_cesantia`,`val_interes_cesantia`,`val_prima`,`val_prima_vac`,`val_prima_nav`,`val_bonifica_recrea`,`mes_prestaciones`,`anio_prestaciones`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_prestaciones_sociales`;

INSERT INTO  `bd_cronhis`.`nom_liq_prima`
    (`id_liq_prima`,`id_empleado`,`cant_dias`,`val_liq_ps`,`val_liq_pns`,`periodo`,`corte`,`anio`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_prima`,`id_empleado`,`cant_dias`,`val_liq_ps`,`val_liq_pns`,`periodo`,`corte`,`anio`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_prima`;

INSERT INTO  `bd_cronhis`.`nom_liq_prima_nav`
    (`id_liq_privac`,`id_empleado`,`cant_dias`,`val_liq_pv`,`val_liq_pnv`,`periodo`,`corte`,`anio`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_privac`,`id_empleado`,`cant_dias`,`val_liq_pv`,`val_liq_pnv`,`periodo`,`corte`,`anio`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_prima_nav`;

INSERT INTO  `bd_cronhis`.`nom_liq_salario`
    (`id_sal_liq`,`id_empleado`,`forma_pago`,`metodo_pago`,`val_liq`,`mes`,`anio`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_sal_liq`,`id_empleado`,`forma_pago`,`metodo_pago`,`val_liq`,`mes`,`anio`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_salario`;

INSERT INTO  `bd_cronhis`.`nom_liq_segsocial_empdo`
    (`id_liq_empdo`,`id_empleado`,`id_eps`,`id_arl`,`id_afp`,`aporte_salud_emp`,`aporte_pension_emp`,`aporte_solidaridad_pensional`,`porcentaje_ps`,`aporte_salud_empresa`,`aporte_pension_empresa`,`aporte_rieslab`,`mes`,`anio`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_empdo`,`id_empleado`,`id_eps`,`id_arl`,`id_afp`,`aporte_salud_emp`,`aporte_pension_emp`,`aporte_solidaridad_pensional`,`porcentaje_ps`,`aporte_salud_empresa`,`aporte_pension_empresa`,`aporte_rieslab`,`mes`,`anio`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_segsocial_empdo`;

INSERT INTO  `bd_cronhis`.`nom_liq_vac`
    (`id_liq_vac`,`id_vac`,`id_contrato`,`fec_inicio`,`fec_fin`,`dias_liqs`,`val_liq`,`val_diavac`,`val_bsp`,`val_prima_vac`,`val_bon_recrea`,`mes_vac`,`anio_vac`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`)
SELECT
    `id_liq_vac`,`id_vac`,`id_contrato`,`fec_inicio`,`fec_fin`,`dias_liqs`,`val_liq`,`val_diavac`,`val_bsp`,`val_prima_vac`,`val_bon_recrea`,`mes_vac`,`anio_vac`,`tipo_liq`,`fec_reg`,`fec_act`,`id_nomina`
FROM `financiero`.`seg_liq_vac`;

INSERT INTO `bd_cronhis`.`nom_valxvigencia`
	(`id_valxvig`,`id_vigencia`,`id_concepto`,`valor`,`fec_reg`,`fec_act`)
SELECT
	`id_valxvig`,`id_vigencia`,`id_concepto`,`valor`,`fec_reg`,`fec_act`
FROM `financiero`.`seg_valxvigencia`;

INSERT INTO `bd_cronhis`.`nom_soporte_ne`
	(`id_soporte`,`id_empleado`,`shash`,`referencia`,`mes`,`anio`,`id_user_reg`,`fec_reg`)
SELECT
	`id_soporte`,`id_empleado`,`shash`,`referencia`,`mes`,`anio`,`id_user_reg`,`fec_reg`
FROM `financiero`.`seg_soporte_ne`;

INSERT INTO `bd_cronhis`.`tb_terceros`
	(`nom_tercero`,`nit_tercero`,`dir_tercero`,`tel_tercero`,`id_municipio`,`email`,`id_tercero_api`)
SELECT
    TRIM(
        CONCAT_WS(
            ' ',
            TRIM(`tb_terceros_api`.`nombre1`),
            TRIM(`tb_terceros_api`.`nombre2`),
            TRIM(`tb_terceros_api`.`apellido1`),
            TRIM(`tb_terceros_api`.`apellido2`),
            TRIM(`tb_terceros_api`.`razon_social`)
        )
    ) AS `nombre` 
    , `financiero`.`tb_terceros_api`.`cc_nit`
    , `financiero`.`tb_terceros_api`.`direccion`
    , `financiero`.`tb_terceros_api`.`telefono`
    , `financiero`.`tb_terceros_api`.`municipio`
    , `financiero`.`tb_terceros_api`.`correo`
    , `financiero`.`tb_terceros_api`.`id_tercero`
FROM
    `financiero`.`seg_terceros`
    INNER JOIN `financiero`.`tb_terceros_api` 
        ON (`seg_terceros`.`id_tercero_api` = `tb_terceros_api`.`id_tercero`);

		
INSERT INTO `bd_cronhis`.`seg_terceros`
	(`id_tercero`,`id_tercero_api`,`tipo_doc`,`no_doc`,`estado`,`fec_inicio`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_tercero`,`id_tercero_api`,`tipo_doc`,`no_doc`,`estado`,`fec_inicio`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`
FROM `financiero`.`seg_terceros`;

INSERT INTO `bd_cronhis`.`tb_tipo_tercero`
            (`id_tipo`,`descripcion`)
SELECT 
	`id_tipo`,`descripcion`
FROM `financiero`.`seg_tipo_tercero`;

INSERT INTO `bd_cronhis`.`tb_rel_tercero`
	(`id_relacion`,`id_tercero_api`,`id_tipo_tercero`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_relacion`,`id_tercero_api`,`id_tipo_tercero`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`
FROM `financiero`.`rel_tipo_tercero`;

INSERT INTO `bd_cronhis`.`ctt_modalidad`
	(`id_modalidad`,`modalidad`,`id_user_reg`,`fec_reg`)
SELECT 
	`id_modalidad`,`modalidad`,`id_user_reg`,`fec_reg`
FROM `financiero`.`seg_modalidad_contrata`;

INSERT INTO `bd_cronhis`.`pto_presupuestos`
	(`id_pto`,`id_tipo`,`id_vigencia`,`nombre`,`descripcion`,`estado`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT 
	`id_pto_presupuestos`,`id_pto_tipo`,
	CASE
		WHEN `vigencia` = 2023 THEN 7
		WHEN `vigencia` = 2024 THEN 8
	END,`nombre`,`descripcion`,0 AS `est`,`id_user_reg`,`fec_reg`,`id_usuer_act`,`fec_act`
FROM `financiero`.`seg_pto_presupuestos`;

INSERT INTO `bd_cronhis`.`pto_cargue`
	(`id_cargue`,`id_pto`,`id_tipo_recurso`,`cod_pptal`,`nom_rubro`,`tipo_dato`,`valor_aprobado`,`tipo_pto`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_pto_cargue`,`id_pto_presupuestos`,1 AS `recurso`,`cod_pptal`,`nom_rubro`,`tipo_dato`,`ppto_aprob`,`id_tipo_recurso`,`id_user_reg`,`fec_reg`,`id_usuer_act`,`fec_act`
FROM `financiero`.`seg_pto_cargue`;

INSERT INTO `bd_cronhis`.`pto_mod` 
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
    `bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`,
    `fec_reg`,
    `id_user_act`,
    `fec_act`
FROM `financiero`.`seg_pto_documento`
LEFT JOIN `bd_cronhis`.`seg_usuarios_sistema`
    ON (`bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `financiero`.`seg_pto_documento`.`id_user_reg`)
WHERE `tipo_doc` = 'ADI' OR `tipo_doc` = 'RED' OR `tipo_doc` = 'TRA';

INSERT INTO `bd_cronhis`.`pto_mod_detalle`
	(`id_pto_mod`,`id_cargue`,`valor_deb`,`valor_cred`)
SELECT
	`bd_cronhis`.`pto_mod`.`id_pto_mod`
	, `bd_cronhis`.`pto_cargue`.`id_cargue`
	, CASE
		WHEN `financiero`.`seg_pto_mvto`.`mov` = 0 THEN `financiero`.`seg_pto_mvto`.`valor`
		ELSE 0
	  END AS `debito`
	, CASE
		WHEN `financiero`.`seg_pto_mvto`.`mov` = 1 THEN `financiero`.`seg_pto_mvto`.`valor`
		ELSE 0
	  END AS `credito`
FROM `bd_cronhis`.`pto_mod`
INNER JOIN  `financiero`.`seg_pto_mvto`
	ON(`bd_cronhis`.`pto_mod`.`id_pto_mod` = `financiero`.`seg_pto_mvto`.`id_pto_doc`)
INNER JOIN `bd_cronhis`.`pto_cargue`
	ON(`bd_cronhis`.`pto_cargue`.`id_pto` = `bd_cronhis`.`pto_mod`.`id_pto`)
WHERE `financiero`.`seg_pto_mvto`.`rubro` = `bd_cronhis`.`pto_cargue`.`cod_pptal`;

INSERT INTO `bd_cronhis`.`pto_cdp`
	(`id_pto_cdp`,`id_pto`,`fecha`,`id_manu`,`objeto`,`num_solicitud`,`estado`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`)
SELECT
	`id_pto_doc`,`id_pto_presupuestos`,`fecha`,`id_manu`,`objeto`,`num_solicitud`,
	CASE
		WHEN `seg_pto_documento`.`estado` = 0 THEN 2
		WHEN `seg_pto_documento`.`estado` = 5 THEN 0
		ELSE 1
	END AS `estado`,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`, `fec_reg`,`id_user_reg`,`fec_act`
FROM `financiero`.`seg_pto_documento`
LEFT JOIN `bd_cronhis`.`seg_usuarios_sistema`
    ON (`bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `financiero`.`seg_pto_documento`.`id_user_reg`)
WHERE `tipo_doc` = 'CDP' OR `tipo_doc` = 'LDP';

INSERT INTO `bd_cronhis`.`pto_cdp_detalle`
	(`id_pto_cdp`,`id_rubro`,`valor`,`valor_liberado`)
	SELECT 
		`id_pto_cdp`
		, `id_cargue`
		, SUM(`valor`) AS `valor`
		, SUM(`liberado`) AS `liberado`
	FROM 
		(SELECT
			`bd_cronhis`.`pto_cdp`.`id_pto_cdp`
			, `bd_cronhis`.`pto_cargue`.`id_cargue`
			, CASE
				WHEN `financiero`.`seg_pto_mvto`.`tipo_mov` = 'CDP' THEN `financiero`.`seg_pto_mvto`.`valor`
				ELSE 0
			END AS `valor`
			, CASE
				WHEN `financiero`.`seg_pto_mvto`.`tipo_mov` = 'LCD' THEN `financiero`.`seg_pto_mvto`.`valor` *(-1)
				ELSE 0
			END AS `liberado`
		FROM `bd_cronhis`.`pto_cdp`
		INNER JOIN  `financiero`.`seg_pto_mvto`
			ON(`bd_cronhis`.`pto_cdp`.`id_pto_cdp` = `financiero`.`seg_pto_mvto`.`id_pto_doc`)
		INNER JOIN `bd_cronhis`.`pto_cargue`
			ON(`bd_cronhis`.`pto_cargue`.`id_pto` = `bd_cronhis`.`pto_cdp`.`id_pto`)
	WHERE `financiero`.`seg_pto_mvto`.`rubro` = `bd_cronhis`.`pto_cargue`.`cod_pptal`) AS `taux`
GROUP BY `taux`.`id_pto_cdp`, `taux`.`id_cargue`;

INSERT INTO `bd_cronhis`.`pto_crp`
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
	END AS `estado`,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`, `fec_reg`,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`,`fec_act`
FROM `financiero`.`seg_pto_documento`
LEFT JOIN `bd_cronhis`.`seg_usuarios_sistema`
    ON (`bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `financiero`.`seg_pto_documento`.`id_user_reg`)
WHERE `tipo_doc` = 'CRP' OR `tipo_doc` = 'LRP';

INSERT INTO `bd_cronhis`.`pto_crp_detalle`
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
		FROM `financiero`.`seg_pto_mvto` 
		INNER JOIN `financiero`.`seg_pto_documento`
			ON(`seg_pto_documento`.`id_pto_doc` = `seg_pto_mvto`.`id_pto_doc`)
			
		WHERE `financiero`.`seg_pto_mvto`.`tipo_mov` = 'CRP' OR `financiero`.`seg_pto_mvto`.`tipo_mov` = 'LRP') AS `t1`
	LEFT JOIN
		(SELECT 
			`pto_cargue`.`id_cargue`
			, `pto_cargue`.`id_pto`
			, `pto_cargue`.`cod_pptal`
		FROM `bd_cronhis`.`pto_cargue`) AS `t2` 
		ON(`t1`.`id_pto_presupuestos` = `t2`.`id_pto` AND `t1`.`rubro` = `t2`.`cod_pptal`)
	LEFT JOIN 
		(SELECT
			`pto_cdp_detalle`.`id_pto_cdp_det`
			, `pto_cdp_detalle`.`id_pto_cdp`
			,`pto_cdp_detalle`.`id_rubro`
		FROM `bd_cronhis`.`pto_cdp_detalle`) AS `t3`
		ON(`t1`.`id_auto_dep` = `t3`.`id_pto_cdp` AND `t2`.`id_cargue` = `t3`.`id_rubro`)) AS `tb`
GROUP BY `id_pto_doc`,`id_pto_cdp_det`,`id_tercero_api`;

INSERT INTO `bd_cronhis`.`ctb_doc`
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
	END AS `estado`,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`,`fec_reg`,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario` AS `user_act`,`fec_act`
FROM `financiero`.`seg_ctb_doc`
LEFT JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON(`financiero`.`seg_ctb_doc`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
LEFT JOIN 
	(SELECT 
		`id_ctb_doc`,`id_crp` 
	FROM 
		(SELECT `id_ctb_doc`,`id_crp` 
		FROM `financiero`.`seg_ctb_libaux`
		ORDER BY `id_crp` DESC) AS `t1`
	GROUP BY `id_ctb_doc`) AS `taux`
	ON(`seg_ctb_doc`.`id_ctb_doc` = `taux`.`id_ctb_doc`)) AS `tt`;

UPDATE `bd_cronhis`.`ctb_doc` SET `id_tercero` = NULL WHERE `id_tercero` = 0;

INSERT INTO `bd_cronhis`.`ctb_pgcp`
	(`id_pgcp`,`fecha`,`cuenta`,`nombre`,`tipo_dato`,`estado`,`id_user_reg`,`fec_reg`,`id_usuer_act`,`fec_act`)
SELECT
	`id_pgcp`,`fecha`,`cuenta`,`nombre`,`tipo_dato`
	, CASE
		WHEN `seg_ctb_pgcp`.`estado` =  0 THEN 1
		ELSE 0
	END AS `estado`
	,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`,`fec_reg`,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`,`fec_act`
FROM `financiero`.`seg_ctb_pgcp`
LEFT JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON(`financiero`.`seg_ctb_pgcp`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`);


INSERT INTO `bd_cronhis`.`ctb_libaux`
	(`id_ctb_libaux`,`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`)
SELECT
	`id_ctb_libaux`,`id_ctb_doc`,`id_tercero`,`bd_cronhis`.`ctb_pgcp`.`id_pgcp`,`debito`,`credito`,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`,`seg_ctb_libaux`.`fec_reg`,`bd_cronhis`.`seg_usuarios_sistema`.`id_usuario`,`seg_ctb_libaux`.`fec_act`
FROM `financiero`.`seg_ctb_libaux`
LEFT JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON(`financiero`.`seg_ctb_libaux`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
LEFT JOIN `bd_cronhis`.`ctb_pgcp`
	ON (`bd_cronhis`.`ctb_pgcp`.`cuenta` = `financiero`.`seg_ctb_libaux`.`cuenta`);

INSERT INTO `bd_cronhis`.`fin_maestro_doc`
	(`id_maestro`,`id_proceso`,`prefijo`,`codigo_doc`,`nombre`,`version_doc`,`fecha_doc`,`consecutivo`,`contador`,`estado`,`control_doc`)
SELECT
	`id_maestro`,`id_proceso`, NULL AS `prefijo`,`codigo_doc`,`nombre`,`version_doc`,`fecha_doc`,`consecutivo`,1 AS `contador`,`estado`,`control_doc`
FROM `financiero`.`seg_fin_maestro_doc`;

INSERT INTO `bd_cronhis`.`ctb_factura`
	(`id_cta_factura`,`id_ctb_doc`,`id_tipo_doc`,`num_doc`,`fecha_fact`,`fecha_ven`,`valor_pago`,`valor_iva`,`valor_base`,`detalle`,`id_user_reg`,`fec_rec`,`id_user_act`,`fec_act`)       
SELECT
	`id_cta_factura`,`id_ctb_doc`,`tipo_doc`,`num_doc`,`fecha_fact`,`fecha_ven`,`valor_pago`,`valor_iva`,`valor_base`,`detalle`,`id_user_reg`,`fec_rec`,`id_user_act`,`fec_act`
FROM `financiero`.`seg_ctb_factura`;

INSERT INTO `bd_cronhis`.`pto_cop_detalle`
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
	FROM `financiero`.`seg_pto_mvto`
	INNER JOIN `financiero`.`seg_pto_documento`
		ON(`financiero`.`seg_pto_mvto`.`id_pto_doc` = `financiero`.`seg_pto_documento`.`id_pto_doc`)
	WHERE `seg_pto_mvto`.`tipo_mov` = 'COP'
	GROUP BY `seg_pto_mvto`.`id_pto_doc`,`seg_pto_mvto`.`id_ctb_doc`,`seg_pto_mvto`.`id_tercero_api`,`seg_pto_mvto`.`rubro`) AS `taux`
	LEFT JOIN 
		(SELECT 
			`pto_cargue`.`id_cargue`
			, `pto_cargue`.`id_pto`
			, `pto_cargue`.`cod_pptal`
		FROM `bd_cronhis`.`pto_cargue`) AS `t1`
		ON(`taux`.`id_pto_presupuestos` = `t1`.`id_pto` AND `taux`.`rubro` = `t1`.`cod_pptal`)
	LEFT JOIN 
		(SELECT
			`pto_cargue`.`id_cargue`
			, `pto_crp_detalle`.`id_pto_crp_det`
			, `pto_crp_detalle`.`id_pto_crp`
			, IFNULL(`pto_crp_detalle`.`id_tercero_api`,0) AS `id_tercero_api`
		FROM
			`bd_cronhis`.`pto_crp_detalle`
		INNER JOIN `bd_cronhis`.`pto_cdp_detalle` 
			ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
		INNER JOIN `bd_cronhis`.`pto_cargue` 
			ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)) AS `t2`
		ON(`t1`.`id_cargue` = `t2`.`id_cargue` 
			AND `t2`.`id_pto_crp` = `taux`.`id_pto_doc` 
			AND `t2`.`id_tercero_api` = `taux`.`id_tercero_api`);

INSERT INTO `bd_cronhis`.`ctb_causa_costos`
	(`id`,`id_ctb_doc`,`id_area_cc`,`id_cc`,`valor`,`id_user_reg`,`fecha_reg`,`id_user_act`,`fecha_act`,`estado`)
SELECT
	`id`,`id_ctb_doc`,94 AS `ids`,`id_cc`,`valor`,`seg_usuarios_sistema`.`id_usuario`,`fecha_reg`,`seg_usuarios_sistema`.`id_usuario`,`fecha_act`
	, CASE
        WHEN `seg_ctb_causa_costos`.`estado` = 0 THEN 2
        WHEN `seg_ctb_causa_costos`.`estado` = 5 THEN 0
        ELSE 1
    END AS `estado`
FROM `financiero`.`seg_ctb_causa_costos`
LEFT JOIN `bd_cronhis`.`seg_usuarios_sistema`
    ON (`bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `financiero`.`seg_ctb_causa_costos`.`id_user_reg`);

INSERT INTO `bd_cronhis`.`ctb_retencion_rango`
	(`id_rango`,`id_vigencia`,`id_retencion`,`valor_base`,`valor_tope`,`tarifa`,`estado`)
SELECT
	`id_rango`,`id_vigencia`,`id_retencion`,`valor_base`,`valor_tope`,`tarifa`,1 AS`estado`
FROM `financiero`.`seg_ctb_retencion_rango`
INNER JOIN `tb_vigencias`
	ON (`tb_vigencias`.`anio` = `seg_ctb_retencion_rango`.`vigencia`);

INSERT INTO `bd_cronhis`.`ctb_causa_retencion`
	(`id_causa_retencion`,`id_ctb_doc`,`id_rango`,`valor_base`,`tarifa`,`valor_retencion`,`id_terceroapi`)
SELECT
	`id_causa_retencion`,`id_ctb_doc`,`seg_ctb_retencion_rango`.`id_rango`,`seg_ctb_causa_retencion`.`valor_base`,`seg_ctb_causa_retencion`.`tarifa`,`seg_ctb_causa_retencion`.`valor_retencion`,`id_terceroapi`
FROM `financiero`.`seg_ctb_causa_retencion`
LEFT JOIN `financiero`.`seg_ctb_retencion_rango`
	ON(`seg_ctb_retencion_rango`.`id_retencion` = `seg_ctb_causa_retencion`.`id_retencion`);

INSERT INTO `bd_cronhis`.`pto_pag_detalle`
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
	FROM `financiero`.`seg_pto_mvto`
	INNER JOIN `financiero`.`seg_pto_documento`
		ON(`financiero`.`seg_pto_mvto`.`id_pto_doc` = `financiero`.`seg_pto_documento`.`id_pto_doc`)
	WHERE `seg_pto_mvto`.`tipo_mov` = 'PAG'
	GROUP BY `seg_pto_mvto`.`id_pto_doc`,`seg_pto_mvto`.`id_ctb_doc`,`seg_pto_mvto`.`id_tercero_api`,`seg_pto_mvto`.`rubro`) AS `taux`
	LEFT JOIN 
		(SELECT 
			`pto_cargue`.`id_cargue`
			, `pto_cargue`.`id_pto`
			, `pto_cargue`.`cod_pptal`
		FROM `bd_cronhis`.`pto_cargue`) AS `t1`
		ON(`taux`.`id_pto_presupuestos` = `t1`.`id_pto` AND `taux`.`rubro` = `t1`.`cod_pptal`)
	LEFT JOIN 
		(SELECT
			`pto_cop_detalle`.`id_ctb_doc`
			, `pto_cop_detalle`.`id_pto_cop_det`
			, `pto_cop_detalle`.`id_pto_crp_det`
			, `pto_cargue`.`id_cargue`
			, IFNULL(`pto_cop_detalle`.`id_tercero_api`,0) AS `id_tercero_api`
		FROM
				`bd_cronhis`.`pto_cop_detalle`
		INNER JOIN `bd_cronhis`.`pto_crp_detalle` 
			ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
		INNER JOIN `bd_cronhis`.`pto_cdp_detalle` 
			ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
		INNER JOIN `bd_cronhis`.`pto_cargue` 
			ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)) AS `t2`
		ON(`t1`.`id_cargue` = `t2`.`id_cargue` 
			AND `t2`.`id_ctb_doc` = `taux`.`id_ctb_cop` 
			AND `t2`.`id_tercero_api` = `taux`.`id_tercero_api`)
WHERE `taux`.`id_ctb_cop` <> 0;

INSERT INTO `bd_cronhis`.`tes_cuentas`
	(`id_tes_cuenta`,`id_banco`,`id_tipo_cuenta`,`id_cuenta`,`nombre`,`numero`,`estado`,`id_user_reg`,`fecha_reg`)
SELECT
	`seg_tes_cuentas`.`id_tes_cuenta`,`seg_tes_cuentas`.`id_banco`,`seg_tes_cuentas`.`id_tipo_cuenta`,`ctb_pgcp`.`id_pgcp`,`seg_tes_cuentas`.`nombre`,`seg_tes_cuentas`.`numero`
	,CASE
		WHEN `seg_tes_cuentas`.`estado` = 1 THEN 0
		WHEN `seg_tes_cuentas`.`estado` = 0 THEN 1
		ELSE 0
	END AS `estado`
	,`seg_tes_cuentas`.`id_user_reg`,`seg_tes_cuentas`.`fecha_reg`
FROM `financiero`.`seg_tes_cuentas`
LEFT JOIN `bd_cronhis`.`ctb_pgcp`
	ON(`seg_tes_cuentas`.`cta_contable` = `ctb_pgcp`.`cuenta`);

INSERT INTO `bd_cronhis`.`tes_detalle_pago`
	(`id_detalle_pago`,`id_ctb_doc`,`id_tes_cuenta`,`id_forma_pago`,`documento`,`valor`,`id_user_reg`,`fecha_reg`)
SELECT
	`id_detalle_pago`,`id_ctb_doc`,`id_tes_cuenta`,`id_forma_pago`,`documento`,`valor`,`seg_usuarios_sistema`.`id_usuario`,`fecha_reg`
FROM `financiero`.`seg_tes_detalle_pago`
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON (`seg_tes_detalle_pago`.`id_user_reg` = `seg_usuarios_sistema`.`id_user_fin`);

INSERT INTO `bd_cronhis`.`tb_tipo_contratacion`
	(`id_tipo`,`id_tipo_compra`,`tipo_contrato`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
VALUES
(1,2,"PRESTACION DE SERVICIOS",NULL,NULL,NULL,NULL),
(2,2,"OTROS SERVICIOS",NULL,NULL,NULL,NULL);

INSERT INTO `bd_cronhis`.`tb_tipo_bien_servicio`
	(`id_tipo_b_s`,`id_tipo_cotrato`,`tipo_bn_sv`,`cta_contable`,`objeto_definido`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_tipo_b_s`,1 AS `tipo_contrato`,`tipo_bn_sv`,`cta_contable`,`objeto_definido`,`seg_usuarios_sistema`.`id_usuario`,`fec_reg`,`seg_usuarios_sistema`.`id_usuario` AS `act`,`fec_act`
FROM `financiero`.`seg_tipo_bien_servicio`
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON (`seg_tipo_bien_servicio`.`id_user_reg` = `seg_usuarios_sistema`.`id_user_fin`)
WHERE `seg_tipo_bien_servicio`.`id_tipo_cotrato` IN (53,52,10,43,44,47,51,26,36,33,31,30,45,56)
UNION ALL
SELECT
	`id_tipo_b_s`,1 AS `tipo_contrato`,`tipo_bn_sv`,`cta_contable`,`objeto_definido`,`seg_usuarios_sistema`.`id_usuario`,`fec_reg`,`seg_usuarios_sistema`.`id_usuario` AS `act`,`fec_act`
FROM `financiero`.`seg_tipo_bien_servicio`
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON (`seg_tipo_bien_servicio`.`id_user_reg` = `seg_usuarios_sistema`.`id_user_fin`)
WHERE `seg_tipo_bien_servicio`.`id_tipo_cotrato` IN (27,34,46,42,18,50,16,21,28,49,55);

INSERT INTO `bd_cronhis`.`ctt_estado_adq`
	(`id`,`descripcion`)
SELECT
	`id`,`descripcion`
FROM `financiero`.`seg_estado_adq`;

INSERT INTO `bd_cronhis`.`ctt_adquisiciones`
	(`id_adquisicion`,`id_modalidad`,`id_empresa`,`id_sede`,`id_area`,`id_cdp`,`fecha_adquisicion`,`val_contrato`,`vigencia`,`id_tipo_bn_sv`,`obligaciones`,`objeto`,`id_tercero`,`entregas`,`estado`,`id_cont_api`,`id_supervision`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_adquisicion`,`id_modalidad`,1 AS `id_empresa`,`id_sede`,`id_area`
	,CASE
		WHEN `seg_adquisiciones`.`id_cdp` = 1 THEN NULL
		ELSE `seg_adquisiciones`.`id_cdp`
	END AS `id_cdp`
	,`fecha_adquisicion`,`val_contrato`,`vigencia`,`id_tipo_bn_sv`
	,`obligaciones`,`seg_adquisiciones`.`objeto`,`id_tercero`,`entregas`,`seg_adquisiciones`.`estado`,`id_cont_api`,`id_supervision`,`seg_usuarios_sistema`.`id_usuario`,`seg_adquisiciones`.`fec_reg`,`seg_usuarios_sistema`.`id_usuario` AS `act`,`seg_adquisiciones`.`fec_act`
FROM `financiero`.`seg_adquisiciones`
INNER JOIN `bd_cronhis`.`tb_tipo_bien_servicio`
	ON(`bd_cronhis`.`tb_tipo_bien_servicio`.`id_tipo_b_s` = `financiero`.`seg_adquisiciones`.`id_tipo_bn_sv`)
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON (`financiero`.`seg_adquisiciones`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
LEFT JOIN `bd_cronhis`.`pto_cdp`
	ON(`financiero`.`seg_adquisiciones`.`id_cdp` = `bd_cronhis`.`pto_cdp`.`id_pto_cdp`);

INSERT INTO `bd_cronhis`.`ctt_adquisicion_detalles`
	(`id_detalle_adq`,`id_adquisicion`,`id_bn_sv`,`cantidad`,`val_estimado_unid`,`id_user_reg`,`fec_reg`)
SELECT
	`id_detalle_adq`,`seg_detalle_adquisicion`.`id_adquisicion`,`id_bn_sv`,`cantidad`,`val_estimado_unid`
	,`seg_usuarios_sistema`.`id_usuario`,`seg_detalle_adquisicion`.`fec_reg`
FROM `financiero`.`seg_detalle_adquisicion`
INNER JOIN `bd_cronhis`.`ctt_adquisiciones`
	ON(`financiero`.`seg_detalle_adquisicion`.`id_adquisicion` = `bd_cronhis`.`ctt_adquisiciones`.`id_adquisicion`)
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON (`financiero`.`seg_detalle_adquisicion`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`);

INSERT INTO `bd_cronhis`.`tb_centrocostos`
	(`id_centro_fin`,`nom_centro`,`es_clinico`)
SELECT `id_centro`,`descripcion`, 0 AS `clinico` 
FROM `financiero`.`seg_centros_costo`
WHERE `seg_centros_costo`.`id_centro` IN (0,3,9,11,12,13,14);

UPDATE `bd_cronhis`.`tb_centrocostos` SET `id_centro_fin` = 1 WHERE `id_centro`= 7;
UPDATE `bd_cronhis`.`tb_centrocostos` SET `id_centro_fin` = 2 WHERE `id_centro`= 2;
UPDATE `bd_cronhis`.`tb_centrocostos` SET `id_centro_fin` = 4 WHERE `id_centro`= 3;
UPDATE `bd_cronhis`.`tb_centrocostos` SET `id_centro_fin` = 5 WHERE `id_centro`= 6;
UPDATE `bd_cronhis`.`tb_centrocostos` SET `id_centro_fin` = 6 WHERE `id_centro`= 20;
UPDATE `bd_cronhis`.`tb_centrocostos` SET `id_centro_fin` = 7 WHERE `id_centro`= 5;
UPDATE `bd_cronhis`.`tb_centrocostos` SET `id_centro_fin` = 8 WHERE `id_centro`= 1;
UPDATE `bd_cronhis`.`tb_centrocostos` SET `id_centro_fin` = 10 WHERE `id_centro`= 8;

INSERT INTO `bd_cronhis`.`far_centrocosto_area`
	(`nom_area`,`id_centrocosto`,`id_x_sede`,`id_tipo_area`,`id_responsable`,`id_sede`)
SELECT
	`tb_centrocostos`.`nom_centro`,`tb_centrocostos`.`id_centro`,`seg_centro_costo_x_sede`.`id_x_sede`,0 AS `tipo`, 1 AS `responsable`
	,CASE
		WHEN `seg_centro_costo_x_sede`.`id_sede` = 1 THEN 1
		WHEN `seg_centro_costo_x_sede`.`id_sede` = 2 THEN 2
		WHEN `seg_centro_costo_x_sede`.`id_sede` = 3 THEN 3
		ELSE 3
	END AS `id_sede`
FROM `financiero`.`seg_centro_costo_x_sede`
LEFT JOIN `bd_cronhis`.`tb_centrocostos`
	ON(`financiero`.`seg_centro_costo_x_sede`.`id_centrocosto` = `bd_cronhis`.`tb_centrocostos`.`id_centro_fin`);

INSERT INTO `bd_cronhis`.`ctt_destino_contrato`
	(`id_destino`,`id_adquisicion`,`id_area_cc`,`horas_mes`,`id_user_reg`,`fec_reg`)
SELECT
	`seg_destino_contrato`.`id_destino`
	,`seg_destino_contrato`.`id_adquisicion`
	,`far_centrocosto_area`.`id_area`
	,`seg_destino_contrato`.`horas_mes`
	,`seg_usuarios_sistema`.`id_usuario`
	,`seg_destino_contrato`.`fec_reg`
FROM `financiero`.`seg_destino_contrato`
INNER JOIN `bd_cronhis`.`ctt_adquisiciones`
	ON(`bd_cronhis`.`ctt_adquisiciones`.`id_adquisicion` = `financiero`.`seg_destino_contrato`.`id_adquisicion`)
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON (`financiero`.`seg_destino_contrato`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
LEFT JOIN `bd_cronhis`.`far_centrocosto_area`
	ON(`financiero`.`seg_destino_contrato`.`id_centro_costo` = `bd_cronhis`.`far_centrocosto_area`.`id_x_sede`);

INSERT INTO `bd_cronhis`.`ctt_orden_compra`
	(`id_adq`,`id_tercero_api`,`estado`,`fec_reg`)
SELECT
	`seg_cotiza_tercero`.`id_cot` AS `id_adq`
	, `seg_cotiza_tercero`.`id_tercero`
	, `seg_cotiza_tercero`.`estado`
	, `seg_cotiza_tercero`.`fec_reg`
FROM `docs_api`.`seg_cotiza_tercero`
INNER JOIN `bd_cronhis`.`ctt_adquisiciones`
	ON(`bd_cronhis`.`ctt_adquisiciones`.`id_adquisicion` = `docs_api`.`seg_cotiza_tercero`.`id_cot`)
WHERE `seg_cotiza_tercero`.`nit` = '844001355'
GROUP BY `id_cot`;

INSERT INTO `bd_cronhis`.`ctt_estudios_previos`
	(`id_est_prev`,`id_compra`,`fec_ini_ejec`,`fec_fin_ejec`,`val_contrata`,`id_forma_pago`,`id_supervisor`,`necesidad`,`act_especificas`,`prod_entrega`
	,`obligaciones`,`forma_pago`,`num_ds`,`requisitos`,`garantia`,`describe_valor`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_est_prev`,`id_compra`,`fec_ini_ejec`,`fec_fin_ejec`,`val_contrata`,`id_forma_pago`,`id_supervisor`,`necesidad`,`act_especificas`,`prod_entrega`
	,`seg_estudios_previos`.`obligaciones`,`forma_pago`,`num_ds`,`requisitos`,`garantia`,`describe_valor`,`seg_usuarios_sistema`.`id_usuario`
	,`seg_estudios_previos`.`fec_reg`,`seg_usuarios_sistema`.`id_usuario` AS `user_act`,`seg_estudios_previos`.`fec_act`
FROM `financiero`.`seg_estudios_previos`
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON(`bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin` = `financiero`.`seg_estudios_previos`.`id_user_reg`)
INNER JOIN `bd_cronhis`.`ctt_adquisiciones`
	ON(`financiero`.`seg_estudios_previos`.`id_compra` = `bd_cronhis`.`ctt_adquisiciones`.`id_adquisicion`);

INSERT INTO `bd_cronhis`.`ctt_contratos`
	(`id_contrato_compra`,`id_compra`,`fec_ini`,`fec_fin`,`val_contrato`,`id_forma_pago`,`id_supervisor`,`id_secop`,`num_contrato`,`id_user_reg`,`fec_reg`,`id_user_act`,`fec_act`)
SELECT
	`id_contrato_compra`,`id_compra`,`fec_ini`,`fec_fin`,`seg_contrato_compra`.`val_contrato`,`id_forma_pago`,`id_supervisor`,`id_secop`,`num_contrato`
	,`seg_usuarios_sistema`.`id_usuario`,`seg_contrato_compra`.`fec_reg`,`seg_usuarios_sistema`.`id_usuario` AS `user_act`,`seg_contrato_compra`.`fec_act`
FROM `financiero`.`seg_contrato_compra`
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON(`financiero`.`seg_contrato_compra`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
INNER JOIN `bd_cronhis`.`ctt_adquisiciones`
	ON(`financiero`.`seg_contrato_compra`.`id_compra` = `bd_cronhis`.`ctt_adquisiciones`.`id_adquisicion`);

UPDATE `financiero`.`seg_novedad_contrato_adi_pror` SET `id_adq` = NULL WHERE `id_adq` = 0;
INSERT INTO `bd_cronhis`.`ctt_novedad_adicion_prorroga`
	(`id_nov_con`,`id_tip_nov`,`id_adq`,`val_adicion`,`fec_adcion`,`id_cdp`,`fec_ini_prorroga`,`fec_fin_prorroga`,`observacion`,`id_user_reg`,`fec_reg`)
SELECT
	`id_nov_con`,`id_tip_nov`,`id_adq`,`val_adicion`,`fec_adcion`
	,CASE
		WHEN `cdp` = 0 THEN NULL
		ELSE `cdp`
	END AS `cdp`,`fec_ini_prorroga`,`fec_fin_prorroga`,`observacion`
	,`seg_usuarios_sistema`.`id_usuario`,`seg_novedad_contrato_adi_pror`.`fec_reg`
FROM `financiero`.`seg_novedad_contrato_adi_pror`
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON(`financiero`.`seg_novedad_contrato_adi_pror`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
INNER JOIN `bd_cronhis`.`ctt_contratos`
	ON (`financiero`.`seg_novedad_contrato_adi_pror`.`id_adq` = `bd_cronhis`.`ctt_contratos`.`id_contrato_compra`);

INSERT INTO `bd_cronhis`.`ctt_novedad_cesion`
	(`id_cesion`,`id_adq`,`id_tipo_nov`,`id_tercero`,`fec_cesion`,`observacion`,`id_user_reg`,`fec_reg`)
SELECT
	 `id_cesion`,`id_adq`,`id_tipo_nov`,`id_tercero`,`fec_cesion`,`observacion`,`seg_usuarios_sistema`.`id_usuario`,`seg_novedad_contrato_cesion`.`fec_reg`
FROM `financiero`.`seg_novedad_contrato_cesion`
INNER JOIN `bd_cronhis`.`seg_usuarios_sistema`
	ON(`financiero`.`seg_novedad_contrato_cesion`.`id_user_reg` = `bd_cronhis`.`seg_usuarios_sistema`.`id_user_fin`)
INNER JOIN `bd_cronhis`.`ctt_contratos`
	ON (`financiero`.`seg_novedad_contrato_cesion`.`id_adq` = `bd_cronhis`.`ctt_contratos`.`id_contrato_compra`);


