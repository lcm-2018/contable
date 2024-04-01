<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../conexion.php';
include '../../permisos.php';
$vigencia = $_SESSION['vigencia'];
$id_nomina = $_POST['id_nomina'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT  `razon_social_ips` AS`nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver` FROM `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_nomina`, `descripcion`, `mes`, `vigencia`, `tipo`, `estado`, `id_user_reg`
            FROM
                `nom_nominas`
            WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $usereg = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios_sistema`
            WHERE (`id_usuario` = $usereg[id_user_reg])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `nom_empleado`.`no_documento`
                , CONCAT_WS(' ',`nom_empleado`.`nombre1`, `nom_empleado`.`nombre2`, `nom_empleado`.`apellido1`, `nom_empleado`.`apellido2`) AS `nombre`
                , `nom_embargos`.`id_juzgado`
                , `nom_juzgados`.`nom_juzgado`
                , `nom_liq_embargo`.`val_mes_embargo`
                , `nom_liq_embargo`.`id_nomina`
                , `nom_meses`.`nom_mes`
                , `nom_nominas`.`vigencia`
                , `nom_nominas`.`descripcion`
                , `nom_nominas`.`estado`
            FROM
                `nom_liq_embargo`
                INNER JOIN `nom_embargos` 
                    ON (`nom_liq_embargo`.`id_embargo` = `nom_embargos`.`id_embargo`)
                INNER JOIN `nom_empleado` 
                    ON (`nom_embargos`.`id_empleado` = `nom_empleado`.`id_empleado`)
                INNER JOIN `nom_nominas` 
                    ON (`nom_liq_embargo`.`id_nomina` = `nom_nominas`.`id_nomina`)
                INNER JOIN `nom_meses` 
                    ON (`nom_nominas`.`mes` = `nom_meses`.`codigo`)
                INNER JOIN `nom_juzgados` 
                    ON (`nom_embargos`.`id_juzgado` = `nom_juzgados`.`id_juzgado`)
            WHERE (`nom_liq_embargo`.`id_nomina` = $id_nomina)
            ORDER BY `nom_juzgados`.`nom_juzgado` ASC, `nombre` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (empty($datos)) {
    echo '
    <div class="text-right py-3">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
    </div>';
    echo '<div class="alert alert-danger text-center" role="alert">
            <strong>No hay datos relacionados a esta nómina</strong>
        </div>';
    exit();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$embargos = [];
foreach ($datos as $dt) {
    $doc = $dt['id_juzgado'];
    $embargos[$doc][] = [
        'doc' => $dt['no_documento'],
        'nombre' => $dt['nombre'],
        'valor' => $dt['val_mes_embargo']
    ];
}
?>
<div class="text-right py-3">
    <?php if (PermisosUsuario($permisos, 5115, 6) || $id_rol == 1) { ?>
        <a type="button" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
            <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
        </a>
        <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"> Imprimir</a>
    <?php } ?>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">

    <head>
        <style>
            @media print {
                .page_break_avoid {
                    page-break-inside: avoid;
                }

                @page {
                    size: auto;
                    margin: 2cm;
                }
            }
        </style>
    </head>
    <div class="p-4 text-left">
        <table class="page_break_avoid" style="width:100% !important;">
            <thead style="background-color: white !important;">
                <tr style="padding: bottom 3px; color:black">
                    <td colspan="8">
                        <table style="width:100% !important;">
                            <tr>
                                <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="../../images/logos/logo.png" width="100"></label></td>
                                <td colspan="7" style="text-align:center; font-size: 20px">
                                    <strong><?php echo $empresa['nombre']; ?> </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="text-align:center">
                                    NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                                </td>
                            </tr>
                            <tr style="text-align:left; font-size: 14px">
                                <td colspan="2">
                                    NOMINA No.: <?php echo $id_nomina; ?>
                                </td>
                                <td colspan="3">
                                    <?php echo $datos[0]['descripcion']; ?>
                                </td>
                                <td colspan="2">
                                    <?php echo $datos[0]['nom_mes'] . '-' . $datos[0]['vigencia']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align:center">
                                    <b>LISTADO DE EMBARGOS</b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align: right; font-size: 14px">
                                    Estado: <?php echo $datos[0]['estado'] == 1 ? 'PARCIAL' : 'DEFINITIVA' ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center;">
                    <th colspan="2">Documento</th>
                    <th colspan="4">Nombre</th>
                    <th colspan="2">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_emp = '';
                foreach ($embargos as $nit => $emb) {
                    $row_emb = '';
                    $tot_emb_banco = 0;
                    foreach ($emb as $l) {
                        $row_emb .= '<tr style="font-size :14px;;">
                                        <td colspan="2" style="text-align: left;">' . $l['doc'] . '</td>
                                        <td colspan="4" style="text-align: left;">' . $l['nombre'] . '</td>
                                        <td colspan="2" style="text-align: right;">' . number_format($l['valor'], 0, ',', '.') . '</td>
                                    </tr>';
                        $tot_emb_banco += $l['valor'];
                    }
                    $key = array_search($nit, array_column($datos, 'id_juzgado'));
                    $nom_banco = $datos[$key]['nom_juzgado'];
                    $row_emp .= '<tr>
                                    <th colspan="2" style="text-align: left;">' . $nit . '</td>
                                    <th colspan="4" style="text-align: left;">' . $nom_banco . '</td>
                                    <th colspan="2" style="text-align: right;">' . number_format($tot_emb_banco, 0, ',', '.') . '</th>
                                </tr>' . $row_emb;
                }
                echo $row_emp;
                ?>
                <tr>
                    <td colspan="8" style="padding: 15px;"></td>
                </tr>
                <tr style="font-size: 10px;">
                    <td colspan="8">
                        <table style="width: 100%;">
                            <tr>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    ___________________________________
                                </td>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    ___________________________________
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    Elaboro:<?php echo $usuario['nombre']; ?>
                                </td>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    Revisó
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    Técnico administrativo
                                </td>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
            <tfoot style="background-color: white !important;">
                <tr>
                    <td colspan="8" style="text-align:right;font-size:70%;color:black">Fecha Imp: <?php echo $date->format('Y-m-d H:m:s') . ' CRONHIS' ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>