<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>CONTAFACIL</title>
    <style>
        .text {
            mso-number-format: "\@"
        }
    </style>

    <?php
    header("Content-type: application/vnd.ms-excel charset=utf-8");
    header("Content-Disposition: attachment; filename=Libro_Auxiliar.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
// estraigo las variables que llegan por post en json
$fecha_inicial = $_POST['fec_inicial'];
$fecha_corte = $_POST['fec_final'];
$cuenta_ini = $_POST['cta_inicial'];
$cuenta_fin = $_POST['cta_final'];
$id_des = $_POST['mpio'];
// contar los caracteres de $cuenta_ini
$long_ini = strlen($cuenta_ini);
$long_fin = strlen($cuenta_fin);

function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Consulto las cuentas que han tenido movimeiento en el libro auxiliar
try {
    $sql = "SELECT DISTINCT
                `ctb_libaux`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , SUBSTRING(`ctb_libaux`.`cuenta`,1,$long_ini) AS cta
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`cuenta` = `ctb_pgcp`.`cuenta`)
            WHERE (SUBSTRING(`ctb_libaux`.`cuenta`,1,$long_ini) BETWEEN $cuenta_ini AND $cuenta_fin);";
    $res = $cmd->query($sql);
    $cuentas = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT
    `nombre`
    , `nit`
    , `dig_ver`
FROM
    `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los id de terceros creado en la tabla ctb_doc
try {
    $sql = "SELECT DISTINCT
                `id_tercero_api`
            FROM
                `seg_terceros`;";
    $res = $cmd->query($sql);
    $id_terceros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
foreach ($id_terceros as $ter) {
    $id_t[] = $ter['id_tercero_api'];
}
$payload = json_encode($id_t);
// Consulta terceros en la api ********************************************* API
$url = $api . 'terceros/datos/res/lista/terceros';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
?>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">
        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="10" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="10" style="text-align:center"><?php echo '<h3>' . $empresa['nombre'] . '</h3>'; ?></td>
            </tr>
            <tr>
                <td colspan="10" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="10" style="text-align:center"><?php echo 'LIBRO AUXILIAR '; ?></td>
            </tr>
            <tr>
                <td colspan="10" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>
        </br>
        </br>

        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td>FECHA INICIO</td>
                <td style='text-align: left;'><?php echo $fecha_inicial; ?></td>
            </tr>
            <tr>
                <td>FECHA FIN</td>
                <td style='text-align: left;'><?php echo $fecha_corte; ?></td>
            </tr>
            <tr>
                <td></td>
                <td style='text-align: left;'></td>
            </tr>
        </table>
        <?php
        foreach ($cuentas as $cta) {
            try {
                $sql = "SELECT
                            `ctb_doc`.`fecha`
                            , `ctb_libaux`.`cuenta`
                            , `ctb_libaux`.`debito`
                            , `ctb_libaux`.`credito`
                            , `ctb_doc`.`tipo_doc`
                            , `ctb_doc`.`id_manu`
                            , IF(LENGTH(`ctb_libaux`.`id_tercero`) >=1,`ctb_libaux`.`id_tercero`,`ctb_doc`.`id_tercero`) AS id_terceroapi
                            , `ctb_doc`.`detalle`
                            , `ctb_doc`.`id_ctb_doc`
                        FROM
                            `ctb_libaux`
                            INNER JOIN `ctb_doc` 
                                ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        WHERE (`ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte'
                            AND `ctb_libaux`.`cuenta` ='{$cta['cuenta']}')
                            ORDER BY  `ctb_doc`.`fecha` ASC;";
                $res = $cmd->query($sql);
                $movimientos = $res->fetchAll();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }
            // Consulta de saldo inicial antes de la fecha de corte
            try {
                $sql = "SELECT
                            `ctb_libaux`.`cuenta`
                            , SUM(`ctb_libaux`.`debito`) as debito
                            , SUM(`ctb_libaux`.`credito`) as credito
                        FROM
                            `ctb_libaux`
                            INNER JOIN `ctb_doc` 
                                ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        WHERE (`ctb_doc`.`fecha` < '$fecha_inicial' AND  `ctb_libaux`.`cuenta`='{$cta['cuenta']}')
                        ;";
                $res = $cmd->query($sql);
                $saldo_ini = $res->fetch();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }

            $saldo = 0;
            $total_deb = 0;
            $total_cre = 0;
            // Comportamiento del saldo de acuerdo a la naturaleza de la cuenta
            $primer_caracter = substr($cta['cuenta'], 0, 1);
        ?>
            <label class="text-right"> <b><?php echo $cta['cuenta'] . ' - ' . $cta['nombre']; ?></b></label>
            <table class="table-bordered bg-light" style="width:100% !important;" border=1>
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
                if ($primer_caracter == 1 || $primer_caracter == 5 || $primer_caracter == 6 || $primer_caracter == 7) {
                    $saldo = $saldo_ini['debito'] - $saldo_ini['credito'];
                } else {
                    $saldo = $saldo_ini['credito'] - $saldo_ini['debito'];
                }
                echo "<tr>
                 <td class='text-right' colspan='7'> Saldo inicial</td>
                 <td class='text-right'></td>
                 <td class='text-right'></td>
                 <td class='text-right'>" . number_format($saldo, 2, ".", ",")  . "</td>
                 </tr>
                 ";
                $total_ret = 0;
                foreach ($movimientos as $tp) {
                    // Consulto la referencia cuando se realizan movimientos de tesoreria
                    try {
                        $sql = "SELECT
                            `seg_tes_detalle_pago`.`id_ctb_doc`
                            , `seg_tes_cuentas`.`cta_contable`
                            , CONCAT(`seg_tes_forma_pago`.`forma_pago` , ' ', `seg_tes_detalle_pago`.`documento`) AS formapago
                        FROM
                            `seg_tes_detalle_pago`
                            INNER JOIN `seg_tes_forma_pago` 
                                ON (`seg_tes_detalle_pago`.`id_forma_pago` = `seg_tes_forma_pago`.`id_forma_pago`)
                            INNER JOIN `seg_tes_cuentas` 
                                ON (`seg_tes_detalle_pago`.`id_tes_cuenta` = `seg_tes_cuentas`.`id_tes_cuenta`)
                        WHERE (`seg_tes_detalle_pago`.`id_ctb_doc` ={$tp['id_ctb_doc']}
                            AND `seg_tes_cuentas`.`cta_contable`= '{$cta['cuenta']}');";
                        $res = $cmd->query($sql);
                        $forma = $res->fetch();
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                    }
                    if ($primer_caracter == 1 || $primer_caracter == 5 || $primer_caracter == 6 || $primer_caracter == 7) {
                        $saldo = $saldo + $tp['debito'] - $tp['credito'];
                    } else {
                        $saldo = $saldo + $tp['credito'] - $tp['debito'];
                    }
                    $key = array_search($tp['id_terceroapi'], array_column($terceros, 'id_tercero'));
                    $nom_ter =  $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['razon_social'];
                    $fecha = date('Y-m-d', strtotime($tp['fecha']));
                    echo "<tr>
                    <td class='text-right'>" .  $fecha . "</td>
                    <td class='text-right'>" . $tp['tipo_doc'] . "</td>
                    <td class='text-right'>" . $tp['id_manu'] . "</td>
                    <td class='text-right'>" . $forma['formapago'] . "</td>
                    <td class='text'>" . $nom_ter . $tp['id_terceroapi'] . "</td>
                    <td class='text'>" . $terceros[$key]['cc_nit'] . "</td>
                    <td class='text-right'>" . $tp['detalle'] . "</td>
                    <td class='text-right'>" . number_format($tp['debito'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($tp['credito'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($saldo, 2, ".", ",")  . "</td>
                    </tr>";
                    $total_deb = $total_deb + $tp['debito'];
                    $total_cre = $total_cre + $tp['credito'];
                }
                echo "<tr>
                        <td class='text-right' colspan='7'> Total</td>
                        <td class='text-right'>" . number_format($total_deb, 2, ".", ",")  . "</td>
                        <td class='text-right'>" . number_format($total_cre, 2, ".", ",")  . "</td>
                        <td class='text-right'>" . number_format($saldo, 2, ".", ",")  . "</td>
                        </tr>
                        ";
                ?>
            </table>
            <table class="table-bordered bg-light" style="width:100% !important;">
                <tr>
                    <td></td>
                    <td style='text-align: left;'></td>
                </tr>
            </table>
        <?php
        }
        ?>
    </div>
</div>

</html>