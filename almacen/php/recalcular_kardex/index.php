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
                                    RECALCULAR KARDEX DE LOTES DE ARTICULOS
                                </div>
                            </div>
                        </div>

                        <!--Cuerpo Principal del formulario -->
                        <div class="card-body" id="divCuerpoPag">

                            <!--Opciones de filtros -->
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <select class="filtro form-control form-control-sm" id="sl_sede_filtro">
                                                <?php sedes_usuario($cmd, '--Sede--') ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <select class="filtro form-control form-control-sm" id="sl_bodega_filtro">
                                            </select>
                                        </div>    
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input chk_aplica" type="radio" name="rdo_opcion" id="rdo_opcion1" value="O">
                                                <label class="form-check-label small" for="rdo_opcion1">Datos Articulo</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_codigo_filtro" placeholder="Codigo" disabled="disabled">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_nombre_filtro" placeholder="Nombre" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-row">    
                                        <div class="form-group col-md-8">
                                            <label class="form-control-sm">Fecha Inicial de proceso Recalcular Kardex</label>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <input type="date" class="filtro form-control form-control-sm" id="txt_fecha_filtro" name="txt_fecha_filtro" placeholder="Fecha Inicial" disabled="disabled">
                                        </div>
                                    </div>   
                                </div>
                                <div class="form-group col-md-4">                                    
                                    <div class="form-row">
                                        <div class="form-group col-md-5">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input chk_aplica" type="radio" name="rdo_opcion" id="rdo_opcion2" value="I">
                                                <label class="form-check-label small" for="rdo_opcion2">Id. Orden Ingreso</label>
                                            </div>
                                        </div>    
                                        <div class="form-group col-md-4">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_id_ing_filtro" placeholder="Id. Ingreso" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-5">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input chk_aplica" type="radio" name="rdo_opcion" id="rdo_opcion3" value="E">
                                                <label class="form-check-label small" for="rdo_opcion3">Id. Orden Egreso</label>
                                            </div>
                                        </div>    
                                        <div class="form-group col-md-4">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_id_egr_filtro" placeholder="Id. Egreso" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-5">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input chk_aplica" type="radio" name="rdo_opcion" id="rdo_opcion4" value="T">
                                                <label class="form-check-label small" for="rdo_opcion4">Id. Orden Traslado</label>
                                            </div>
                                        </div>    
                                        <div class="form-group col-md-4">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_id_tra_filtro" placeholder="Id. Traslado" disabled="disabled">
                                        </div>
                                    </div>                              
                                </div> 
                                <div class="form-group col-md-1">
                                    <a type="button" id="btn_buscar_filtro" class="btn btn-outline-success btn-sm" title="Filtrar">
                                        <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                    </a>
                                </div>
                                    <div class="form-group col-md-1">    
                                    <a type="button" id="btn_recalcular_filtro" class="btn btn-outline-success btn-sm" title="Imprimir">
                                        <span class="fas fa-cog fa-lg" aria-hidden="true"></span>
                                        <label class="form-check-label small">Recalcular Lotes</label>
                                    </a>
                                </div>   
                            </div>
                           
                            <!--Lista de registros en la tabla-->
                            <form id="frm_lotes">
                                <table id="tb_lotes" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                    <thead>
                                        <tr class="text-center centro-vertical">
                                            <th rowspan="2">
                                                <label for="chk_sel_filtro">Sel.</label>
                                                <input type="checkbox" id="chk_sel_filtro">
                                            </th>
                                            <th colspan="3">Articulo</th>
                                            <th colspan="5">Lote</th>
                                            <th colspan="3">Existencia Total</th>
                                        </tr>
                                        <tr class="text-center centro-vertical">
                                            <th>Id.</th>
                                            <th>Código</th>
                                            <th>Descripción</th>
                                            <th>Sede</th>
                                            <th>Bodega</th>
                                            <th>Id.</th>
                                            <th>Lote</th>
                                            <th>Existencia</th>
                                            <th>Código Articulo</th>
                                            <th>Existencia</th>
                                            <th>Vr. Promedio</th>
                                        </tr>
                                    </thead>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <?php include '../../../modales.php' ?>
    </div>
    <?php include '../../../scripts.php' ?>
    <script type="text/javascript" src="../../js/recalcular_kardex/recalcular_kardex.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>