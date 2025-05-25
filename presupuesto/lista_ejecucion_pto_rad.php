<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `nombre` FROM `pto_presupuestos` WHERE `id_pto` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $nomPresupuestos = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>

<body class="sb-nav-fixed <?= $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : ''; ?>">

    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    EJECUCION <?php echo strtoupper($nomPresupuestos['nombre']); ?> -RECONOCIMIENTO PRESUPUESTAL
                                </div>
                                <input type="hidden" id="id_pto_ppto" value="<?php echo $_POST['id_pto']; ?>">
                                <?php if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
                                    echo  '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo  '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <input type="hidden" id="id_pto_presupuestos" value="1">
                                <?php
                                if (false) {
                                ?>
                                    <div class="row">
                                        <div class="form-group col-md-5">
                                            <label for="txt_tercero_filtro" class="small">Historial Terceros</label>
                                            <input type="text" class="filtro form-control form-control-sm" id="txt_tercero_filtro" name="txt_tercero_filtro" placeholder="Tercero">
                                            <input type="hidden" id="id_txt_tercero" name="id_txt_tercero" class="form-control form-control-sm">
                                        </div>
                                        <div class="form-group col-md-1">
                                            <label for="txtbl" class="small">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                            <input hidden id="txtbl" value="1">
                                            <a type="button" id="btn_historialtercero" class="btn btn-outline-success btn-sm" title="Historial tercero">
                                                <span class="fas fa-history fa-lg" aria-hidden="true"></span>
                                            </a>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                <br>
                                <!--Opciones de filtros -->
                                <div class="form-row">
                                    <div class="form-group col-md-1">
                                        <input type="text" class="filtro form-control form-control-sm" id="txt_idmanu_filtro" placeholder="Id. Manu">
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
                                    <div class="form-group col-md-5">
                                        <input type="text" class="filtro form-control form-control-sm" id="txt_objeto_filtro" placeholder="Objeto">
                                    </div>
                                    <div class="form-group col-md-1">
                                        <select class="form-control form-control-sm" id="sl_estado_filtro">
                                            <option value="0">--Estado--</option>
                                            <option value="1">Abierto</option>
                                            <option value="2">Cerrado</option>
                                            <option value="3">Anulado</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-1">
                                        <a type="button" id="btn_buscar_filtro" class="btn btn-outline-success btn-sm" title="Filtrar">
                                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </div>

                                <table id="tablePptoRad" class="table table-striped table-bordered table-sm table-hover shadow" style="table-layout: fixed;width: 100%;">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width: 8%;">Numero</th>
                                            <th style="width: 10%;">Factura</th>
                                            <th style="width: 10%;">Fecha</th>
                                            <th style="width: 30%;">Tercero</th>
                                            <th style="width: 30%;">Objeto</th>
                                            <th style="width: 12%;">Valor</th>
                                            <th style="min-width: 150px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarPptoRad">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Numero</th>
                                            <th>Factura</th>
                                            <th>Fecha</th>
                                            <th>Tercero</th>
                                            <th>Objeto</th>
                                            <th>Valor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-danger" style="width: 7rem;" href="lista_presupuestos.php"> VOLVER</a>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <?php include '../modales.php' ?>
        <!-- Modal formulario-->
        <div class="modal fade" id="divModalForms3" tabindex="-2" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalForms3" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divForms3">
                        <div class="text-right pt-3">
                            <a type="button" class="close btn btn-danger btn-sm" data-dismiss="modal"> Cerrar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <?php include '../scripts.php' ?>
</body>

</html>