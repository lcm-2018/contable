<?php
session_start();
set_time_limit(5600);
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
    header("Content-Disposition: attachment; filename=Balance de prueba.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
</head>
<?php
include '../../conexion.php';
// Consexion a cronhis asistencial
$vigencia = $_SESSION['vigencia'];
// estraigo las variables que llegan por post en json
$fecha_inicial = $_POST['fec_inicial'];
$fecha_corte = $_POST['fec_final'];
// contar los caracteres de $cuenta_ini
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$_post = json_decode(file_get_contents('php://input'), true);
$tercero = $_post['tercero'];
$fecha = $_post['fecha'];
// Sumo valores de copagos de facturas activas sin anulación
try {
    $sql = "SELECT cuenta,SUM(debitoi) AS debitoi, SUM(creditoi) AS creditoi,SUM(debito) AS debito,SUM(credito) AS credito FROM (
        SELECT
             ctb_libaux.cuenta	 
            , ctb_libaux.debito AS debitoi
            , ctb_libaux.credito AS creditoi
            , 0 AS debito
            , 0 AS credito
        FROM ctb_libaux
            INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
        WHERE ctb_doc.fecha <= '$fecha_inicial' AND ctb_doc.estado=1
        UNION ALL
        SELECT
             ctb_libaux.cuenta
            , 0 AS debitoi
            , 0 AS creditoi 
            , ctb_libaux.debito AS debito
            , ctb_libaux.credito AS credito
        FROM ctb_libaux
            INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
        WHERE ctb_doc.fecha BETWEEN '$fecha_inicial' AND '$fecha_corte' AND ctb_doc.estado=1
        UNION ALL
        SELECT vista_ctb_libaux.cuenta
            , vista_ctb_libaux.valordeb AS debitoi 
            , vista_ctb_libaux.valorcred AS creditoi
            , 0 AS debito
            , 0 AS credito              
        FROM vista_ctb_libaux
        WHERE vista_ctb_libaux.tipo NOT IN ('REC','RAD') AND vista_ctb_libaux.fecha <'$fecha_inicial'
        UNION ALL
        SELECT vista_ctb_libaux.cuenta
            , 0 AS debitoi
            , 0 AS creditoi 
            , vista_ctb_libaux.valordeb AS debito 
            , vista_ctb_libaux.valorcred AS credito 
        FROM vista_ctb_libaux    
        WHERE vista_ctb_libaux.tipo NOT IN ('REC','RAD') AND vista_ctb_libaux.fecha  BETWEEN '$fecha_inicial' AND '$fecha_corte'
        GROUP BY vista_ctb_libaux.cuenta) AS valance
        GROUP BY cuenta
        ORDER BY cuenta";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll();
} catch (Exception $e) {
    echo $e->getMessage();
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
                <td colspan="10" style="text-align:center"><?php echo 'LIBRO MAYOR Y BALANCE'; ?></td>
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
        <label class="text-right"> <b></b></label>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Cuenta</td>
                <td>Inicial</td>
                <td>Debito</td>
                <td>Credito</td>
                <td>Saldo Final</td>
            </tr>
            <?php
            foreach ($datos as $tp) {
                $nat1 = substr($tp['cuenta'], 0, 1);
                $nat2 = substr($tp['cuenta'], 0, 2);
                if ($nat1 == '1' or $nat1 == '5' or $nat1 == '6' or $nat1 == '7' or $nat2 == '81' or $nat2 == '83' or $nat2 == '99') {
                    $naturaleza = "D";
                }
                if ($nat1 == '2' or $nat1 == '3' or $nat1 == '4' or $nat2 == '91' or $nat2 == '92'  or $nat2 == '93' or $nat2 == '89') {
                    $naturaleza = "C";
                }
                if ($naturaleza == "D") {
                    $saldo_ini = $tp['debitoi'] - $tp['creditoi'];
                    $saldo = $saldo_ini + $tp['debito'] - $tp['credito'];
                } else {
                    $saldo = $tp['creditoi'] - $tp['debitoi'];
                    $saldo = $saldo_ini + $tp['credito'] - $tp['debito'];
                }

                echo "<tr>
                    <td class='text'>" . $tp['cuenta'] . "</td>
                    <td class='text-right'>" . $saldo_ini . "</td>
                    <td class='text-right'>" . $tp['debito'] . "</td>
                    <td class='text-right'>" . $tp['credito'] . "</td>
                    <td class='text-right'>" . $saldo . "</td>
                    </tr>";
                $saldo_ini = 0;
                $saldo = 0;
            }
            ?>
        </table>
    </div>
</div>