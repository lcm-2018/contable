<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
$id = isset($_POST['id_cuenta']) ? $_POST['id_cuenta'] : exit('Acceso no disponible');
$mes = $_POST['mes'];
$vigencia = $_SESSION['vigencia'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT `fin_mes`, `nom_mes` FROM `nom_meses` WHERE (`codigo` = '$mes')";
    $rs = $cmd->query($sql);
    $dia = $rs->fetch(PDO::FETCH_ASSOC);
    $last = $mes == '02' ? cal_days_in_month(CAL_GREGORIAN, 2, $vigencia) : $dia['fin_mes'];
    $fin_mes = !(empty($dia)) ? $vigencia . '-' . $mes . '-' . $last : 0;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `ctb_doc`.`fecha`
                , `ctb_fuente`.`cod`
                , `ctb_doc`.`id_manu`
                , `ctb_libaux`.`id_tercero_api`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , '--' AS `documento`
                , `ctb_libaux`.`id_ctb_libaux`
                , `tes_conciliacion_detalle`.`id_ctb_libaux` AS `conciliado`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `tes_cuentas` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `tes_conciliacion_detalle`
                    ON (`tes_conciliacion_detalle`.`id_ctb_libaux` = `ctb_libaux`.`id_ctb_libaux`)   
            WHERE (`tes_cuentas`.`id_tes_cuenta` = $id AND `ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` <= '$fin_mes')";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
    $tot_deb = 0;
    $tot_cre = 0;
    $tdc = 0;
    $tcc = 0;
    foreach ($lista as $lp) {
        $tot_deb += $lp['debito'];
        $tot_cre += $lp['credito'];
        if ($lp['conciliado'] > 0) {
            $tdc += $lp['debito'];
            $tcc += $lp['credito'];
        }
    }
    $tot_deb = $tot_deb - $tdc;
    $tot_cre = $tot_cre - $tcc;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `tes_conciliacion`.`id_conciliacion`
                , `tes_conciliacion`.`saldo_extracto`
                , `tes_conciliacion`.`estado`
                , IFNULL(`t1`.`debito`,0) AS `debito`
                , IFNULL(`t1`.`credito`,0) AS `credito`
            FROM
                `tes_conciliacion`
                INNER JOIN `tes_cuentas` 
                    ON (`tes_conciliacion`.`id_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                LEFT JOIN
                (SELECT
                    `tes_conciliacion_detalle`.`id_concilia`
                    , SUM(`ctb_libaux`.`debito`) AS `debito`
                    , SUM(`ctb_libaux`.`credito`) AS `credito`
                FROM
                    `tes_conciliacion_detalle`
                    INNER JOIN `ctb_libaux` 
                        ON (`tes_conciliacion_detalle`.`id_ctb_libaux` = `ctb_libaux`.`id_ctb_libaux`)
                GROUP BY `tes_conciliacion_detalle`.`id_concilia`) AS `t1`
                ON (`t1`.`id_concilia` = `tes_conciliacion`.`id_conciliacion`)
            WHERE (`tes_cuentas`.`id_tes_cuenta` = $id AND `tes_conciliacion`.`vigencia` = '$vigencia' AND `tes_conciliacion`.`mes` = '$mes')";
    $rs = $cmd->query($sql);
    $data = $rs->fetch(PDO::FETCH_ASSOC);
    if (!empty($data)) {
        $id_conciliacion = $data['id_conciliacion'];
        $saldo = $data['saldo_extracto'];
        $estado = $data['estado'];
        $debito = $data['debito'];
        $credito = $data['credito'];
    } else {
        $id_conciliacion = 0;
        $saldo = 0;
        $estado = 0;
        $debito = 0;
        $credito = 0;
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                    `tb_bancos`.`id_banco`
                    , `tes_cuentas`.`id_cuenta`
                    , `tes_cuentas`.`id_tes_cuenta`
                    , `tb_bancos`.`nom_banco`
                    , `tes_tipo_cuenta`.`tipo_cuenta`
                    , `tes_cuentas`.`numero`
                    , `tes_cuentas`.`nombre` AS `descripcion`
                    , `t1`. `debito`
                    , `t1`.`credito`
                    , `ctb_pgcp`.`cuenta` AS `cta_contable`
                FROM
                    `tes_cuentas`
                    INNER JOIN `ctb_pgcp` 
                        ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    INNER JOIN `tb_bancos` 
                        ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                    INNER JOIN `tes_tipo_cuenta` 
                        ON (`tes_cuentas`.`id_tipo_cuenta` = `tes_tipo_cuenta`.`id_tipo_cuenta`)
                    INNER JOIN 
                        (SELECT
                            `ctb_libaux`.`id_cuenta`
                            , SUM(`ctb_libaux`.`debito`) AS `debito` 
                            , SUM(`ctb_libaux`.`credito`) AS `credito`
                            , `ctb_doc`.`fecha`
                        FROM
                            `ctb_libaux`
                            INNER JOIN `ctb_doc` 
                                ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                            LEFT JOIN `tes_conciliacion_detalle`
                                ON (`ctb_libaux`.`id_ctb_libaux` = `tes_conciliacion_detalle`.`id_ctb_libaux`)
                            LEFT JOIN `tes_conciliacion`
                                ON (`tes_conciliacion`.`id_conciliacion` = `tes_conciliacion_detalle`.`id_concilia`)
                        WHERE (`ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` <= '$fin_mes' 
                                AND (`tes_conciliacion`.`mes` = '$mes' OR `tes_conciliacion`.`mes` IS NULL)
                                AND (`tes_conciliacion`.`vigencia` = '$vigencia' OR `tes_conciliacion`.`vigencia` IS NULL))
                        GROUP BY `ctb_libaux`.`id_cuenta`)AS `t1`  
                        ON (`t1`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                WHERE `tes_cuentas`.`id_tes_cuenta` = $id";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$conciliar = ($saldo + $tot_deb - $tot_cre) - ($detalles['debito'] - $detalles['credito']);
$ver = 'readonly';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php'; ?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ?  'sb-sidenav-toggled' : '' ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    DETALLES CONCILIACIÓN BANCARIA
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <input type="hidden" id="tot_deb" value="<?= $tot_deb; ?>">
                            <input type="hidden" id="tot_cre" value="<?= $tot_cre; ?>">
                            <form id="formAddDetallePag">
                                <input type="hidden" id="id_cuenta" value="<?php echo $id; ?>">
                                <input type="hidden" id="cod_mes" value="<?php echo $mes; ?>">
                                <input type="hidden" id="id_conciliacion" value="<?php echo $id_conciliacion; ?>">
                                <div class="right-block">
                                    <div class="row mb-1">
                                        <div class="col-md-2">
                                            <span class="small">CUENTA </span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-control form-control-sm" readonly><?php echo $detalles['cta_contable'] ?></div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="small">SALDO LIBROS </span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-control form-control-sm text-right" readonly><?php echo pesos($detalles['debito'] - $detalles['credito']) ?></div>
                                            <input type="hidden" id="salLib" value="<?php echo $detalles['debito'] - $detalles['credito'] ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-2">
                                            <span class="small">NOMBRE </span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-control form-control-sm" readonly><?php echo $detalles['descripcion'] ?></div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="small">SALDO EXTRACTO:</span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="saldoExtracto" id="saldoExtracto" class="form-control text-right" value="<?php echo $saldo ?>">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-primary" type="button" onclick="GuardaSaldoExtracto()" title="Guardar Saldo"><i class="far fa-save fa-lg"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-2">
                                            <span class="small">MES </span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-control form-control-sm" readonly><?php echo $dia['nom_mes'] ?></div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="small">SALDO A CONCILIAR:</span>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="saldoConcilia" id="saldoConcilia" class="form-control form-control-sm" style="text-align: right;" readonly value="<?php echo pesos($conciliar) ?>">
                                        </div>
                                    </div>
                                </div>
                                <table id="tableDetConciliacion" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Fecha</th>
                                            <th>Comprobante</th>
                                            <th>Tercero</th>
                                            <th>Documento</th>
                                            <th>Débito</th>
                                            <th>Crédito</th>
                                            <th>Estado</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificaDetConciliacion">
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>

                    </table>
                    <div class="text-center pt-4">
                        <button type="button" class="btn btn-primary btn-sm" onclick="ImpConcBanc(<?= $id; ?>);" style="width: 5rem;"> <span class="fas fa-print "></span></button>
                        <a class="btn btn-danger btn-sm" style="width: 7rem;" href="conciliacion_bancaria.php"> Terminar</a>
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