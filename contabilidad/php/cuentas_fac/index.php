<?php
session_start();

/* Activar si desea verificar Errores desde el Servidor
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

include '../../../conexion.php';
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
                                    <i class="fas fa-list-ul fa-lg" style="color:#1D80F7"></i>
                                    CUENTAS DE FACTURACIÓN
                                </div>
                            </div>
                        </div>

                        <!--Cuerpo Principal del formulario -->
                        <div class="card-body" id="divCuerpoPag">

                            <!--Opciones de filtros -->
                            <div class="form-row">                                
                                <div class="form-group col-md-2">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_nombre_filtro" placeholder="Nombre">
                                </div>
                                <div class="form-group col-md-1">
                                    <a type="button" id="btn_buscar_filtro" class="btn btn-outline-success btn-sm" title="Filtrar">
                                        <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                    </a>
                                    <a type="button" id="btn_imprime_filtro" class="btn btn-outline-success btn-sm" title="Imprimir">
                                        <span class="fas fa-print" aria-hidden="true"></span>                                       
                                    </a>
                                </div>
                            </div>

                            <!--Lista de registros en la tabla
                                5507-Opcion [Otros][Cuentas Facturación]
                                1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
                            -->
                            <?php
                            if (PermisosUsuario($permisos, 5507, 2) || $id_rol == 1) {
                                echo '<input type="hidden" id="peReg" value="1">';
                            } else {
                                echo '<input type="hidden" id="peReg" value="0">';
                            }
                            ?>
                            <table id="tb_cuentas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                <thead>
                                    <tr class="text-center centro-vertical">
                                        <th rowspan="2">Id</th>
                                        <th rowspan="2">Régimen</th>
                                        <th rowspan="2">Cobertura</th>
                                        <th rowspan="2">Modalidad</th>
                                        <th rowspan="2">Fecha Inicio de Vigencia</th>
                                        <th colspan="14">Cuentas Contables</th>
                                        <th rowspan="2">Estado</th>
                                        <th rowspan="2">Acciones</th>
                                    </tr>
                                    <tr class="text-center centro-vertical">
                                        <th>Presto.</th>
                                        <th>Presto.Ant.</th>
                                        <th>Débito</th>
                                        <th>Crédito</th>
                                        <th>Copago</th>
                                        <th>Cop.Cap.</th>
                                        <th>Glo.Ini.deb.</th>
                                        <th>Glo.Ini.cre.</th>
                                        <th>Glo_def.</th>
                                        <th>Devol.</th>
                                        <th>Caja</th>
                                        <th>Fac.Glob.</th>
                                        <th>Por Iden.</th>
                                        <th>Vigente</th>
                                    </tr>
                                </thead>
                            </table>
                            <table class="table-bordered table-sm col-md-2">
                                <tr>
                                    <td style="background-color:#ffc107">Cuentas Contables Vigentes</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <?php include '../../../modales.php' ?>
    </div>
    <?php include '../../../scripts.php' ?>    
    <script type="text/javascript" src="../../js/cuentas_fac/cuentas_fac.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>