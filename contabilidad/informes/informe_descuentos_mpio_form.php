<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
include '../../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php
$vigencia = $_SESSION['vigencia'];
// concateno la fecha con el año vigencia
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$fecha_min = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_actual = $fecha->format('Y-m-d');
// obtengo la lista de municipio asociados a las sedes de la empresa
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$sql = "SELECT
            `tb_municipios`.`nom_municipio`
            , `tb_sedes`.`id_tercero`
        FROM
            `tb_sedes`
        INNER JOIN `tb_municipios` 
        ON (`tb_sedes`.`id_municipio` = `tb_municipios`.`id_municipio`);";
$rs = $cmd->query($sql);
$sedes = $rs->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-sm-8 ">
        <div class="card">
            <h5 class="card-header small">Informe de impuestos municipales aplicados</h5>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">Municipio:</div>
                        <div class="col-3">
                            <select name="tipo_sede" id="tipo_sede" class="form-control form-control-sm">
                                <option value="0">-- Selecionar --</option>
                                <?php
                                foreach ($sedes as $sed) {
                                    echo '<option value="' . $sed['id_tercero'] . '">' . $sed['nom_municipio'] .  '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">Fecha de inicial:</div>
                        <div class="col-3"><input type="date" name="fecha_ini" id="fecha_ini" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_min; ?>"></div>
                    </div>

                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">Fecha de corte:</div>
                        <div class="col-3"><input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_actual; ?>"></div>
                    </div>

                    <div class="px-50">&nbsp; </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center pt-3">
                                <a type="button" class="btn btn-primary btn-sm" onclick="generarInformeCtb(1);"> Resumen</a>
                                <a type="button" class="btn btn-warning btn-sm" onclick="generarInformeCtb(2);"> Detallado</a>
                                <a type="button" class="btn btn-secondary btn-sm" onclick="generarInformeCtb(3);"> Exogena</a>
                            </div>
                        </div>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>