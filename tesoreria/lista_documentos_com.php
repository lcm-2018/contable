<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
$tipo_doc = isset($_POST['id_tipo_doc']) ? $_POST['id_tipo_doc'] : '0';
$tipo = isset($_POST['var']) ? $_POST['var'] : '';
unset($_SESSION['id_doc']);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_doc_fuente`, `cod`, `nombre` FROM `ctb_fuente` WHERE `tesor` = $tipo";
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
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php'; ?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : '' ?>">

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
                                <?php
                                if (((PermisosUsuario($permisos, 5601, 2) && $tipo == 1) || (PermisosUsuario($permisos, 5602, 2) && $tipo == 2) || PermisosUsuario($permisos, 5603, 2) && $tipo == 3) || (PermisosUsuario($permisos, 5604, 2) && $tipo == 4) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>
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
                                                        <option value="0">-- Seleccionar --</option>
                                                        <?php
                                                        foreach ($docsFuente as $df) {
                                                            if ($df['id_doc_fuente'] == $tipo_doc) {
                                                                echo '<option value="' . $df['id_doc_fuente'] . '" selected>' . $df['nombre'] .  '</option>';
                                                            } else {
                                                                echo '<option value="' . $df['id_doc_fuente'] . '">' . $df['nombre'] . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                    <input type="hidden" name="var_tip" id="var_tip" value="<?php echo $tipo; ?>">
                                                </form>
                                                <?php
                                                if ($tipo_doc == '4') {
                                                    echo '<div class="input-group-prepend px-1">
                                                        <button type="button" class="btn btn-primary" onclick ="CargaObligaPago(this)">
                                                          Ver Listado
                                                        </button>
                                                     </div>
                                                     <div class="input-group-prepend px-1">
                                                        <button type="button" class="btn btn-outline-secondary" onclick ="cargaListaReferenciaPago(2)">
                                                          Referencias <span class="badge badge-light"><?php echo $tipo_doc; ?></span>
                                                        </button>
                                                     </div>
                                                     <div class="input-group-prepend px-1">
                                                     <input type="hidden" id="total" value="' . $total . '">
                                                         <button type="button" class="btn btn-outline-success" onclick ="CegresoNomina(this)">
                                                           Nómina <span class="badge badge-light" id="totalCausa">' . $total . '</span>
                                                         </button>
                                                      </div>';
                                                }
                                                if ($tipo_doc == '11') {
                                                    echo '<div class="input-group-prepend px-1">
                                                        <button type="button" class="btn btn-secondary" onclick ="CargaArqueoCaja(2)">
                                                          Ver Listado <span class="badge badge-light"><?php echo $tipo_doc; ?></span>
                                                        </button>
                                                     </div>';
                                                }
                                                if ($tipo_doc == '6') {
                                                    echo '<div class="input-group-prepend px-1">
                                                        <button type="button" class="btn btn-primary" onclick ="CargaListaRads()">
                                                          Ver Listado
                                                        </button>
                                                     </div>';
                                                }
                                                ?>
                                                <div class="input-group-prepend px-1">
                                                    <button type="button" class="btn btn-success" title="Imprimir por Lotes" id="btnImpLotesTes">
                                                        <i class="fas fa-print fa-lg"></i>
                                                    </button>
                                                </div>
                                                <?php
                                                if ($tipo_doc > '0') {
                                                    echo '<div class="input-group-prepend px-1">
                                                            <button  onclick="cargarConsecutivos(' . $tipo_doc . ')" class="btn btn-outline-info btn-sm btn-circle" title="Consultar Consecutivos"><span class="fas fa-info-circle"></span></button>
                                                        </div>';
                                                }
                                                ?>
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
                                        <input type="text" class="filtro form-control form-control-sm" id="txt_ccnit_filtro" placeholder="CC / Nit">
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
                                        <a type="button" id="btn_buscar_filtro" class="btn btn-outline-success btn-sm" title="Filtrar">
                                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </div>


                                <?php if ($tipo_doc != '0') { ?>
                                    <table id="tableMvtoTesoreriaPagos" class="table table-striped table-bordered table-sm table-hover shadow" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <?php if ($tipo_doc != '13') { ?>
                                                    <th style="width: 8%;">Numero</th>
                                                    <th style="width: 8%;">Fecha</th>
                                                    <th style="width: 8%;">CC/Nit</th>
                                                    <th style="width: 40%;">Tercero</th>
                                                    <th style="width: 12%;">Valor</th>
                                                    <th style="width: 12%;">Acciones</th>
                                                <?php
                                                } else { ?>
                                                    <th>Acto</th>
                                                    <th>Num. Acto</th>
                                                    <th>Nombre Caja</th>
                                                    <th>Inicia</th>
                                                    <th>Acto</th>
                                                    <th>Total</th>
                                                    <th>Minimo.</th>
                                                    <th>Póliza</th>
                                                    <th>%</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody id="modificartableMvtoTesoreriaPagos">
                                        </tbody>
                                    </table>
                                <?php } ?>
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

<script type="text/javascript" src="js/informes/common.js?v=<?php echo date('YmdHis') ?>"></script>