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
    header("Content-Disposition: attachment; filename=FORMATO_LIBRO_MODIFICACIONES_ANULADAS.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
$fecha_corte = $_POST['fecha'];
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT
    `pto_documento_detalles`.`tipo_mov`
    , `pto_documento`.`id_manu`
    , `pto_documento`.`fecha` as fecha
    , `pto_documento`.`objeto`
    , `pto_documento_detalles`.`rubro`
    , `pto_cargue`.`nom_rubro`
    , sum(`pto_documento_detalles`.`valor`) as valor
    , `pto_documento_detalles`.`id_documento`
    , pto_anula.fecha as fecha_anula
    ,pto_anula.concepto
    ,CONCAT(seg_usuarios_sistema.nombre1,' ', seg_usuarios_sistema.nombre2,' ',seg_usuarios_sistema.apellido1,' ',seg_usuarios_sistema.apellido2)as usuario
    FROM
        `pto_documento_detalles`
        LEFT JOIN `pto_cargue` ON (`pto_documento_detalles`.`rubro` = `pto_cargue`.`cod_pptal`)
        INNER JOIN `pto_documento` ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
        INNER JOIN pto_anula ON (pto_documento.id_pto_doc = pto_anula.id_pto_doc)
        INNER JOIN seg_usuarios_sistema ON (pto_anula.id_user_reg = seg_usuarios_sistema.id_usuario)
    WHERE ((`pto_documento_detalles`.`tipo_mov` ='ADI' OR `pto_documento_detalles`.`tipo_mov`='RED' OR `pto_documento_detalles`.`tipo_mov`='TRA' OR `pto_documento_detalles`.`tipo_mov`='APL') AND `pto_documento`.`fecha` <= '$fecha_corte' AND `pto_documento`.`estado`=5)
    GROUP BY `pto_documento_detalles`.`tipo_mov`,`pto_documento_detalles`.`rubro`, `pto_documento_detalles`.`id_documento`
    ORDER BY `pto_documento`.`fecha`, `pto_documento_detalles`.`tipo_mov` ASC;
";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
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
?> <div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="10" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="10" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="10" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="10" style="text-align:center"><?php echo 'RELACION DE DOCUMENTOS DE MODIFICACION PRESUPUESTAL ANULADOS'; ?></td>
            </tr>
            <tr>
                <td colspan="10" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="10" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Tipo</td>
                <td>No disponibilidad</td>
                <td>Fecha</td>
                <td>Objeto</td>
                <td>Rubro</td>
                <td>Nombre rubro</td>
                <td>Valor</td>
                <td>Fecha anulación</td>
                <td>Concepto anulación</td>
                <td>Usuario anulación</td>

            </tr>
            <?php

            foreach ($causaciones as $rp) {
                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                $fecha_anula = date('Y-m-d', strtotime($rp['fecha_anula']));
                echo "<tr>
                <td class='text'>" . $rp['tipo_mov'] .  "</td>
                <td class='text-left'>" . $rp['id_manu'] . "</td>
                <td class='text-right'>" .   $fecha   . "</td>
                <td class='text-right'>" . $rp['objeto'] . "</td>
                <td class='text'>" . $rp['rubro'] . "</td>
                <td class='text-right'>" .  $rp['nom_rubro'] . "</td>
                <td class='text-right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
                <td class='text-right'>" .   $fecha_anula . "</td>
                <td class='text-right'>" .  $rp['concepto'] . "</td>
                <td class='text-right'>" .  $rp['usuario'] . "</td>
                </tr>";
                $saldo = 0;
                $valor_cdp = 0;
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>

</html>