<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT tb_homologacion.*,
            IF(c_presto.cod_pptal IS NULL,'',CONCAT_WS(' - ',c_presto.cod_pptal,c_presto.nom_rubro)) AS cta_presupuesto,
            IF(c_presto_ant.cod_pptal IS NULL,'',CONCAT_WS(' - ',c_presto_ant.cod_pptal,c_presto_ant.nom_rubro)) AS cta_presupuesto_ant,
            IF(c_debito.cuenta IS NULL,'',CONCAT_WS(' - ',c_debito.cuenta,c_debito.nombre)) AS cta_debito,
            IF(c_credito.cuenta IS NULL,'',CONCAT_WS(' - ',c_credito.cuenta,c_credito.nombre)) AS cta_credito,
            IF(c_copago.cuenta IS NULL,'',CONCAT_WS(' - ',c_copago.cuenta,c_copago.nombre)) AS cta_copago,
            IF(c_copago_cap.cuenta IS NULL,'',CONCAT_WS(' - ',c_copago_cap.cuenta,c_copago_cap.nombre)) AS cta_copago_capitado,
            IF(c_gloini_deb.cuenta IS NULL,'',CONCAT_WS(' - ',c_gloini_deb.cuenta,c_gloini_deb.nombre)) AS cta_glosaini_debito,
            IF(c_gloini_cre.cuenta IS NULL,'',CONCAT_WS(' - ',c_gloini_cre.cuenta,c_gloini_cre.nombre)) AS cta_glosaini_credito,
            IF(c_glo_def.cuenta IS NULL,'',CONCAT_WS(' - ',c_glo_def.cuenta,c_glo_def.nombre)) AS cta_glosadefinitiva,
            IF(c_devol.cuenta IS NULL,'',CONCAT_WS(' - ',c_devol.cuenta,c_devol.nombre)) AS cta_devolucion,            
            IF(c_caja.cuenta IS NULL,'',CONCAT_WS(' - ',c_caja.cuenta,c_caja.nombre)) AS cta_caja,
            IF(c_fac_glo.cuenta IS NULL,'',CONCAT_WS(' - ',c_fac_glo.cuenta,c_fac_glo.nombre)) AS cta_fac_global,
            IF(c_x_ide.cuenta IS NULL,'',CONCAT_WS(' - ',c_x_ide.cuenta,c_x_ide.nombre)) AS cta_x_ident
        FROM tb_homologacion 
        LEFT JOIN pto_cargue  AS c_presto ON (c_presto.id_cargue=tb_homologacion.id_cta_presupuesto)
        LEFT JOIN pto_cargue  AS c_presto_ant ON (c_presto_ant.id_cargue=tb_homologacion.id_cta_presupuesto_ant)
        LEFT JOIN ctb_pgcp AS c_debito ON (c_debito.id_pgcp=tb_homologacion.id_cta_debito)
        LEFT JOIN ctb_pgcp AS c_credito ON (c_credito.id_pgcp=tb_homologacion.id_cta_credito)
        LEFT JOIN ctb_pgcp AS c_copago ON (c_copago.id_pgcp=tb_homologacion.id_cta_copago)
        LEFT JOIN ctb_pgcp AS c_copago_cap ON (c_copago_cap.id_pgcp=tb_homologacion.id_cta_copago_capitado)
        LEFT JOIN ctb_pgcp AS c_gloini_deb ON (c_gloini_deb.id_pgcp=tb_homologacion.id_cta_glosaini_debito)
        LEFT JOIN ctb_pgcp AS c_gloini_cre ON (c_gloini_cre.id_pgcp=tb_homologacion.id_cta_glosaini_credito)
        LEFT JOIN ctb_pgcp AS c_glo_def ON (c_glo_def.id_pgcp=tb_homologacion.id_cta_glosadefinitiva)        
        LEFT JOIN ctb_pgcp AS c_devol ON (c_devol.id_pgcp=tb_homologacion.id_cta_devolucion)
        LEFT JOIN ctb_pgcp AS c_caja ON (c_caja.id_pgcp=tb_homologacion.id_cta_caja)
        LEFT JOIN ctb_pgcp AS c_fac_glo ON (c_fac_glo.id_pgcp=tb_homologacion.id_cta_fac_global)
        LEFT JOIN ctb_pgcp AS c_x_ide ON (c_x_ide.id_pgcp=tb_homologacion.id_cta_x_ident)
        WHERE tb_homologacion.id_homo=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if(empty($obj)){
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++):
        $col = $rs->getColumnMeta($i);
        $name=$col['name'];
        $obj[$name]=NULL;
    endfor;    
    //Inicializa variable por defecto
    $obj['estado'] = 1;
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR CUENTAS DE FACTURACION</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_cuentas_fac">
                <input type="hidden" id="id_cuentafac" name="id_cuentafac" value="<?php echo $id ?>">
                <div class=" form-row">                    
                    <div class="form-group col-md-3">
                        <label for="sl_regimen" class="small">Régimen</label>
                        <select class="form-control form-control-sm" id="sl_regimen" name="sl_regimen" required>
                            <?php regimenes($cmd, '', $obj['id_regimen']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="sl_cobertura" class="small">Cobertura</label>
                        <select class="form-control form-control-sm" id="sl_cobertura" name="sl_cobertura" required>
                            <?php cobertura($cmd, '', $obj['id_regimen']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_modalidad" class="small">Modalidad</label>
                        <select class="form-control form-control-sm" id="sl_modalidad" name="sl_modalidad" required>
                            <?php modalidad($cmd, '', $obj['id_regimen']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_pre" class="small">Cta. Presupuesto</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta_pre" id="txt_cta_pre" data-campoid="id_txt_cta_pre" value="<?php echo $obj['cta_presupuesto'] ?>">
                        <input type="hidden" id="id_txt_cta_pre" name="id_txt_cta_pre" value="<?php echo $obj['id_cta_presupuesto'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_pre_ant" class="small">Cta. Presupuesto Anterior</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta_pre" id="txt_cta_pre_ant" data-campoid="id_txt_cta_pre_ant" value="<?php echo $obj['cta_presupuesto_ant'] ?>">
                        <input type="hidden" id="id_txt_cta_pre_ant" name="id_txt_cta_pre_ant" value="<?php echo $obj['id_cta_presupuesto_ant'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_deb" class="small">Cta. Debito</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_deb" data-campoid="id_txt_cta_deb" value="<?php echo $obj['cta_debito'] ?>">
                        <input type="hidden" id="id_txt_cta_deb" name="id_txt_cta_deb" value="<?php echo $obj['id_cta_debito'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_cre" class="small">Cta. Crédito</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_cre" data-campoid="id_txt_cta_cre" value="<?php echo $obj['cta_credito'] ?>">
                        <input type="hidden" id="id_txt_cta_cre" name="id_txt_cta_cre" value="<?php echo $obj['id_cta_credito'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_cop" class="small">Cta. Copago</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_cop" data-campoid="id_txt_cta_cop" value="<?php echo $obj['cta_copago'] ?>">
                        <input type="hidden" id="id_txt_cta_cop" name="id_txt_cta_cop" value="<?php echo $obj['id_cta_copago'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_cop_cap" class="small">Cta. Copago Capitado</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_cop_cap" data-campoid="id_txt_cta_cop_cap" value="<?php echo $obj['cta_copago_capitado'] ?>">
                        <input type="hidden" id="id_txt_cta_cop_cap" name="id_txt_cta_cop_cap" value="<?php echo $obj['id_cta_copago_capitado'] ?>">
                    </div>                    
                    <div class="form-group col-md-3">
                        <label for="txt_cta_gli_deb" class="small">Cta. Glosa Inicial Debito</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_gli_deb" data-campoid="id_txt_cta_gli_deb" value="<?php echo $obj['cta_glosaini_debito'] ?>">
                        <input type="hidden" id="id_txt_cta_gli_deb" name="id_txt_cta_gli_deb" value="<?php echo $obj['id_cta_glosaini_debito'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_gli_cre" class="small">Cta. Glosa Inicial Crédito</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_gli_cre" data-campoid="id_txt_cta_gli_cre" value="<?php echo $obj['cta_glosaini_credito'] ?>">
                        <input type="hidden" id="id_txt_cta_gli_cre" name="id_txt_cta_gli_cre" value="<?php echo $obj['id_cta_glosaini_credito'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_glo_def" class="small">Cta. Glosa Definitiva</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_glo_def" data-campoid="id_txt_cta_glo_def" value="<?php echo $obj['cta_glosadefinitiva'] ?>">
                        <input type="hidden" id="id_txt_cta_glo_def" name="id_txt_cta_glo_def" value="<?php echo $obj['id_cta_glosadefinitiva'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_dev" class="small">Cta. Devolución</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_dev" data-campoid="id_txt_cta_dev" value="<?php echo $obj['cta_devolucion'] ?>">
                        <input type="hidden" id="id_txt_cta_dev" name="id_txt_cta_dev" value="<?php echo $obj['id_cta_devolucion'] ?>">
                    </div>                    
                    <div class="form-group col-md-3">
                        <label for="txt_cta_caj" class="small">Cta. Caja</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_caj" data-campoid="id_txt_cta_caj" value="<?php echo $obj['cta_caja'] ?>">
                        <input type="hidden" id="id_txt_cta_caj" name="id_txt_cta_caj" value="<?php echo $obj['id_cta_caja'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_fac_glo" class="small">Cta. Factura Global</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_fac_glo" data-campoid="id_txt_cta_fac_glo" value="<?php echo $obj['cta_fac_global'] ?>">
                        <input type="hidden" id="id_txt_cta_fac_glo" name="id_txt_cta_fac_glo" value="<?php echo $obj['id_cta_fac_global'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_x_ide" class="small">Cta. Por Identificar</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_x_ide" data-campoid="id_txt_cta_x_ide" value="<?php echo $obj['cta_x_ident'] ?>">
                        <input type="hidden" id="id_txt_cta_x_ide" name="id_txt_cta_x_ide" value="<?php echo $obj['id_cta_x_ident'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_vig" class="small">Fecha Inicio de Vigencia</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fec_vig" name="txt_fec_vig" value="<?php echo $obj['fecha_vigencia'] ?>">
                    </div> 
                    <div class="form-group col-md-2">
                        <label for="sl_estado" class="small">Estado</label>
                        <select class="form-control form-control-sm" id="sl_estado" name="sl_estado">
                            <?php estados_registros('',$obj['estado']) ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>