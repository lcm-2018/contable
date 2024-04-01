<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_contrato, nom_empleado.id_empleado, no_documento, CONCAT(nombre1, ' ', nombre2, ' ', apellido1, ' ', apellido2) AS nombre, fec_inicio, fec_fin, vigencia, tot_dias_lab, tot_dias_vac, sal_base, aux_transp, val_prima, val_cesantias, val_icesantias, val_vacaciones
            FROM
                nom_liq_contrato_emp
            INNER JOIN nom_contratos_empleados 
                ON (nom_liq_contrato_emp.id_contrato = nom_contratos_empleados.id_contrato_emp)
            INNER JOIN nom_empleado 
                ON (nom_contratos_empleados.id_empleado = nom_empleado.id_empleado)
            WHERE vigencia = '$vigencia'";
    $rs = $cmd->query($sql);
    $contratos_liquidados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT nom_liq_vac.id_vac, id_contrato, dias_habiles, anio_vac
            FROM
                nom_liq_vac
            INNER JOIN nom_vacaciones 
                ON (nom_liq_vac.id_vac = nom_vacaciones.id_vac)
            WHERE anio_vac = '$vigencia'";
    $rs = $cmd->query($sql);
    $vac_liq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, SUM(val_liq_ps) AS tot_prima 
            FROM 
                (SELECT * FROM nom_liq_prima WHERE anio = '$vigencia') AS t
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $prima_liq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTA CONTRATOS LIQUIDADOS VIGENCIA <?php echo $vigencia ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="table-responsive w-100">
                                <table id="dataTableDetallLiqContratos" class="table-bordered table-sm  order-column nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="background-color: rgb(22, 160, 133);">Nombre Completo</th>
                                            <th class="text-center">No. Documento</th>
                                            <th class="text-center">No. Contratdo</th>
                                            <th class="text-center">Fecha Inicio</th>
                                            <th class="text-center">Fecha Terminación</th>
                                            <th class="text-center">Días Laborados</th>
                                            <th class="text-center">Salario Base</th>
                                            <th class="text-center">Aux. Transporte</th>
                                            <th class="text-center">Prima</th>
                                            <th class="text-center">Cesantias</th>
                                            <th class="text-center">I. Cesantias</th>
                                            <th class="text-center">Vacaciones</th>
                                            <th class="text-center">Liquidación <?php echo $vigencia ?></th>
                                            <th class="text-center">Reporte</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contratos_liquidados as $cl) {
                                            $id_emp = $cl['id_empleado'];
                                            $key = array_search($id_emp, array_column($prima_liq, 'id_empleado'));
                                            if (false !== $key) {
                                                $v_prima = $prima_liq[$key]['tot_prima'];
                                            } else {
                                                $v_prima = 0;
                                            }
                                            $id_contra = $cl['id_contrato'];
                                            $t_diasvac = $cl['tot_dias_vac'];
                                            $key = array_search($id_contra, array_column($vac_liq, 'id_contrato'));
                                            if (false !== $key) {
                                                $v_vacaciones = ($vac_liq[$key]['dias_habiles']) * ($cl['val_vacaciones'] / $t_diasvac);
                                            } else {
                                                $v_vacaciones = 0;
                                            }
                                            $tot_liquidacion = $cl['val_cesantias'] + $cl['val_icesantias'] + $cl['val_prima']  + $cl['val_vacaciones'] - $v_prima - $v_vacaciones;
                                        ?>
                                            <tr>
                                                <td><?php echo mb_strtoupper($cl['nombre']) ?></td>
                                                <td><?php echo $cl['no_documento'] ?></td>
                                                <td><?php echo 'CNE-' . $cl['id_contrato'] ?></td>
                                                <td><?php echo $cl['fec_inicio'] ?></td>
                                                <td><?php echo $cl['fec_fin'] ?></td>
                                                <td><?php echo $cl['tot_dias_lab'] ?></td>
                                                <td><?php echo pesos($cl['sal_base']) ?></td>
                                                <td><?php echo pesos($cl['aux_transp']) ?></td>
                                                <td><?php echo pesos($cl['val_prima']) ?></td>
                                                <td><?php echo pesos($cl['val_cesantias']) ?></td>
                                                <td><?php echo pesos($cl['val_icesantias']) ?></td>
                                                <td><?php echo pesos($cl['val_vacaciones']) ?></td>
                                                <td><?php echo pesos($tot_liquidacion) ?></td>
                                                <td>
                                                    <div class="text-center"><a value="<?php echo $cl['id_contrato'] ?>" class="btn btn-outline-danger btn-sm btn-circle shadow-gb reporte" title="Reporte"><span class="fas fa-file-pdf fa-lg"></span></a></div>
                                                </td>
                                            </tr>
                                        <?php }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="center-block py-2">
                                <div class="form-group">
                                    <a type="button" class="btn btn-secondary" href="javascript: history.go(-1)"> Regresar</a>
                                    <a type="button" class="btn btn-secondary " href="../../inicio.php"> Cancelar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>