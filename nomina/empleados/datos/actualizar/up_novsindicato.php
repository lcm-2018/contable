<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$res = '';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
                FROM
                nom_cuota_sindical
                WHERE id_cuota_sindical = '$id'";
    $rs = $cmd->query($sql);
    $sindicato = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_sindicatos ORDER BY nom_sindicato ASC";
    $rs = $cmd->query($sql);
    $listsind = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR CUOTA SINDICAL</h5>
        </div>
        <form id="formUpSindicato">
            <input type="number" name="numidSindicato" value="<?php echo $sindicato['id_cuota_sindical'] ?>" hidden="true">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-8">
                    <label class="small" for="slcUpSindicato">sindicato</label>
                    <select id="slcUpSindicato" name="slcUpSindicato" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <?php
                        $porcentaje = $sindicato['porcentaje_cuota'];
                        $fecinsind = $sindicato['fec_inicio'];
                        $fecfinsind = $sindicato['fec_fin'];
                        $sindactual = $sindicato['id_sindicato'];
                        $val_sind = $sindicato['val_sidicalizacion'];
                        foreach ($listsind as $l) {
                            if ($l['id_sindicato'] !== $sindactual) {
                                echo '<option value="' . $l['id_sindicato'] . '">' . mb_strtoupper($l['nom_sindicato']) . '</option>';
                            } else {
                                echo '<option selected value="' . $l['id_sindicato'] . '">' . mb_strtoupper($l['nom_sindicato']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="small" for="txtUpPorcentajeSind">Porcentaje %</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" name="txtUpPorcentajeSind" id="txtUpPorcentajeSind" value="<?php echo $porcentaje ?>" placeholder="En decimal">
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label class="small" for="datUpFecInicioSind">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecInicioSind" name="datUpFecInicioSind" value="<?php echo $fecinsind ?>">
                    </div>
                    <div id="edatUpFecInicioSind" class="invalid-tooltip">
                        Inicio debe ser menor
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label class="small" for="datUpFecFinSind">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecFinSind" name="datUpFecFinSind" value="<?php echo $fecfinsind ?>">
                    </div>
                    <div id="edatUpFecFinSind" class="invalid-tooltip">
                        Fin debe ser Mayor
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label class="small" for="datFecFinSind">Valor sindicalización</label>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm" id="numValSindicalizar" name="numValSindicalizar" value="<?php echo $val_sind ?>">
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarSind">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>

        </form>
    </div>
</div>