<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$anio = $_SESSION['vigencia'];
include '../../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$dia = '01';
if (isset($_POST['idMesViat'])) {
    $mes = $_POST['idMesViat'];
    $idempviat = $_POST['idUpViat'];
} else {
    $mes = $_GET['idMesViat'];
    $idempviat = $_GET['idUpViat'];
}

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

switch ($mes) {
    case '00':
        $fec_i = $anio . '-01-01';
        $fec_f = $anio . '-12-31';
        break;
    case '01':
    case '03':
    case '05':
    case '07':
    case '08':
    case '10':
    case '12':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        $fec_f = $anio . '-' . $mes . '-31';
        break;
    case '02':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        if (date('L', strtotime("$anio-01-01")) === '1') {
            $bis = '29';
        } else {
            $bis = '28';
        }
        $fec_f = $anio . '-' . $mes . '-' . $bis;
        break;
    case '04':
    case '06':
    case '09':
    case '11':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        $fec_f = $anio . '-' . $mes . '-30';
        break;
    default:
        echo 'Error Fatal';
        break;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_detviatic, CONCAT(nombre1, ' ', nombre2, ' ',apellido1, ' ', apellido2) AS nomcomp, no_documento, desc_general,concepto,valor,fviatico
            FROM
                nom_viaticos
            INNER JOIN nom_empleado 
                ON (nom_viaticos.id_emplead = nom_empleado.id_empleado)
            INNER JOIN seg_detalle_viaticos 
                ON (seg_detalle_viaticos.id_viaticos = nom_viaticos.id_viaticos)
            WHERE  nom_empleado.id_empleado = '$idempviat' AND fviatico BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
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
                            <i class="fas fa-list-alt fa-lg" style="color: #07CF74;"></i>
                            ACTUALIZAR VIÁTICOS.
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="row">
                                <div class="center-block">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text" id="divNomComp">
                                                <?php
                                                if ($obj) {
                                                    foreach ($obj as $f) {
                                                        $nombrecompleto = $f['nomcomp'];
                                                        $doc = $f['no_documento'];
                                                    }
                                                    echo mb_strtoupper($nombrecompleto);
                                                } else {
                                                    echo 'No hay registros';
                                                    $doc = '0';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="input-group-prepend">
                                            <div class="input-group-text" id="divNomComp">
                                                <?php echo $doc ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="dataTable" class="table table-striped table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Des_general</th>
                                            <th class="text-center">Concepto</th>
                                            <th class="text-center">Valor</th>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($obj as $o) {
                                        ?>
                                            <tr id="filaempl">
                                                <td><?php echo $o['desc_general'] ?></td>
                                                <td><?php echo $o['concepto'] ?></td>
                                                <td><?php echo pesos($o['valor']) ?></td>
                                                <td><?php echo $o['fviatico'] ?></td>
                                                <td>
                                                    <div class="center-block">
                                                        <div class="input-group">
                                                            <?php
                                                            if (intval($permisos['editar']) === 1) {
                                                            ?>
                                                                <form action="formupviat.php" method="POST">
                                                                    <input type="number" name="numIdUpViat" value="<?php echo $o['id_detviatic'] ?>" hidden="true">
                                                                    <input type="number" name="numIdMesUpViat" value="<?php echo $mes ?>" hidden="true">
                                                                    <input type="number" name="numIdUpEmplViatic" value="<?php echo $idempviat ?>" hidden="true">
                                                                    <button type="submit" class="btn btn-outline-primary btn-sm btn-circle" title="Editar">
                                                                        <i class="fas fa-pencil-alt fa-lg"></i>
                                                                    </button>
                                                                </form>
                                                            <?php }
                                                            if (intval($permisos['borrar']) === 1) {
                                                            ?>
                                                                <div id="elimviat">
                                                                    <button class="btn btn-outline-danger btn-sm btn-circle" value="<?php echo $o['id_detviatic'] ?>" title="Eliminar">
                                                                        <i class="fas fa-trash-alt fa-lg"></i>
                                                                    </button>
                                                                </div>
                                                            <?php } ?>
                                                        </div>

                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>

                                    </tbody>

                                </table>
                            </div>
                            <br>
                            <a type="button" class="btn btn-secondary  btn-sm" href="../listviaticos.php"> Cancelar</a>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../../footer.php' ?>
        </div>
        <div class="modal fade" id="divModalExitoDelViat" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgExitoDelViat">

                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="divConfirmdelViat" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeaderConfir">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                            ¡Confirmar!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgConfirmDelviat">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" id="btnModalConfdelViat">Aceptar</button>
                        <button type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</button>
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
                        <button type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../../../scripts.php' ?>
</body>

</html>