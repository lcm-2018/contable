<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id_c = isset($_POST['id_c']) ? $_POST['id_c'] : 0;
$id_ter = $_POST['tercero'];
$id_adquisicion = $_POST['id_adquisicion'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`id_tercero`, `seg_terceros`.`no_doc`
            FROM
                `tb_rel_tercero`
                INNER JOIN `seg_terceros` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
            WHERE `seg_terceros`.`estado` = 1 AND `tb_rel_tercero`.`id_tipo_tercero` = 3";
    $rs = $cmd->query($sql);
    $terceros_sup = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($terceros_sup)) {
    $ced = '0';
    foreach ($terceros_sup as $tE) {
        $ced .= ',' . $tE['no_doc'];
    }
    //API URL
    $url = $api . 'terceros/datos/res/lista/' . $ced;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $supervisor = json_decode($result, true);
} else {
    echo "No se ha registrado ningun tercero" . '<br><br><a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>';
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">DESIGNAR SUPERVISOR DE CONTRATO</h5>
        </div>
        <form id="formDesingSupervisor">
            <input type="hidden" name="id_con_final" value="<?php echo $id_c ?>">
            <input type="hidden" name="id_ter_sup" value="<?php echo $id_ter ?>">
            <input type="hidden" name="id_adquisicion" value="<?php echo $id_adquisicion ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="datFecDesigSup" class="small">FECHA DESIGNACÓN</label>
                    <input type="date" name="datFecDesigSup" id="datFecDesigSup" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-6">
                    <label for="numMemorando" class="small">Número Memorando</label>
                    <input type="number" name="numMemorando" id="numMemorando" class="form-control form-control-sm">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="txtaObservaciones" class="small">OBSERVACIONES</label>
                    <textarea name="txtaObservaciones" id="txtaObservaciones" class="form-control form-control-sm" rows="3"></textarea>
                </div>
            </div>
            <div class="text-center pb-3">
                <button class="btn btn-primary btn-sm" id="btnDesigSupervisor">Registrar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>