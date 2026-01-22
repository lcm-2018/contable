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
                                    HOJA DE VIDA ACTIVOS FIJOS
                                </div>
                            </div>
                        </div>

                        <!--Cuerpo Principal del formulario -->
                        <div class="card-body" id="divCuerpoPag">

                            <!--Opciones de filtros -->
                            <div class="form-row">
                                <div class="form-group col-md-1">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_placa_filtro" placeholder="Placa">
                                </div>
                                <div class="form-group col-md-2">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_nombre_filtro" placeholder="Nombre">
                                </div>
                                <div class="form-group col-md-1">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_serial_filtro" placeholder="Serial">
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_marcas_filtro">
                                        <?php marcas($cmd,'--Marca--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_estadogen_filtro">
                                        <?php estado_general_activo('--Estado General--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="filtro form-control form-control-sm" id="sl_estado_filtro">
                                        <?php estado_activo('--Estado Proceso--') ?>
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
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_sede_filtro">
                                        <?php sedes($cmd, '--Sede--') ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <select class="form-control form-control-sm" id="sl_area_filtro">
                                    </select>
                                </div> 
                            </div>
                            
                            <!--Lista de registros-->                            
                            <?php
                            if (PermisosUsuario($permisos, 5704, 2) || $id_rol == 1) {
                                echo '<input type="hidden" id="peReg" value="1">';
                            } else {
                                echo '<input type="hidden" id="peReg" value="0">';
                            }
                            ?>
                            <table id="tb_hojavida" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                <thead>
                                    <tr class="text-center centro-vertical">
                                        <th>Id</th>
                                        <th>Placa</th>                                        
                                        <th>Cod. Articulo</th>
                                        <th>Articulo</th>
                                        <th>Nombre Activo Fijo</th>
                                        <th>No. Serial</th>
                                        <th>Marca</th>
                                        <th>Valor</th>                                        
                                        <th>Sede</th>
                                        <th>Area</th>
                                        <th>Responsable</th>
                                        <th>Id.Estado General</th>
                                        <th>Estado</br>Funcionam.</th>
                                        <th>Mantenimiento</br>(Apro.-Eejec.)</th>
                                        <th>Id.Estado</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                            <table class="table-bordered table-sm col-md-5">
                                <tr>
                                    <td>Activo</td>
                                    <td style="background-color:yellow">Para mantenimiento</td>
                                    <td style="background-color:DodgerBlue">En mantenimiento</td>
                                    <td style="background-color:green">Inactivo</td>
                                    <td style="background-color:gray">Dado de baja</td>
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
    <script type="text/javascript" src="../../js/hojavida/hojavida.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>