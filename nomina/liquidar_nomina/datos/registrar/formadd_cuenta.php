<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_causacion = isset($_POST['id_causacion']) ? $_POST['id_causacion'] : exit("Acción no permitida");
include '../../../../conexion.php';
$centros = [
    '0' => ['id' => 'ADMIN', 'nombre' => 'ADMINISTRATIVO'],
    '1' => ['id' => 'URG', 'nombre' => 'URGENCIAS'],
    '2' => ['id' => 'PASIVO', 'nombre' => 'PASIVOS'],
];
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
                `nom_causacion`.`id_causacion`
                , `ctb_pgcp`.`id_pgcp` AS `id_cuenta`
                , CONCAT_wS(' -> ', `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`) AS `nom_cta`
                , `ctb_pgcp`.`tipo_dato` AS `tp`
                , `nom_causacion`.`centro_costo` AS `id_cc`
                , CASE
                    WHEN `nom_causacion`.`centro_costo` = 'ADMIN' THEN 'ADMINISTRATIVO'
                    WHEN `nom_causacion`.`centro_costo` = 'URG' THEN 'URGENCIAS'
                    WHEN `nom_causacion`.`centro_costo` = 'PASIVO' THEN 'PASIVOS'
                    ELSE `nom_causacion`.`centro_costo`
                END AS `centro_costo`
                , `nom_causacion`.`id_tipo`
                , `nom_tipo_rubro`.`nombre`
            FROM
                `nom_causacion`
                LEFT JOIN `nom_tipo_rubro` 
                    ON (`nom_causacion`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
                LEFT JOIN `ctb_pgcp` 
                    ON (`nom_causacion`.`cuenta` = `ctb_pgcp`.`id_pgcp`)
            WHERE (`nom_causacion`.`id_causacion` = $id_causacion)";
    $rs = $cmd->query($sql);
    $cuenta = $rs->fetch(PDO::FETCH_ASSOC);
    if (empty($cuenta)) {
        $cuenta = [
            'id_causacion' => '0',
            'id_cuenta' => '0',
            'nom_cta' => '',
            'tp' => 'M',
            'id_cc' => '0',
            'centro_costo' => '',
            'id_tipo' => '0',
            'nombre' => ''
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
            <h5 style="color: white;">GESTIONAR CUENTA CONTABLE DE NÓMINA</h5>
        </div>
        <div class="px-2">
            <form id="formGestCtaNom">
                <input type="hidden" id="id_causacion" name="id_causacion" value="<?php echo $cuenta['id_causacion']; ?>">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="slcTipo" class="small">TIPO</label>
                        <select name="slcTipo" id="slcTipo" class="form-control form-control-sm">
                            <option value="0" <?php $cuenta['id_tipo'] == '0' ? 'selected' : ''; ?>>--Seleccione--</option>
                            <?php foreach ($tipo as $tp) {
                                $slc = $cuenta['id_tipo'] == $tp['id_rubro'] ? 'selected' : '';
                                echo '<option value="' . $tp['id_rubro'] . '" ' . $slc . '>' . mb_strtoupper($tp['nombre']) . '</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="slcCentroCosto" class="small">CENTRO DE COSTO</label>
                        <select name="slcCentroCosto" id="slcCentroCosto" class="form-control form-control-sm">
                            <option value="0" <?php $cuenta['id_cc'] == '0' ? 'selected' : ''; ?>>--Seleccione--</option>
                            <?php foreach ($centros as $tp) {
                                $slc = $cuenta['id_cc'] == $tp['id'] ? 'selected' : '';
                                echo '<option value="' . $tp['id'] . '" ' . $slc . '>' . mb_strtoupper($tp['nombre']) . '</option>';
                            } ?>
                        </select>
                    </div>

                </div>
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="txtBuscaCuentaCtb" class="small">CUENTA CONTABLE</label>
                        <input type="text" id="txtBuscaCuentaCtb" name="txtBuscaCuentaCtb" class="form-control form-control-sm" value="<?php echo $cuenta['nom_cta']; ?>">
                        <input type="hidden" id="idCtaCtb" name="idCtaCtb" value="<?php echo $cuenta['id_cuenta']; ?>">
                        <input type="hidden" id="tipoCta" value="<?php echo $cuenta['tp']; ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnGuardaCuentaNom">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>