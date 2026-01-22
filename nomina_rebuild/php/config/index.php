<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('80', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$contador = 1;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../../head.php' ?>

<body class="sb-nav-fixed <?= $_SESSION['navarlat'] == '1' ? 'sb-sidenav-toggled' : '' ?>">
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
                                    <i class="fas fa-cogs fa-lg" style="color:#1D80F7"></i>
                                    CONFIGURACIONES
                                </div>
                                <?php
                                $opc = PermisosUsuario($permisos, 8001, 2) || $id_rol == 1 ? 1 : 0;
                                echo '<input type="hidden" id="peReg" value="' . $opc . '">';
                                ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configone" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="far fa-list-alt fa-lg" style="color: #3498DB;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. PARAMETROS DE LIQUIDACIÓN.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configone" class="collapse" aria-labelledby="headingOne">
                                        <?php
                                        if (PermisosUsuario($permisos, 5114, 2) || $id_rol == 1) {
                                            echo '<input type="hidden" id="peReg" value="1">';
                                        } else {
                                            echo '<input type="hidden" id="peReg" value="0">';
                                        }
                                        ?>

                                        <div class="card-body">
                                            <table id="tableParamLiq" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>ID CONCEPTO</th>
                                                        <th>CONCEPTO</th>
                                                        <th>VALOR</th>
                                                        <th>ACCIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaParamLiq">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingcinco">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configcinco" aria-expanded="true" aria-controls="collapsecinco">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-user-tie fa-lg" style="color: #145a32;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. CARGOS.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configcinco" class="collapse" aria-labelledby="headingcinco">
                                        <div class="card-body">
                                            <table id="tableCargosNomina" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Código</th>
                                                        <th>Cargo</th>
                                                        <th>Grado</th>
                                                        <th>Perfíl SIHO</th>
                                                        <th>Nombramiento</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaCargoNomina">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingcuatro">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configcuatro" aria-expanded="true" aria-controls="collapsecuatro">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-users fa-lg" style="color: #9B59B6;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. TERCEROS.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configcuatro" class="collapse" aria-labelledby="headingcuatro">
                                        <div class="card-body">
                                            <table id="tableTerceroNomina" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>CÓD.</th>
                                                        <th>CATEGORIA</th>
                                                        <th>NOMBRE</th>
                                                        <th>NIT</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaTerceroNomina">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingtres">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configtres" aria-expanded="true" aria-controls="collapsetres">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-sort-amount-up fa-lg" style="color: #F9E79F;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. INCREMENTO SALARIAL.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configtres" class="collapse" aria-labelledby="headingtres">
                                        <div class="card-body">
                                            <table id="tableIncremento" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>PORCENTAJE</th>
                                                        <th>FECHA INICIO</th>
                                                        <th>ESTADO</th>
                                                        <th>ACCIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaIncremento">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingseis">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configseis" aria-expanded="true" aria-controls="collapseseis">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-clipboard-list fa-lg" style="color: #dc7633;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. RUBROS PRESUPUESTALES.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configseis" class="collapse" aria-labelledby="headingseis">
                                        <div class="card-body">
                                            <table id="tableRubrosNomina" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Tipo</th>
                                                        <th colspan="2">Rubro Administrativo</th>
                                                        <th colspan="2">Rubro Operativo</th>
                                                        <th rowspan="2">Acciones</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Código</th>
                                                        <th>Nombre</th>
                                                        <th>Código</th>
                                                        <th>Nombre</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaRubrosNomina">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingsiete">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configsiete" aria-expanded="true" aria-controls="collapsesiete">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-sort-amount-down-alt fa-lg" style="color: #e74c3c;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. CUENTAS CONTABLES.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configsiete" class="collapse" aria-labelledby="headingsiete">
                                        <div class="card-body">
                                            <table id="tableCtaCtbNomina" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Centro Costo</th>
                                                        <th>Tipo</th>
                                                        <th>Nombre</th>
                                                        <th>Cuenta</th>
                                                        <th>Nombre</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaCtaCtbNomina">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <?php include '../../../modales.php' ?>
    </div>
    <?php include '../../../script.php' ?>
    <script type="text/javascript" src="../../js/common/common.js?v=<?= date('YmdHis') ?>"></script>
    <script type="text/javascript" src="../../js/config/config.js?v=<?= date('YmdHis') ?>"></script>
</body>

</html>