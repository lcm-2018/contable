<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
if (isset($_POST['mesNomElec'])) {
    $mes = $_POST['mesNomElec'];
} else {
    header('Location: ../listempliquidar.php');
    exit();
}
$anio = $_SESSION['vigencia'];
function pesos($valor)
{
    return number_format($valor, 2, ",", ".");
}

include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`, `vigencia`, `salario_basico`, `no_documento`, `estado`, CONCAT_WS(' ', `apellido1`, `apellido2`, `nombre1`, `nombre2`) AS `nombre`, `descripcion_carg`
            FROM
                (SELECT  
                    `nom_empleado`.`id_empleado`
                    , `nom_empleado`.`tipo_doc`
                    , `nom_empleado`.`no_documento`
                    , `nom_empleado`.`genero`
                    , `nom_empleado`.`apellido1`
                    , `nom_empleado`.`apellido2`
                    , `nom_empleado`.`nombre2`
                    , `nom_empleado`.`nombre1`
                    , `nom_empleado`.`estado`
                    , `nom_salarios_basico`.`id_salario`
                    , `nom_salarios_basico`.`vigencia`
                    , `nom_salarios_basico`.`salario_basico`
                    , `nom_liq_salario`.`mes`
                    , `nom_liq_salario`.`anio`
                    , `nom_liq_salario`.`tipo_liq`
                    , `nom_cargo_empleado`.`descripcion_carg`
                FROM `nom_salarios_basico`
                    INNER JOIN `nom_empleado`
                        ON(`nom_salarios_basico`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    INNER JOIN `nom_liq_salario` 
                        ON (`nom_liq_salario`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    INNER JOIN `nom_cargo_empleado` 
		                ON (`nom_empleado`.`cargo` = `nom_cargo_empleado`.`id_cargo`)
                WHERE `nom_salarios_basico`.`id_salario` 
                    IN(SELECT MAX(`id_salario`) FROM `nom_salarios_basico` WHERE `vigencia` <= '$anio' GROUP BY `id_empleado`)) AS t
            WHERE `mes` = '$mes' AND `anio` = '$anio' AND `tipo_liq` = 'N' GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes, anios, dias_liq, pago_empresa, pago_eps, pago_arl
            FROM
                nom_liq_incap
            INNER JOIN nom_incapacidad 
                ON (nom_liq_incap.id_incapacidad = nom_incapacidad.id_incapacidad)
            WHERE mes = '$mes' AND anios = '$anio'";
    $rs = $cmd->query($sql);
    $incap = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes_lic, anio_lic, dias_liqs, val_liq
            FROM
                nom_liq_licmp
            INNER JOIN nom_licenciasmp 
                ON (nom_liq_licmp.id_licmp = nom_licenciasmp.id_licmp)
            WHERE mes_lic = '$mes' AND anio_lic ='$anio'";
    $rs = $cmd->query($sql);
    $lic = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes_vac, anio_vac, dias_liqs, val_liq
            FROM
                nom_liq_vac
            INNER JOIN nom_vacaciones
                ON (nom_liq_vac.id_vac = nom_vacaciones.id_vac)
            WHERE mes_vac = '$mes' AND anio_vac = '$anio'";
    $rs = $cmd->query($sql);
    $vac = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes_liq, anio_liq, dias_liq, val_liq_dias, val_liq_auxt, aux_alim
            FROM
                nom_liq_dlab_auxt
            WHERE mes_liq = '$mes' AND anio_liq = '$anio'";
    $rs = $cmd->query($sql);
    $dlab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                nom_liq_prestaciones_sociales
            WHERE mes_prestaciones = '$mes' AND anio_prestaciones = '$anio'";
    $rs = $cmd->query($sql);
    $presoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                nom_liq_segsocial_empdo
            WHERE mes = '$mes' AND anio = '$anio'";
    $rs = $cmd->query($sql);
    $segsoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_mes_lib
            FROM
                nom_liq_libranza
            INNER JOIN nom_libranzas 
                ON (nom_liq_libranza.id_libranza = nom_libranzas.id_libranza)
            WHERE mes_lib = '$mes' AND anio_lib = '$anio'";
    $rs = $cmd->query($sql);
    $lib = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_mes_embargo
            FROM
                nom_liq_embargo
            INNER JOIN nom_embargos
                ON (nom_liq_embargo.id_embargo = nom_embargos.id_embargo)
            WHERE mes_embargo = '$mes' AND anio_embargo = '$anio'";
    $rs = $cmd->query($sql);
    $emb = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_aporte
            FROM
                nom_liq_sindicato_aportes
            INNER JOIN nom_cuota_sindical
                ON (nom_liq_sindicato_aportes.id_cuota_sindical = nom_cuota_sindical.id_cuota_sindical)
            WHERE mes_aporte = '$mes' AND anio_aporte = '$anio'";
    $rs = $cmd->query($sql);
    $sind = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, SUM(val_liq) AS tot_he
            FROM
                (SELECT id_empleado,val_liq, mes_he, anio_he
                FROM
                    nom_liq_horex
                INNER JOIN nom_horas_ex_trab 
                    ON (nom_liq_horex.id_he_lab = nom_horas_ex_trab.id_he_trab)
                WHERE mes_he = '$mes' AND anio_he = '$anio') AS t
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $hoex = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_liq, fec_reg, nom_mes
    FROM nom_liq_salario,nom_meses
    WHERE  nom_liq_salario.mes = nom_meses.codigo AND mes = '$mes' AND anio = '$anio'";
    $rs = $cmd->query($sql);
    $saln = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM nom_liq_parafiscales
            WHERE mes_pfis = '$mes' AND anio_pfis = '$anio'";
    $rs = $cmd->query($sql);
    $pfis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_viaticos, id_emplead, SUM(valor)AS tot_viat, rango
            FROM   
                (SELECT *
                    FROM 
                        (SELECT seg_detalle_viaticos.id_viaticos, id_emplead, concepto, valor, SUBSTRING(fviatico,1,7) AS rango
                        FROM
                            seg_detalle_viaticos
                        INNER JOIN nom_viaticos 
                            ON (seg_detalle_viaticos.id_viaticos = nom_viaticos.id_viaticos))AS t
                WHERE rango = '$anio-$mes')AS t_res
            GROUP BY id_emplead";
    $rs = $cmd->query($sql);
    $viaticos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM nom_meses
            WHERE codigo = '$mes'";
    $rs = $cmd->query($sql);
    $nombmes = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`,`val_ret` FROM `nom_retencion_fte` WHERE mes = '$mes' AND anio = '$anio'";
    $rs = $cmd->query($sql);
    $ret_fte = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$nomMes = $saln[0]['nom_mes'];
