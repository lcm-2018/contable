<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';

$id_doc = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `pto_crp_detalle`.`id_tercero_api`
                , IFNULL(`pto_crp_detalle`.`valor`,0) - IFNULL(`pto_crp_detalle`.`valor_liberado`,0) AS `valor_crp` 
                , `pto_cdp_detalle`.`id_rubro`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , IFNULL(`t1`.`valor`,0) - IFNULL(`t1`.`valor_liberado`,0) AS `valor_cop`
                , `pto_crp_detalle`.`id_pto_crp_det`

            FROM
                `ctb_doc`
                INNER JOIN `pto_crp` 
                    ON (`ctb_doc`.`id_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
            (SELECT
                `id_pto_crp_det`
                , IFNULL(SUM(`valor`),0) AS `valor`
                , IFNULL(SUM(`valor_liberado`),0) AS `valor_liberado`
            FROM
                `pto_cop_detalle`
            WHERE (`id_ctb_doc` = $id_doc) 
            GROUP BY `id_pto_crp_det`) AS `t1`  
                    ON (`t1`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
//consulto los datos del cop
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_pto_crp_det`
                , `valor`
                , `valor_liberado`
                , `id_pto_cop_det`
            FROM
                `pto_cop_detalle`
            WHERE (`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE IMPUTACIONES DE CUENTA POR PAGAR </h5>
        </div>
        <div class="p-3">
            <form id="formImputacion">
                <input type="hidden" name="id_doc" value="<?php echo $id_doc ?>">
                <?php
                $band = true;
                foreach ($listado as $l) {
                    $max = $l['valor_crp'] - $l['valor_cop'];
                    $key = array_search($l['id_pto_crp_det'], array_column($detalles, 'id_pto_crp_det'));
                    $bg_color = $key !== false ? 'border-success' : 'border-secondary';
                    $id_detalle = $key !== false ? $detalles[$key]['id_pto_cop_det'] . '-' . $l['id_pto_crp_det'] : '0-' . $l['id_pto_crp_det'];
                    $id_detalle = $id_detalle . '-' . $l['id_tercero_api'];
                    $value = $key !== false ? $detalles[$key]['valor'] : $max;
                    $max = $key !== false ? $max + $detalles[$key]['valor'] : $max;
                ?>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <?php if ($band) { ?>
                                <span class="small">Código presupuestal</span>
                            <?php } ?>
                            <div class="form-control form-control-sm text-left <?php echo $bg_color ?>"><?php echo $l['cod_pptal'] ?></div>
                        </div>
                        <div class="form-group col-md-5">
                            <?php if ($band) { ?>
                                <span class="small">Rubro</span>
                            <?php } ?>
                            <div class="form-control form-control-sm text-left <?php echo $bg_color ?>"><?php echo $l['nom_rubro'] ?></div>
                        </div>
                        <div class="form-group col-md-2">
                            <?php if ($band) { ?>
                                <span for="valor" class="small">Valor RP</span>
                            <?php } ?>
                            <div class="form-control form-control-sm text-left <?php echo $bg_color ?>"><?php echo number_format($max,2) ?></div>
                        </div>
                        <div class="form-group col-md-2">
                            <?php if ($band) { ?>
                                <span for="valor" class="small">Valor CxP</span>
                            <?php } ?>
                            <input type="text" name="valor[<?php echo $id_detalle ?>]" id="valor" onkeyup="valorMiles(id)" class="form-control form-control-sm text-right ValImputacion" min="0" max="<?php echo $max ?>" value="<?php echo number_format($value, 2) ?>">
                        </div>
                    </div>
                <?php
                    $band = false;
                }
                ?>
            </form>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-primary btn-sm" onclick="DetalleImputacionCtasPorPagar()">Guardar</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>
<?php
