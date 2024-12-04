<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

function listarhe($mes)
{
    include '../../../conexion.php';
    $anio = $_SESSION['vigencia'];
    $dia = '01';
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
        $sql = "SELECT  id_empleado, no_documento, CONCAT(nombre1, ' ', nombre2) AS nombres, CONCAT(apellido1, ' ', apellido2) AS apellidos, SUM( valor) AS total_viat
                FROM
                    nom_viaticos
                INNER JOIN nom_empleado 
                    ON (nom_viaticos.id_emplead = nom_empleado.id_empleado)
                INNER JOIN seg_detalle_viaticos 
                    ON (seg_detalle_viaticos.id_viaticos = nom_viaticos.id_viaticos)
                WHERE seg_detalle_viaticos.fviatico BETWEEN '$fec_i' AND '$fec_f'
                GROUP BY nom_empleado.id_empleado";
        $rs = $cmd->query($sql);
        $obj = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $obj;
}

include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_meses";
    $rs = $cmd->query($sql);
    $meses = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-clipboard-list fa-lg" style="color:#1D80F7"></i>
                                    LISTADO DE VIÁTICOS.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div clas="row">
                                <div class="center-block">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                                                <select class="custom-select" id="slcMesHe" name="slcMesHe" onChange="elegirmes(this.value);">
                                                    <option selected disabled>--Seleccionar mes--</option>
                                                    <?php
                                                    foreach ($meses as $m) {
                                                        echo '<option value="' . $m['codigo'] . '">' . $m['nom_mes'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </form>
                                            <div class="input-group-append">
                                                <?php
                                                if (isset($_POST["slcMesHe"])) {
                                                    $mes = $_POST["slcMesHe"];
                                                    echo '<label class="input-group-text">';
                                                    switch ($mes) {
                                                        case '01':
                                                            echo 'Enero';
                                                            break;
                                                        case '02':
                                                            echo 'Febrero';
                                                            break;
                                                        case '03':
                                                            echo 'Marzo';
                                                            break;
                                                        case '04':
                                                            echo 'Abril';
                                                            break;
                                                        case '05':
                                                            echo 'Mayo';
                                                            break;
                                                        case '06':
                                                            echo 'Junio';
                                                            break;
                                                        case '07':
                                                            echo 'Julio';
                                                            break;
                                                        case '08':
                                                            echo 'Agosto';
                                                            break;
                                                        case '09':
                                                            echo 'Septiembre';
                                                            break;
                                                        case '10':
                                                            echo 'Octubre';
                                                            break;
                                                        case '11':
                                                            echo 'Noviembre';
                                                            break;
                                                        case '12':
                                                            echo 'Diciembre';
                                                            break;
                                                    }
                                                    echo '</label>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if (isset($_POST["slcMesHe"])) {
                                $mes = $_POST["slcMesHe"];
                                $obj = listarhe($mes);
                            } else {
                                $mes = '00';
                                $obj = listarhe($mes);
                            }
                            ?>
                            <div class="table-responsive">
                                <table id="dataTable" class="table table-striped table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No. Doc.</th>
                                            <th class="text-center">Nombres</th>
                                            <th class="text-center">Apellidos</th>
                                            <th class="text-center">Total Viáticos</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($obj as $o) {
                                        ?>
                                            <tr id="filaempl">
                                                <td><?php echo $o['no_documento'] ?></td>
                                                <td><?php echo mb_strtoupper($o['nombres']) ?></td>
                                                <td><?php echo mb_strtoupper($o['apellidos']) ?></td>
                                                <td><?php echo pesos($o['total_viat']) ?></td>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-md-3 offset-md-3">
                                                            <?php
                                                            if (intval($permisos['editar']) === 1) {
                                                            ?>
                                                                <form action="actualizar/listupviat.php" method="POST">
                                                                    <input type="text" name="idUpViat" value="<?php echo $o['id_empleado'] ?>" hidden="true">
                                                                    <input type="text" name="idMesViat" value="<?php echo $mes ?>" hidden="true">
                                                                    <button type="submit" class="btn btn-outline-primary btn-sm btn-circle" title="Editar">
                                                                        <i class="fas fa-pencil-alt fa-lg"></i>
                                                                    </button>
                                                                </form>
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
                        </div>
                    </div>

                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
    </div>
    <?php include '../../../scripts.php' ?>
</body>

</html>