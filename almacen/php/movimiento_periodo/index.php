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
                                    MOVIMIENTOS PERIODO
                                </div>
                            </div>
                        </div>

                        <!--Cuerpo Principal del formulario -->
                        <div class="card-body" id="divCuerpoPag">

                            <!--Opciones de filtros -->
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <select class="filtro form-control form-control-sm" id="sl_sede_filtro">
                                        <?php sedes_usuario($cmd, '--Sede--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="filtro form-control form-control-sm" id="sl_bodega_filtro">
                                    </select>
                                </div> 
                                <div class="form-group col-md-6">
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <input type="date" class="filtro form-control form-control-sm" id="txt_fecini_filtro" name="txt_fecini_filtro" placeholder="Fecha Inicio">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <input type="date" class="filtro form-control form-control-sm" id="txt_fecfin_filtro" name="txt_fecfin_filtro" placeholder="Fecha Fin">
                                        </div>                                                                              
                                        <div class="form-group col-md-3">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_codigo_filtro" placeholder="Codigo">
                                        </div>   
                                        <div class="form-group col-md-3">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_nombre_filtro" placeholder="Nombre">
                                        </div>
                                    </div>
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
                                <div class="form-group col-md-2">
                                    <select class="filtro form-control form-control-sm" id="sl_tipoasis_filtro">
                                        <?php estados_sino('--Uso Asistencial--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="filtro form-control form-control-sm" id="sl_subgrupo_filtro">
                                        <?php subgrupo_articulo($cmd,'--Subgrupo--') ?>
                                    </select>
                                </div>                              
                                <div class="form-group col-md-2">
                                    <div class="form-check form-check-inline">
                                        <input class="filtro form-check-input" type="checkbox" id="chk_artact_filtro" checked>
                                        <label class="form-check-label small" for="chk_artact_filtro">Articulos Activos</label>
                                    </div>    
                                </div>                                 
                                <div class="form-group col-md-2">
                                    <input class="filtro form-check-input" type="checkbox" id="chk_conexi_filtro" checked>
                                    <label class="form-check-label small" for="chk_conexi_filtro">Con Existencias</label>
                                </div>                                                                                              
                            </div>

                            <!--Lista de registros en la tabla-->
                            <table id="tb_articulos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                <thead>                               
                                    <tr class="text-center centro-vertical">
                                        <th rowspan="2">Id</th>
                                        <th rowspan="2">Código</th>
                                        <th rowspan="2">Nombre</th>
                                        <th rowspan="2">Subgrupo</th>
                                        <th colspan="2">Saldo Inicial</th>
                                        <th colspan="2">Entradas</th>
                                        <th colspan="2">Salidas</th>
                                        <th colspan="2">Saldo Final</th>
                                    </tr>
                                    <tr class="text-center centro-vertical">
                                        <th>Existencia</th>
                                        <th>Valores</th>
                                        <th>Cantidad</th>
                                        <th>Valores</th>
                                        <th>Cantidad</th>
                                        <th>Valores</th>
                                        <th>Existencia</th>
                                        <th>Valores</th>
                                    </tr>    
                                </thead>                                
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
    <script type="text/javascript" src="../../js/movimiento_periodo/movimiento_periodo.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>