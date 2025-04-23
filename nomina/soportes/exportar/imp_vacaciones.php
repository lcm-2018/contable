<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}

function pesos($valor)
{
    return '$' . number_format($valor, 0, ",", ".");
}
include '../../../conexion.php';
$id_vac = $_POST['id'];
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `nom_valxvigencia`.`id_concepto`
                , `nom_valxvigencia`.`valor`
            FROM
                `nom_valxvigencia`
            INNER JOIN `nom_conceptosxvigencia` 
                ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
            INNER JOIN `tb_vigencias` 
                ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE `anio` = '$vigencia'";
    $rs = $cmd->query($sql);
    $valxvig = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

foreach ($valxvig as $vxv) {
    if ($vxv['id_concepto'] == '1') {
        $smmlv = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '2') {
        $auxiliotranporte = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '3') {
        $auxalim = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '6') {
        $uvt = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '7') {
        $bbs = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '8') {
        $representacion = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '9') {
        $basealim = floatval($vxv['valor']);
    }
}

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT 
                `razon_social_ips` AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver`, `nom_municipio`
            FROM `tb_datos_ips`
                INNER JOIN `tb_municipios` 
                    ON (`tb_datos_ips`.`idmcpio` = `tb_municipios`.`id_municipio`)";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios_sistema`
            WHERE (`id_usuario` = $_SESSION[id_user])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `tb_tipos_documento`.`codigo_ne`
                , `tb_tipos_documento`.`descripcion`
                , `nom_empleado`.`no_documento`
                , `nom_empleado`.`fech_inicio`
                , CONCAT_WS(' ',`nom_empleado`.`nombre1`, `nom_empleado`.`nombre2`, `nom_empleado`.`apellido1`, `nom_empleado`.`apellido2`) AS `nombre`
                , `nom_vacaciones`.`corte`
                , `nom_vacaciones`.`fec_inicial`
                , `nom_vacaciones`.`fec_inicio`
                , `nom_vacaciones`.`fec_fin`
                , `nom_vacaciones`.`dias_inactivo`
                , `nom_vacaciones`.`dias_habiles`
                , `nom_vacaciones`.`dias_liquidar`
                , `nom_empleado`.`id_empleado`
                , `nom_empleado`.`representacion`
                , `nom_vacaciones`.`id_vac`
            FROM
                `nom_vacaciones`
                INNER JOIN `nom_empleado` 
                    ON (`nom_vacaciones`.`id_empleado` = `nom_empleado`.`id_empleado`)
                INNER JOIN `tb_tipos_documento` 
                    ON (`nom_empleado`.`tipo_doc` = `tb_tipos_documento`.`id_tipodoc`)
            WHERE (`nom_vacaciones`.`id_vac` = '$id_vac')";
    $res = $cmd->query($sql);
    $datos = $res->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `salario_basico`
            FROM
                `nom_salarios_basico`
            WHERE  `id_salario` = (SELECT MAX(`id_salario`) FROM `nom_salarios_basico` WHERE `id_empleado` = {$datos['id_empleado']})";
    $res = $cmd->query($sql);
    $salario = $res->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT   
                `id_empleado`
                , `corte`
                , SUM(`val_liq_ps`) AS `val_liq_ps`
            FROM `nom_liq_prima` 
            WHERE `id_liq_prima` IN 
                (SELECT
                    MAX(`id_liq_prima`) AS `id_lp`
                FROM
                    `nom_liq_prima`
                INNER JOIN `nom_nominas`
                    ON (`nom_liq_prima`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE `nom_nominas`.`tipo` = 'PV'
                GROUP BY `id_empleado`
                UNION ALL 
                SELECT
                    MAX(`id_liq_prima`) AS `id_lp`
                FROM
                    `nom_liq_prima`
                INNER JOIN `nom_nominas`
                    ON (`nom_liq_prima`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE `nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` = '$vigencia'
                GROUP BY `id_empleado`)
            GROUP BY `id_empleado`";
    $res = $cmd->query($sql);
    $prima = $res->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_empleado`, SUM(`val_bsp`) AS `val_bsp`
            FROM 
                `nom_liq_bsp`
            WHERE `id_bonificaciones` IN 
                (SELECT
                    MAX(`nom_liq_bsp`.`id_bonificaciones`) AS `id_bonificaciones`
                FROM
                    `nom_liq_bsp`
                INNER JOIN `nom_nominas`
                    ON (`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE ((`nom_nominas`.`tipo` = 'N' OR `nom_nominas`.`tipo` = 'PS') AND `nom_nominas`.`vigencia` <= '$vigencia')
                GROUP BY `nom_liq_bsp`.`id_empleado`
                UNION ALL
                SELECT
                    MAX(`nom_liq_bsp`.`id_bonificaciones`) AS `id_bonificaciones`
                FROM
                    `nom_liq_bsp`
                INNER JOIN `nom_nominas` 
                    ON (`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE (`nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` <= '$vigencia')
                GROUP BY `nom_liq_bsp`.`id_empleado`)
            GROUP BY `id_empleado`";
    $res = $cmd->query($sql);
    $bpserv = $res->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$meses = [
    'enero',
    'febrero',
    'marzo',
    'abril',
    'mayo',
    'junio',
    'julio',
    'agosto',
    'septiembre',
    'octubre',
    'noviembre',
    'diciembre'
];
$inicia_lab = new DateTime($datos['fech_inicio']);
$ini_vac = new DateTime($datos['fec_inicial']);
$fin_vac = new DateTime($datos['fec_fin']);
$key = array_search($datos['id_empleado'], array_column($salario, 'id_empleado'));
$salbase = $key !== false ? $salario[$key]['salario_basico'] : 0;
$dossml = $smmlv * 2;
if ($salbase <= $dossml) {
    $auxtransp = $auxiliotranporte;
} else {
    $auxtransp = 0;
}

if ($salbase <= $basealim) {
    $auxali = $auxalim;
} else {
    $auxali = 0;
}
$grepresenta = $datos['representacion'];
if ($grepresenta == 1) {
    $gasrep = $representacion;
} else {
    $gasrep = 0;
}
$dayvac = $datos['dias_inactivo'];
$dayhab = $datos['dias_habiles'];
$diastocalc = $datos['dias_liquidar'];
//prima de servicios
$key = array_search($datos['id_empleado'], array_column($prima, 'id_empleado'));
$primservicio = $key !== false ? $prima[$key]['val_liq_ps'] : 0;
$doceavaps = $primservicio / 12;
//bonificacion de servicios prestados
$key = array_search($datos['id_empleado'], array_column($bpserv, 'id_empleado'));
$bsp = $key !== false ? $bpserv[$key]['val_bsp'] : 0;
$doceavabsp = $bsp / 12;
//prima de vacaciones
if ($_SESSION['caracter'] == '1') {
    $bsp = $primservicio = $gasrep = $auxtransp = $auxali = 0;
    $dayvac = $dayhab;
    $datos['dias_inactivo'] = $dayhab;
}
$primvacacion  = (($salbase + $gasrep + $auxtransp + $auxali + $bsp / 12 + $primservicio / 12) * $dayhab) / 30;
$primavacn = ($primvacacion / 360) * $diastocalc;
//liquidacion vacaciones
$liqvacacion  = (($salbase + $gasrep + $auxtransp + $auxali + $bsp / 12 + $primservicio / 12) * $dayvac) / 30;
$vacacion = ($liqvacacion / 360) * $diastocalc;
$bonrecrea = ($salbase / 30) * 2;
$bonrecreacion = ($bonrecrea / 360) * $diastocalc;
$bonserpres = 0;
if ($_SESSION['caracter'] == '1') {
    $primavacn = $bonrecreacion = $doceavaps = 0;
}
?>
<div class="text-right py-3">
    <!--<a type="button" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>-->
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir','<?php echo 0; ?>');"> Imprimir</a>
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
                            <tr>
                                <td colspan="7" style="padding:15px">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align:center">
                                    <b>LIQUIDACIÓN DE VACACIONES</b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="padding:15px">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="1">Tipo Doc.:</td>
                    <td colspan="7"><?php echo $datos['descripcion'] ?></td>
                </tr>
                <tr>
                    <td colspan="1">Número:</td>
                    <td colspan="7"><?php echo number_format($datos['no_documento'], 0, '', '.') ?></td>
                </tr>
                <tr>
                    <td colspan="1">Nombre:</td>
                    <td colspan="7"><?php echo $datos['nombre'] ?></td>
                </tr>
                <tr>
                    <td colspan="1">Fecha Ingreso:</td>
                    <td colspan="7"><?php echo $inicia_lab->format('d') . ' de ' . $meses[$inicia_lab->format('m') - 1] . ' de ' . $inicia_lab->format('Y') ?></td>
                </tr>
                <tr>
                    <td colspan="1">Programadas de:</td>
                    <td colspan="7"><?php echo $ini_vac->format('d') . ' de ' . $meses[$ini_vac->format('m') - 1] . ' de ' . $ini_vac->format('Y') . ' a ' . $fin_vac->format('d') . ' de ' . $meses[$fin_vac->format('m') - 1] . ' de ' . $fin_vac->format('Y') ?></td>
                </tr>
                <tr>
                    <td colspan="8" style="padding: 15px;"></td>
                </tr>
                <tr>
                    <td colspan="2">LIQUIDACIÓN</td>
                    <td colspan="4">La erogación se hará con cargo a</td>
                    <td>Días</td>
                    <td style="text-align: center;">Valor</td>
                </tr>
                <?php
                if ($_SESSION['caracter'] == 2) {
                ?>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="4">PRIMA DE VACACIONES</td>
                        <td><?php echo $datos['dias_habiles'] ?></td>
                        <td style="text-align: right;"><?php echo pesos($primavacn) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="4">BONIFICACIÓN RECREACIÓN</td>
                        <td>2</td>
                        <td style="text-align: right;"><?php echo pesos($bonrecreacion) ?></td>
                    </tr>
                <?php
                } else {
                    $primavacn = $bonrecreacion = 0;
                }
                ?>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="4">VACACIONES</td>
                    <td><?php echo $datos['dias_inactivo'] ?></td>
                    <td style="border-bottom-style: double; border-bottom-width: 4px; text-align: right;"><?php echo pesos($vacacion) ?></td>
                </tr>
                <tr>
                    <td colspan="7"></td>
                    <td style="text-align: right;"><?php echo pesos($primavacn + $bonrecreacion + $vacacion) ?></td>
                </tr>
                <tr>
                    <td colspan="8" style="padding: 10px;"></td>
                </tr>
                <tr>
                    <td colspan="2">FACTORES SALARIALES</td>
                    <td colspan="4">SUELDO BÁSICO</td>
                    <td></td>
                    <td style="text-align: right;"><?php echo pesos($salbase) ?></td>
                </tr>
                <?php
                if ($auxtransp > 0) {
                ?>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="4">SUBSIDIO DE TRANSPORTE</td>
                        <td></td>
                        <td style="text-align: right;"><?php echo pesos($auxtransp) ?></td>
                    </tr>
                <?php
                }
                if ($auxali > 0) {
                ?>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="4">SUBSIDIO DE ALIMENTACIÓN</td>
                        <td></td>
                        <td style="text-align: right;"><?php echo pesos($auxali) ?></td>
                    </tr>
                <?php
                }
                if ($gasrep > 0) {
                ?>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="4">GASTOS DE REPRESENTACIÓN</td>
                        <td></td>
                        <td style="text-align: right;"><?php echo pesos($gasrep) ?></td>
                    </tr>
                <?php
                }
                if ($doceavaps > 0) {
                ?>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="4">PRIMA DE SERVICIOS</td>
                        <td></td>
                        <td style="text-align: right;"><?php echo pesos($doceavaps) ?></td>
                    </tr>
                <?php
                }
                if ($doceavabsp > 0) {
                ?>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="4">BONIFICACIÓN POR SERVICIOS</td>
                        <td></td>
                        <td style="border-bottom-style: double; border-bottom-width: 4px;text-align: right;"><?php echo pesos($doceavabsp) ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td colspan="7"></td>
                    <td style="text-align: right;"><?php echo pesos($doceavabsp + $salbase + $auxtransp + $auxali + $gasrep + $doceavaps) ?></td>
                </tr>
                <tr>
                    <td colspan="8" style="height: 30px;"></td>
                </tr>
                <tr>
                    <td colspan="8">
                        Dada en <?= $empresa['nom_municipio']; ?>, a los <?php echo $date->format('d') ?> días del mes de <?php echo $meses[date('n') - 1] ?> de <?php echo $date->format('Y') ?>.
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="padding: 15px;"></td>
                </tr>
                <tr>
                    <td colspan="8" style="text-align: center;">
                        ______________________________________________
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="text-align: center;">
                        <?php echo mb_strtoupper($usuario['nombre']); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="text-align: center;">
                        Técnico Administrativo
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