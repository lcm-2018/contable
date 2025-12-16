<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../terceros.php';
$id_vigencia = $_SESSION['id_vigencia'];
unset($_SESSION['id_doc']);
// Consulta tipo de presupuesto
function pesos($valor)
{
    return number_format($valor, 2, ',', '.');
}
$id_r = $_POST['dato'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto` FROM `pto_presupuestos` WHERE (`id_tipo` = 1 AND `id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `pto_rad`.`id_pto_rad`
                , `pto_rad`.`id_manu`
                , `pto_rad`.`id_tercero_api`
                , `pto_rad`.`fecha`
                , `pto_rad`.`objeto`
                , `pto_rad`.`num_factura`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                `pto_rad`
                LEFT JOIN `tb_terceros` 
                    ON (`pto_rad`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE (`pto_rad`.`estado` = 2  AND `id_pto` = {$listappto['id_pto']} AND `pto_rad`.`tipo_movimiento` IS NULL)";
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `pto_rad`.`id_pto_rad`
                , SUM(IFNULL(`pto_rad_detalle`.`valor`,0) - IFNULL(`pto_rad_detalle`.`valor_liberado`,0)) AS `valor`
            FROM
                `pto_rad_detalle`
                INNER JOIN `pto_rad` 
                    ON (`pto_rad_detalle`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
            WHERE (`pto_rad`.`estado` = 2 AND `pto_rad`.`id_pto` = {$listappto['id_pto']})
            GROUP BY `pto_rad`.`id_pto_rad`";
    $rs = $cmd->query($sql);
    $radicados = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultas totales obligados
try {
    $sql = "SELECT
                `ctb_doc`.`id_rad`
                , SUM(`ctb_libaux`.`debito`) AS `valor`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (`ctb_doc`.`estado` > 0 AND `ctb_doc`.`id_vigencia` = $id_vigencia AND  `ctb_doc`.`id_rad` IS NOT NULL )
            GROUP BY `ctb_doc`.`id_rad`";
    $rs = $cmd->query($sql);
    $causados = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$fecha = date('Y-m-d', strtotime($listado[0]['fecha']));

?>
<script>
    $('#tableContrtacionRp').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ],
        columnDefs: [{
            class: 'text-wrap',
            targets: [4]
        }],
    });
    $('#tableContrtacionCdp').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE RECONOCIMIENTOS PRESUPUESTALES</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableContrtacionRp" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Num</th>
                        <?= $_SESSION['pto'] == '1' ? '<th>RAD</th><th>Contrato</th>' : ''; ?>
                        <th>Fecha</th>
                        <th>Terceros</th>
                        <?= $_SESSION['pto'] == '1' ? '<th>Valor</th>' : ''; ?>
                        <th>Acciones</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    $acciones = null;
                    if (true) {
                        $id_t = [];
                        foreach ($listado as $ce) {
                            $detalles = $editar = null;
                            $id_ter = $ce['id_tercero_api'];
                            $id_rad = $ce['id_pto_rad'];
                            $key = array_search($id_rad, array_column($radicados, 'id_pto_rad'));
                            $key2 = array_search($id_rad, array_column($causados, 'id_rad'));
                            $sum_rad = $key !== false ? $radicados[$key]['valor'] : 0;
                            $sum_cau = $key2 !== false ? $causados[$key2]['valor'] : 0;
                            $saldoo = $sum_rad - $sum_cau;
                            $saldo_rad = pesos($saldoo);

                            $numeroc = $ce['num_factura'];
                            if (PermisosUsuario($permisos, 5501, 3)  || $id_rol == 1) {
                                $editar = '<a value="' . $id_rad . '" onclick="cargarListaDetalleCtbInvoice(' . $id_rad . ', 0)" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></a>';
                            }
                            $fecha = date('Y-m-d', strtotime($ce['fecha']));
                            if ($saldoo > 0) {
                    ?>
                                <tr>
                                    <td class="text-center"><input type="checkbox" value="" id="defaultCheck1"></td>
                                    <td class="text-left"><?php echo $ce['id_manu']; ?></td>
                                    <td class="text-left"><?php echo $numeroc  ?></td>
                                    <td class="text-left"><?php echo $fecha; ?></td>
                                    <td class="text-left"><?php echo $ce['nom_tercero']; ?></td>
                                    <td class="text-right"> <?php echo  $saldo_rad; ?></td>
                                    <td class="text-center"> <?php echo $editar .  $acciones; ?></td>
                                </tr>
                    <?php
                            }
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center">No hay registros</td></tr>';
                    }

                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
    </div>
</div>
<?php
