<?php
session_start();
set_time_limit(5600);

if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

$fecha_inicial = $_POST['fecha_inicial'];
$fecha_corte = $_POST['fecha_final'];
$cta_inicial = $_POST['cta_inicial'];
$cta_final = $_POST['cta_final'];

function pesos($valor)
{
    return '$' . number_format($valor, 2);
}

include '../../conexion.php';
include '../../terceros.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT `id_pgcp`, `cuenta`, `nombre` FROM `ctb_pgcp` WHERE `id_pgcp` IN ('$cta_inicial', '$cta_final') ORDER BY `cuenta` ASC";
    $res = $cmd->query($sql);
    $cta = $res->fetchAll();
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}
$cta_inicial = $cta[0]['cuenta'];
$cta_final = $cta[0]['cuenta'];
$where = '';
if (isset($_POST['id_tercero']) && $_POST['id_tercero'] > 0) {
    $id_tercero = $_POST['id_tercero'];
    $where .= " AND `ctb_libaux`.`id_tercero_api` = $id_tercero";
}
if (isset($_POST['tp_doc']) && $_POST['tp_doc'] > 0) {
    $id_documento = $_POST['tp_doc'];
    $where .= " AND `ctb_doc`.`id_tipo_doc` = $id_documento";
}
try {

    // Consultar cuentas con movimiento
    $sql = "SELECT
                `ctb_doc`.`fecha`,
                `ctb_pgcp`.`cuenta`,
                `ctb_libaux`.`id_tercero_api`,
                `ctb_libaux`.`debito`,
                `ctb_libaux`.`credito`,
                `ctb_doc`.`id_tipo_doc`,
                `ctb_fuente`.`cod` AS `cod_tipo_doc`,
                `ctb_fuente`.`nombre` AS `nom_tipo_doc`,
                `ctb_doc`.`id_manu`,
                `ctb_doc`.`detalle`,
                `tes_forma_pago`.`forma_pago`,
                `tb_terceros`.`nom_tercero`,
                `tb_terceros`.`nit_tercero`
            FROM 
                `ctb_libaux`
            INNER JOIN `ctb_doc` 
                ON `ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
            INNER JOIN `ctb_pgcp` 
                ON `ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`
            INNER JOIN `ctb_fuente` 
                ON `ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`
            LEFT JOIN `tes_detalle_pago` 
                ON `tes_detalle_pago`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
            LEFT JOIN `tes_forma_pago` 
                ON `tes_detalle_pago`.`id_forma_pago` = `tes_forma_pago`.`id_forma_pago`
            LEFT JOIN `tb_terceros` 
                ON `tb_terceros`.`id_tercero_api` = `ctb_libaux`.`id_tercero_api`
            WHERE `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte' AND `ctb_doc`.`estado` = 2 
                AND (`ctb_pgcp`.`cuenta` LIKE '$cta_inicial%' OR `ctb_pgcp`.`cuenta` LIKE '$cta_final%')
                $where
            ORDER BY `ctb_pgcp`.`fecha`, `ctb_pgcp`.`cuenta` ASC";
    $res = $cmd->query($sql);
    $cuentas = $res->fetchAll();
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}

$nom_informe = "LIBRO AUXILIAR";
include_once '../../financiero/encabezado_empresa.php';

$saldo = 0;
$total_deb = 0;
$total_cre = 0;
?>
<label class="text-right"> <b><?php echo $cta[0]['cuenta'] . ' - ' . $cta[0]['nombre'] . ' => ' . $cta[1]['cuenta'] . ' - ' . $cta[1]['nombre']; ?></b></label>
<table class="table-bordered bg-light" style="width:100% !important;" border="1">
    <tr style="text-align: center;">
        <td>Fecha</td>
        <td>Tipo documento</td>
        <td>Documento</td>
        <td>Referencia</td>
        <td>Tercero</td>
        <td>CC/nit</td>
        <td>Detalle</td>
        <td>Debito</td>
        <td>Credito</td>
        <td>Saldo</td>
    </tr>
    <?php
    echo "<tr>
             <td class='text-right' colspan='7'> Saldo inicial</td>
             <td class='text-right'></td>
             <td class='text-right'></td>
             <td class='text-right'>" . number_format($saldo, 2, ".", ",") . "</td>
          </tr>";
    $total_ret = 0;
    foreach ($cuentas as $tp) {
        $primer_caracter = substr($tp['cuenta'], 0, 1);
        $bandera = in_array($primer_caracter, [1, 5, 6, 7]);
        if ($bandera) {
            $saldo = $saldo + $tp['debito'] - $tp['credito'];
        } else {
            $saldo = $saldo + $tp['credito'] - $tp['debito'];
        }
        $nom_ter = $tp['nom_tercero'] != '' ? $tp['nom_tercero'] : '---';
        $cc_nit = $tp['nit_tercero'] != '' ? $tp['nit_tercero'] : '---';
        $fecha = date('Y-m-d', strtotime($tp['fecha']));
        echo "<tr>
                <td class='text-left' style='white-space: nowrap;'>" . $fecha . "</td>
                <td class='text-left'>" . $tp['cod_tipo_doc'] . "</td>
                <td class='text-left'>" . $tp['id_manu'] . "</td>
                <td class='text-left'>" . $tp['forma_pago'] . "</td>
                <td class='text-left'>" . $nom_ter . "</td>
                <td class='text-right'>" . $cc_nit . "</td>
                <td class='text-left'>" . $tp['detalle'] . "</td>
                <td class='text-right'>" . number_format($tp['debito'], 2, ".", ",") . "</td>
                <td class='text-right'>" . number_format($tp['credito'], 2, ".", ",") . "</td>
                <td class='text-right'>" . number_format($saldo, 2, ".", ",") . "</td>
              </tr>";
        $total_deb += $tp['debito'];
        $total_cre += $tp['credito'];
    }
    echo "<tr>
            <td class='text-right' colspan='7'> Total</td>
            <td class='text-right'>" . number_format($total_deb, 2, ".", ",") . "</td>
            <td class='text-right'>" . number_format($total_cre, 2, ".", ",") . "</td>
            <td class='text-right'>" . number_format($saldo, 2, ".", ",") . "</td>
          </tr>";
    ?>
</table>