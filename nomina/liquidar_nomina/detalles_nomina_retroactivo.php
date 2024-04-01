<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$anio = $_SESSION['vigencia'];
if (isset($_GET['mes'])) {
    $mes = $_GET['mes'];
} else {
    header('Location: listempliquidar.php');
    exit();
}

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`, `vigencia`, `salario_basico`, `no_documento`, `estado`, CONCAT_WS(' ', `apellido1`, `apellido2`, `nombre1`, `nombre2`) AS `nombre`
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
                    , `nom_liq_salario`.`tipo_liq`
                FROM `nom_salarios_basico`
                    INNER JOIN `nom_empleado`
                        ON(`nom_salarios_basico`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    INNER JOIN `nom_liq_salario` 
                        ON (`nom_liq_salario`.`id_empleado` = `nom_empleado`.`id_empleado`)
                WHERE `nom_salarios_basico`.`id_salario` 
                    IN(SELECT MAX(`id_salario`) FROM `nom_salarios_basico` WHERE `vigencia` <= '$anio' GROUP BY `id_empleado`)) AS t
            WHERE `mes` = '$mes' AND `tipo_liq` = 'R' group by `id_empleado`";
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
            WHERE mes = '$mes' AND anios = '$anio' AND tipo_liq = 'R'";
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
            WHERE mes_lic = '$mes' AND anio_lic ='$anio' AND tipo_liq = 'R'";
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
            WHERE mes_vac = '$mes' AND anio_vac = '$anio' AND tipo_liq = 'R'";
    $rs = $cmd->query($sql);
    $vac = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes_liq, anio_liq, dias_liq, val_liq_dias, val_liq_auxt
            FROM
                nom_liq_dlab_auxt
            WHERE mes_liq = '$mes' AND anio_liq = '$anio' AND tipo_liq = 'R'";
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
            WHERE mes_prestaciones = '$mes' AND anio_prestaciones = '$anio' AND tipo_liq = 'R'";
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
            WHERE mes = '$mes' AND anio = '$anio' AND tipo_liq = 'R'";
    $rs = $cmd->query($sql);
    $segsoc = $rs->fetchAll();
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
            WHERE mes_aporte = '$mes' AND anio_aporte = '$anio' AND tipo_liq = 'R'";
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
                WHERE mes_he = '$mes' AND anio_he = '$anio' AND tipo_liq = 'R') AS t
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
    $sql = "SELECT id_empleado, val_liq, fec_reg
            FROM nom_liq_salario
            WHERE mes = '$mes' AND anio = '$anio' AND tipo_liq = 'R'";
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
            WHERE mes_pfis = '$mes' AND anio_pfis = '$anio' AND tipo_liq = 'R'";
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
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <?php
                        if (!empty($saln)) {
                        ?>
                            <div class="card-header" id="divTituloPag">
                                <div class="row">
                                    <div class="col-md-11">
                                        <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                        LISTA DE EMPLEADOS NOMINA LIQUIDADA <b> <?php echo $nombmes['nom_mes'] ?></b> RETROACTIVO
                                        <input type="text" id="fecLiqNomElec" value="<?php echo date('Y-m-d', strtotime($saln[0]['fec_reg'])) ?>" hidden>
                                    </div>

                                </div>
                            </div>
                            <div class="card-body" id="divCuerpoPag">
                                <table id="dataTableLiqNom" class="table-bordered table-sm  order-column nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" style="background-color: #16A085" class="text-center centro-vertical">Nombre completo</th>
                                            <th rowspan="2" class="text-center centro-vertical">No. Doc.</th>
                                            <th rowspan="2" class="text-center centro-vertical">Sal. Base</th>
                                            <th colspan="4" class="text-center centro-vertical">Días</th>
                                            <th colspan="4" class="text-center centro-vertical">Valor</th>
                                            <th rowspan="2" class="text-center centro-vertical">Aux. Transp.</th>
                                            <th rowspan="2" class="text-center centro-vertical">Val. HoEx</th>
                                            <th rowspan="2" class="text-center centro-vertical">Viáticos</th>
                                            <th colspan="3" class="text-center centro-vertical">Parafiscales</th>
                                            <th colspan="4" class="text-center centro-vertical">Apropiaciones</th>
                                            <th colspan="6" class="text-center centro-vertical">Seguridad Social</th>
                                            <th colspan="4" class="text-center centro-vertical">Deducciones</th>
                                            <th rowspan="2" class="text-center centro-vertical">Salario</th>
                                        </tr>
                                        <tr>
                                            <th>Incap.</th>
                                            <th>Lic.</th>
                                            <th>Vac.</th>
                                            <th>Lab.</th>
                                            <th>Incap.</th>
                                            <th>Lic.</th>
                                            <th>Vac.</th>
                                            <th>Lab.</th>
                                            <th>SENA</th>
                                            <th>ICBF</th>
                                            <th>COMFAM</th>
                                            <th>Vac.</th>
                                            <th>Cesan.</th>
                                            <th>ICesan.</th>
                                            <th>Prima</th>
                                            <th>Salud</th>
                                            <th>Riesgos</th>
                                            <th>Pensión</th>
                                            <th>SaludEmpresa</th>
                                            <th>PensiónEmpresa</th>
                                            <th>Pensión Solid.</th>
                                            <th>Libranza</th>
                                            <th>Embargo</th>
                                            <th>Sindicato</th>
                                            <th>Ret. Fte.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($obj as $o) {
                                            $status = true;
                                            $id = $o["id_empleado"];
                                            $keysaln = array_search($id, array_column($saln, 'id_empleado'));
                                            if ($o['estado'] == '0') {
                                                if ($keysaln === false) {
                                                    $status = false;
                                                }
                                            }
                                            if ($status) {
                                        ?>
                                                <tr>
                                                    <td> <?php echo mb_strtoupper($o['nombre']) ?> </td>
                                                    <td><?php echo $o['no_documento'] ?></td>
                                                    <td class="text-right"><?php echo pesos($o['salario_basico']) ?></td>
                                                    <?php
                                                    $keyincap = array_search($id, array_column($incap, 'id_empleado'));
                                                    $keylic = array_search($id, array_column($lic, 'id_empleado'));
                                                    $keyvac = array_search($id, array_column($vac, 'id_empleado'));
                                                    $keydlab = array_search($id, array_column($dlab, 'id_empleado'));
                                                    $keypresoc = array_search($id, array_column($presoc, 'id_empleado'));
                                                    $keysegsoc = array_search($id, array_column($segsoc, 'id_empleado'));
                                                    $keysind = array_search($id, array_column($sind, 'id_empleado'));
                                                    $keyhoex = array_search($id, array_column($hoex, 'id_empleado'));
                                                    $keypfis = array_search($id, array_column($pfis, 'id_empleado'));
                                                    $keyviat = array_search($id, array_column($viaticos, 'id_emplead'));
                                                    ?>
                                                    <td><?php
                                                        if (false !== $keyincap) {
                                                            echo $incap[$keyincap]['dias_liq'];
                                                        } else {
                                                            echo '0';
                                                        } ?></td>
                                                    <td><?php
                                                        if (false !== $keylic) {
                                                            echo $lic[$keylic]['dias_liqs'];
                                                        } else {
                                                            echo '0';
                                                        } ?></td>
                                                    <td><?php
                                                        if (false !== $keyvac) {
                                                            echo $vac[$keyvac]['dias_liqs'];
                                                        } else {
                                                            echo '0';
                                                        } ?></td>
                                                    <td><?php
                                                        if (false !== $keydlab) {
                                                            echo $dlab[$keydlab]['dias_liq'];
                                                        } else {
                                                            echo '0';
                                                        } ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keyincap) {
                                                                                echo pesos($incap[$keyincap]['pago_empresa'] + $incap[$keyincap]['pago_eps'] + $incap[$keyincap]['pago_arl']);
                                                                                $a = $incap[$keyincap]['pago_empresa'] + $incap[$keyincap]['pago_eps'] + $incap[$keyincap]['pago_arl'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                                $a = 0;
                                                                            } ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keylic) {
                                                                                echo pesos($lic[$keylic]['val_liq']);
                                                                                $b = $lic[$keylic]['val_liq'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                                $b = 0;
                                                                            } ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keyvac) {
                                                                                echo pesos($vac[$keyvac]['val_liq']);
                                                                                $c = $vac[$keyvac]['val_liq'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                                $c = 0;
                                                                            } ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keydlab) {
                                                                                echo pesos($dlab[$keydlab]['val_liq_dias']);
                                                                                $d = $dlab[$keydlab]['val_liq_dias'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                                $d = 0;
                                                                            } ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keydlab) {
                                                                                echo pesos($dlab[$keydlab]['val_liq_auxt']);
                                                                                $e = $dlab[$keydlab]['val_liq_auxt'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                                $e = 0;
                                                                            } ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keyhoex) {
                                                                                echo pesos($hoex[$keyhoex]['tot_he']);
                                                                                $f = $hoex[$keyhoex]['tot_he'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                                $f = 0;
                                                                            } ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keyviat) {
                                                                                echo pesos($viaticos[$keyviat]['tot_viat']);
                                                                                $viat = $viaticos[$keyviat]['tot_viat'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                                $viat = 0;
                                                                            } ?></td>
                                                    <?php
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
                                                    <?php
                                                    if (false !== $keypresoc) {
                                                        $valvac = $presoc[$keypresoc]['val_vacacion'];
                                                        $valces = $presoc[$keypresoc]['val_cesantia'];
                                                        $valices = $presoc[$keypresoc]['val_interes_cesantia'];
                                                        $valpri = $presoc[$keypresoc]['val_prima'];
                                                    } else {
                                                        $valvac = '$0.00';
                                                        $valces = '$0.00';
                                                        $valices = '$0.00';
                                                        $valpri = '$0.00';
                                                    } ?>
                                                    <td class="text-right"><?php echo pesos($valvac); ?></td>
                                                    <td class="text-right"><?php echo pesos($valces); ?></td>
                                                    <td class="text-right"><?php echo pesos($valices); ?></td>
                                                    <td class="text-right"><?php echo pesos($valpri); ?></td>
                                                    <?php
                                                    if (false !== $keysegsoc) {
                                                        $g = $segsoc[$keysegsoc]['aporte_salud_emp'];
                                                        $ge = $segsoc[$keysegsoc]['aporte_salud_empresa'];
                                                        $rl = $segsoc[$keysegsoc]['aporte_rieslab'];
                                                        $i = $segsoc[$keysegsoc]['aporte_pension_emp'];
                                                        $ie = $segsoc[$keysegsoc]['aporte_pension_empresa'];
                                                        $j = $segsoc[$keysegsoc]['aporte_solidaridad_pensional'];
                                                    } else {
                                                        $g = '0';
                                                        $ge = '0';
                                                        $rl = '0';
                                                        $i = '0';
                                                        $ie = '0';
                                                        $j = '0';
                                                    } ?>
                                                    <td class="text-right"><?php echo pesos($g); ?></td>
                                                    <td class="text-right"><?php echo pesos($rl); ?></td>
                                                    <td class="text-right"><?php echo pesos($i); ?></td>
                                                    <td class="text-right"><?php echo pesos($ge); ?></td>
                                                    <td class="text-right"><?php echo pesos($ie); ?></td>
                                                    <td class="text-right"><?php echo pesos($j); ?></td>
                                                    <td class="text-right"><?php
                                                                            echo '$0.00';
                                                                            $k = 0; ?></td>
                                                    <td class="text-right"><?php
                                                                            echo '$0.00';
                                                                            $l = 0; ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keysind) {
                                                                                echo pesos($sind[$keysind]['val_aporte']);
                                                                                $m = $sind[$keysind]['val_aporte'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                                $m = 0;
                                                                            } ?></td>

                                                    <td class="text-right"><?php
                                                                            echo '$0.00';
                                                                            $n = 0;
                                                                            ?></td>
                                                    <td class="text-right"><?php
                                                                            if (false !== $keysaln) {
                                                                                echo pesos($saln[$keysaln]['val_liq']);
                                                                                $n = $saln[$keysaln]['val_liq'];
                                                                            } else {
                                                                                echo '$0.00';
                                                                            } ?></td>
                                                    <!--<td>
                                                <?php
                                                // pesos($a + $b + $c + $d + $e + $f - $g - $i - $j - $k - $l - $m);
                                                //$o = $a + $b + $c + $d + $e + $f - $g - $i - $j - $k - $l - $m;
                                                ?>
                                            </td>
                                            <td><?php //echo pesos($n - $o) 
                                                ?></td>-->
                                                </tr>
                                        <?php }
                                        } ?>
                                    </tbody>
                                </table>
                                <div class="center-block">
                                    <div class="form-group">
                                        <a type="button" class="btn btn-secondary" href="javascript:history.back()"> Regresar</a>
                                        <a type="button" class="btn btn-secondary " href="../../inicio.php"> Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
    <style>
        .DTFC_LeftBodyLiner {
            overflow-y: unset !important
        }

        .DTFC_RightBodyLiner {
            overflow-y: unset !important
        }
    </style>
</body>

</html>