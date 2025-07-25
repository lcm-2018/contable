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
                                    EXISTENCIA A UNA FECHA
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
                                <div class="form-group col-md-4">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <input type="date" class="filtro form-control form-control-sm" id="txt_fecha_filtro" name="txt_fecha_filtro" placeholder="Fecha Corte">
                                        </div>                                        
                                        <div class="form-group col-md-4">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_codigo_filtro" placeholder="Codigo">
                                        </div>    
                                        <div class="form-group col-md-4">
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_nombre_filtro" placeholder="Nombre">
                                        </div> 
                                    </div> 
                                </div>     
                                <div class="form-group col-md-2">
                                    <select class="filtro form-control form-control-sm" id="sl_subgrupo_filtro">
                                        <?php subgrupo_articulo($cmd,'--Subgrupo--') ?>
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
                                <div class="form-group col-md-5">                                    
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <select class="filtro form-control form-control-sm" id="sl_tipoasis_filtro">
                                                <?php estados_sino('--Uso Asistencial--') ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <select class="filtro form-control form-control-sm" id="sl_conexi_filtro">
                                                <?php con_existencia('--Existencia--') ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <select class="filtro form-control form-control-sm" id="sl_lotven_filtro">
                                                <?php lotes_vencidos('--Lotes--') ?>
                                            </select>
                                        </div>                                
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-row">                                                                                
                                        <div class="form-group col-md-7">
                                            <div class="form-check form-check-inline">
                                                <input class="filtro form-check-input" type="checkbox" id="chk_artact_filtro" checked>
                                                <label class="form-check-label small" for="chk_artact_filtro">Articulos Activos</label>
                                            </div>    
                                        </div>
                                        <div class="form-group col-md-5">
                                            <input class="filtro form-check-input" type="checkbox" id="chk_lotact_filtro" checked>
                                            <label class="form-check-label small" for="chk_lotact_filtro">Lotes Activos</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <select class="filtro form-control form-control-sm text-primary" id="sl_tipo_reporte">
                                        <?php tipo_reporte_exi_fecha('--TIPO DE REPORTE--') ?>
                                    </select>
                                </div>                                                                                            
                            </div>

                            <!--Lista de registros en la tabla-->
                            <table id="tb_articulos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                <thead>
                                    <tr class="text-center centro-vertical">
                                        <th>Id</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Subgrupo</th>
                                        <th>Existencia</th>
                                        <th>Vr. Promedio</th>
                                        <th>Vr. Total</th>
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
    <script type="text/javascript" src="../../js/existencia_fecha/existencia_fecha.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>