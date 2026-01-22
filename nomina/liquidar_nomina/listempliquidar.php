<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
$anio = $_SESSION['vigencia'];

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
function DiasLicNR($fi, $ff, $id)
{
    include '../../conexion.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                    IFNULL(SUM(`dias_inactivo`),0) AS `dias`
                    , IFNULL(`id_empleado`,0) AS `id_empleado`
                FROM
                    `nom_licenciasnr`
                WHERE `fec_inicio` >= '$fi' AND `fec_fin` <= '$ff' AND `id_empleado` = $id LIMIT 1";
        $rs = $cmd->query($sql);
        $datas = $rs->fetch();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $datas['dias'];
}
function DiasVac($fechaInicial, $fechaFinal)
{
    if ($fechaInicial < $fechaFinal) {
        $diferenciaEnSegundos = strtotime($fechaFinal) - strtotime($fechaInicial);
        return $diferenciaEnSegundos / (60 * 60 * 24);
    } else {
        return 0;
    }
}
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `nom_empleado`.`id_empleado`
                , `nom_empleado`.`tipo_doc`
                , `nom_empleado`.`no_documento`
                , `nom_empleado`.`genero`
                , `nom_empleado`.`apellido1`
                , `nom_empleado`.`apellido2`
                , `nom_empleado`.`nombre2`
                , `nom_empleado`.`nombre1`
                , `nom_empleado`.`fech_inicio`
                , `nom_empleado`.`fec_retiro`
                , `nom_empleado`.`estado`
                , `nom_salarios_basico`.`id_salario`
                , `nom_salarios_basico`.`vigencia`
                , `nom_salarios_basico`.`salario_basico`
            FROM (SELECT
                MAX(`id_salario`) AS `id_salario`, `id_empleado`
                FROM
                    `nom_salarios_basico`
                WHERE `vigencia` <= '$anio'
                GROUP BY `id_empleado`) AS `t`
            INNER JOIN `nom_salarios_basico`
                ON (`nom_salarios_basico`.`id_salario` = `t`.`id_salario`)
            INNER JOIN `nom_empleado`
                ON (`nom_empleado`.`id_empleado` = `t`.`id_empleado`)
            WHERE `nom_empleado`.`estado` = 1";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_vac`, `id_empleado`, `fec_inicial`, `fec_fin`
            FROM
                `nom_vacaciones`
            WHERE `id_vac` IN (SELECT MAX(`id_vac`) FROM `nom_vacaciones` GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $tienevacs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_meses";
    $rs = $cmd->query($sql);
    $meses = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_indemniza`, `id_empleado`, `cant_dias`, `estado`
            FROM
                `nom_indemniza_vac`
            WHERE (`estado` = 1)";
    $res = $cmd->query($sql);
    $indemnizaciones = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (isset($_GET['mes'])) {
    $dia = '01';
    $mes = $_GET['mes'];
    switch ($mes) {
        case '01':
        case '03':
        case '05':
        case '07':
        case '08':
        case '10':
        case '12':
            $fec_i = $anio . '-' . $mes . '-' . $dia;
            $fec_f = $anio . '-' . $mes . '-31';
            break;
        case '02':
            $fec_i = $anio . '-' . $mes . '-' . $dia;
            if (date('L', strtotime("$anio-01-01")) === '1') {
                $bis = '29';
            } else {
                $bis = '28';
            }
            $fec_f = $anio . '-' . $mes . '-' . $bis;
            break;
        case '04':
        case '06':
        case '09':
        case '11':
            $fec_i = $anio . '-' . $mes . '-' . $dia;
            $fec_f = $anio . '-' . $mes . '-30';
            break;
        default:
            echo 'Error Fatal';
            break;
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT *
            FROM nom_incapacidad
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
        $rs = $cmd->query($sql);
        $incapac = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT *
            FROM nom_licenciasmp
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
        $rs = $cmd->query($sql);
        $licencia = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT *
            FROM nom_licenciasnr
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
        $rs = $cmd->query($sql);
        $licencianr = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT *
            FROM `nom_licencia_luto`
            WHERE `fec_inicio` BETWEEN '$fec_i' AND '$fec_f'";
        $rs = $cmd->query($sql);
        $licluto = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }

    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT *
            FROM nom_vacaciones
            WHERE estado = 1";
        $rs = $cmd->query($sql);
        $vacacion = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT * FROM nom_metodo_pago ORDER BY metodo ASC";
        $rs = $cmd->query($sql);
        $metpago = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT * FROM nom_contratos_empleados
                WHERE estado = '0'
                ORDER BY fec_fin DESC";
        $rs = $cmd->query($sql);
        $contratos = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
$carcater_empresa = $_SESSION['caracter'] == 2 ? $_SESSION['caracter'] : 1;
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
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <span class="fas fa-users fa-lg" style="color:#1D80F7"></span>
                                    LISTA DE EMPLEADOS A LIQUIDAR POR MES
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="">
                                <form id="formLiqNomina">
                                    <input type="hidden" id="caracter_empresa" value="<?php echo $carcater_empresa ?>">
                                    <div class="form-row">
                                        <div class="col left-block py-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <select class="custom-select" id="slcMesLiqNom" name="slcMesLiqNom">
                                                        <?php
                                                        if (isset($_GET['mes'])) {
                                                            foreach ($meses as $m) {
                                                                if ($_GET['mes'] === $m['codigo']) {
                                                                    echo '<option selected value="' . $m['codigo'] . '">' . $m['nom_mes'] . '</option>';
                                                                } else {
                                                                    echo '<option value="' . $m['codigo'] . '">' . $m['nom_mes'] . '</option>';
                                                                }
                                                            }
                                                        } else {
                                                            echo '<option selected value="00">--Selecionar mes a liquidar--</option>';
                                                            foreach ($meses as $m) {
                                                                echo '<option value="' . $m['codigo'] . '">' . $m['nom_mes'] . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    if (isset($_GET['mes'])) {
                                    ?>
                                        <table id="dataTable" class="table table-striped table-bordered table-sm nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th class="text-center centro-vertical" rowspan="2"> <br><input id="selectAll" type="checkbox" checked></th>
                                                    <th class="text-center centro-vertical" rowspan="2">No. Doc.</th>
                                                    <th class="text-center centro-vertical" rowspan="2">Nombre Completo</th>
                                                    <th class="text-center centro-vertical" rowspan="2">Observaciones</th>
                                                    <th class="text-center centro-vertical" colspan="5">Dias</th>
                                                    <th class="text-center centro-vertical" rowspan="2">Método Pago</th>
                                                </tr>
                                                <tr>
                                                    <th class="text-center centro-vertical">Lab.</th>
                                                    <th class="text-center centro-vertical">Incap.</th>
                                                    <th class="text-center centro-vertical">Lic.</th>
                                                    <th class="text-center centro-vertical">Vac.</th>
                                                    <th class="text-center centro-vertical">Otros</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <div></div>
                                                <?php
                                                foreach ($obj as $o) {
                                                    $idbusc = $o['id_empleado'];
                                                    $key = array_search($idbusc, array_column($tienevacs, 'id_empleado'));
                                                    $corte = $key !== false ? date('Y-m-d', strtotime($tienevacs[$key]['fec_fin'])) : $o['fech_inicio'];
                                                    $diasLCNR = DiasLicNR($corte, $fec_f, $idbusc);
                                                    $totDiasVac = DiasVac($corte, $fec_f);
                                                    $diasVacacionar = $totDiasVac - $diasLCNR;
                                                    if ($o['estado'] == '1') {
                                                ?>
                                                        <tr id="filaempl">
                                                            <td>
                                                                <div class="center-block listado">
                                                                    <input clase="setAll" type="checkbox" name="check[]" checked value="<?php echo $o['id_empleado'] ?>">
                                                                </div>
                                                            </td>
                                                            <td><?php echo $o['no_documento'] ?></td>
                                                            <td><?php echo mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2'] . ' ' . $o['nombre1'] . ' ' . $o['nombre2']) ?></td>
                                                            <td>
                                                                <?php
                                                                if ($diasVacacionar >= 360) {
                                                                    echo '<span class="badge badge-danger">VACACIONES (+ 360)</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?php
                                                                $d = 0;
                                                                $dIncap = 0;
                                                                $dLic = 0;
                                                                $dLicNR = 0;
                                                                $dLicLuto = 0;
                                                                $dVac = 0;
                                                                $diasIndem = 0;
                                                                $dfin = 0;
                                                                $key = array_search($idbusc, array_column($incapac, 'id_empleado'));
                                                                if (false !== $key) {
                                                                    $filtro = [];
                                                                    $filtro = array_filter($incapac, function ($incapac) use ($idbusc) {
                                                                        return ($incapac['id_empleado'] == $idbusc);
                                                                    });
                                                                    foreach ($filtro as $f) {
                                                                        $dIncap += $f['can_dias'];
                                                                    }
                                                                }
                                                                $key = array_search($idbusc, array_column($licencia, 'id_empleado'));
                                                                if (false !== $key) {
                                                                    $dLic = $licencia[$key]['dias_inactivo'];
                                                                }
                                                                $key = array_search($idbusc, array_column($licencianr, 'id_empleado'));
                                                                if (false !== $key) {
                                                                    $dLicNR = $licencianr[$key]['dias_inactivo'];
                                                                }
                                                                $key = array_search($idbusc, array_column($licluto, 'id_empleado'));
                                                                if (false !== $key) {
                                                                    $dLicLuto = $licluto[$key]['dias_inactivo'];
                                                                }
                                                                $key = array_search($idbusc, array_column($vacacion, 'id_empleado'));
                                                                if (false !== $key) {
                                                                    $dVac = $vacacion[$key]['dias_inactivo'];
                                                                }
                                                                $keyindem = array_search($idbusc, array_column($indemnizaciones, 'id_empleado'));
                                                                if (false !== $keyindem) {
                                                                    $diasIndem = $indemnizaciones[$keyindem]['cant_dias'];
                                                                }
                                                                $date1 = new DateTime($o['fech_inicio']);
                                                                $date2 = new DateTime($fec_i);
                                                                $diff = $date1->diff($date2);
                                                                $dias = $diff->days;
                                                                if ($dias > 0 && $diff->invert == 1 && $dias < 30) {
                                                                    $dias = $dias;
                                                                } else {
                                                                    $dias = 0;
                                                                }
                                                                $diaslabor = 30 - $dIncap - $dLic - $dLicNR - $dLicLuto - $dVac  - $dias;
                                                                if ($o['fec_retiro'] != '') {
                                                                    $date1 = new DateTime($o['fec_retiro']);
                                                                    $date2 = new DateTime($fec_i);
                                                                    $diff = $date1->diff($date2);
                                                                    $dfin = $diff->days;
                                                                    if ($dfin > 0 && $diff->invert == 1 && $dfin < 30) {
                                                                        $dfin = $dfin + 1;
                                                                        if ($mes == '02' && $dfin >= 28) {
                                                                            $diaslabor = 30 - $dIncap - $dLic - $dLicNR - $dLicLuto - $dVac;
                                                                        } else {
                                                                            $diaslabor = $dfin - $dIncap - $dLic - $dLicNR - $dLicLuto - $dVac;
                                                                        }
                                                                    } else if ($dfin == 0) {
                                                                        $diaslabor = 1;
                                                                    }
                                                                }
                                                                if ($dIncap >= 28 && $mes == '02') {
                                                                    $diaslabor = 0;
                                                                }
                                                                if ($dLic + $dLicNR + $dLicLuto >= 28 && $mes == '02') {
                                                                    $diaslabor = 0;
                                                                }
                                                                echo '<div class="diaslab"><input type="number" class="form-control altura" name="numDiaLab_' . $o['id_empleado'] . '" max="' . $diaslabor . '" min= "0" placeholder="1-30" value="' . $diaslabor . '"></div>'
                                                                    . '   <input type="number" name="numSalBas_' . $o['id_empleado'] . '" value="' . $o['salario_basico'] . '" hidden>';
                                                                ?></td>
                                                            <td class="text-center">
                                                                <?php
                                                                echo $dIncap;
                                                                ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php
                                                                echo $dLic + $dLicNR + $dLicLuto;
                                                                ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php
                                                                echo $dVac == '' ? 0 : $dVac;
                                                                ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php
                                                                echo $diasIndem;
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <select class="form-control form-control-sm w-100 altura py-0" name="slcMetPag<?php echo $o['id_empleado'] ?>">
                                                                    <?php
                                                                    foreach ($metpago as $mp) {
                                                                        if ($mp['codigo'] !== '47') {
                                                                            echo '<option value="' . $mp['codigo'] . '">' . $mp['metodo'] . '</option>';
                                                                        } else {
                                                                            echo '<option selected value="' . $mp['codigo'] . '">' . $mp['metodo'] . '</option>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                </form>
                            </div>
                            <div class="center-block py-2">
                                <div class="form-group">
                                    <?php
                                        if (PermisosUsuario($permisos, 5104, 2) || $id_rol == 1) {
                                    ?>
                                        <button class="btn btn-success" id="btnLiqNom">Liquidar nómina</button>
                                    <?php
                                        } ?>
                                    <a type="button" class="btn btn-secondary " href="../../inicio.php"> Cancelar</a>
                                </div>
                            </div>
                        <?php
                                    }
                        ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>