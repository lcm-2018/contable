<?php
session_start();

/* Activar si desea verificar Errores desde el Servidor
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}

include '../../../conexion.php';
include '../../../permisos.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

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
                                    ORDENES DE TRASLADO
                                </div>
                            </div>
                        </div>

                        <!--Cuerpo Principal del formulario -->
                        <div class="card-body" id="divCuerpoPag">

                            <!--Opciones de filtros -->
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_sedori_filtro">
                                        <?php sedes_usuario($cmd, '--Sede Origen--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_bodori_filtro">
                                    </select>
                                </div>                                
                                <div class="form-group col-md-1">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_idtra_filtro" placeholder="Id. Traslado">
                                </div>
                                <div class="form-group col-md-1">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_numtra_filtro" placeholder="No. Traslado">
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <input type="date" class="form-control form-control-sm" id="txt_fecini_filtro" name="txt_fecini_filtro" placeholder="Fecha Inicial">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <input type="date" class="form-control form-control-sm" id="txt_fecfin_filtro" name="txt_fecfin_filtro" placeholder="Fecha Final">
                                        </div>
                                    </div>
                                </div>   
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_seddes_filtro">
                                        <?php sedes($cmd, '--Sede Destino--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_boddes_filtro">
                                    </select>
                                </div>                                                           
                                <div class="form-group col-md-1">
                                    <select class="form-control form-control-sm" id="sl_estado_filtro">
                                        <?php estados_movimientos('--Estado--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <a type="button" id="btn_buscar_filtro" class="btn btn-outline-success btn-sm" title="Filtrar">
                                        <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                    </a>
                                    <button id="btnImprimeTraslados" class="btn btn-outline-success btn-sm" title="Imprimir Traslados"><i class="fas fa-print"></i></button>
                                </div>
                            </div>

                            <!--Lista de registros en la tabla-->
                            <?php
                            if (PermisosUsuario($permisos, 5008, 2) || $id_rol == 1) {
                                echo '<input type="hidden" id="peReg" value="1">';
                            } else {
                                echo '<input type="hidden" id="peReg" value="0">';
                            }
                            ?>
                            <table id="tb_traslados" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                <thead>                               
                                    <tr class="text-center centro-vertical">
                                        <th rowspan="2">Id</th>
                                        <th rowspan="2">No. Traslado</th>
                                        <th rowspan="2">Fecha Traslado</th>
                                        <th rowspan="2">Hora Traslado</th>
                                        <th rowspan="2">Detalle</th>
                                        <th colspan="2">Unidad Origen</th>
                                        <th colspan="2">Unidad Destino</th>
                                        <th rowspan="2">Vr. Total</th>
                                        <th rowspan="2">Estado</th>
                                        <th rowspan="2">Acciones</th>
                                    </tr>
                                    <tr class="text-center centro-vertical">
                                        <th>Sede</th>
                                        <th>Bodega</th>
                                        <th>Sede</th>
                                        <th>Bodega</th>
                                    </tr>    
                                </thead>
                            </table>
                            <table class="table-bordered table-sm col-md-2">
                                <tr>
                                    <td style="background-color:yellow">Pendiente</td>
                                    <td>Cerrado</td>
                                    <td style="background-color:gray">Anulado</td>
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
    <script type="text/javascript" src="../../js/traslados/traslados.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>