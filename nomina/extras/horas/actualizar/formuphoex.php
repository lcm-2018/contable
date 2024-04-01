<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$idhetrab = isset($_POST['idUpHoexs']) ? $_POST['idUpHoexs'] : exit('Acción no permitida');
$m = $_POST['valmesreghe'];
$idreg = $_POST['validreghe'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_he_trab,nom_horas_ex_trab.id_he,desc_he, fec_inicio, fec_fin, hora_inicio, hora_fin, cantidad_he FROM nom_horas_ex_trab
            INNER JOIN nom_tipo_horaex 
                ON (nom_horas_ex_trab.id_he = nom_tipo_horaex.id_he)
            WHERE id_he_trab = '$idhetrab'";
    $res = $cmd->query($sql);
    $obj = $res->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_tipo_horaex";
    $rs = $cmd->query($sql);
    $fila = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../../../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <i class="fas fa-clock fa-lg" style="color: #07CF74;"></i>
                            MODIFICAR HORAS EXTRA.
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formupHoex">
                                <input type="number" id="idHelab" name="idHelab" value="<?php echo $obj['id_he_trab'] ?>" hidden="true">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="slcTipoHeup" class="small">Tipo de hora extra</label>
                                        <select id="slcTipoHeup" name="slcTipoHeup" class="form-control form-control-sm" aria-label="Default select example">
                                            <option selected value="<?php echo $obj['id_he'] ?>"><?php echo $obj['desc_he'] ?></option>
                                            <?php
                                            foreach ($fila as $f) {
                                                if ($f['id_he'] !== $obj['id_he']) {
                                                    echo '<option value="' . $f['id_he'] . '">' . $f['desc_he'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="datFecLabHeIup" class="small">Fecha inicio</label>
                                        <input type="date" class="form-control form-control-sm" id="datFecLabHeIup" name="datFecLabHeIup" value="<?php echo $obj['fec_inicio'] ?>">
                                        <div id="edatFecLabHeIup" class="invalid-tooltip">
                                            <?php echo "Fecha Fin es Menor" ?>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="datFecLabHeFup" class="small">Fecha fin</label>
                                        <input type="date" class="form-control form-control-sm" id="datFecLabHeFup" name="datFecLabHeFup" value="<?php echo $obj['fec_fin'] ?>">
                                        <div id="edatFecLabHeFup" class="invalid-tooltip">
                                            <?php echo "Fecha Inicio es Mayor" ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="timeInicioHeup" class="small">Hora inicio</label>
                                        <input type="time" class="form-control form-control-sm" id="timeInicioHeup" name="timeInicioHeup" step="2" value="<?php echo $obj['hora_inicio'] ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="timeFinHeup" class="small">Hora fin</label>
                                        <input type="time" class="form-control form-control-sm" id="timeFinHeup" name="timeFinHeup" step="2" value="<?php echo $obj['hora_fin'] ?>">
                                        <div id="etimeFinHeup" class="invalid-tooltip">
                                            <?php echo "Hora Fin es Menor" ?>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="" class="small">Cantidad</label>
                                        <input type="number" id="numCantHeup" name="numCantHe" class="form-control form-control-sm" value="<?php echo $obj['cantidad_he'] ?>">
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-sm" id="btnUpHoex"> Actualizar</button>
                                <a type="button" class="btn btn-secondary  btn-sm" href="../listhoraextra.php"> Cancelar</a>
                                <a type="button" class="btn btn-secondary btn-sm" href="listuphoraex.php?idUpHe=<?php echo $idreg ?>&idMesHe=<?php echo $m ?>"> Regresar</a>
                            </form>

                        </div>
                    </div>
            </main>
            <?php include '../../../../footer.php' ?>
        </div>
        <div class="modal fade" id="divModalHoExDone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgDoneHoEx">

                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" href="listuphoraex.php?idUpHe=<?php echo $idreg ?>&idMesHe=<?php echo $m ?>"> Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="divModalErrorUpHoEx" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeader">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                            ¡Error!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgErrorUpHoEx">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="divModalErrorDelHex" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeader">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                            ¡Error!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgErrorDel">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary  btn-sm" id="btnXErrorDel">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../../../scripts.php' ?>
</body>

</html>