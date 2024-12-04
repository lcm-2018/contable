<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Consulto las cuentas que han tenido movimeiento en el libro auxiliar
try {
    $sql = "SELECT
            `ctb_libaux`.`cuenta`
            , `ctb_libaux`.`debito`
            , `ctb_libaux`.`credito`   
            FROM
            `ctb_libaux`
            INNER JOIN `ctb_doc` ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE `ctb_doc`.`fecha` <'2023-03-31' AND `ctb_doc`.`estado` =0
            UNION ALL
            SELECT cuenta,SUM(valordeb) AS debito,SUM(valorcred) AS credito
            FROM `vista_ctb_libaux`
            WHERE `vista_ctb_libaux`.`fecha` BETWEEN '2023-01-01' AND '2023-02-28' AND (`vista_ctb_libaux`.`fec_anulacion` > '2023-02-28' OR `vista_ctb_libaux`.`fec_anulacion` IS NULL)
            GROUP BY cuenta";
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
        <label class="text-right"> <b></b></label>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Cuenta</td>
                <td>Debito</td>
                <td>Credito</td>
            </tr>
            <?php
            foreach ($datos as $tp) {

                /*
                if ($primer_caracter == 1 || $primer_caracter == 5 || $primer_caracter == 6 || $primer_caracter == 7) {
                    $saldo = $saldo_ini['debito'] - $saldo_ini['credito'];
                } else {
                    $saldo = $saldo_ini['credito'] - $saldo_ini['debito'];
                }
                */
                echo "<tr>
                    <td class='text-right'>" . $tp['cuenta'] . "</td>
                    <td class='text-right'>" . $tp['debito'] . "</td>
                    <td class='text-right'>" . $tp['credito']  . "</td>
                    </tr>";
            }
            ?>
        </table>
    </div>
</div>

</html>