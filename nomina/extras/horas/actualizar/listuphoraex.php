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
if (isset($_POST['idMesHe'])) {
    $mes = $_POST['idMesHe'];
    $idemphex = $_POST['idUpHe'];
} else {
    $mes = $_GET['idMesHe'];
    $idemphex = $_GET['idUpHe'];
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
    $sql = "SELECT CONCAT(nombre1, ' ', nombre2, ' ',apellido1, ' ', apellido2) AS nomcomp, no_documento, id_he_trab,desc_he,cantidad_he, fec_inicio, fec_fin, hora_inicio, hora_fin
            FROM
                nom_horas_ex_trab
            INNER JOIN nom_empleado 
                ON (nom_horas_ex_trab.id_empleado = nom_empleado.id_empleado)
            INNER JOIN nom_tipo_horaex 
                ON (nom_horas_ex_trab.id_he = nom_tipo_horaex.id_he)
            WHERE  nom_empleado.id_empleado = '$idemphex' AND fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
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
                            ACTUALIZAR HORAS EXTRA.
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
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Fecha Incio</th>
                                            <th class="text-center">Fecha Fin</th>
                                            <th class="text-center">Hora Inicio</th>
                                            <th class="text-center">Hora Fin</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($obj as $o) {
                                        ?>
                                            <tr id="filaempl">
                                                <td><?php echo $o['desc_he'] ?></td>
                                                <td class="text-center"><?php echo $o['fec_inicio'] ?></td>
                                                <td class="text-center"><?php echo $o['fec_fin'] ?></td>
                                                <td class="text-center"><?php echo date("g:i:s a", strtotime($o['hora_inicio'])) ?></td>
                                                <td class="text-center"><?php echo date("g:i:s a", strtotime($o['hora_fin'])) ?></td>
                                                <td class="text-center"><?php echo $o['cantidad_he'] ?></td>
                                                <td>
                                                    <div class="center-block">
                                                        <div class="input-group">
                                                            <?php
                                                            if (PermisosUsuario($permisos, 5102, 3) || $id_rol == 1) {
                                                            ?>
                                                                <button value="<?php echo $o['id_he_trab'] . '|' . $mes . '|' . $idemphex ?>" type="button" class="btn btn-outline-primary btn-sm btn-circle editarHEespecifca" title="Editar">
                                                                    <i class="fas fa-pencil-alt fa-lg"></i>
                                                                </button>
                                                            <?php }
                                                            if (PermisosUsuario($permisos, 5102, 4) || $id_rol == 1) {
                                                            ?>
                                                                <div id="elimhoex">
                                                                    <button class="btn btn-outline-danger btn-sm btn-circle" value="<?php echo $o['id_he_trab'] ?>" title="Eliminar">
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
                            <a type="button" class="btn btn-secondary  btn-sm" href="../listhoraextra.php"> Cancelar</a>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../../footer.php' ?>
        </div>
        <div class="modal fade" id="divModalHoExitoExito" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgExitoHoex">

                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="divConfirmdelHex" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeaderConfir">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                            ¡Confirmar!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgConfirmDel">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" id="btnModalConfdelHe">Aceptar</button>
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
                        <button type="button" class="btn btn-secondary  btn-sm" id="btnXErrorDel">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../../../scripts.php' ?>
</body>

</html>