<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
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
    $fin_mes = !(empty($dia)) ? $vigencia . '-' . $mes . '-' . $dia['fin_mes'] : 0;
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
                        WHERE (`ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` <= '$fin_mes')
                        GROUP BY `ctb_libaux`.`id_cuenta`)AS `t1`  
                        ON (`t1`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                WHERE `tes_cuentas`.`id_tes_cuenta` = $id";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

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
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    DETALLES CONCILIACIÓN BANCARIA
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formAddDetallePag">
                                <input type="hidden" id="id_cuenta" value="<?php echo $id; ?>">
                                <div class="right-block">
                                    <div class="row mb-1">
                                        <div class="col-2">
                                            <span class="small">CUENTA </span>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-control form-control-sm" readonly><?php echo $detalles['cta_contable'] ?></div>
                                        </div>
                                        <div class="col-2">
                                            <span class="small">SALDO LIBROS </span>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-control form-control-sm" readonly><?php echo pesos($detalles['debito'] - $detalles['credito']) ?></div>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-2">
                                            <span class="small">NOMBRE </span>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-control form-control-sm" readonly><?php echo $detalles['descripcion'] ?></div>
                                        </div>
                                        <div class="col-2">
                                            <span class="small">SALDO EXTRACTO:</span>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="saldoExtracto" id="saldoExtracto" class="form-control form-control-sm" style="text-align: right;" required readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-2">
                                            <span class="small">MES </span>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-control form-control-sm" readonly><?php echo $dia['nom_mes'] ?></div>
                                        </div>
                                        <div class="col-2">
                                            <span class="small">SALDO A CONCILIAR:</span>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="saldoConcilia" id="saldoConcilia" class="form-control form-control-sm" style="text-align: right;" required readonly>
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
                        <a type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoCons();" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                        <a onclick="terminarDetalleCons()" class="btn btn-danger btn-sm" style="width: 7rem;" href="#"> Terminar</a>
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