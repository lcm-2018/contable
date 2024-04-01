<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT *
            FROM
                nom_novedades_arl
            WHERE id_novarl = '$id'";
    $rs = $cmd->query($sql);
    $nov_arl = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_riesgos_laboral";
    $rs = $cmd->query($sql);
    $rlaboral = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_arl ORDER BY nombre_arl ASC";
    $rs = $cmd->query($sql);
    $arls = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR NOVEDAD ARL</h5>
        </div>
        <form id="formUpNovArl">
            <input type="number" name="numidnovarl" value="<?php echo $nov_arl['id_novarl'] ?>" hidden="true">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-5">
                    <label for="slcUpNovArl" class="small">ARL</label>
                    <select id="slcUpNovArl" name="slcUpNovArl" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <?php
                        $fec_afil = $nov_arl['fec_afiliacion'];
                        $fec_retir = $nov_arl['fec_retiro'];
                        $idar = $nov_arl['id_arl'];
                        $idrl = $nov_arl['id_riesgo'];
                        foreach ($arls as $a) {
                            if ($a['id_arl'] !== $idar) {
                                echo '<option value="' . $a['id_arl'] . '">' . $a['nombre_arl'] . '</option>';
                            } else {
                                echo '<option selected value="' . $a['id_arl'] . '">' . $a['nombre_arl'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="slcRiesLabNovup" class="small">Riesgo laboral</label>
                    <select id="slcRiesLabNovup" name="slcRiesLabNovup" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <?php
                        foreach ($rlaboral as $r) {
                            if ($r['id_rlab'] !== $idrl) {
                                echo '<option value="' . $r['id_rlab'] . '">' . $r['clase'] . ' - ' . $r['riesgo'] . '</option>';
                            } else {
                                echo  '<option selected value="' . $r['id_rlab'] . '">' . $r['clase'] . ' - ' . $r['riesgo'];
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="datFecAfilUpNovArl" class="small">Afilición</label>
                    <input type="date" class="form-control form-control-sm" id="datFecAfilUpNovArl" name="datFecAfilUpNovArl" value="<?php echo $fec_afil ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="datFecRetUpNovArl" class="small">Retiro</label>
                    <input type="date" class="form-control form-control-sm" id="datFecRetUpNovArl" name="datFecRetUpNovArl" value="<?php echo $fec_retir ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm actualizarArl">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>