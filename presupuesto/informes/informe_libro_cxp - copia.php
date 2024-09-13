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
                `pto_documento`.`fecha`
                , `pto_documento`.`id_tercero`
                , `pto_documento_detalles`.`id_tercero_api`
                , `pto_documento`.`id_manu`
                , `pto_documento`.`objeto`
                , `pto_documento_detalles`.`rubro`
                , SUM(`pto_documento_detalles`.`valor`) as valor
                , `pto_documento_detalles`.`tipo_mov`
                , `pto_documento_detalles`.`id_documento`
            FROM
                `pto_documento_detalles`
                INNER JOIN `pto_documento` 
                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
            WHERE (`pto_documento`.`fecha` <='$fecha_corte'
                AND `pto_documento_detalles`.`tipo_mov` ='CDP')
                GROUP BY `pto_documento_detalles`.`id_documento`,`pto_documento_detalles`.`rubro`;
";
    $res = $cmd->query($sql);
    $cdp = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los valores unicos id_tercero de la tabla pto_documento
try {
    $sql = "SELECT DISTINCT `id_tercero` FROM `pto_documento` WHERE `id_tercero` IS NOT NULL;";
    $res = $cmd->query($sql);
    $id_terceros = $res->fetchAll();
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
                <td colspan="13" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="13" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo 'ESTADO DE CUENTAS POR PAGAR'; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Fecha</td>
                <td>No CDP</td>
                <td>No CRP</td>
                <td>Tercero</td>
                <td>cc/nit</td>
                <td>detalle</td>
                <td>Rubro</td>
                <td>Valor Disponibilidad</td>
                <td>Valor registrado</td>
                <td>Valor causado</td>
                <td>Valor Pagado</td>
                <td>Compromisos por pagar</td>
                <td>Cuentas por pagar</td>
            </tr>
            <?php

            $id_t = [];
            foreach ($id_terceros as $ca) {
                if ($ca['id_tercero'] !== null) {
                    $id_t[] = $ca['id_tercero'];
                }
            }
            $ids = implode(',', $id_t);
            $terceros = getTerceros($ids, $cmd);
            foreach ($cdp as $rp) {
                $fecha = date('Y-m-d', strtotime($rp['fecha']));

                // Consultar el valor registrado por rubro y cdp 

                $sql = "SELECT
                            `pto_documento_detalles`.`tipo_mov`
                            , `pto_documento_detalles`.`rubro`
                            , SUM(`pto_documento_detalles`.`valor`) as valor
                            , `pto_documento`.`id_tercero`
                            , `pto_documento`.`id_manu`
                        FROM
                            `pto_documento_detalles`
                            INNER JOIN `pto_documento` 
                                ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
                        WHERE (`pto_documento_detalles`.`tipo_mov` ='CRP'
                            AND `pto_documento_detalles`.`rubro` ='{$rp['rubro']}'
                            AND `pto_documento`.`fecha` <='$fecha_corte'
                            AND `pto_documento_detalles`.`id_auto_dep` =$rp[id_pto_doc]);";
                $res = $cmd->query($sql);
                $crp = $res->fetch();
                $key = array_search($crp['id_tercero'], array_column($terceros, 'id_tercero_api'));
                $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
                $cc_nit = $key !== false ? number_format($terceros[$key]['nit_tercero'], 0, "", ".") : '---';

                // Consulto el valor causado
                $sql = "SELECT
                            `tipo_mov`
                            , `rubro`
                            ,  SUM(`valor`) as valor
                            , `id_auto_dep`
                        FROM
                            `pto_documento_detalles`
                        WHERE (`tipo_mov` ='COP'
                            AND `rubro` ='{$rp['rubro']}'
                            AND `id_auto_dep` =$rp[id_pto_doc]);";
                $res = $cmd->query($sql);
                $cop = $res->fetch();
                // Consulto el valor pagado
                $sql = "SELECT
                            `tipo_mov`
                            , `rubro`
                            ,  SUM(`valor`) as valor
                            , `id_auto_dep`
                        FROM
                            `pto_documento_detalles`
                        WHERE (`tipo_mov` ='PAG'
                            AND `rubro` ='{$rp['rubro']}'
                            AND `id_auto_dep` =$rp[id_pto_doc]);";
                $res = $cmd->query($sql);
                $pag = $res->fetch();
                echo "<tr>
                <td class='text'>" . $fecha .  "</td>
                <td class='text-left'>" . $rp['id_manu'] . "</td>
                <td class='text-left'>" . $crp['id_manu'] . "</td>
                <td class='text-right'>" .     $tercero  . "</td>
                <td class='text-right'>" .   $cc_nit  . "</td>
                <td class='text-right'>" .  $rp['objeto'] . "</td>
                <td class='text-right'>" . $rp['rubro']   . "</td>
                <td class='text-right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($crp['valor'], 2, ".", ",")   . "</td>
                <td class='text-right'>" .  number_format($cop['valor'], 2, ".", ",")  . "</td>
                <td class='text-right'>" .  number_format($pag['valor'], 2, ".", ",")  . "</td>
                <td class='text-right'>" .  number_format(($crp['valor'] - $cop['valor']), 2, ".", ",")  . "</td>
                <td class='text-right'>" .  number_format(($cop['valor'] - $pag['valor']), 2, ".", ",")  . "</td>
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