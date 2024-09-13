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
?>
<table style="width:100% !important; border-collapse: collapse;">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
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
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <th>Fecha</th>
            <th>No CDP</th>
            <th>No CRP</th>
            <th>Fecha causacion</th>
            <th>Tercero</th>
            <th>cc/nit</th>
            <th>detalle</th>
            <th>Rubro</th>
            <th>Valor Disponibilidad</th>
            <th>Valor registrado</th>
            <th>Valor causado</th>
            <th>Valor Pagado</th>
            <th>Compromisos por pagar</th>
            <th>Cuentas por pagar</th>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
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
            if ($crp['id_tercero'] == '') {
                $tercero = '';
                $cc_nit = '';
            } else {
                $key = array_search($crp['id_tercero'], array_column($terceros, 'id_tercero_api'));
                $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
                $cc_nit = $key !== false ? number_format($terceros[$key]['nit_tercero'], 0, "", ".") : '---';
            }
            // Consulto el valor causado
            /*
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
            */
            $sql = "SELECT
                        SUM(`pto_documento_detalles`.`valor`) as valor
                        , `ctb_doc`.`fecha`
                    FROM
                        `pto_documento_detalles`
                        INNER JOIN `ctb_doc` 
                            ON (`pto_documento_detalles`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`pto_documento_detalles`.`tipo_mov` ='COP'
                        AND `pto_documento_detalles`.`rubro` ={$rp['rubro']}
                        AND `pto_documento_detalles`.`id_auto_dep` =$rp[id_pto_doc]
                        AND `ctb_doc`.`fecha` <='$fecha_corte');";
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
                        INNER JOIN `ctb_doc` 
                            ON (`pto_documento_detalles`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`pto_documento_detalles`.`tipo_mov` ='PAG'
                        AND `pto_documento_detalles`.`rubro` ={$rp['rubro']}
                        AND `pto_documento_detalles`.`id_auto_dep` =$rp[id_pto_doc]
                        AND `ctb_doc`.`fecha` <='$fecha_corte');";
            $res = $cmd->query($sql);
            $pag = $res->fetch();
            if ($cop['fecha'] == null) {
                $fecha_causa = '';
            } else {
                $fecha_causa = date('Y-m-d', strtotime($cop['fecha']));
            }
            echo "<tr>
            <td style='text-aling:left'>" . $fecha .  "</td>
            <td style='text-aling:left'>" . $rp['id_manu'] . "</td>
            <td style='text-aling:left'>" . $crp['id_manu'] . "</td>
            <td style='text-aling:left'>" . $fecha_causa . "</td>
            <td style='text-aling:left'>" .     $tercero  . "</td>
            <td style='text-aling:left'>" .   $cc_nit  . "</td>
            <td style='text-aling:left'>" .  $rp['objeto'] . "</td>
            <td style='text-aling:left'>" . $rp['rubro']   . "</td>
            <td style='text-aling:right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
            <td style='text-aling:right'>" . number_format($crp['valor'], 2, ".", ",")   . "</td>
            <td style='text-aling:right'>" .  number_format($cop['valor'], 2, ".", ",")  . "</td>
            <td style='text-aling:right'>" .  number_format($pag['valor'], 2, ".", ",")  . "</td>
            <td style='text-aling:right'>" .  number_format(($crp['valor'] - $cop['valor']), 2, ".", ",")  . "</td>
            <td style='text-aling:right'>" .  number_format(($cop['valor'] - $pag['valor']), 2, ".", ",")  . "</td>
            </tr>";
            $tercero = '';
            $cc_nit = '';
        }
        ?>
    </tbody>
</table>