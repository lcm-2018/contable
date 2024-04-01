<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_eaf = isset($_POST['id']) ? $_POST['id'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_entrada_activo_fijo`.`id_entra_af`
                , `seg_entrada_activo_fijo`.`id_tercero_api`
                , `seg_entrada_activo_fijo`.`id_tipo_entrada`
                , `seg_tipo_entrada`.`descripcion`
                , `seg_entrada_activo_fijo`.`acta_remision`
                , `seg_entrada_activo_fijo`.`fec_acta_remision`
                , `seg_entrada_activo_fijo`.`observacion`
                , `seg_entrada_activo_fijo`.`estado`
            FROM
                `seg_entrada_activo_fijo`
                INNER JOIN `seg_tipo_entrada` 
                    ON (`seg_entrada_activo_fijo`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`)
            WHERE `id_entra_af` = '$id_eaf'";
    $rs = $cmd->query($sql);
    $ent_acfij = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipol = $ent_acfij['id_tipo_entrada'] == 3 ? 'DONACIÓN' : 'OTRA';
$tipoente = $ent_acfij['id_tipo_entrada'] == 3 ? 'DONANTE' : 'OTRO';
$id_ter = $ent_acfij['id_tercero_api'];
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['razon_social'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR ENTRADA DE ACTIVOS FIJOS POR <?php echo $tipol ?></h5>
        </div>
        <div class="px-2">
            <form id="formUpEntraActFijoDO">
                <input type="hidden" id="id_entra_af" name="id_entra_af" value="<?php echo $id_eaf ?>">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="ccnit" class="small"><?php echo $tipoente ?></label>
                        <input type="text" id="compleTerecero" class="form-control form-control-sm" value="<?php echo $tercero ?>">
                        <input type="hidden" id="id_tercero_pd" name="id_tercero_pd" value="<?php echo $id_ter ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="numActaRem" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRem" name="numActaRem" class="form-control form-control-sm" value="<?php echo $ent_acfij['acta_remision'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecActRem" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRem" name="fecActRem" class="form-control form-control-sm" value="<?php echo $ent_acfij['fec_acta_remision'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaActFijo" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaActFijo" name="txtObservaActFijo" rows="3"><?php echo $ent_acfij['observacion'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button id="btnUpEntraActFijoDO" type="button" class="btn btn-primary btn-sm">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>