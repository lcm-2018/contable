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
                    nom_novedades_afp
                WHERE id_novafp = '$id'";
    $rs = $cmd->query($sql);
    $novafp = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_afp ORDER BY nombre_afp ASC";
    $rs = $cmd->query($sql);
    $afps = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR NOVEDAD AFP</h5>
        </div>
        <form id="formUpNovAfp">
            <input type="number" name="numidnovafp" value="<?php echo $novafp['id_novafp'] ?>" hidden="true">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="slcUpNovAfp" class="small">AFP</label>
                    <select id="slcUpNovAfp" name="slcUpNovAfp" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <?php
                        $fec_afilafp = $novafp['fec_afiliacion'];
                        $fec_retirafp = $novafp['fec_retiro'];
                        $idaf = $novafp['id_afp'];
                        foreach ($afps as $a) {
                            if ($a['id_afp'] !== $idaf) {
                                echo '<option value="' . $a['id_afp'] . '">' . $a['nombre_afp'] . '</option>';
                            } else {
                                echo '<option selected value="' . $a['id_afp'] . '">' . $a['nombre_afp'] . '</option>';
                            }
                        }
                        ?>
                        <select>
                </div>
                <div class="form-group col-md-3">
                    <label for="datFecAfilUpNovAfp" class="small">Afilición</label>
                    <input type="date" class="form-control form-control-sm" id="datFecAfilUpNovAfp" name="datFecAfilUpNovAfp" value="<?php echo $fec_afilafp ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="datFecRetUpNovAfp" class="small">Retiro</label>
                    <input type="date" class="form-control form-control-sm" id="datFecRetUpNovAfp" name="datFecRetUpNovAfp" value="<?php echo $fec_retirafp ?>">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarAfp">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>