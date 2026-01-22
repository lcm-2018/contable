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
                                    REPORTES PERSONALIZADOS
                                </div>
                            </div>
                        </div>

                        <!--Cuerpo Principal del formulario -->
                        <div class="card-body" id="divCuerpoPag">
                            <table style="width:100% !important">
                                <tr>
                                    <td style="width:50% !important" class="align-top">
                                        <div class="form-row">
                                            <input type="hidden" id="txt_id_opcion" value="<?php echo $_POST['id_opcion'] ?>">
                                            <div class="form-group col-md-6">
                                                <input type="text" class="filtro form-control form-control-sm" id="txt_nombre_filtro" placeholder="Nombre">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <a type="button" id="btn_buscar_filtro" class="btn btn-outline-success btn-sm" title="Filtrar">
                                                    <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                                </a>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <table id="tb_consultas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                                    <thead>
                                                        <tr class="text-center centro-vertical">
                                                            <th>Id</th>
                                                            <th>Nombre</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width:50% !important">
                                        <div class="form-row">
                                            <input type="hidden" class="form-control form-control-sm" id="txt_id_consulta" name="txt_id_consulta" readonly="readonly">
                                            <div class="form-group col-md-12">
                                                <label for="txt_nom_con" class="small">Detalles del Reporte</label>
                                                <input type="text" class="form-control form-control-sm" id="txt_nom_consulta" name="txt_nom_consulta" readonly="readonly">
                                            </div>
                                            <div class="form-group col-md-12">
                                                <textarea class="form-control form-control-sm" id="txt_des_consulta" name="txt_des_consulta" rows="3" readonly="readonly"></textarea>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <form id="frm_parametros"></form>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <a type="button" id="btn_buscar_consulta" class="btn btn-outline-success btn-sm">
                                                    <span class="fas fa-search fa-lg" aria-hidden="true">
                                                        <label class="small">Consultar</label>
                                                    </span>
                                                </a>
                                                <a type="button" id="btn_imprimir_consulta" class="btn btn-outline-success btn-sm">
                                                    <span class="fas fa-search fa-lg" aria-hidden="true">
                                                        <label class="small">Imprimir</label>
                                                    </span>
                                                </a>
                                                <a type="button" id="btn_exportar_consulta" class="btn btn-outline-success btn-sm">
                                                    <span class="fas fa-search fa-lg" aria-hidden="true">
                                                        <label class="small">Exportar</label>
                                                    </span>                                                    
                                                </a>       
                                                <label id="lbl_archivo"></label>                                        
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="txt_limite" class="small">Límite Registros a Visualizar</label>
                                                <input type="number" class="form-control form-control-sm" id="txt_limite" name="txt_limite" value="100">
                                            </div>
                                            <div class="form-group col-md-8">
                                                <label class="small">
                                                    Esto solo aplica en el caso de visualizar los datos en pantalla.
                                                    Utilice la opción Exportar para envía el total de los datos a un archivo.
                                                    En consultas grandes y/o pesadas es recomendable limitar el máximo de registros
                                                    a visualizar.
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div id="dv_resultado"></div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <?php include '../../../modales.php' ?>
    </div>
    <?php include '../../../scripts.php' ?>
    <script type="text/javascript" src="../../js/inf_personalizados/inf_personalizados.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>