$path = "http://" . $_SERVER['HTTP_HOST'] . $_SESSION['urlin'] . "/images/logos/logo.png";
?>
<!DOCTYPE html>
<html lang="es">
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<meta name="description" content="" />
<meta name="author" content="" />

<body>
    <?php
    header("Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition:attachment;filename=NOMINA_" . $nomMes . ".xls");
    $outputFile = fopen('php://output', 'w+');
    ?>
    <table border>
        <thead class="text-center centro-vertical">
            <tr>
                <th rowspan="2" style="text-align:center"><img src="<?php echo $path; ?>" width="60"></th>
                <th colspan="18" style="background-color: gray;"><?php echo $empresa['nombre']; ?> </th>
            </tr>
            <tr>
                <th colspan="18">NOMINA PERSONAL DE PLANTA CORRESPONDIENTE AL MES <?php echo $nomMes . ' DE ' . $anio ?></th>
            </tr>
            <tr style="background-color: gray;">
                <th rowspan="2">Nombre completo</th>
                <th rowspan="2">C. C.</th>
                <th rowspan="2">Cargo</th>
                <th rowspan="2">Salario Base</th>
                <th rowspan="2">Gastos <br> Repre.</th>
                <th rowspan="2">Días <br>Labor</th>
                <th colspan="4">Devengado</th>
                <th colspan="8">Deducido</th>
                <th rowspan="2">Neto a Pagar</th>
                <th rowspan="2" style="width: 7rem;">Firma</th>
            </tr>
            <tr style="background-color: gray;">
                <th>Básico</th>
                <th>Aux. Alimentación</th>
                <th>Horas Extra</th>
                <th>Total Devengado</th>
                <th>Salud</th>
                <th>Pensión</th>
                <th>Pensión Solidaria</th>
                <th>Libranza</th>
                <th>Embargo</th>
                <th>Sindicato</th>
                <th>Ret. Fte.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($obj as $o) { ?>
                <tr class="ajustar">
                    <td> <?php echo mb_strtoupper($o['nombre']) ?> </td>
                    <td><?php echo $o['no_documento'] ?></td>
                    <td><?php echo mb_strtoupper($o['descripcion_carg']) ?></td>
                    <td class="text-right"><?php echo pesos($o['salario_basico']) ?></td>
                    <?php
                    $id = $o["id_empleado"];
                    $keyincap = array_search($id, array_column($incap, 'id_empleado'));
                    $keylic = array_search($id, array_column($lic, 'id_empleado'));
                    $keyvac = array_search($id, array_column($vac, 'id_empleado'));
                    $keydlab = array_search($id, array_column($dlab, 'id_empleado'));
                    $keypresoc = array_search($id, array_column($presoc, 'id_empleado'));
                    $keysegsoc = array_search($id, array_column($segsoc, 'id_empleado'));
                    $keylib = array_search($id, array_column($lib, 'id_empleado'));
                    $keyemb = array_search($id, array_column($emb, 'id_empleado'));
                    $keysind = array_search($id, array_column($sind, 'id_empleado'));
                    $keyhoex = array_search($id, array_column($hoex, 'id_empleado'));
                    $keysaln = array_search($id, array_column($saln, 'id_empleado'));
                    $keyviat = array_search($id, array_column($viaticos, 'id_emplead'));
                    $keyrfte = array_search($id, array_column($ret_fte, 'id_empleado'));
                    ?>
                    <td class="text-right">0</td>
                    <td><?php
                        if (false !== $keydlab) {
                            echo $dlab[$keydlab]['dias_liq'];
                        } else {
                            echo '0';
                        } ?></td>
                    <td class="text-right">
                        <?php
                        if (false !== $keydlab) {
                            echo pesos($d = $dlab[$keydlab]['val_liq_dias']);
                            $d = $dlab[$keydlab]['val_liq_dias'];
                        } else {
                            echo pesos($d = '0');
                        } ?></td>
                    <td class="text-right">
                        <?php
                        if (false !== $keydlab) {
                            echo pesos($e = $dlab[$keydlab]['aux_alim']);
                        } else {
                            echo pesos($e = 0);
                        } ?></td>
                    <td class="text-right">
                        <?php
                        if (false !== $keyhoex) {
                            echo pesos($f = $hoex[$keyhoex]['tot_he']);
                        } else {
                            echo pesos($f = 0);
                        } ?></td>
                    <?php
                    $a = false !== $keyincap ? $incap[$keyincap]['pago_empresa'] + $incap[$keyincap]['pago_eps'] + $incap[$keyincap]['pago_arl'] : 0;
                    $b = false !== $keylic ?  $lic[$keylic]['val_liq'] : 0;
                    $c = false !== $keyvac ? $vac[$keyvac]['val_liq'] : 0;
                    ?>
                    <td class="text-right">
                        <?php echo pesos($a + $b + $c + $f + $d + $e); ?>
                    </td>
                    <?php
                    if (false !== $keysegsoc) {
                        $g = $segsoc[$keysegsoc]['aporte_salud_emp'];
                        $i = $segsoc[$keysegsoc]['aporte_pension_emp'];
                        $j = $segsoc[$keysegsoc]['aporte_solidaridad_pensional'];
                    } else {
                        $g = '0';
                        $i = '0';
                        $j = '0';
                    } ?>
                    <td class="text-left"><?php echo pesos($g); ?></td>
                    <td class="text-right"><?php echo pesos($i); ?></td>
                    <td class="text-right"><?php echo pesos($j); ?></td>
                    <td class="text-right">
                        <?php
                        $k =  false !== $keylib ? $lib[$keylib]['val_mes_lib'] : 0;
                        echo pesos($k);
                        ?></td>
                    <td class="text-right">
                        <?php
                        $l = false !== $keyemb  ? $emb[$keyemb]['val_mes_embargo'] : 0;
                        echo pesos($l); ?></td>
                    <td class="text-right">
                        <?php
                        $m = false !== $keysind ? $sind[$keysind]['val_aporte'] : 0;
                        echo pesos($m);
                        ?></td>
                    <td class="text-right">
                        <?php
                        $rft = false !== $keyrfte ? $ret_fte[$keyrfte]['val_ret'] : 0;
                        echo pesos($rft);
                        ?></td>
                    <td class="text-right">
                        <?php
                        $deducidos = $g + $i + $j + $k + $l + $m + $rft;
                        echo pesos($deducidos);
                        ?>
                    </td>
                    <td class="text-right">
                        <?php
                        $n = false !== $keysaln ? $saln[$keysaln]['val_liq'] : 0;
                        echo pesos($n); ?></td>
                    <td></td>
                </tr>
            <?php
            } ?>
        </tbody>
    </table>
    <br>
    <table border>
        <thead class="text-center centro-vertical">
            <tr style="background-color: gray;">
                <th rowspan="2">Nombre completo</th>
                <th colspan="3">Parafiscales</th>
            </tr>
            <tr style="background-color: gray;">
                <th>SENA</th>
                <th>ICBF</th>
                <th>COMFAMILIAR</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($obj as $o) { ?>
                <tr class="ajustar">
                    <td> <?php echo mb_strtoupper($o['nombre']) ?> </td>
                    <?php
                    $id = $o["id_empleado"];
                    $keypfis = array_search($id, array_column($pfis, 'id_empleado'));
                    if (false !== $keypfis) {
                        $valsena = $pfis[$keypfis]['val_sena'];
                        $valicbf = $pfis[$keypfis]['val_icbf'];
                        $valconfam = $pfis[$keypfis]['val_comfam'];
                    } else {
                        $valsena = '0';
                        $valicbf = '0';
                        $valconfam = '0';
                    } ?>
                    <td class="text-right"><?php echo pesos($valsena) ?></td>
                    <td class="text-right"><?php echo pesos($valicbf) ?></td>
                    <td class="text-right"><?php echo pesos($valconfam) ?></td>
                </tr>
            <?php
            } ?>
        </tbody>
    </table>

</body>

</html>