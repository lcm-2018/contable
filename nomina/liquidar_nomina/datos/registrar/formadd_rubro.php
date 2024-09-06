<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_relacion = isset($_POST['id_relacion']) ? $_POST['id_relacion'] : exit("Acción no permitida");
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_rubro`,`nombre`
            FROM `nom_tipo_rubro` ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $tipo = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_rel_rubro`.`id_relacion`
                , `nom_rel_rubro`.`id_tipo`
                , `nom_rel_rubro`.`r_admin`
                , CONCAT_WS(' -> ', `pto_admin`.`cod_pptal`
                , `pto_admin`.`nom_rubro`) AS `nom_admin`
                , `pto_admin`.`tipo_dato` AS `tp_a`
                , `nom_rel_rubro`.`r_operativo`
                , CONCAT_WS(' -> ',`pto_operativo`.`cod_pptal`
                , `pto_operativo`.`nom_rubro`) AS `nom_operativo`
                , `pto_operativo`.`tipo_dato` AS `tp_o`
            FROM
                `nom_rel_rubro`
                INNER JOIN `pto_cargue` AS `pto_operativo` 
                    ON (`nom_rel_rubro`.`r_operativo` = `pto_operativo`.`id_cargue`)
                INNER JOIN `pto_cargue` AS `pto_admin`
                    ON (`nom_rel_rubro`.`r_admin` = `pto_admin`.`id_cargue`)
            WHERE (`nom_rel_rubro`.`id_relacion` = $id_relacion)";
    $rs = $cmd->query($sql);
    $rubro = $rs->fetch(PDO::FETCH_ASSOC);
    if (empty($rubro)) {
        $rubro = [
            'id_relacion' => 0,
            'id_tipo' => 0,
            'r_admin' => 0,
            'nom_admin' => '',
            'tp_a' => 0,
            'r_operativo' => 0,
            'nom_operativo' => '',
            'tp_o' => 0,
        ];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIONAR RUBRO DE NÓMINA</h5>
        </div>
        <div class="px-2">
            <form id="formGestRubroNom">
                <input type="hidden" id="id_relacion" name="id_relacion" value="<?php echo $rubro['id_relacion']; ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="slcTipo" class="small">TIPO</label>
                        <select name="slcTipo" id="slcTipo" class="form-control form-control-sm">
                            <option value="0" <?php $rubro['id_tipo'] == '0' ? 'selected' : ''; ?>>--Seleccione--</option>
                            <?php foreach ($tipo as $tp) {
                                $slc = $rubro['id_tipo'] == $tp['id_rubro'] ? 'selected' : '';
                                echo '<option value="' . $tp['id_rubro'] . '" ' . $slc . '>' . mb_strtoupper($tp['nombre']) . '</option>';
                            } ?>
                        </select>
                    </div>

                </div>
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="txtRubroAdmin" class="small">RUBRO ADMINISTRATIVO</label>
                        <input type="text" id="txtRubroAdmin" class="form-control form-control-sm buscaRubro" value="<?php echo $rubro['nom_admin']; ?>">
                        <input type="hidden" id="idRubroAdmin" name="idRubroAdmin" class="id_rb" value="<?php echo $rubro['r_admin']; ?>">
                        <input type="hidden" id="tp_dato_radm" class="id_tp" value="<?php echo $rubro['tp_a']; ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="txtRubroOpera" class="small">RUBRO OPERATIVO</label>
                        <input type="text" id="txtRubroOpera" class="form-control form-control-sm buscaRubro" value="<?php echo $rubro['nom_operativo']; ?>">
                        <input type="hidden" id="idRubroOpera" name="idRubroOpera" class="id_rb" value="<?php echo $rubro['r_operativo']; ?>">
                        <input type="hidden" id="tp_dato_rope" class="id_tp" value="<?php echo $rubro['tp_o']; ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnGuardaRubroNom">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>