<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT tb_homologacion.*,
            IF(c_presup.cod_pptal IS NULL,'',CONCAT_WS(' - ',c_presup.cod_pptal,c_presup.nom_rubro)) AS cta_presupuesto,
            IF(c_debito.cuenta IS NULL,'',CONCAT_WS(' - ',c_debito.cuenta,c_debito.nombre)) AS cta_debito,
            IF(c_credito.cuenta IS NULL,'',CONCAT_WS(' - ',c_credito.cuenta,c_credito.nombre)) AS cta_credito,
            IF(c_copago.cuenta IS NULL,'',CONCAT_WS(' - ',c_copago.cuenta,c_copago.nombre)) AS cta_copago,
            IF(c_glindeb.cuenta IS NULL,'',CONCAT_WS(' - ',c_glindeb.cuenta,c_glindeb.nombre)) AS cta_glosaini_debito,
            IF(c_glincre.cuenta IS NULL,'',CONCAT_WS(' - ',c_glincre.cuenta,c_glincre.nombre)) AS cta_glosaini_credito,
            IF(c_gldef.cuenta IS NULL,'',CONCAT_WS(' - ',c_gldef.cuenta,c_gldef.nombre)) AS cta_glosadefinitiva,
            IF(c_devol.cuenta IS NULL,'',CONCAT_WS(' - ',c_devol.cuenta,c_devol.nombre)) AS cta_devolucion,
            IF(c_caja.cuenta IS NULL,'',CONCAT_WS(' - ',c_caja.cuenta,c_caja.nombre)) AS cta_caja
        FROM tb_homologacion 
        LEFT JOIN pto_cargue  AS c_presup ON (c_presup.id_cargue=tb_homologacion.id_cta_presupuesto)
        LEFT JOIN ctb_pgcp AS c_debito ON (c_debito.id_pgcp=tb_homologacion.id_cta_debito)
        LEFT JOIN ctb_pgcp AS c_credito ON (c_credito.id_pgcp=tb_homologacion.id_cta_credito)
        LEFT JOIN ctb_pgcp AS c_copago ON (c_copago.id_pgcp=tb_homologacion.id_cta_copago)
        LEFT JOIN ctb_pgcp AS c_glindeb ON (c_glindeb.id_pgcp=tb_homologacion.id_cta_glosaini_debito)
        LEFT JOIN ctb_pgcp AS c_glincre ON (c_glincre.id_pgcp=tb_homologacion.id_cta_glosaini_credito)
        LEFT JOIN ctb_pgcp AS c_gldef ON (c_gldef.id_pgcp=tb_homologacion.id_cta_glosadefinitiva)
        LEFT JOIN ctb_pgcp AS c_devol ON (c_devol.id_pgcp=tb_homologacion.id_cta_devolucion)
        LEFT JOIN ctb_pgcp AS c_caja ON (c_caja.id_pgcp=tb_homologacion.id_cta_caja)
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
                        <label for="txt_cta_pre" class="small">Cuenta Presupuesto</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm" id="txt_cta_pre" value="<?php echo $obj['cta_presupuesto'] ?>">
                        <input type="hidden" id="id_txt_cta_pre" name="id_txt_cta_pre" value="<?php echo $obj['id_cta_presupuesto'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cta_deb" class="small">Cuenta Debito</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_deb" data-campoid="id_txt_cta_deb" value="<?php echo $obj['cta_debito'] ?>">
                        <input type="hidden" id="id_txt_cta_deb" name="id_txt_cta_deb" value="<?php echo $obj['id_cta_debito'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado" class="small">Cuenta Crédito</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_cre" data-campoid="id_txt_cta_cre" value="<?php echo $obj['cta_credito'] ?>">
                        <input type="hidden" id="id_txt_cta_cre" name="id_txt_cta_cre" value="<?php echo $obj['id_cta_credito'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado" class="small">Cuenta Copago</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_cop" data-campoid="id_txt_cta_cop" value="<?php echo $obj['cta_copago'] ?>">
                        <input type="hidden" id="id_txt_cta_cop" name="id_txt_cta_cop" value="<?php echo $obj['id_cta_copago'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado" class="small">Cta. Glosa Inicial Debito</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_gid" data-campoid="id_txt_cta_gid" value="<?php echo $obj['cta_glosaini_debito'] ?>">
                        <input type="hidden" id="id_txt_cta_gid" name="id_txt_cta_gid" value="<?php echo $obj['id_cta_glosaini_debito'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado" class="small">Cta. Glosa Inicial Crédito</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_gic" data-campoid="id_txt_cta_gic" value="<?php echo $obj['cta_glosaini_credito'] ?>">
                        <input type="hidden" id="id_txt_cta_gic" name="id_txt_cta_gic" value="<?php echo $obj['id_cta_glosaini_credito'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado" class="small">Cuenta Glosa Definitiva</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_gde" data-campoid="id_txt_cta_gde" value="<?php echo $obj['cta_glosadefinitiva'] ?>">
                        <input type="hidden" id="id_txt_cta_gde" name="id_txt_cta_gde" value="<?php echo $obj['id_cta_glosadefinitiva'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado" class="small">Cuenta Devolución</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_dev" data-campoid="id_txt_cta_dev" value="<?php echo $obj['cta_devolucion'] ?>">
                        <input type="hidden" id="id_txt_cta_dev" name="id_txt_cta_dev" value="<?php echo $obj['id_cta_devolucion'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado" class="small">Cuenta Caja</label>
                    </div>  
                    <div class="form-group col-md-9">                            
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_caj" data-campoid="id_txt_cta_caj" value="<?php echo $obj['cta_caja'] ?>">
                        <input type="hidden" id="id_txt_cta_caj" name="id_txt_cta_caj" value="<?php echo $obj['id_cta_caja'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_vig" class="small">Fecha de Vigencia</label>
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