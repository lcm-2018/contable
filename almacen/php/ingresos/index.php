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
                                    ORDENES DE INGRESO
                                </div>
                            </div>
                        </div>

                        <!--Cuerpo Principal del formulario -->
                        <div class="card-body" id="divCuerpoPag">

                            <!--Opciones de filtros -->
                            <div class="form-row">
                                <div class="form-group col-md-1">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_iding_filtro" placeholder="Id. Ingreso">
                                </div>
                                <div class="form-group col-md-1">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_numing_filtro" placeholder="No. Ingreso">
                                </div>
                                <div class="form-group col-md-2">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_numfac_filtro" placeholder="No. Factura">
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
                                    <select class="form-control form-control-sm" id="sl_tiping_filtro">
                                        <?php tipo_ingreso($cmd,'--Tipo Ingreso--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_tercero_filtro">
                                        <?php terceros($cmd,'--Tercero--') ?>
                                    </select>
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
                            <div class="form-row">                                   
                                <div class="form-group col-md-1">
                                    <select class="form-control form-control-sm" id="sl_estado_filtro">
                                        <?php estados_movimientos('--Estado--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <select class="form-control form-control-sm" id="sl_modulo_origen">
                                        <?php modulo_origen('--Origen--',0) ?>
                                    </select>
                                </div>   
                                <div class="form-group col-md-3">
                                    <select class="filtro form-control form-control-sm text-primary" id="sl_tipo_reporte">
                                        <?php tipo_reporte_ingresos('--TIPO DE REPORTE--') ?>
                                    </select>
                                </div>                              
                            </div>

                            <!--Lista de registros en la tabla-->
                            <?php
                            if (PermisosUsuario($permisos, 5006, 2) || $id_rol == 1) {
                                echo '<input type="hidden" id="peReg" value="1">';
                            } else {
                                echo '<input type="hidden" id="peReg" value="0">';
                            }
                            ?>
                            <table id="tb_ingresos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                <thead>
                                    <tr class="text-center centro-vertical">
                                        <th>Id</th>
                                        <th>No. Ingreso</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Hora Ingreso</th>
                                        <th>No. Fac./Acta/Rem.</th>
                                        <th>Fecha Fac./Acta/Rem.</th>
                                        <th>Detalle</th>
                                        <th>Tipo Ingreso</th>
                                        <th>Tercero</th>                                                                                
                                        <th>Sede</th>
                                        <th>Bodega</th>
                                        <th>Vr. Total</th>
                                        <th>Id.Estado</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
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
    <script type="text/javascript" src="../../js/ingresos/ingresos.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>