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
    header("Content-Disposition: attachment; filename=FORMATO_201101_F07_AGR.xls");
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
include '../../terceros.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT
    `pto_documento_detalles`.`tipo_mov`
    , `ctb_doc`.`id_manu`
    , `ctb_doc`.`fecha`
    , `pto_documento_detalles`.`id_tercero_api`
    , `ctb_doc`.`id_tercero`
    , `ctb_doc`.`detalle`
    , `ctb_doc`.`id_ctb_doc`
    , `pto_documento_detalles`.`rubro`
    , `pto_cargue`.`nom_rubro`
    , `pto_documento_detalles`.`valor`
FROM
    `pto_documento_detalles`
    INNER JOIN `ctb_doc` 
        ON (`pto_documento_detalles`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
    INNER JOIN `pto_cargue` 
        ON (`pto_documento_detalles`.`rubro` = `pto_cargue`.`cod_pptal`)
WHERE (`ctb_doc`.`fecha` <='$fecha_corte' AND `pto_documento_detalles`.`tipo_mov` = 'COP')
ORDER BY `ctb_doc`.`fecha` ASC;
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
                <td colspan="11" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="11" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo 'RELACION DE OBLIGACIONES PRESUPUESTALES'; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Tipo</td>
                <td>No causaci&oacute;n</td>
                <td>Fecha</td>
                <td>Tercero</td>
                <td>Cc/Nit</td>
                <td>Objeto</td>
                <td>Rubro</td>
                <td>Nombre rubro</td>
                <td>Valor</td>
                <td>Valor pagado</td>
            </tr>
            <?php
            $id_t = [];
            foreach ($causaciones as $ca) {
                if ($ca['id_tercero_api'] == false) {
                    $id_t[] = $ca['id_tercero'];
                } else {
                    $id_t[] = $ca['id_tercero_api'];
                }
            }
            $ids = implode(',', $id_t);
            $terceros = getTerceros($ids, $cmd);
            foreach ($causaciones as $rp) {

                $key = array_search($rp['id_tercero'], array_column($terceros, 'id_tercero_api'));
                $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
                $ccnit = $key !== false ? number_format($terceros[$key]['nit_tercero'], 0, "", ".") : '---';
                if ($tercero == null) {
                    $recero = 'NOMINA DE EMPLEADOS';
                }
                //busco los pagos que ha tenido la causacion
                try {
                    $sql = "SELECT
                                `pto_documento_detalles`.`rubro`
                                , SUM(`pto_documento_detalles`.`valor`) as pagado
                            FROM
                                `pto_documento_detalles`
                                INNER JOIN `pto_documento` 
                                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
                            WHERE (`pto_documento_detalles`.`id_ctb_cop` =$rp[id_ctb_cop]
                                AND `pto_documento`.`fecha` <'$fecha_corte'
                                AND `pto_documento_detalles`.`tipo_mov` ='PAG')
                            GROUP BY `pto_documento_detalles`.`rubro`;";
                    $res = $cmd->query($sql);
                    $pagos = $res->fetch();
                    $valor_pagado = $pagos['pagado'];
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }



                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                echo "<tr>
                    <td class='text'>" . $rp['tipo_mov'] .  "</td>
                    <td class='text-left'>" . $rp['id_manu'] . "</td>
                    <td class='text-right'>" .   $fecha   . "</td>
                    <td class='text-right'>" .   $tercero . "</td>
                    <td class='text-right'>" . $ccnit . "</td>
                    <td class='text-right'>" . $rp['detalle'] . "</td>
                    <td class='text'>" . $rp['rubro'] . "</td>
                    <td class='text-right'>" .  $rp['nom_rubro'] . "</td>
                    <td class='text-right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($valor_pagado, 2, ".", ",")  . "</td>
                    </tr>";
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>

</html>