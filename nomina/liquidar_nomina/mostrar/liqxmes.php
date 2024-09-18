<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencias = $_SESSION['vigencia'];

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
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
            WHERE vigencia = '$vigencias'";
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
            WHERE anio_vac = '$vigencias'";
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
                (SELECT * FROM nom_liq_prima WHERE anio = '$vigencias') AS t
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $prima_liq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT periodo, anio FROM nom_liq_prima GROUP BY periodo HAVING anio = $vigencias ORDER BY periodo ASC";
    $rs = $cmd->query($sql);
    $periodos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT periodo, anio FROM nom_liq_prima_nav GROUP BY periodo HAVING anio = $vigencias ORDER BY periodo ASC";
    $rs = $cmd->query($sql);
    $periodos_vac = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$j = 0;
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
                            <i class="fas fa-calendar-check fa-lg" style="color: #1D80F7"></i> LIQUIDADO.
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <table id="tableNominas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr class="text-center centro-vertical">
                                        <th>ID</th>
                                        <th>descripción</th>
                                        <th>Mes</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="accionNominas">
                                </tbody>
                            </table>
                            <?php if (false) { ?>
                                <div id="accordion">
                                    <!-- parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-clipboard-list fa-lg" style="color: #3498DB;"></span>
                                                        </div>
                                                        <div>
                                                            <?php $j++;
                                                            echo $j ?>. NÓMINA POR MES.
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseTwo" class="collapse" aria-labelledby="headingOne">
                                            <div class="card-body">

                                            </div>
                                        </div>
                                    </div>

                                    <!-- parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="headingPri">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsePri" aria-expanded="false" aria-controls="collapsePri">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-business-time fa-lg" style="color: #1ABC9C;"></span>
                                                        </div>
                                                        <div>
                                                            <?php $j++;
                                                            echo $j ?>. PRIMA<?php echo $_SESSION['caracter'] == 2 ? 'S' : ' DE SERVICIOS' ?>.
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapsePri" class="collapse" aria-labelledby="headingPri">
                                            <div class="card-body">
                                                <?php
                                                if (!empty($periodos)) { ?>
                                                    <div class="center-block periodospri">
                                                        <div class="container-fluid">
                                                            <div class="input-group">
                                                                <?php
                                                                foreach ($periodos as $per) { ?>
                                                                    <div id="gp<?php echo $per['periodo'] ?>" class="col-mb-4 py-2 px-3">
                                                                        <div class="card shadow-g" style="width: 6rem; border-radius: 1.4rem !important;">
                                                                            <a data-toggle="collapse" href="#" class="btn btn-link" role="button" aria-expanded="false" value="<?php echo $per['periodo'] ?>">
                                                                                <img class="card-img-top " src="../../../images/periodos/<?php echo 'p' . $per['periodo'] ?>.png" title="<?php echo ucfirst(strtolower($per['periodo'])) ?>" alt="periodo">
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                                ?>
                                                                <?php
                                                                if ($_SESSION['caracter'] == 2) {
                                                                    foreach ($periodos_vac as $per) { ?>
                                                                        <div id="gp<?php echo $per['periodo'] ?>" class="col-mb-4 py-2 px-3">
                                                                            <div class="card shadow-g" style="width: 6rem; border-radius: 1.4rem !important;">
                                                                                <button data-toggle="collapse" class="btn btn-link" role="button" aria-expanded="false" value="<?php echo $per['periodo'] ?>">
                                                                                    <img class="card-img-top " src="../../../images/periodos/<?php echo 'p' . $per['periodo'] ?>.png" title="Prima Navidad" alt="periodo">
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($_SESSION['caracter'] == '2') { ?>
                                        <!-- parte-->
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingVac">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseVac" aria-expanded="false" aria-controls="collapseVac">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-umbrella-beach fa-lg" style="color: #F7DC6F;"></span>
                                                            </div>
                                                            <div>
                                                                <?php $j++;
                                                                echo $j ?>. VACACIONES.
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseVac" class="collapse" aria-labelledby="headingVac">
                                                <div class="card-body">
                                                    <table id="tableLiqVacaciones" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th rowspan="2">ID</th>
                                                                <th rowspan="2">No. Doc.</th>
                                                                <th rowspan="2">Nombre completo</th>
                                                                <th rowspan="2">Fecha<br>Inicia</th>
                                                                <th rowspan="2">Fecha<br>Termina</th>
                                                                <th rowspan="2">Dias<br>Liquidados</th>
                                                                <th colspan="4">Valor</th>
                                                                <th rowspan="2">Corte</th>
                                                                <th rowspan="2">Anticipo</th>
                                                                <th rowspan="2">Días<br>Hábiles</th>
                                                                <th rowspan="2">TOTAL</th>

                                                            </tr>
                                                            <tr class="text-center">
                                                                <th>Vacaciones</th>
                                                                <th>Prima<br>Vacaciones</th>
                                                                <th>Bonificación<br>servicios</th>
                                                                <th>Bonificación<br>Recreacion</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                    if ($_SESSION['caracter'] == '1') {
                                    ?>
                                        <!-- parte-->
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingTwo">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-swatchbook fa-lg" style="color: #EC7063;"></span>
                                                            </div>
                                                            <div>
                                                                <?php $j++;
                                                                echo $j ?>. CONTRATOS.
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseThree" class="collapse" aria-labelledby="headingTwo">
                                                <div class="card-body">
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
                                                                <th class="text-center">Liquidación <?php echo $vigencias ?></th>
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
                                            </div>
                                        </div>
                                    <?php }
                                    if ($_SESSION['caracter'] == '2') {
                                    ?>
                                        <!-- parte-->
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseReact" aria-expanded="true" aria-controls="collapseTwo">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-exchange-alt fa-lg" style="color: #A569BD;"></span>
                                                            </div>
                                                            <div>
                                                                <?php $j++;
                                                                echo $j ?>. RETROACTIVO.
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseReact" class="collapse" aria-labelledby="headingOne">
                                                <div class="card-body">
                                                    <table id="tableRetroactivosLiquidados" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th>ID</th>
                                                                <th>Fecha inicia</th>
                                                                <th>Fecha termina</th>
                                                                <th>Cantidad<br>(meses)</th>
                                                                <th>Incremento %</th>
                                                                <th>Observaciones</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="modificarRetroactivoLiquidado">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                </div>
                        <?php }
                                } ?>
                        <div class="text-center pt-3">
                            <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal" href="javascript: history.go(-1)">Regresar</a>
                        </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <?php include '../../../modales.php' ?>
    </div>
    <?php include '../../../scripts.php' ?>
    <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/nomina/js/funciones.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>