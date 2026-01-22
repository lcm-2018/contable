<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('53', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php $_SESSION['navarlat'] == '1' ? 'sb-sidenav-toggled' : '' ?>">
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
                                    <i class="fas fa-copy fa-lg" style="color:#1D80F7"></i>
                                    OPCIONES
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="modContrata">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodContrata" aria-expanded="true" aria-controls="collapsemodContrata">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-file-contract fa-lg" style="color: #2ECC71;"></span>
                                                    </div>
                                                    <div>
                                                        1. MODALIDAD DE CONTRATACIÓN
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapsemodContrata" class="collapse" aria-labelledby="modContrata">
                                        <div class="card-body">
                                            <table id="tableModalidad" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Modalidad</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarModalidades">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="tipoContrato">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsetipoContrato" aria-expanded="true" aria-controls="collapsetipoContrato">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-file-signature fa-lg" style="color: #E74C3C;"></span>
                                                    </div>
                                                    <div>
                                                        2. TIPO DE CONTRATO
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapsetipoContrato" class="collapse" aria-labelledby="tipoContrato">
                                        <div class="card-body">
                                            <table id="tableTipoContrato" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo de compra</th>
                                                        <th>Tipo de contrato</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarTipoContratos">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="tipoSerBien">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseTipoSerBien" aria-expanded="true" aria-controls="collapseTipoSerBien">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-mail-bulk fa-lg" style="color: #5DADE2;"></span>
                                                    </div>
                                                    <div>
                                                        3. TIPO DE BIEN O SERVICIO
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapseTipoSerBien" class="collapse" aria-labelledby="tipoSerBien">
                                        <div class="card-body">
                                            <table id="tableTipoBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo de compra</th>
                                                        <th>Tipo de contrato</th>
                                                        <th>Tipo de Bien y/o servicio</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarTipoBnSvs">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!--parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="servicosBienes">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapeseBnSv" aria-expanded="true" aria-controls="collapeseBnSv">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-cart-arrow-down fa-lg" style="color: #E67E22;"></span>
                                                    </div>
                                                    <div>
                                                        4. BIENES Y SERVICIOS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <?php if (PermisosUsuario($permisos, 5301, 0) || $id_rol == 1) {
                                        echo '<input type="hidden" id="peReg" value="1">';
                                    } else {
                                        echo '<input type="hidden" id="peReg" value="0">';
                                    } ?>
                                    <div id="collapeseBnSv" class="collapse" aria-labelledby="servicosBienes">
                                        <div class="card-body">
                                            <table id="tableBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo de compra</th>
                                                        <th>Tipo de contrato</th>
                                                        <th>Tipo de Bien y/o servicio</th>
                                                        <th>Bien y/o servicio</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarBnSvs">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!--parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="formCtt">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapeseFCtt" aria-expanded="true" aria-controls="collapeseFCtt">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-file-word fa-lg" style="color: #2980B9;"></span>
                                                    </div>
                                                    <div>
                                                        5. FORMATOS DE CONTRATACIÓN
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapeseFCtt" class="collapse" aria-labelledby="formCtt">
                                        <div class="card-body">
                                            <div class="text-right">
                                                <button type="button" class="btn btn-outline-info mb-1" id="btnDownloadVarsCtt" title="Descargar variables de contratación">
                                                    <span class="fas fa-download mr-2"></span>Variables
                                                </button>
                                            </div>
                                            <table id="tableFormCtt" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Tipo de Formato</th>
                                                        <th>Tipo de Bien/Servicio</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaFormCtt">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!--parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="masOpciones">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapeseMsOp" aria-expanded="true" aria-controls="collapeseMsOp">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-bars fa-lg" style="color: #34495e;"></span>
                                                    </div>
                                                    <div>
                                                        6. MÁS OPCIONES
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapeseMsOp" class="collapse" aria-labelledby="masOpciones">
                                        <div class="card-body form-inline">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-file-excel mr-2 text-success"></span>
                                                        Homolagación de servicios
                                                    </span>
                                                </div>
                                                <div class="input-group-append" id="button-addon4">
                                                    <button type="button" class="btn btn-outline-primary" id="btnExcelHomolgBnSv" title="Descargar Formato de homologación de servicios">
                                                        <span class="fas fa-download"></span>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning subirHomologacion" text="1" title="Subir Formato de homologación de servicios">
                                                        <span class="fas fa-upload"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="input-group px-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <span class="fas fa-file-excel mr-2 text-success"></span>
                                                        Homolagación escala de honorarios
                                                    </span>
                                                </div>
                                                <div class="input-group-append" id="button-addon4">
                                                    <button type="button" class="btn btn-outline-primary" id="btnExcelHomolgEscHonor" title="Descargar Formato de homologación escala de honorarios">
                                                        <span class="fas fa-download"></span>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning subirHomologacion" text="2" title="Subir Formato de homologación escala honorarios">
                                                        <span class="fas fa-upload"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>