<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$idviat = isset($_POST['numIdUpViat']) ? $_POST['numIdUpViat'] : exit('Acción no permitida');
$idmes = $_POST['numIdMesUpViat'];
$idemupviat = $_POST['numIdUpEmplViatic'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT id_detviatic, nom_viaticos.id_viaticos AS id_viat, desc_general, concepto, valor, fviatico
            FROM
                nom_viaticos 
            INNER JOIN seg_detalle_viaticos
                ON (seg_detalle_viaticos.id_viaticos = nom_viaticos.id_viaticos)
            WHERE id_detviatic = '$idviat'";
    $res = $cmd->query($sql);
    $obj = $res->fetch();
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
                            <i class="fas fa-clipboard-check fa-lg" style="color: #07CF74;"></i>
                            MODIFICAR VIÁTICOS
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formUpViat">
                                <input type="number" id="idViatUp" name="idViatUp" value="<?php echo $obj['id_detviatic'] ?>" hidden="true">
                                <input type="number" id="idViatUpNew" name="idViatUpNew" value="<?php echo $obj['id_viat'] ?>" hidden="true">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Descripción general</label>
                                        <div class="form-group input-group-text" id="slcTipoHe">
                                            <?php echo $obj['desc_general'] ?>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="txtUpConcepto">Concepto</label>
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="txtUpConcepto" name="txtUpConcepto" value="<?php echo $obj['concepto'] ?>" placeholder="Concepto de viático">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="numUpValor">Valor</label>
                                        <div class="form-group">
                                            <input type="number" class="form-control" id="numUpValor" name="numUpValor" value="<?php echo $obj['valor'] ?>" placeholder="Valor del viático">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="datFecViarUp">Fecha</label>
                                        <div class="form-group">
                                            <input type="date" class="form-control" id="datFecViarUp" name="datFecViarUp" value="<?php echo $obj['fviatico'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-1">
                                        <div class="row">
                                            <div class="col-md-1 offset-md-4">
                                                <button class="btn btn-info btn-circle-plus btn-sm" id="btnAddRowUpViat"><i class="fas fa-plus-circle fa-lg"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                for ($i = 1; $i < 5; $i++) {
                                    echo '<div class="form-row" id="filaup' . $i . '"></div>';
                                }
                                ?>
                                <button class="btn btn-primary btn-sm" id="btnUpViat"> Actualizar</button>
                                <a type="button" class="btn btn-secondary  btn-sm" href="../listviaticos.php">Cancelar</a>
                                <a type="button" class="btn btn-secondary btn-sm" href="listupviat.php?idMesViat=<?php echo $idmes ?>&idUpViat=<?php echo $idemupviat ?>"> Regresar</a>
                            </form>

                        </div>
                    </div>
            </main>
            <?php include '../../../../footer.php' ?>
        </div>
        <div class="modal fade" id="divModalViatDone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgDoneViat">

                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/viaticos/actualizar/listupviat.php?idMesViat=<?php echo $idmes ?>&idUpViat=<?php echo $idemupviat ?>"> Aceptar</a>
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