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
// Lista de datos datos_ejecucion_presupuesto_crp.php
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
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

<body class="sb-nav-fixed <?php echo  $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : ''; ?>">
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
                                    EJECUCION <?php echo strtoupper($nomPresupuestos['nombre']); ?> - REGISTROS PRESUPUESTALES
                                </div>
                                <input type="hidden" id="id_pto_ppto" value="<?php echo $_POST['id_pto']; ?>">
                                <input type="hidden" id="peReg" value="0">
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div clas="row">
                                    <div class="center-block">
                                        <div class="input-group">
                                            <div class="input-group-prepend px-1">
                                                <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                                                    <select class="custom-select" id="slcMesHe" name="slcMesHe" onchange="cambiaListado(value)">
                                                        <option value='1'>CDP - CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL</option>
                                                        <option selected value='2'>CRP - CERTIFICADO DE REGISTRO PRESUPUESTAL</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <br>

                                <!--Opciones de filtros -->
                                <div class="form-row">
                                    <div class="form-group col-md-1">
                                        <input type="text" class="filtrocrp form-control form-control-sm" id="txt_idmanu_filtrocrp" placeholder="Id. Manu CRP">
                                    </div>
                                    <div class="form-group col-md-1">
                                        <input type="text" class="filtrocrp form-control form-control-sm" id="txt_idmanucdp_filtrocrp" placeholder="Id. Manu CDP">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <input type="date" class="form-control form-control-sm" id="txt_fecini_filtrocrp" name="txt_fecini_filtro" placeholder="Fecha Inicial">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <input type="date" class="form-control form-control-sm" id="txt_fecfin_filtrocrp" name="txt_fecfin_filtro" placeholder="Fecha Final">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-1">
                                        <input type="text" class="filtrocrp form-control form-control-sm" id="txt_contrato_filtrocrp" placeholder="Contrato">
                                    </div>
                                    <div class="form-group col-md-1">
                                        <input type="text" class="filtrocrp form-control form-control-sm" id="txt_ccnit_filtrocrp" placeholder="CC / Nit">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <input type="text" class="filtrocrp form-control form-control-sm" id="txt_tercero_filtrocrp" placeholder="Tercero">
                                    </div>
                                    <div class="form-group col-md-1">
                                        <select class="form-control form-control-sm" id="sl_estado_filtrocrp">
                                            <option value="0">--Estado--</option>
                                            <option value="1">Abierto</option>
                                            <option value="2">Cerrado</option>
                                            <option value="3">Anulado</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-1">
                                        <a type="button" id="btn_buscar_filtrocrp" class="btn btn-outline-success btn-sm" title="Filtrar">
                                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </div>

                                <table id="tableEjecPresupuestoCrp" class="table table-striped table-bordered table-sm table-hover shadow" style="table-layout: fixed;width: 100%;">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width: 8%;">Numero</th>
                                            <th style="width: 8%;">Cdp</th>
                                            <th style="width: 10%;">Fecha</th>
                                            <th style="width: 10%;">Contrato</th>
                                            <th style="width: 10%;">CC/Nit</th>
                                            <th style="width: 32%;">Tercero</th>
                                            <th style="width: 12%;">Valor</th>
                                            <th style="min-width: 150px;">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificarEjecPresupuestoCrp">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Numero</th>
                                            <th>Cdp</th>
                                            <th>Fecha</th>
                                            <th>Contrato</th>
                                            <th>CC/Nit</th>
                                            <th>Tercero</th>
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
    </div>
    <?php include '../scripts.php' ?>
</body>

</html>