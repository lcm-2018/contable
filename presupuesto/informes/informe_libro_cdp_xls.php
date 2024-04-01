<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$fecha_corte = file_get_contents("php://input");
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
    , `pto_documento`.`fecha`
    , `pto_documento`.`objeto`
    , `pto_documento_detalles`.`rubro`
    , `pto_cargue`.`nom_rubro`
    , sum(`pto_documento_detalles`.`valor`) as valor
    , `pto_documento_detalles`.`id_documento`
    FROM
        `pto_documento_detalles`
        LEFT JOIN `pto_cargue` 
            ON (`pto_documento_detalles`.`rubro` = `pto_cargue`.`cod_pptal`)
        INNER JOIN `pto_documento` 
            ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
    WHERE ((`pto_documento_detalles`.`tipo_mov` ='CDP' OR `pto_documento_detalles`.`tipo_mov`='LCD') AND `pto_documento`.`fecha` <= '$fecha_corte' AND `pto_documento`.`estado`=0)
    GROUP BY `pto_documento_detalles`.`tipo_mov`,`pto_documento_detalles`.`rubro`, `pto_documento_detalles`.`id_documento`
    ORDER BY `pto_documento`.`fecha` ASC;
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
?>
<style>
    .resaltar:nth-child(even) {
        background-color: #F8F9F9;
    }

    .resaltar:nth-child(odd) {
        background-color: #ffffff;
    }
</style>
<table style="width:100% !important; border-collapse: collapse;">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="7" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align:center"><?php echo 'RELACION DE CERTIFICADOS DE DISPONIBILIDAD PRESUPUESTAL'; ?></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <th>Tipo</th>
            <th>No disponibilidad</th>
            <th>Fecha</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Nombre rubro</th>
            <th>Valor</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        foreach ($causaciones as $rp) {
            // consulto el valor registrado de cada cdp y rubro
            $sql = "SELECT
                SUM(`pto_documento_detalles`.`valor`) AS `valor_rp`
            FROM
                `pto_documento_detalles`
                INNER JOIN `pto_documento` 
                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
            WHERE `pto_documento_detalles`.`rubro` ='{$rp['rubro']}'
                AND `pto_documento`.`fecha` <='$fecha_corte'
                AND `pto_documento_detalles`.`id_auto_dep` ={$rp['id_pto_doc']}
                AND `pto_documento_detalles`.`tipo_mov`='CRP' 
            GROUP BY `pto_documento_detalles`.`rubro`;";
            $res = $cmd->query($sql);
            $reg2 = $res->fetch();
            // consulto el valor registrado de cada cdp y rubro
            $sql = "SELECT
     SUM(`pto_documento_detalles`.`valor`) AS `valor_rp`
 FROM
     `pto_documento_detalles`
     INNER JOIN `pto_documento` 
         ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
 WHERE `pto_documento_detalles`.`rubro` ='{$rp['rubro']}'
     AND `pto_documento`.`fecha` <='$fecha_corte'
     AND `pto_documento_detalles`.`id_auto_dep` ={$rp['id_pto_doc']}
     AND `pto_documento_detalles`.`tipo_mov`='LRP' 
 GROUP BY `pto_documento_detalles`.`rubro`;";
            $res = $cmd->query($sql);
            $reg3 = $res->fetch();
            // consulto el valor anulado de cada cdp y rubro
            $sql = "SELECT
                SUM(`pto_documento_detalles`.`valor`) AS `valor_lcd`
            FROM
                `pto_documento_detalles`
                INNER JOIN `pto_documento` 
                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
            WHERE `pto_documento_detalles`.`rubro` ='{$rp['rubro']}'
                AND `pto_documento`.`fecha` <='$fecha_corte'
                AND `pto_documento_detalles`.`id_auto_dep` ={$rp['id_pto_doc']}
                AND `pto_documento_detalles`.`tipo_mov`='LCD' 
            GROUP BY `pto_documento_detalles`.`rubro`;";
            $sql2 = $sql;
            $res = $cmd->query($sql);
            $reg = $res->fetch();
            $valor_cdp = $rp['valor'] +  $reg['valor_lcd'];
            $saldo =  $valor_cdp - $reg2['valor_rp'] - $reg3['valor_rp'];
            $fecha = date('Y-m-d', strtotime($rp['fecha']));
            if ($valor_cdp >= 0) {
                echo "<tr>
                        <td style='text-aling:left'>" . $rp['tipo_mov'] .  "</td>
                        <td style='text-aling:left'>" . $rp['id_manu'] . "</td>
                        <td style='text-aling:left'>" .   $fecha   . "</td>
                        <td style='text-aling:left'>" . $rp['objeto'] . "</td>
                        <td style='text-aling:left'" . $rp['rubro'] . "</td>
                        <td style='text-aling:left'>" .  $rp['nom_rubro'] . "</td>
                        <td style='text-aling:right'>" . number_format($valor_cdp, 2, ".", ",")  . "</td>
                        <td style='text-aling:right'>" . number_format($saldo, 2, ".", ",") . "</td>
                    </tr>";
                $saldo = 0;
                $valor_cdp = 0;
            }
        }
        ?>
    </tbody>
</table>