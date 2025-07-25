<?php
session_start();
set_time_limit(5600);

if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

$fecha_inicial = $_POST['f_ini'];
$fecha_corte = $_POST['f_fin'];
$cuenta = $_POST['cuenta'];
$tipo = $_POST['tipo'];
$saldo = $_POST['saldo'];
$nit = $_POST['nit'];
$condicion = $tipo == 'M' ? "LIKE '$cuenta%'" : "= '$cuenta'";
$where = $_POST['xTercero'] == 1 ? " AND `tb_terceros`.`nit_tercero` = '$nit'" : '';


function pesos($valor)
{
    return '$' . number_format($valor, 2);
}

include '../../conexion.php';
include '../../terceros.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

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
                ON `ctb_libaux`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`
            WHERE `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte' AND `ctb_doc`.`estado` = 2 AND `ctb_pgcp`.`cuenta` $condicion $where
            ORDER BY `ctb_pgcp`.`fecha`, `ctb_pgcp`.`cuenta` ASC";
    $res = $cmd->query($sql);
    $cuentas = $res->fetchAll();
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}

try {
    $sql = "SELECT `cuenta`, `nombre` FROM `ctb_pgcp` WHERE `cuenta` = '$cuenta'";
    $res = $cmd->query($sql);
    $cta = $res->fetch();
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}

$id_t = [];
foreach ($cuentas as $ter) {
    if ($ter['id_tercero_api'] != '') {
        $id_t[] = $ter['id_tercero_api'];
    }
}

$terceros = [];
if (count($id_t) > 0) {
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);
}

$nom_informe = "LIBRO AUXILIAR DETALLADO DE LA CUENTA $cuenta";
include_once '../../financiero/encabezado_empresa.php';

$saldo = 0;
$total_deb = 0;
$total_cre = 0;
$primer_caracter = substr($cta['cuenta'], 0, 1);
$bandera = in_array($primer_caracter, [1, 5, 6, 7]);
?>
<label class="text-right"> <b><?php echo $cta['cuenta'] . ' - ' . $cta['nombre']; ?></b></label>
<table class="table-bordered bg-light" style="width:100% !important;" border="1">
    <tr>
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
        if ($bandera) {
            $saldo = $saldo + $tp['debito'] - $tp['credito'];
        } else {
            $saldo = $saldo + $tp['credito'] - $tp['debito'];
        }
        $key = array_search($tp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
        $nom_ter = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
        $cc_nit = $key !== false ? $terceros[$key]['nit_tercero'] : '---';
        $fecha = date('Y-m-d', strtotime($tp['fecha']));
        echo "<tr>
                <td class='text-right'>" . $fecha . "</td>
                <td class='text-right'>" . $tp['cod_tipo_doc'] . "</td>
                <td class='text-right'>" . $tp['id_manu'] . "</td>
                <td class='text-right'>" . $tp['forma_pago'] . "</td>
                <td class='text'>" . $nom_ter . "</td>
                <td class='text'>" . $cc_nit . "</td>
                <td class='text-right'>" . $tp['detalle'] . "</td>
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