<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$idemviat = isset($_POST['idEmViat']) ? $_POST['idEmViat'] : exit('Acción no permitida');
$vig = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                `nom_salarios_basico`
            INNER JOIN nom_empleado 
                ON (`nom_salarios_basico`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE `id_salario`
		        IN(SELECT MAX(`id_salario`) FROM `nom_salarios_basico` WHERE `vigencia` = '$vig' AND `id_empleado` = '$idemviat')";
    $rs = $cmd->query($sql);
    $empleado = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$sal = $empleado['salario_basico'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_vigencia FROM tb_vigencias WHERE anio = '$vig'";
    $rs = $cmd->query($sql);
    $vigen = $rs->fetch();
    $idvig = $vigen['id_vigencia'];
    $sql = "SELECT val_viatico_dia FROM nom_rango_viaticos WHERE val_min <= '$sal' AND val_max >= '$sal' AND vigencia = '$idvig'";
    $rs = $cmd->query($sql);
    $valviat = $rs->fetch();
    $valviatdia = $valviat['val_viatico_dia'];
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
                            <i class="fas fa-clipboard-list fa-lg" style="color: #07CF74;"></i>
                            REGISTRO DE VIÁTICOS.
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="row">
                                <div class="center-block">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text" id="divNomComp">
                                                <?php
                                                echo mb_strtoupper($empleado['nombre1'] . ' ' . $empleado['nombre2'] . ' ' . $empleado['apellido1'] . ' ' . $empleado['apellido2']);
                                                ?>
                                            </div>
                                        </div>
                                        <div class="input-group-prepend">
                                            <div class="input-group-text" id="divNomComp">
                                                <?php echo $empleado['no_documento']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <form id="formAddViat">
                                <div class="form-row">
                                    <input type="text" id="txtDocEmpViat" name="txtDocEmpViat" value="<?php echo $empleado['no_documento'] ?>" hidden>
                                    <div class="form-group col-md-12">
                                        <label for="txtDescViat">Descripción general</label>
                                        <input type="text" class="form-control" id="txtDescViat" name="txtDescViat" placeholder="Breve descripción de los viáticos">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-1 text-center">
                                        <label for="txtConcepViat">Pernoctado</label>
                                        <div class="input-group-text form-control center-block">
                                            <input type="checkbox" name="checkP0" checked value="1">
                                        </div>
                                    </div>
                                    <!--
                                    <div class="form-group col-md-1 text-center">
                                        <label for="slcCaracSalarial">Salarial</label>
                                        <select id="slcCaracSalarial" name="slcCaracSalarial" class="form-control py-0" aria-label="Default select example">
                                            <option value="1">Si</option>
                                            <option selected value="0">No</option>
                                        </select>
                                    </div>
                                    -->
                                    <div class="form-group col-md-4 text-center">
                                        <label for="txtConcepViat">Concepto</label>
                                        <input type="text" class="form-control" id="txtConcepViat0" name="txtConcepViat0" value="Dia 1" placeholder="Descripción especifica viático">
                                    </div>
                                    <div class="form-group col-md-3 text-center">
                                        <label for="numValViat">Valor</label>
                                        <input type="number" class="form-control" id="numValViat0" name="numValViat0" value="<?php echo $valviatdia ?>" placeholder="Valor viático">
                                    </div>
                                    <div class="form-group col-md-3 text-center">
                                        <label for="datFecViat">Fecha viático</label>
                                        <input type="date" class="form-control" id="datFecViat0" name="datFecViat0" value="<?php echo date("Y-m-d") ?>" placeholder="Fecha viáticos">
                                    </div>
                                    <div class="form-group col-md-1 text-center" id="btnAddRowViat">
                                        <button class="btn btn-info btn-circle-plus btn-sm" value="<?php echo $valviatdia ?>"><i class="fas fa-plus-circle fa-lg"></i></i></button>
                                    </div>
                                </div>
                                <div id="conten-viaticos">
                                    <?php
                                    for ($i = 1; $i < 20; $i++) {
                                        echo '<div class="form-row" id="fila' . $i . '"></div>';
                                    }
                                    ?>
                                </div>
                                <br>
                                <button class="btn btn-primary btn-sm" id="btnAddViat">Registrar</button>
                                <a type="button" class="btn btn-secondary  btn-sm" href="../../../empleados/listempleados.php"> Cancelar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../../footer.php' ?>
        </div>
        <div class="modal fade" id="divModalExitoNewViat" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgExitoNewviat">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" id="btnModalExitoNewViat">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="divModalErrorNewViat" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeader">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                            ¡Error!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgErrorNewViat">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" id="btnModalErrorNewViat">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../../../scripts.php' ?>
</body>

</html>