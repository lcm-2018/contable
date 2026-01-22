<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
                FROM
                    nom_novedades_eps
                WHERE id_novedad = '$id'";
    $rs = $cmd->query($sql);
    $eps = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_epss ORDER BY nombre_eps ASC";
    $rs = $cmd->query($sql);
    $epsnov = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR NOVEDAD EPS</h5>
        </div>
        <form id="formUpNovEps">
            <input type="number" name="numidnov" value="<?php echo $id ?>" hidden="true">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="slcUpNovEps" class="small">EPS</label>
                    <select id="slcUpNovEps" name="slcUpNovEps" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <?php
                        $fec_afil = $eps['fec_afiliacion'];
                        $fec_retir = $eps['fec_retiro'];
                        $idep = $eps['id_eps'];
                        foreach ($epsnov as $e) {
                            if ($e['id_eps'] !== $idep) {
                                echo '<option value="' . $e['id_eps'] . '">' . $e['nombre_eps'] . '</option>';
                            } else {
                                echo '<option selected value="' . $e['id_eps'] . '">' . $e['nombre_eps'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="datFecAfilUpNovEps" class="small">Afilición</label>
                    <input type="date" class="form-control form-control-sm" id="datFecAfilUpNovEps" name="datFecAfilUpNovEps" value="<?php echo $fec_afil ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="datFecRetUpNovEps" class="small">Retiro</label>
                    <input type="date" class="form-control form-control-sm" id="datFecRetUpNovEps" name="datFecRetUpNovEps" value="<?php echo $fec_retir ?>">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarEps">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>