SET FOREIGN_KEY_CHECKS = 0;
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

DELETE FROM `tb_terceros`;
ALTER TABLE `tb_terceros` AUTO_INCREMENT = 1;
DELETE FROM `tb_tipo_tercero`;
ALTER TABLE `tb_tipo_tercero` AUTO_INCREMENT = 1;
DELETE FROM `seg_garantias_compra`;
ALTER TABLE `seg_garantias_compra` AUTO_INCREMENT = 1;
DELETE FROM `seg_permisos_modulos`;
ALTER TABLE `seg_permisos_modulos` AUTO_INCREMENT = 1;
DELETE FROM `seg_soporte_fno`;
ALTER TABLE `seg_soporte_fno` AUTO_INCREMENT = 1;
DELETE FROM `seg_garantias_compra`;
ALTER TABLE `seg_garantias_compra` AUTO_INCREMENT = 1;
DELETE FROM `seg_modulos`;
ALTER TABLE `seg_modulos` AUTO_INCREMENT = 1;
DELETE FROM `seg_rol`;
ALTER TABLE `seg_rol` AUTO_INCREMENT = 1;
DELETE FROM `seg_rol_permisos`;
ALTER TABLE `seg_rol_permisos` AUTO_INCREMENT = 1;
DELETE FROM `seg_rol_usuario`;
ALTER TABLE `seg_rol_usuario` AUTO_INCREMENT = 1;
DELETE FROM `seg_usuarios_sistema`;
ALTER TABLE `seg_usuarios_sistema` AUTO_INCREMENT = 1;
DELETE FROM `ctb_doc`;
ALTER TABLE `ctb_doc` AUTO_INCREMENT = 1;
DELETE FROM `ctb_factura`;
ALTER TABLE `ctb_factura` AUTO_INCREMENT = 1;
DELETE FROM `ctb_pgcp`;
ALTER TABLE `ctb_pgcp` AUTO_INCREMENT = 1;
DELETE FROM `ctb_libaux`;
ALTER TABLE `ctb_libaux` AUTO_INCREMENT = 1;
DELETE FROM `ctb_retencion_rango`;
ALTER TABLE `ctb_retencion_rango` AUTO_INCREMENT = 1;
DELETE FROM `ctb_causa_retencion`;
ALTER TABLE `ctb_causa_retencion` AUTO_INCREMENT = 1;
DELETE FROM `ctb_causa_costos`;
ALTER TABLE `ctb_causa_costos` AUTO_INCREMENT = 1;
DELETE FROM `ctt_modalidad`;
ALTER TABLE `ctt_modalidad` AUTO_INCREMENT = 1;
DELETE FROM `ctt_adquisiciones`;
ALTER TABLE `ctt_adquisiciones` AUTO_INCREMENT = 1;
DELETE FROM `ctt_bien_servicio`;
ALTER TABLE `ctt_bien_servicio` AUTO_INCREMENT = 1;
DELETE FROM `ctt_adquisicion_detalles`;
ALTER TABLE `ctt_adquisicion_detalles` AUTO_INCREMENT = 1;
DELETE FROM `ctt_clasificacion_bn_sv`;
ALTER TABLE `ctt_clasificacion_bn_sv` AUTO_INCREMENT = 1;
DELETE FROM `ctt_contratos`;
ALTER TABLE `ctt_contratos` AUTO_INCREMENT = 1;
DELETE FROM `ctt_destino_contrato`;
ALTER TABLE `ctt_destino_contrato` AUTO_INCREMENT = 1;
DELETE FROM `ctt_orden_compra`;
ALTER TABLE `ctt_orden_compra` AUTO_INCREMENT = 1;
DELETE FROM `ctt_escala_honorarios`;
ALTER TABLE `ctt_escala_honorarios` AUTO_INCREMENT = 1;
DELETE FROM `ctt_estado_adq`;
ALTER TABLE `ctt_estado_adq` AUTO_INCREMENT = 1;
DELETE FROM `ctt_estudios_previos`;
ALTER TABLE `ctt_estudios_previos` AUTO_INCREMENT = 1;
DELETE FROM `ctt_garantias_compra`;
ALTER TABLE `ctt_garantias_compra` AUTO_INCREMENT = 1;
DELETE FROM `ctt_novedad_adicion_prorroga`;
ALTER TABLE `ctt_novedad_adicion_prorroga` AUTO_INCREMENT = 1;
DELETE FROM `ctt_novedad_cesion`;
ALTER TABLE `ctt_novedad_cesion` AUTO_INCREMENT = 1;
DELETE FROM `ctt_novedad_liquidacion`;
ALTER TABLE `ctt_novedad_liquidacion` AUTO_INCREMENT = 1;
DELETE FROM `ctt_novedad_suspension`;
ALTER TABLE `ctt_novedad_suspension` AUTO_INCREMENT = 1;
DELETE FROM `ctt_novedad_reinicio`;
ALTER TABLE `ctt_novedad_reinicio` AUTO_INCREMENT = 1;
DELETE FROM `ctt_novedad_terminacion`;
ALTER TABLE `ctt_novedad_terminacion` AUTO_INCREMENT = 1;
DELETE FROM `tb_polizas`;
ALTER TABLE `tb_polizas` AUTO_INCREMENT = 1;
DELETE FROM `tb_centrocostos`;
ALTER TABLE `tb_centrocostos` AUTO_INCREMENT = 1;
DELETE FROM `tb_tipo_contratacion`;
ALTER TABLE `tb_tipo_contratacion` AUTO_INCREMENT = 1;
DELETE FROM `tb_tipo_bien_servicio`;
ALTER TABLE `tb_tipo_bien_servicio` AUTO_INCREMENT = 1;
DELETE FROM `fin_maestro_doc`;
ALTER TABLE `fin_maestro_doc` AUTO_INCREMENT = 1;
DELETE FROM `fin_respon_doc`;
ALTER TABLE `fin_respon_doc` AUTO_INCREMENT = 1;
DELETE FROM `nom_afp`;
ALTER TABLE `nom_afp` AUTO_INCREMENT = 1;
DELETE FROM `nom_arl`;
ALTER TABLE `nom_arl` AUTO_INCREMENT = 1;
DELETE FROM `nom_cargo_empleado`;
ALTER TABLE `nom_cargo_empleado` AUTO_INCREMENT = 1;
DELETE FROM `nom_causacion`;
ALTER TABLE `nom_causacion` AUTO_INCREMENT = 1;
DELETE FROM `nom_incremento_salario`;
ALTER TABLE `nom_incremento_salario` AUTO_INCREMENT = 1;
DELETE FROM `nom_retroactivos`;
ALTER TABLE `nom_retroactivos` AUTO_INCREMENT = 1;
DELETE FROM `nom_nominas`;
ALTER TABLE `nom_nominas` AUTO_INCREMENT = 1;
DELETE FROM `nom_cdp_empleados`;
ALTER TABLE `nom_cdp_empleados` AUTO_INCREMENT = 1;
DELETE FROM `nom_consecutivo_viaticos`;
ALTER TABLE `nom_consecutivo_viaticos` AUTO_INCREMENT = 1;
DELETE FROM `nom_empleado`;
ALTER TABLE `nom_empleado` AUTO_INCREMENT = 1;
DELETE FROM `nom_contratos_empleados`;
ALTER TABLE `nom_contratos_empleados` AUTO_INCREMENT = 1;
DELETE FROM `nom_sindicatos`;
ALTER TABLE `nom_sindicatos` AUTO_INCREMENT = 1;
DELETE FROM `nom_cuota_sindical`;
ALTER TABLE `nom_cuota_sindical` AUTO_INCREMENT = 1;
DELETE FROM `nom_juzgados`;
ALTER TABLE `nom_juzgados` AUTO_INCREMENT = 1;
DELETE FROM `nom_embargos`;
ALTER TABLE `nom_embargos` AUTO_INCREMENT = 1;
DELETE FROM `nom_empleados_retirados`;
ALTER TABLE `nom_empleados_retirados` AUTO_INCREMENT = 1;
DELETE FROM `nom_epss`;
ALTER TABLE `nom_epss` AUTO_INCREMENT = 1;
DELETE FROM `nom_fondo_censan`;
ALTER TABLE `nom_fondo_censan` AUTO_INCREMENT = 1;
DELETE FROM `nom_horas_ex_trab`;
ALTER TABLE `nom_horas_ex_trab` AUTO_INCREMENT = 1;
DELETE FROM `nom_incapacidad`;
ALTER TABLE `nom_incapacidad` AUTO_INCREMENT = 1;
DELETE FROM `nom_indemniza_vac`;
ALTER TABLE `nom_indemniza_vac` AUTO_INCREMENT = 1;
DELETE FROM `nom_libranzas`;
ALTER TABLE `nom_libranzas` AUTO_INCREMENT = 1;
DELETE FROM `nom_licencia_luto`;
ALTER TABLE `nom_licencia_luto` AUTO_INCREMENT = 1;
DELETE FROM `nom_licenciasmp`;
ALTER TABLE `nom_licenciasmp` AUTO_INCREMENT = 1;
DELETE FROM `nom_licenciasnr`;
ALTER TABLE `nom_licenciasnr` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_bsp`;
ALTER TABLE `nom_liq_bsp` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_cesantias`;
ALTER TABLE `nom_liq_cesantias` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_compesatorio`;
ALTER TABLE `nom_liq_compesatorio` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_dias_lab`;
ALTER TABLE `nom_liq_dias_lab` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_dlab_auxt`;
ALTER TABLE `nom_liq_dlab_auxt` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_embargo`;
ALTER TABLE `nom_liq_embargo` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_empleado`;
ALTER TABLE `nom_liq_empleado` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_horex`;
ALTER TABLE `nom_liq_horex` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_incap`;
ALTER TABLE `nom_liq_incap` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_indemniza_vac`;
ALTER TABLE `nom_liq_indemniza_vac` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_libranza`;
ALTER TABLE `nom_liq_libranza` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_licluto`;
ALTER TABLE `nom_liq_licluto` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_licmp`;
ALTER TABLE `nom_liq_licmp` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_licnr`;
ALTER TABLE `nom_liq_licnr` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_parafiscales`;
ALTER TABLE `nom_liq_parafiscales` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_prestaciones_sociales`;
ALTER TABLE `nom_liq_prestaciones_sociales` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_prima`;
ALTER TABLE `nom_liq_prima` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_prima_nav`;
ALTER TABLE `nom_liq_prima_nav` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_salario`;
ALTER TABLE `nom_liq_salario` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_segsocial_empdo`;
ALTER TABLE `nom_liq_segsocial_empdo` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_sindicato_aportes`;
ALTER TABLE `nom_liq_sindicato_aportes` AUTO_INCREMENT = 1;
DELETE FROM `nom_vacaciones`;
ALTER TABLE `nom_vacaciones` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_vac`;
ALTER TABLE `nom_liq_vac` AUTO_INCREMENT = 1;
DELETE FROM `nom_nomina_pto_ctb_tes`;
ALTER TABLE `nom_nomina_pto_ctb_tes` AUTO_INCREMENT = 1;
DELETE FROM `nom_novedades_afp`;
ALTER TABLE `nom_novedades_afp` AUTO_INCREMENT = 1;
DELETE FROM `nom_novedades_arl`;
ALTER TABLE `nom_novedades_arl` AUTO_INCREMENT = 1;
DELETE FROM `nom_novedades_eps`;
ALTER TABLE `nom_novedades_eps` AUTO_INCREMENT = 1;
DELETE FROM `nom_novedades_fc`;
ALTER TABLE `nom_novedades_fc` AUTO_INCREMENT = 1;
DELETE FROM `nom_otros_descuentos`;
ALTER TABLE `nom_otros_descuentos` AUTO_INCREMENT = 1;
DELETE FROM `nom_pago_dependiente`;
ALTER TABLE `nom_pago_dependiente` AUTO_INCREMENT = 1;
DELETE FROM `nom_rango_viaticos`;
ALTER TABLE `nom_rango_viaticos` AUTO_INCREMENT = 1;
DELETE FROM `pto_cdp_detalle`;
ALTER TABLE `pto_cdp_detalle` AUTO_INCREMENT = 1;
DELETE FROM `pto_crp`;
ALTER TABLE `pto_crp` AUTO_INCREMENT = 1;
DELETE FROM `pto_crp_detalle`;
ALTER TABLE `pto_crp_detalle` AUTO_INCREMENT = 1;
DELETE FROM `pto_cop_detalle`;
ALTER TABLE `pto_cop_detalle` AUTO_INCREMENT = 1;
DELETE FROM `pto_homologa_gastos`;
ALTER TABLE `pto_homologa_gastos` AUTO_INCREMENT = 1;
DELETE FROM `pto_homologa_ingresos`;
ALTER TABLE `pto_homologa_ingresos` AUTO_INCREMENT = 1;
DELETE FROM `pto_mod`;
ALTER TABLE `pto_mod` AUTO_INCREMENT = 1;
DELETE FROM `pto_mod_detalle`;
ALTER TABLE `pto_mod_detalle` AUTO_INCREMENT = 1;
DELETE FROM `pto_pag_detalle`;
ALTER TABLE `pto_pag_detalle` AUTO_INCREMENT = 1;
DELETE FROM `pto_cargue`;
ALTER TABLE `pto_cargue` AUTO_INCREMENT = 1;
DELETE FROM `pto_presupuestos`;
DELETE FROM `pto_rad`;
ALTER TABLE `pto_rad` AUTO_INCREMENT = 1;
DELETE FROM `pto_rad_detalle`;
ALTER TABLE `pto_rad_detalle` AUTO_INCREMENT = 1;
DELETE FROM `pto_rec`;
ALTER TABLE `pto_rec` AUTO_INCREMENT = 1;
DELETE FROM `pto_rec_detalle`;
ALTER TABLE `pto_rec_detalle` AUTO_INCREMENT = 1;
ALTER TABLE `pto_presupuestos` AUTO_INCREMENT = 1;
DELETE FROM `pto_cdp`;
ALTER TABLE `pto_cdp` AUTO_INCREMENT = 1;
DELETE FROM `nom_rel_rubro`;
ALTER TABLE `nom_rel_rubro` AUTO_INCREMENT = 1;
DELETE FROM `nom_resolucion_viaticos`;
ALTER TABLE `nom_resolucion_viaticos` AUTO_INCREMENT = 1;
DELETE FROM `nom_resoluciones`;
ALTER TABLE `nom_resoluciones` AUTO_INCREMENT = 1;
DELETE FROM `nom_retencion_fte`;
ALTER TABLE `nom_retencion_fte` AUTO_INCREMENT = 1;
DELETE FROM `nom_salarios_basico`;
ALTER TABLE `nom_salarios_basico` AUTO_INCREMENT = 1;
DELETE FROM `nom_soporte_ne`;
ALTER TABLE `nom_soporte_ne` AUTO_INCREMENT = 1;
DELETE FROM `nom_valxvigencia`;
ALTER TABLE `nom_valxvigencia` AUTO_INCREMENT = 1;
DELETE FROM `nom_viaticos`;
ALTER TABLE `nom_viaticos` AUTO_INCREMENT = 1;
DELETE FROM `tb_rel_tercero`;
ALTER TABLE `tb_rel_tercero` AUTO_INCREMENT = 1;
DELETE FROM `tes_cuentas`;
ALTER TABLE `tes_cuentas` AUTO_INCREMENT = 1;
DELETE FROM `tes_caja_const`;
ALTER TABLE `tes_caja_const` AUTO_INCREMENT = 1;
DELETE FROM `tes_caja_doc`;
ALTER TABLE `tes_caja_doc` AUTO_INCREMENT = 1;
DELETE FROM `tes_caja_mvto`;
ALTER TABLE `tes_caja_mvto` AUTO_INCREMENT = 1;
DELETE FROM `tes_caja_respon`;
ALTER TABLE `tes_caja_respon` AUTO_INCREMENT = 1;
DELETE FROM `tes_caja_rubros`;
ALTER TABLE `tes_caja_rubros` AUTO_INCREMENT = 1;
DELETE FROM `tes_causa_arqueo`;
ALTER TABLE `tes_causa_arqueo` AUTO_INCREMENT = 1;
DELETE FROM `tes_conciliacion_detalle`;
ALTER TABLE `tes_conciliacion_detalle` AUTO_INCREMENT = 1;
DELETE FROM `tes_detalle_pago`;
ALTER TABLE `tes_detalle_pago` AUTO_INCREMENT = 1;
DELETE FROM `tes_facturador`;
ALTER TABLE `tes_facturador` AUTO_INCREMENT = 1;
DELETE FROM `tes_rel_pag_cop`;
ALTER TABLE `tes_rel_pag_cop` AUTO_INCREMENT = 1;
DELETE FROM `nom_liq_contrato_emp`;
ALTER TABLE `nom_liq_contrato_emp` AUTO_INCREMENT = 1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
SET FOREIGN_KEY_CHECKS = 1;