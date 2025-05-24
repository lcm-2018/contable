<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
$tipo_doc = isset($_POST['cod_doc']) ? $_POST['cod_doc'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de documento
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `cod`, `nombre` FROM `ctb_fuente` WHERE `contab` = 2 ORDER BY `nombre`";
    $rs = $cmd->query($sql);
    $docsFuente = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_doc_fuente` FROM `ctb_fuente` WHERE `cod` = '$tipo_doc'";
    $rs = $cmd->query($sql);
    $tipo_doc_fuente = $rs->fetch();
    $id_tipo_doc = !empty($tipo_doc_fuente) ? $tipo_doc_fuente['id_doc_fuente'] : 0;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$cmd = null;
?>

<body class="sb-nav-fixed <?= $_SESSION['navarlat'] === '1' ? 'toggled' : ''; ?>">

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
                                    REGISTRO DE MOVIMIENTOS CONTABLES FACTURACIÓN
                                </div>
                                <?php
                                if ((PermisosUsuario($permisos, 5511, 2)  || $id_rol == 1)) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <input type="hidden" id="id_ctb_doc" value="<?php echo $id_tipo_doc; ?>">
                            <div>
                                <div clas="row">
                                    <div class="center-block">
                                        <div class="input-group">
                                            <div class="input-group-prepend px-1">
                                                <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                                                    <select class="custom-select " id="cod_ctb_doc" name="cod_ctb_doc" onchange="cambiaListadoCtbInvoice(value)">
                                                        <option value="">-- Seleccionar --</option>
                                                        <?php
                                                        foreach ($docsFuente as $mov) {
                                                            if ($mov['cod'] == $tipo_doc) {
                                                                echo '<option value="' . $mov['cod'] . '" selected>' . $mov['nombre'] .  '</option>';
                                                            } else {
                                                                echo '<option value="' . $mov['cod'] . '">' . $mov['nombre'] . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </form>
                                                <?php
                                                if ($tipo_doc == 'FELE' && $_SESSION['pto'] == '1') {
                                                    echo '<div class="input-group-prepend px-1">
                                                        <button type="button" class="btn btn-primary" onclick ="CargaObligaRad(2)">
                                                          Ver Listado <span class="badge badge-light"></span>
                                                        </button>
                                                     </div>';
                                                }
                                                ?>
                                                <button type="button" class="btn btn-success" title="Imprimir por Lotes" id="btnImpLotes">
                                                    <i class="fas fa-print fa-lg"></i>
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <br>

                                <!--Opciones de filtros -->
                                <div class="form-row">
                                    <div class="form-group col-md-1">
                                        <input type="text" class="filtro form-control form-control-sm" id="txt_idmanu_filtro" placeholder="Id. Manu">
                                    </div>
                                    <div class="form-group col-md-1">
                                        <input type="text" class="filtro form-control form-control-sm" id="txt_rad_filtro" placeholder="RP">
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
                                    <div class="form-group col-md-3">
                                        <input type="text" class="filtro form-control form-control-sm" id="txt_tercero_filtro" placeholder="Tercero">
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
                                        <a type="button" id="btn_buscar_filtro_Invoice" class="btn btn-outline-success btn-sm" title="Filtrar">
                                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </div>

                                <?php
                                if ($tipo_doc != '') {
                                ?>
                                    <table id="tableMvtCtbInvoice" class="table table-striped table-bordered table-sm table-hover shadow" style="table-layout: fixed;width: 98%;">
                                        <thead>
                                            <tr>
                                                <th style="width: 8%;">Numero</th>
                                                <th style="width: 8%;">RAD</th>
                                                <th style="width: 8%;">Fecha</th>
                                                <th style="width: 44%;">Tercero</th>
                                                <th style="width: 12%;">Valor</th>
                                                <th style="width: 12%;">Acciones</th>

                                            </tr>
                                        </thead>
                                        <tbody id="modificarMvtCtbInvoice">
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Numero</th>
                                                <th>RAD</th>
                                                <th>Fecha</th>
                                                <th>Tercero</th>
                                                <th>Valor</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php
                                }
                                ?>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <!-- Modal formulario-->
        <?php include '../modales.php' ?>

    </div>
    <?php include '../scripts.php' ?>

</body>

</html>