<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
if (!isset($_POST['tipo_doc'])) $tipo_doc = '';
else $tipo_doc = $_POST['tipo_doc'];
if (!isset($_GET['var'])) $tipo = $_POST['var'];
else $tipo = $_GET['var'];
// Consulta tipo de documento
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT cod,nombre FROM ctb_fuente WHERE tesor=$tipo";
    $sql3 = $sql;
    $rs = $cmd->query($sql);
    $docsFuente = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_nomina_pto_ctb_tes`.`id`
                , `nom_nomina_pto_ctb_tes`.`id_nomina`
                , `nom_nomina_pto_ctb_tes`.`tipo`
                , `nom_nomina_pto_ctb_tes`.`cdp`
                , `nom_nomina_pto_ctb_tes`.`crp`
                , `nom_nominas`.`descripcion`
                , `nom_nominas`.`mes`
                , `nom_nominas`.`vigencia`
                , `nom_nominas`.`estado`
            FROM
                `nom_nomina_pto_ctb_tes`
                INNER JOIN `nom_nominas` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE (`nom_nominas`.`estado` = 4) AND`nom_nomina_pto_ctb_tes`.`tipo` <> 'PL'
            UNION 
            SELECT
                `nom_nomina_pto_ctb_tes`.`id`
                , `nom_nomina_pto_ctb_tes`.`id_nomina`
                , `nom_nomina_pto_ctb_tes`.`tipo`
                , `nom_nomina_pto_ctb_tes`.`cdp`
                , `nom_nomina_pto_ctb_tes`.`crp`
                , `nom_nominas`.`descripcion`
                , `nom_nominas`.`mes`
                , `nom_nominas`.`vigencia`
                , `nom_nominas`.`planilla` AS `estado`
            FROM
                `nom_nomina_pto_ctb_tes`
                INNER JOIN `nom_nominas` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE (`nom_nominas`.`planilla` = 4 AND `nom_nomina_pto_ctb_tes`.`tipo` = 'PL')";
    $rs = $cmd->query($sql);
    $nominas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $total = count($nominas);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">

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
                                    REGISTRO DE MOVIMIENTOS DE TESORERIA
                                </div>
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div clas="row">
                                    <div class="center-block">
                                        <div class="input-group">
                                            <div class="input-group-prepend px-1">
                                                <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                                                    <select class="custom-select " id="id_ctb_tipo" name="id_ctb_tipo" onchange="cambiaListadoTesoreria(value,'<?php echo $tipo; ?>')">
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
                                                    <input type="hidden" name="var_tip" id="var_tip" value="<?php echo $tipo; ?>">
                                                </form>
                                                <?php
                                                if ($tipo_doc == 'CEVA') {
                                                    echo '<div class="input-group-prepend px-1">
                                                        <button type="button" class="btn btn-primary" onclick ="CargaObligaPago(2)">
                                                          Ver Listado <span class="badge badge-light"><?php echo $tipo_doc; ?></span>
                                                        </button>
                                                     </div>
                                                     <div class="input-group-prepend px-1">
                                                        <button type="button" class="btn btn-outline-secondary" onclick ="cargaListaReferenciaPago(2)">
                                                          Referencias <span class="badge badge-light"><?php echo $tipo_doc; ?></span>
                                                        </button>
                                                     </div>';
                                                }
                                                if ($tipo_doc == 'CTCB') {
                                                    echo '<div class="input-group-prepend px-1">
                                                        <button type="button" class="btn btn-secondary" onclick ="CargaArqueoCaja(2)">
                                                          Ver Listado <span class="badge badge-light"><?php echo $tipo_doc; ?></span>
                                                        </button>
                                                     </div>';
                                                }
                                                if ($tipo_doc == 'CEVA') {
                                                    echo '<div class="input-group-prepend px-1">
                                                    <input type="hidden" id="total" value="' . $total . '">
                                                        <button type="button" class="btn btn-outline-success" onclick ="CegresoNomina()">
                                                          Nómina <span class="badge badge-light" id="totalCausa">' . $total . '</span>
                                                        </button>
                                                     </div>';
                                                }
                                                ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <br>
                                <table id="tableMvtoTesoreriaPagos" class="table table-striped table-bordered table-sm table-hover shadow" style="table-layout: fixed;width: 98%;">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">Numero</th>
                                            <th style="width: 8%;">Fecha</th>
                                            <th style="width: 8%;">CC/Nit</th>
                                            <th style="width: 40%;">Tercero</th>
                                            <th style="width: 12%;">Valor</th>
                                            <th style="width: 12%;">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificartableMvtoTesoreriaPagos">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Numero</th>
                                            <th>Fecha</th>
                                            <th>CC/Nit</th>
                                            <th>Tercero</th>
                                            <th>Valor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="text-center pt-4">
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