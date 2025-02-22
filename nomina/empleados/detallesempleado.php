<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';

function pesos($valor)
{
    return '$' . number_format($valor, 0, ",", ".");
}

$id = isset($_POST['idDetalEmpl']) ? $_POST['idDetalEmpl'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT *
            FROM
                nom_empleado
            INNER JOIN tb_municipios 
                ON (municipio = tb_municipios.id_municipio)
            INNER JOIN tb_departamentos 
                ON (departamento = tb_departamentos.id_departamento) AND (tb_municipios.id_departamento = tb_departamentos.id_departamento)
            INNER JOIN nom_cargo_empleado 
                ON (nom_empleado.cargo = nom_cargo_empleado.id_cargo)
            WHERE id_empleado = '$id'
            LIMIT 1";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_novedad, nom_epss.id_eps, nombre_eps, CONCAT(nit, '-', digito_verific) AS nit, fec_afiliacion, fec_retiro
            FROM
                nom_novedades_eps
            INNER JOIN nom_epss 
                ON (nom_novedades_eps.id_eps = nom_epss.id_eps)
            WHERE id_empleado = '$id'
                ORDER BY fec_afiliacion ASC";
    $rs = $cmd->query($sql);
    $eps = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_novarl, nom_arl.id_arl, nombre_arl, CONCAT(nit_arl, '-', dig_ver) AS nitarl, id_riesgo, CONCAT(clase, ' - ', riesgo) AS riesgo, fec_afiliacion, fec_retiro
            FROM
                nom_novedades_arl
            INNER JOIN nom_arl 
                ON (nom_novedades_arl.id_arl = nom_arl.id_arl)
            INNER JOIN nom_riesgos_laboral 
                ON (nom_novedades_arl.id_riesgo = nom_riesgos_laboral.id_rlab)
            WHERE id_empleado = '$id'
            ORDER BY fec_afiliacion ASC";
    $rs = $cmd->query($sql);
    $arl = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_novafp, nom_novedades_afp.id_afp, nombre_afp, CONCAT(nit_afp, '-',dig_verf) AS nitafp, fec_afiliacion, nom_novedades_afp.fec_retiro
            FROM
                nom_novedades_afp
            INNER JOIN nom_afp 
                ON (nom_novedades_afp.id_afp = nom_afp.id_afp)
            INNER JOIN nom_empleado 
                ON (nom_novedades_afp.id_empleado = nom_empleado.id_empleado)
            WHERE nom_empleado.id_empleado = '$id'
            ORDER BY fec_afiliacion ASC";
    $rs = $cmd->query($sql);
    $afpnov = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT  
                `id_empleado`, `vigencia`, `salario_basico`, `fec_reg`
            FROM
                `nom_salarios_basico` 
            WHERE `id_empleado` = $id
            ORDER BY `id_salario` DESC";
    $rs = $cmd->query($sql);
    $salemp = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT  
                `id_empleado`, `vigencia`, `salario_basico`, `fec_reg`
            FROM
                `nom_salarios_basico` 
            WHERE `id_empleado` = $id
            ORDER BY `id_salario` DESC";
    $rs = $cmd->query($sql);
    $salemp = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../permisos.php';
$contador = 1;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>
<style>
    .popover {
        font-size: 11px !important;
    }

    .popover-content {
        min-width: 120px;
        white-space: nowrap;
    }
</style>

<body class="sb-nav-fixed 
<?php
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
                                    <i class="fas fa-address-book fa-lg" style="color: #07CF74;"></i>
                                    DETALLES EMPLEADO
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#datosperson" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="far fa-address-book fa-lg" style="color: #3498DB;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. DATOS PERSONALES
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="datosperson" class="collapse show" aria-labelledby="headingOne">
                                        <div class="card-body" style="font-size: 13px;">
                                            <div class="shadow detalles-empleado">
                                                <div class="row">
                                                    <div class="div-mostrar bor-top-left col-md-2">
                                                        <label class="lbl-mostrar">IDENTIFICACIÓN</label>
                                                        <div class="div-cont"><?php echo $obj['no_documento'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-4">
                                                        <label class="lbl-mostrar">NOMBRE COMPLETO</label>
                                                        <div class="div-cont"><?php echo mb_strtoupper($obj['nombre1'] . ' ' . $obj['nombre2'] . ' ' . $obj['apellido1'] . ' ' . $obj['apellido2']) ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-2">
                                                        <label class="lbl-mostrar">DEPARTAMENTO</label>
                                                        <div class="div-cont"><?php echo mb_strtoupper($obj['nom_departamento']) ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-2">
                                                        <label class="lbl-mostrar">MUNICIPIO</label>
                                                        <div class="div-cont"><?php echo mb_strtoupper($obj['nom_municipio']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="div-mostrar bor-top-right col-md-2">
                                                        <label class="lbl-mostrar">DIRECCIÓN</label>
                                                        <div class="div-cont"><?php echo mb_strtoupper($obj['direccion']) ?></div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="div-mostrar col-md-3">
                                                        <label class="lbl-mostrar">CORREO</label>
                                                        <div class="div-cont"><?php echo $obj['correo'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-2">
                                                        <label class="lbl-mostrar">CONTACTO</label>
                                                        <div class="div-cont"><?php echo $obj['telefono'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-2">
                                                        <label class="lbl-mostrar">FECHA DE INGRESO</label>
                                                        <div class="div-cont"><?php echo $obj['fech_inicio'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-3">
                                                        <label class="lbl-mostrar">CARGO ACTUAL</label>
                                                        <div class="div-cont"><?php echo mb_strtoupper($obj['descripcion_carg']) ?></div>
                                                    </div>
                                                    <input type="text" id="txtSalBas" value="<?php echo $salemp[0]['salario_basico'] ?>" hidden>
                                                    <div class="div-mostrar col-md-2">
                                                        <label class="lbl-mostrar">SALARIO BÁSICO</label>
                                                        <?php
                                                        $salario = '';
                                                        $salario .= "<div class='popover-content'>";
                                                        foreach ($salemp as $s) {
                                                            $salario .= "<div class='row'>";
                                                            $salario .= "<div class='col-md-6'>" . date('Y-m-d', strtotime($s["fec_reg"])) . "</div>";
                                                            $salario .= "<div class='col-md-6'>" . pesos($s["salario_basico"]) . "</div>";
                                                            $salario .= "</div>";
                                                        }
                                                        $salario .= "</div>";
                                                        ?>
                                                        <br>
                                                        <a type="button" class="text-left " data-toggle="popover" title="Fecha - Salario" data-content="<?php echo $salario ?>" data-html="true"><?php echo pesos($salemp[0]['salario_basico']) ?></a>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="div-mostrar col-md-4">
                                                        <label class="lbl-mostrar">EPS ACTUAL</label>
                                                        <div class="div-cont">
                                                            <?php
                                                            $nomeps = "NO SE HA REGISTRADO EPS";
                                                            foreach ($eps as $e) {
                                                                $nomeps = $e['nombre_eps'];
                                                            }
                                                            echo mb_strtoupper($nomeps);
                                                            ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-4">
                                                        <label class="lbl-mostrar">AFP ACTUAL</label>
                                                        <div class="div-cont">
                                                            <?php $nomAFP = "NO SE HA REGISTRADO AFP";
                                                            foreach ($afpnov as $afps) {
                                                                $nomAFP = $afps['nombre_afp'];
                                                            }
                                                            echo mb_strtoupper($nomAFP);
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="div-mostrar col-md-4">
                                                        <label class="lbl-mostrar">ARL ACTUAL</label>
                                                        <div class="div-cont">
                                                            <?php $nomarl = "NO SE HA REGISTRADO ARL";
                                                            foreach ($arl as $a) {
                                                                $nomarl = $a['nombre_arl'];
                                                            }
                                                            echo mb_strtoupper($nomarl);
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="div-mostrar bor-bottom-left col-md-2">
                                                        <label class="lbl-mostrar">CLASE DE RIESGO</label>
                                                        <div class="div-cont">
                                                            <?php
                                                            $riesgo = "NO SE HA REGISTRADO ARL";
                                                            foreach ($arl as $a) {
                                                                $riesgo = $a['riesgo'];
                                                            }
                                                            echo $riesgo;
                                                            ?></div>

                                                    </div>
                                                    <div class="div-mostrar col-md-2">
                                                        <label class="lbl-mostrar">ESTADO</label>
                                                        <div class="div-cont"><?php echo mb_strtoupper($obj['estado'] == '1' ? 'Activo' : 'Inactivo') ?></div>
                                                    </div>
                                                    <div class="div-mostrar bor-bottom-right col-md-8">
                                                        <label class="lbl-mostrar">MAS DATOS</label>
                                                        <div class="div-cont"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if (!empty($salemp)) {
                                ?>
                                    <!-- parte-->
                                    <div>
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingTwo">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-hospital fa-lg" style="color: #EC7063;"></span>
                                                            </div>
                                                            <div>
                                                                <?php echo $contador;
                                                                $contador++ ?>. HISTORIAL EPSs
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <input type="number" id="idEmpNovEps" name="idEmpNovEps" value="<?php echo $id ?>" hidden>
                                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo">
                                                <div class="card-body">
                                                    <?php
                                                    if ((PermisosUsuario($permisos, 5101, 2) || $id_rol == 1)) {
                                                        echo '<input type="hidden" id="peReg" value="1">';
                                                    } else {
                                                        echo '<input type="hidden" id="peReg" value="0">';
                                                    } ?>
                                                    <div>
                                                        <table id="tableEps" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nombres</th>
                                                                    <th>NIT</th>
                                                                    <th>Fecha afiliación</th>
                                                                    <th>Fecha retiro</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="modificarEpss">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="headingThree">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="far fa-hospital fa-lg" style="color: #F8C471;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. HISTORIAL ARLs
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree">
                                            <div class="card-body">
                                                <table id="tableArl" class="table table-striped table-bordered table-sm display nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>NIT</th>
                                                            <th>Clase riesgo</th>
                                                            <th>Fecha afiliación</th>
                                                            <th>Fecha retiro</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarArls">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="historyafp">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-gopuram fa-lg" style="color: #E59866;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. HISTORIAL AFPs
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseFour" class="collapse" aria-labelledby="historyafp">
                                            <div class="card-body">
                                                <table id="tableAfp" class="table table-striped table-bordered table-sm display nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>NIT</th>
                                                            <th>Fecha afiliación</th>
                                                            <th>Fecha retiro</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarAfps">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="historyCCosto">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseCcosto" aria-expanded="false" aria-controls="collapseCcosto">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-landmark fa-lg" style="color: #3498db;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. CENTROS DE COSTO
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseCcosto" class="collapse" aria-labelledby="historyCCosto">
                                            <div class="card-body">
                                                <table id="tableCCostoEmp" class="table table-striped table-bordered table-sm display nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th>ID</th>
                                                            <th>Nombre</th>
                                                            <th>Fecha</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarCCostoEmp">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="historyCesan">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseFourTwo" aria-expanded="false" aria-controls="collapseFour">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-university fa-lg" style="color: #AED6F1;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. HISTORIAL FONDO CESANTÍAS
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseFourTwo" class="collapse" aria-labelledby="historyCesan">
                                            <div class="card-body">
                                                <table id="tableFCesan" class="table table-striped table-bordered table-sm display nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>NIT</th>
                                                            <th>Fecha afiliación</th>
                                                            <th>Fecha retiro</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarFCesans">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="libranzas">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-file-invoice-dollar fa-lg" style="color: #28B463;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. LIBRANZAS
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseFive" class="collapse" aria-labelledby="libranzas">
                                            <div class="card-body">
                                                <table id="tableLibranza" class="table table-striped table-bordered table-sm display nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Entidad</th>
                                                            <th>Total</th>
                                                            <th>Cuotas</th>
                                                            <th>Val. Mes</th>
                                                            <th>Pagado</th>
                                                            <th>Cuotas</th>
                                                            <th>Inicia</th>
                                                            <th>Termina</th>
                                                            <th>Estado</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarLibranzas">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!--parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="embargos">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-coins fa-lg" style="color: #F1C40F;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. EMBARGOS
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseSix" class="collapse" aria-labelledby="embargo">
                                            <div class="card-body">
                                                <table id="tableEmbargo" class="table table-striped table-bordered table-sm display nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Juzgado</th>
                                                            <th>Total</th>
                                                            <th>Valor mes</th>
                                                            <th>Pagado</th>
                                                            <th>Inicia</th>
                                                            <th>Termina</th>
                                                            <th>Estado</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarEmbargos">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!--parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="sindicatos">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-users fa-lg" style="color: #95A5A6;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. SINDICATOS
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseSeven" class="collapse" aria-labelledby="sindicato">
                                            <div class="card-body">
                                                <div>
                                                    <table id="tableSindicato" class="table table-striped table-bordered table-sm display nowrap table-hover shadow" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th>Sindicato</th>
                                                                <th>Porcentaje</th>
                                                                <th>Cantidad Aportes</th>
                                                                <th>Total Aportes</th>
                                                                <th>Fecha Inicio</th>
                                                                <th>Fecha Fin</th>
                                                                <th>Sindicalización</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="modificarSindicatos">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="incapacidades">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-procedures fa-lg" style="color: #1ABC9C;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. INCAPACIDADES
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseEight" class="collapse" aria-labelledby="incapacidad">
                                            <div class="card-body">
                                                <div>
                                                    <table id="tableIncapacidad" class="table table-striped table-bordered table-sm display table-hover shadow" style="width:100%">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th>Tipo Incapacidad</th>
                                                                <th>Fecha Inicio</th>
                                                                <th>Fecha Fin</th>
                                                                <th>Días</th>
                                                                <th>Categoría</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="modificarIncapacidades">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="otros">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-angle-double-right fa-lg" style="color: #8E44AD;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador ?>. OTROS: VACIONES,LICENCIAS
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <?php
                                        $decimal = 1;
                                        ?>
                                        <div id="collapseNine" class="collapse" aria-labelledby="otros">
                                            <div class="card-body">
                                                <div class="card">
                                                    <div class="card-header  card-header-detalles py-0 headings" id="vacaciones">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseVac" aria-expanded="false" aria-controls="collapseVac">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-umbrella-beach fa-lg" style="color: #F4D03F;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php echo $contador . '.' . $decimal . '.';
                                                                        $decimal++ ?> VACACIONES
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseVac" class="collapse" aria-labelledby="vacaciones">
                                                        <div class="card-body">
                                                            <div>
                                                                <table id="tableVacaciones" class="table table-striped table-bordered table-sm display table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Anticipada</th>
                                                                            <th>Fecha Inicio</th>
                                                                            <th>Fecha Fin</th>
                                                                            <th>Días Inactivo</th>
                                                                            <th>Dias hábiles</th>
                                                                            <th>Corte</th>
                                                                            <th>Dias a Liquidar</th>
                                                                            <th>Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="modificarVacaciones">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="Licencia">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseLic" aria-expanded="false" aria-controls="collapseLic">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-baby fa-lg" style="color: #2980B9;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php echo $contador . '.' . $decimal . '.';
                                                                        $decimal++ ?> LICENCIAS MATERNA/PATERNA
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseLic" class="collapse" aria-labelledby="licencia">
                                                        <div class="card-body">
                                                            <div>
                                                                <table id="tableLicencia" class="table table-striped table-bordered table-sm display table-hover table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Fecha Inicio</th>
                                                                            <th>Fecha Fin</th>
                                                                            <th>Días Inactivo</th>
                                                                            <th>Dias hábiles</th>
                                                                            <th>Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="modificarLicencias">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="Luto">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseLuto" aria-expanded="false" aria-controls="collapseLuto">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-ribbon fa-lg" style="color: black;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php echo $contador . '.' . $decimal . '.';
                                                                        $decimal++ ?> LICENCIAS POR LUTO
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseLuto" class="collapse" aria-labelledby="luto">
                                                        <div class="card-body">
                                                            <div>
                                                                <table id="tableLuto" class="table table-striped table-bordered table-sm display table-hover table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Fecha Inicio</th>
                                                                            <th>Fecha Fin</th>
                                                                            <th>Días Inactivo</th>
                                                                            <th>Dias hábiles</th>
                                                                            <th>Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="modificarLuto">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="LicenciaNR">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseLicNR" aria-expanded="false" aria-controls="collapseLicNR">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-user-alt-slash fa-lg" style="color: #DC7633;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php echo $contador . '.' . $decimal . '.';
                                                                        $decimal++ ?> LICENCIAS NO REMUNERADA
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseLicNR" class="collapse" aria-labelledby="licenciaNR">
                                                        <div class="card-body">
                                                            <div>
                                                                <table id="tableLicenciaNR" class="table table-striped table-bordered table-sm display table-hover table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Fecha Inicio</th>
                                                                            <th>Fecha Fin</th>
                                                                            <th>Días Inactivo</th>
                                                                            <th>Dias hábiles</th>
                                                                            <th>Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="modificarLicenciasNR">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="IndemnizaVac">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseIndVac" aria-expanded="false" aria-controls="collapseIndVac">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-handshake fa-lg" style="color: #F4AFF2;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php echo $contador . '.' . $decimal . '.';
                                                                        $decimal++ ?> INDEMNIZACIÓN POR VACACIONES
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseIndVac" class="collapse" aria-labelledby="IndemnizaVac">
                                                        <div class="card-body">
                                                            <div>
                                                                <table id="tableIndemnizaVac" class="table table-striped table-bordered table-sm display table-hover table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Fecha Inicio</th>
                                                                            <th>Fecha Fin</th>
                                                                            <th>Días Indemniza</th>
                                                                            <th>Estado</th>
                                                                            <th>Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="modificaIndemnVac">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="OtroDcto">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseOtroDcto" aria-expanded="false" aria-controls="collapseOtroDcto">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas  fa-funnel-dollar fa-lg" style="color: #5D6D7E ;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php echo $contador . '.' . $decimal . '.';
                                                                        $decimal++ ?> OTROS DESCUENTOS
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseOtroDcto" class="collapse" aria-labelledby="OtroDcto">
                                                        <div class="card-body">
                                                            <div>
                                                                <table id="tableOtroDcto" class="table table-striped table-bordered table-sm display table-hover table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>ID</th>
                                                                            <th>Fecha</th>
                                                                            <th>Tipo</th>
                                                                            <th>Concepto</th>
                                                                            <th>Valor</th>
                                                                            <th>Estado</th>
                                                                            <th>Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="modificaOtroDcto">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="IntVivienda">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseIntVivienda" aria-expanded="false" aria-controls="collapseIntVivienda">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-home fa-lg" style="color:rgb(7, 234, 250) ;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php echo $contador . '.' . $decimal . '.';
                                                                        $decimal++ ?> INTERESES DE VIVIENDA
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseIntVivienda" class="collapse" aria-labelledby="IntVivienda">
                                                        <div class="card-body">
                                                            <div>
                                                                <table id="tableIntVivienda" class="table table-striped table-bordered table-sm display table-hover table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr class="text-center">
                                                                            <th>ID</th>
                                                                            <th>Fecha</th>
                                                                            <th>Valor</th>
                                                                            <th>Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="modificaIntVivienda">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else {
                                    echo '<div class=" text-center input-red" style="color: white"><b>EMPLEADO NO TIENE CONTRATO VIGENTE</b></div>';
                                } ?>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-secondary " style="width: 6rem;" href="listempleados.php"> Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <input type="text" id="delrow" value="0" hidden>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
    <script>
        $(function() {
            $('[data-toggle="popover"]').popover()
        });
    </script>
</body>

</html>