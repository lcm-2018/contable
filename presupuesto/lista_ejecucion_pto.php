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
// consulto el numero de registros en ctt_adquisiciones con estado 5 e id_cdp =0
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_adquisicion` FROM `ctt_adquisiciones` WHERE `estado` = 6 AND (`id_cdp` = 1 OR `id_cdp` IS NULL) AND `vigencia` = $vigencia";
    $rs = $cmd->query($sql);
    // buscar num rows de la consulta
    $numadq = $rs->rowCount();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    // consulta la cantidad de registros que tiene la tabla  ctt_novedad_adicion_prorroga donde cdp es null
    $sql = "SELECT COUNT(*) FROM `ctt_novedad_adicion_prorroga` WHERE (`id_cdp` IS NULL);";
    $rs = $cmd->query($sql);
    $total = $rs->fetchColumn();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT '0' AS`patronal`, `id_nomina`, `estado`, `descripcion`, `mes`, `vigencia`, `tipo` FROM `nom_nominas` WHERE `estado` = 2
            UNION
            SELECT	
                    `t1`.`seg_patronal` + `t2`.`parafiscales` AS `patronal`
                    , `t1`.`id_nomina`
                    , `nom_nominas`.`planilla` AS estado
                    , `nom_nominas`.`descripcion`
                    , `nom_nominas`.`mes`
                    , `nom_nominas`.`vigencia`
                    , 'P' AS `tipo`
            FROM
                    (SELECT
                        SUM(`aporte_salud_empresa`) + SUM(`aporte_pension_empresa`) + SUM(`aporte_rieslab`) AS `seg_patronal`
                        , `anio`
                        , `id_nomina`
                    FROM
                        `nom_liq_segsocial_empdo`
                    WHERE `anio` = '$vigencia'
                    GROUP BY `id_nomina`) AS`t1`
                    LEFT JOIN 
                    (SELECT
                        SUM(`val_sena`) + SUM(`val_icbf`) + SUM(`val_comfam`) AS `parafiscales`
                        , `anio_pfis`
                        , `id_nomina` 
                    FROM
                        `nom_liq_parafiscales`
                    WHERE `anio_pfis` = '$vigencia'
                    GROUP BY `id_nomina`) AS `t2`
                    ON (`t1`.`id_nomina` = `t2`.`id_nomina`)
            INNER JOIN `nom_nominas` 
                    ON (`t1`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE `nom_nominas`.`planilla` = 2";
    $rs = $cmd->query($sql);
    $cant_nominas = $rs->rowCount();
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
                                    EJECUCION <?php echo strtoupper($nomPresupuestos['nombre']); ?>
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
                                <div clas="row">
                                    <div class="center-block">
                                        <div class="input-group">
                                            <div class="input-group-prepend px-1">
                                                <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                                                    <select class="custom-select" id="slcMesHe" name="slcMesHe" onchange="cambiaListado(value)">
                                                        <option selected value='1'>CDP - CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL</option>
                                                        <option value='2'>CRP - CERTIFICADO DE REGISTRO PRESUPUESTAL</option>
                                                    </select>
                                                </form>

                                            </div>
                                            <div class="input-group-prepend px-1">
                                                <button type="button" class="btn btn-primary" id="botonContrata">
                                                    Contratación <span class="badge badge-light"><?php echo $numadq; ?></span>
                                                </button>
                                            </div>
                                            <div class="input-group-prepend px-1">
                                                <button type="button" class="btn btn-warning" id="botonOtrosi">
                                                    Adición <span class="badge badge-light"><?php echo $total; ?></span>
                                                </button>
                                            </div>
                                            <div class="input-group-prepend px-1">
                                                <button type="button" class="btn btn-success" id="btnPtoNomina">
                                                    <input type="hidden" id="cantidad" value="<?php echo $cant_nominas ?>">
                                                    Nomina <span class="badge badge-light" id="nCant"> <?php echo $cant_nominas ?></span>
                                                </button>
                                            </div>

                                        </div>

                                    </div>
                                </div>

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

                                <br>
                                <table id="tableEjecPresupuesto" class="table table-striped table-bordered table-sm table-hover shadow" style="table-layout: fixed;width: 98%;">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%;">Numero</th>
                                            <th style="width: 8%;">Fecha</th>
                                            <th style="width: 38%;">Objeto</th>
                                            <th style="width: 12%;">Valor CDP</th>
                                            <th style="width: 12%;">X Registrar</th>
                                            <th style="width: 8%;">Registro</th>
                                            <th style="width: 12%;">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificarEjecPresupuesto">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Numero</th>
                                            <th>objeto</th>
                                            <th>Valor CDP</th>
                                            <th>X Registrar</th>
                                            <th>Registro</th>
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