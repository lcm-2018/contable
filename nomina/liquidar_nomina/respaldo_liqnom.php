<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$anio = $_SESSION['vigencia'];
$er = '';
$er .= '
  <div class="table-responsive w-100">
  <table class="table table-striped table-bordered table-sm">
  <thead>
    <tr>
      <th scope="col">Documento</th>
      <th scope="col">Nombre</th>
      <th scope="col">Estado</th>
    </tr>
  </thead>
  <tbody>';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                nom_valxvigencia
            INNER JOIN nom_conceptosxvigencia 
                ON (nom_valxvigencia.id_concepto = nom_conceptosxvigencia.id_concp)
            INNER JOIN tb_vigencias 
                ON (nom_valxvigencia.id_vigencia = tb_vigencias.id_vigencia)
            WHERE anio = '$anio';";
    $rs = $cmd->query($sql);
    $valxvig = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

foreach ($valxvig as $vxv) {
    if ($vxv['id_concepto'] === '1') {
        $smmlv = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] === '2') {
        $auxt = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] === '3') {
        $auxalim = floatval($vxv['valor']);
    }
}
$dia = '01';
$mes = $_POST['slcMesLiqNom'];
$res = '';
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
        exit();
        break;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_he_trab, id_empleado, nom_horas_ex_trab.id_he, fec_inicio, fec_fin, hora_inicio, hora_fin, cantidad_he, codigo, factor
            FROM
                nom_horas_ex_trab
            INNER JOIN nom_tipo_horaex 
                ON (nom_horas_ex_trab.id_he = nom_tipo_horaex.id_he)
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $horas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado FROM nom_empleado
            ORDER BY id_empleado DESC LIMIT 1;";
    $rs = $cmd->query($sql);
    $limite = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_empleado, no_documento, CONCAT(nombre1, ' ', nombre2, ' ',apellido1, ' ', apellido2) AS nombre FROM nom_empleado
                WHERE  estado = '1'";
    $rs = $cmd->query($sql);
    $emple = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * 
            FROM 	
                    (SELECT nom_novedades_arl.id_empleado, nom_novedades_arl.id_arl, nom_novedades_arl.id_riesgo, salario_basico AS salario, cotizacion, nom_novedades_arl.fec_afiliacion
                    FROM
                    nom_novedades_arl
                    INNER JOIN nom_empleado 
                        ON (nom_novedades_arl.id_empleado = nom_empleado.id_empleado)
                    INNER JOIN nom_riesgos_laboral 
                        ON (nom_novedades_arl.id_riesgo = nom_riesgos_laboral.id_rlab)
                    INNER JOIN nom_novedades_arl AS nom_novedades_arl_1 
                        ON (nom_novedades_arl_1.id_empleado = nom_empleado.id_empleado) AND (nom_novedades_arl_1.id_riesgo = nom_riesgos_laboral.id_rlab)
                    INNER JOIN nom_salarios_basico 
                        ON (nom_salarios_basico.id_empleado = nom_empleado.id_empleado)
                    WHERE vigencia = '$anio'
            ORDER BY fec_afiliacion DESC) AS t1
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $riesgos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                id_empleado, COUNT(id_embargo) AS cant_embargos
            FROM 
                (SELECT * 
                FROM
                    nom_embargos
                WHERE estado = '1') AS t 
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $embargos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM nom_incapacidad
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $incapacidades = $rs->fetchAll();
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
    $licencias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM nom_vacaciones
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $vacaciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$dossml = $smmlv * 2;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$max = intval($limite['id_empleado']);
$idemliq = array();
$mesliq = 0;
for ($i = 0; $i <= $max; $i++) {
    if (isset($_POST['check' . $i])) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $sql = "SELECT * FROM nom_liq_salario
            WHERE mes = '$mes' AND anio = '$anio' AND id_empleado = $i";
        $rs = $cmd->query($sql);
        $nomliq = $rs->fetch();
        $cmd = null;
        if (!isset($nomliq['id_empleado'])) {
            $salario = 0;
            $empleado = $i;
            if (isset($_POST['numIdEmplDiaLab_' . $i])) {
                $salbase = $_POST['numSalBas_' . $i];
                $diaslab = $_POST['numDiaLab_' . $i];
                //liquida horas extras 
                $devhe = 0;
                $auxtransp = 0;
                if ($salbase <= $dossml) {
                    $auxtransp = $auxt / 30;
                }
                $valhora = $salbase / 240;
                if (isset($horas)) {
                    foreach ($horas as $h) {
                        if (intval($h['id_empleado']) === intval($i)) {
                            $idhe = $h['id_he_trab'];
                            $valhe = $valhora * $h['cantidad_he'] * (1 + $h['factor'] / 100);
                            try {
                                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                                $sql = "INSERT INTO nom_liq_horex (id_he_lab, val_liq, mes_he, anio_he, fec_reg) VALUES (?, ?, ?, ?, ?)";
                                $sql = $cmd->prepare($sql);
                                $sql->bindParam(1, $idhe, PDO::PARAM_INT);
                                $sql->bindParam(2, $valhe, PDO::PARAM_STR);
                                $sql->bindParam(3, $mes, PDO::PARAM_STR);
                                $sql->bindParam(4, $anio, PDO::PARAM_STR);
                                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                                $sql->execute();
                                if ($cmd->lastInsertId() > 0) {
                                } else {
                                    echo print_r($cmd->errorInfo());
                                }
                                $cmd = null;
                            } catch (PDOException $e) {
                                $res = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                            }
                        }
                    }
                }
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "SELECT id_empleado, SUM(val_liq) AS tot_he
                                FROM 
                                    (SELECT id_empleado, val_liq
                                    FROM
                                        nom_liq_horex
                                    INNER JOIN nom_horas_ex_trab 
                                        ON (nom_liq_horex.id_he_lab = nom_horas_ex_trab.id_he_trab)
                                    WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f' AND id_empleado = '$empleado') AS the
                                GROUP BY id_empleado";
                    $rs = $cmd->query($sql);
                    $tothe = $rs->fetch();
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                if ($tothe) {
                    $devhe = $tothe['tot_he'];
                }
                //liquidar licencia
                $vallic = 0;
                $lic = 0;
                $daylc = 0;
                $saludlc = 0;
                $pensionlc = 0;
                $saludlcem = 0;
                $pensionlcem = 0;
                $vallicen = 0;
                foreach ($licencias as $lc) {
                    if (intval($i) === intval($lc['id_empleado'])) {
                        $diflc = null;
                        $filc = intval(date('Ym', strtotime($lc['fec_inicio'])));
                        $fflc = intval(date('Ym', strtotime($lc['fec_fin'])));
                        $diflc = $fflc - $filc;
                        $idlc = $lc['id_licmp'];
                        $tiplc = $lc['tipo'];
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $sql = "SELECT id_licmp, SUM(dias_liqs) AS tot_dias
                        FROM nom_liq_licmp
                        GROUP BY id_licmp
                        HAVING id_licmp = '$idlc'";
                        $rs = $cmd->query($sql);
                        $diaslic = $rs->fetch();
                        $cmd = null;
                        if (!isset($diaslic)) {
                            $dialic = $diaslic['tot_dias'];
                            $valdialc = $diaslic['val_dialc'];
                        } else {
                            $dialic = 0;
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $sql = "SELECT id_empleado, SUM(cant_dias) AS dias_cot
                            FROM nom_liq_dias_lab
                            GROUP BY id_empleado
                            HAVING id_empleado = '$i'";
                            $rs = $cmd->query($sql);
                            $diascot = $rs->fetch();
                            $cmd = null;
                            if ($tiplc === '1') {
                                if (intval($diascot['dias_cot']) >= 270) {
                                    $valdialc = $salbase / 30;
                                } else {
                                    $valdialc = ($diascot['dias_cot'] * $salbase) / (30 * 270);
                                }
                            } else {
                                $valdialc = $salbase / 30;
                            }
                        }
                        $inlic = $lc['fec_inicio'];
                        $finlic = $lc['fec_fin'];
                        if (intval($diflc) > 0) {
                            $nextday = date("Y-m-d", strtotime($fec_f . "+1 day"));
                            $aperlic = new DateTime($inlic);
                            $cierlic = new DateTime($fec_f);
                            $timelc = $aperlic->diff($cierlic);
                            $daylc = intval($timelc->format('%d')) + 1;
                            $finlic = $fec_f;
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $sql = "UPDATE nom_licenciasmp SET  fec_inicio = ? WHERE id_licmp = ?";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $nextday, PDO::PARAM_STR);
                            $sql->bindParam(2, $idlc, PDO::PARAM_INT);
                            $sql->execute();
                            $cmd = null;
                        } else {
                            $aperlc = new DateTime($inlic);
                            $closelc = new DateTime($finlic);
                            $timelic = $aperlc->diff($closelc);
                            $daylc = intval($timelic->format('%d')) + 1;
                        }
                        $banlc = 0;
                        $feblc = 0;
                        $dayliqlc = $dialic + $daylc;
                        if (intval($daylc) === 31) {
                            $daylc = 30;
                            $banlc = 1;
                        }
                        if ($mes === '02' && intval($daylc) >= 28) {
                            $daylc = 30;
                            $feblc = 1;
                        }
                        $vallic = $valdialc * $daylc;
                        $saludlc = $vallic * 0.04;
                        $pensionlc = $vallic * 0.04;
                        $saludlcem = $vallic * 0.085;
                        $pensionlcem = $vallic * 0.12;
                        $vallicen = $vallic;
                        $lic = 1;
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                            $sql = "INSERT INTO nom_liq_licmp (id_licmp, fec_inicio, fec_fin, dias_liqs, val_liq, val_dialc, mes_lic, anio_lic, fec_reg) VALUES (?, ?, ?, ?, ?,?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idlc, PDO::PARAM_INT);
                            $sql->bindParam(2, $inlic, PDO::PARAM_STR);
                            $sql->bindParam(3, $finlic, PDO::PARAM_STR);
                            $sql->bindParam(4, $daylc, PDO::PARAM_INT);
                            $sql->bindParam(5, $vallicen, PDO::PARAM_STR);
                            $sql->bindParam(6, $valdialc, PDO::PARAM_STR);
                            $sql->bindParam(7, $mes, PDO::PARAM_STR);
                            $sql->bindParam(8, $anio, PDO::PARAM_STR);
                            $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
                            $sql->execute();
                            if ($cmd->lastInsertId() > 0) {
                            } else {
                                echo print_r($cmd->errorInfo());
                            }
                            $cmd = null;
                        } catch (PDOException $e) {
                            $res = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                    }
                }
                //liquidar vacaciones
                $valvac = 0;
                $vac = 0;
                $dayvac = 0;
                $saludvac = 0;
                $pensionvac = 0;
                $saludvacem = 0;
                $pensionvacem = 0;
                $valvacac = 0;
                foreach ($vacaciones as $vs) {
                    if (intval($i) === intval($vs['id_empleado'])) {
                        $difvac = null;
                        $fivac = intval(date('Ym', strtotime($vs['fec_inicio'])));
                        $ffvac = intval(date('Ym', strtotime($vs['fec_fin'])));
                        $difvac = $ffvac - $fivac;
                        $idvac = $vs['id_vac'];
                        $valdiavac = $salbase / 30;
                        $invac = $vs['fec_inicio'];
                        $finvac = $vs['fec_fin'];
                        if (intval($difvac) > 0) {
                            $nextday = date("Y-m-d", strtotime($fec_f . "+1 day"));
                            $apervac = new DateTime($invac);
                            $ciervac = new DateTime($fec_f);
                            $timevac = $apervac->diff($ciervac);
                            $dayvac = intval($timevac->format('%d')) + 1;
                            $finvac = $fec_f;
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $sql = "UPDATE nom_vacaciones SET  fec_inicio = ? WHERE id_vac = ?";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $nextday, PDO::PARAM_STR);
                            $sql->bindParam(2, $idvac, PDO::PARAM_INT);
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                            } else {
                                echo print_r($cmd->errorInfo());
                            }
                            $cmd = null;
                        } else {
                            $apervac = new DateTime($invac);
                            $closevac = new DateTime($finvac);
                            $timevac = $apervac->diff($closevac);
                            $dayvac = intval($timevac->format('%d')) + 1;
                        }
                        $banvac = 0;
                        $febvac = 0;
                        if (intval($dayvac) === 31) {
                            $dayvac = 30;
                            $banvac = 1;
                        }
                        if ($mes === '02' && intval($dayvac) >= 28) {
                            $dayvac = 30;
                            $febvac = 1;
                        }
                        $valvac = $valdiavac * $dayvac;
                        $saludvac = $valvac * 0.04;
                        $pensionvac = $valvac * 0.04;
                        $saludvacem = $valvac * 0.085;
                        $pensionvacem = $valvac * 0.12;
                        $valvacac = $valvac;
                        $vac = 1;
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                            $sql = "INSERT INTO nom_liq_vac (id_vac, fec_inicio, fec_fin, dias_liqs, val_liq, val_diavac, mes_vac, anio_vac, fec_reg) VALUES (?, ?, ?, ?, ?,?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idvac, PDO::PARAM_INT);
                            $sql->bindParam(2, $invac, PDO::PARAM_STR);
                            $sql->bindParam(3, $finvac, PDO::PARAM_STR);
                            $sql->bindParam(4, $dayvac, PDO::PARAM_INT);
                            $sql->bindParam(5, $valvacac, PDO::PARAM_STR);
                            $sql->bindParam(6, $valdiavac, PDO::PARAM_STR);
                            $sql->bindParam(7, $mes, PDO::PARAM_STR);
                            $sql->bindParam(8, $anio, PDO::PARAM_STR);
                            $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
                            $sql->execute();
                            if ($cmd->lastInsertId() > 0) {
                            } else {
                                echo print_r($cmd->errorInfo());
                            }
                            $cmd = null;
                        } catch (PDOException $e) {
                            $res = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                    }
                }
                //liquidar Incapacida
                $valincap = '0';
                $days = '0';
                foreach ($incapacidades as $inc) {
                    if (intval($i) === intval($inc['id_empleado'])) {
                        $dif = null;
                        $fi = intval(date('Ym', strtotime($inc['fec_inicio'])));
                        $ff = intval(date('Ym', strtotime($inc['fec_fin'])));
                        $dif = $ff - $fi;
                        $idinc = $inc['id_incapacidad'];
                        $tipoinc = $inc['id_tipo'];
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $sql = "SELECT id_incapacidad, SUM(dias_liq) AS tot_dias_liq
                        FROM nom_liq_incap
                        GROUP BY id_incapacidad
                        HAVING id_incapacidad = '$idinc'";
                        $rs = $cmd->query($sql);
                        $diasinc = $rs->fetch();
                        $cmd = null;
                        if ($diasinc['tot_dias_liq'] !== '') {
                            $diasliq = $diasinc['tot_dias_liq'];
                        } else {
                            $diasliq = 0;
                        }
                        $inincap = $inc['fec_inicio'];
                        $finincap = $inc['fec_fin'];
                        if (intval($dif) > 0) {
                            $nextday = date("Y-m-d", strtotime($fec_f . "+1 day"));
                            $apertura = new DateTime($inincap);
                            $cierre = new DateTime($fec_f);
                            $tiempod = $apertura->diff($cierre);
                            $days = intval($tiempod->format('%d')) + 1;
                            $finincap = $fec_f;
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $sql = "UPDATE nom_incapacidad SET  fec_inicio = ? WHERE id_incapacidad = ?";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $nextday, PDO::PARAM_STR);
                            $sql->bindParam(2, $idinc, PDO::PARAM_INT);
                            $sql->execute();
                            $cmd = null;
                        } else {
                            $aper = new DateTime($inincap);
                            $close = new DateTime($finincap);
                            $timet = $aper->diff($close);
                            $days = intval($timet->format('%d')) + 1;
                        }
                        $ban = 0;
                        $feb = 0;
                        $dayliq = $diasliq + $days;
                        if (intval($dayliq) <= 180) {
                            if (intval($days) === 31) {
                                $days = 30;
                                $ban = 1;
                            }
                            if ($mes === '02' && intval($days) >= 28) {
                                $days = 30;
                                $feb = 1;
                            }
                            if (intval($tipoinc) === 1) {
                                if (intval($dayliq) <= 90) {
                                    $valdia = floatval($salbase) / 30 * 0.6666;
                                    if (intval($days) >= 2) {
                                        switch (intval($diasliq)) {
                                            case 0:
                                                $pagoempre = $valdia * 2;
                                                $pagoeps = $valdia * (intval($days) - 2);
                                                $pagoarl = '0';
                                                break;
                                            case 1:
                                                $pagoempre = $valdia;
                                                $pagoeps = $valdia * (intval($days) - 1);
                                                $pagoarl = '0';
                                                break;
                                            default:
                                                $pagoempre = '0';
                                                $pagoeps = $valdia * intval($days);
                                                $pagoarl = '0';
                                                break;
                                        }
                                    } else {
                                        $pagoempre = $valdia * intval($days);
                                        $pagoeps = '0';
                                        $pagoarl = '0';
                                    }
                                } else {
                                    $rdia = 90 - intval($diasliq);
                                    $peps = 0;
                                    $pageps = 0;
                                    if (intval($rdia) > 0) {
                                        $valdia = floatval($salbase) / 30 * 0.6666;
                                        $peps = $valdia * $rdia;
                                        if ($ban === 1) {
                                            $rdia = $rdia - 1;
                                        }
                                        $rdiar = $dayliq - 90;
                                        $valdiar = floatval($salbase) / 30 * 0.5;
                                        $pageps = $valdiar * $rdiar;
                                    } else {
                                        $valdia = floatval($salbase) / 30 * 0.5;
                                        $pageps = $valdia * $days;
                                    }
                                    $pagoempre = '0';
                                    $pagoeps = $pageps + $peps;
                                    $pagoarl = '0';
                                }
                            } else {
                                $valordia = floatval($salbase) / 30;
                                $pagoempre = '0';
                                $pagoeps = '0';
                                $pagoarl = $valordia * intval($days);
                            }
                            $fec_final = $fec_f;
                        } else {
                            $rdias = 180 - intval($diasliq);
                            $valdia = floatval($salbase) / 30 * 0.5;
                            if ($ban === 1) {
                                $rdias = $rdias - 1;
                            }
                            if (intval($tipoinc) === 1) {
                                $pagoempre = '0';
                                $pagoeps = $valdia * $rdias;
                                $pagoarl = '0';
                            } else {
                                $valordia = floatval($salbase) / 30;
                                $pagoempre = '0';
                                $pagoeps = '0';
                                $pagoarl = $valordia * $rdias;
                            }
                            $days = $rdias;
                            echo 'Se han liquidado 180 días en total';
                            $fec_final = date("d-m-Y", strtotime($inincap . "+ '$days' day"));
                        }
                        if ($ban === 1) {
                            $days = 31;
                        }
                        if ($feb === 1) {
                            $days = $bis;
                        }
                        if ($dayliq >= $inc['can_dias']) {
                            $fec_final = date('Y-m-d', strtotime($inc['fec_fin']));
                        }
                        $valincap = $pagoempre + $pagoeps + $pagoarl + $valincap;
                        $lic = 1;
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                            $sql = "INSERT INTO nom_liq_incap (id_incapacidad, fec_inicio, fec_fin, dias_liq, pago_empresa, pago_eps, pago_arl, mes, anios, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idinc, PDO::PARAM_INT);
                            $sql->bindParam(2, $inincap, PDO::PARAM_STR);
                            $sql->bindParam(3, $fec_final, PDO::PARAM_STR);
                            $sql->bindParam(4, $days, PDO::PARAM_STR);
                            $sql->bindParam(5, $pagoempre, PDO::PARAM_STR);
                            $sql->bindParam(6, $pagoeps, PDO::PARAM_STR);
                            $sql->bindParam(7, $pagoarl, PDO::PARAM_STR);
                            $sql->bindParam(8, $mes, PDO::PARAM_STR);
                            $sql->bindParam(9, $anio, PDO::PARAM_STR);
                            $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
                            $sql->execute();
                            if ($cmd->lastInsertId() > 0) {
                            } else {
                                echo print_r($cmd->errorInfo());
                            }
                            $cmd = null;
                        } catch (Exception $ex) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                    }
                }
                //liquidación dias laborados
                $diatovaca = $diaslab + $days + $daylc + $dayvac;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO nom_liq_dias_lab (id_empleado, cant_dias, mes, anio, fec_reg) VALUES (?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $empleado, PDO::PARAM_INT);
                    $sql->bindParam(2, $diatovaca, PDO::PARAM_INT);
                    $sql->bindParam(3, $mes, PDO::PARAM_STR);
                    $sql->bindParam(4, $anio, PDO::PARAM_STR);
                    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                    } else {
                        echo print_r($cmd->errorInfo());
                    }
                    $cmd = null;
                } catch (Exception $ex) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                $devtotal = $devhe + $valincap + (($salbase / 30) * $diaslab);
                $pensolid = $devtotal + $vallic + $valvac;
                //liquidar parafiscales
                $liqpfisc = (($salbase / 30) * $diaslab) +  $valvac;
                $sena = $liqpfisc * 0.02;
                $icbf = $liqpfisc * 0.03;
                $comfam = $liqpfisc * 0.04;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO nom_liq_parafiscales (id_empleado, val_sena, val_icbf, val_comfam, mes_pfis, anio_pfis, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $i, PDO::PARAM_INT);
                    $sql->bindParam(2, $sena, PDO::PARAM_STR);
                    $sql->bindParam(3, $icbf, PDO::PARAM_STR);
                    $sql->bindParam(4, $comfam, PDO::PARAM_STR);
                    $sql->bindParam(5, $mes, PDO::PARAM_STR);
                    $sql->bindParam(6, $anio, PDO::PARAM_STR);
                    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                    } else {
                        echo print_r($cmd->errorInfo());
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                //liquida seguridad social
                if ($pensolid >= $smmlv * 4) {
                    $solidpension = $pensolid * 0.01;
                } else {
                    $solidpension = 0;
                }
                $saludempleado = $devtotal * 0.04 + $saludlc + $saludvac;
                $pensionempleado = $devtotal * 0.04 + $pensionlc + $pensionvac;
                $saludempresa = $devtotal * 0.085 + $saludlcem + $saludvacem;
                $pensionempresa = $devtotal * 0.12 + $pensionlcem + $pensionvacem;
                $ibc = (($salbase / 30) * $diaslab) + $devhe;
                foreach ($riesgos as $r) {
                    if (intval($r['id_empleado']) === intval($i)) {
                        $rieslab = $ibc * $r['cotizacion'];
                    }
                }
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO nom_liq_segsocial_empdo (id_empleado, aporte_salud_emp, aporte_pension_emp, aporte_solidaridad_pensional, aporte_salud_empresa, aporte_pension_empresa, aporte_rieslab, mes, anio, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $i, PDO::PARAM_INT);
                    $sql->bindParam(2, $saludempleado, PDO::PARAM_STR);
                    $sql->bindParam(3, $pensionempleado, PDO::PARAM_STR);
                    $sql->bindParam(4, $solidpension, PDO::PARAM_STR);
                    $sql->bindParam(5, $saludempresa, PDO::PARAM_STR);
                    $sql->bindParam(6, $pensionempresa, PDO::PARAM_STR);
                    $sql->bindParam(7, $rieslab, PDO::PARAM_STR);
                    $sql->bindParam(8, $mes, PDO::PARAM_STR);
                    $sql->bindParam(9, $anio, PDO::PARAM_STR);
                    $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                    } else {
                        echo print_r($cmd->errorInfo());
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                //Liaquidar auxilio de transporte y dias laborados
                $valdiaslab = $diaslab * ($salbase / 30);
                $valauxtr = $diaslab * $auxtransp;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO nom_liq_dlab_auxt (id_empleado, dias_liq, val_liq_dias, val_liq_auxt, mes_liq, anio_liq, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $empleado, PDO::PARAM_INT);
                    $sql->bindParam(2, $diaslab, PDO::PARAM_INT);
                    $sql->bindParam(3, $valdiaslab, PDO::PARAM_STR);
                    $sql->bindParam(4, $valauxtr, PDO::PARAM_STR);
                    $sql->bindParam(5, $mes, PDO::PARAM_STR);
                    $sql->bindParam(6, $anio, PDO::PARAM_STR);
                    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                    } else {
                        echo print_r($cmd->errorInfo());
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                //liquidar prestaciones sociales 
                $salpresoc = $pensolid + ($auxtransp * $diaslab);
                $vacacion = $salbase * $diatovaca / 720;
                $cesantia = $salpresoc * $diatovaca / 360;
                $icesant = $cesantia * 0.12;
                $prima = ($salbase + $devhe + ($auxtransp * $diatovaca)) * $diatovaca / 360;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO nom_liq_prestaciones_sociales (id_empleado, val_vacacion, val_cesantia, val_interes_cesantia, val_prima, mes_prestaciones, anio_prestaciones, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $empleado, PDO::PARAM_INT);
                    $sql->bindParam(2, $vacacion, PDO::PARAM_STR);
                    $sql->bindParam(3, $cesantia, PDO::PARAM_STR);
                    $sql->bindParam(4, $icesant, PDO::PARAM_STR);
                    $sql->bindParam(5, $prima, PDO::PARAM_STR);
                    $sql->bindParam(6, $mes, PDO::PARAM_STR);
                    $sql->bindParam(7, $anio, PDO::PARAM_STR);
                    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                    } else {
                        echo print_r($cmd->errorInfo());
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                //liquidar Libranzas
                if ($lic === 0) {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                        $sql = "SELECT *
                                FROM nom_libranzas
                                WHERE estado = '1' AND id_empleado = '$empleado'";
                        $rs = $cmd->query($sql);
                        $libranza = $rs->fetchAll();
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
                $dctolib = 0;
                if (isset($libranza)) {
                    foreach ($libranza as $libranza) {
                        $idlib = $libranza['id_libranza'];
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                            $sql = "SELECT id_libranza, SUM(val_mes_lib) AS pag_lib
                            FROM nom_liq_libranza
                            GROUP BY id_libranza
                            HAVING id_libranza = '$idlib'";
                            $rs = $cmd->query($sql);
                            $pagolib = $rs->fetch();
                            $cmd = null;
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                        if ($pagolib['pag_lib'] !== '') {
                            $abonadolib = floatval($pagolib['pag_lib']);
                            $saldo = floatval($libranza['valor_total']) - $abonadolib;
                        } else {
                            $abonadolib = 0;
                            $saldo = floatval($libranza['valor_total']);
                        }
                        $ablib = floatval($salbase * $libranza['porcentaje']);
                        if ($saldo > $ablib) {
                            $abonolib = $ablib;
                        } else {
                            $abonolib = $saldo;
                            desactivarlib($idlib, $date);
                        }
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                            $sql = "INSERT INTO nom_liq_libranza (id_libranza, val_mes_lib, mes_lib, anio_lib, fec_reg) VALUES (?, ?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idlib, PDO::PARAM_INT);
                            $sql->bindParam(2, $abonolib, PDO::PARAM_STR);
                            $sql->bindParam(3, $mes, PDO::PARAM_STR);
                            $sql->bindParam(4, $anio, PDO::PARAM_STR);
                            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                            $sql->execute();
                            $cmd = null;
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                        $dctolib = $dctolib + $abonolib;
                    }
                }
                //liquidar Embargos
                if ($lic === 0) {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                        $sql = "SELECT * 
                                FROM nom_embargos
                                WHERE id_empleado = '$empleado' AND estado = '1'";
                        $rs = $cmd->query($sql);
                        $tienembg = $rs->fetchAll();
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
                $dctoemb = 0;
                if (isset($tienembg)) {
                    foreach ($embargos as $e) {
                        $idemplea = $e['id_empleado'];
                        if (intval($idemplea) === intval($i)) {
                            $cantemb = intval($e['cant_embargos']);
                            try {
                                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                                $sql = "SELECT *
                                FROM nom_embargos
                                WHERE estado = '1' AND id_empleado = '$empleado'
                                ORDER BY tipo_embargo DESC LIMIT 1";
                                $rx = $cmd->query($sql);
                                $cmd = null;
                            } catch (PDOException $e) {
                                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                            }
                            $detembargos = $rx->fetch();
                            $idembargo = $detembargos['id_embargo'];
                            if ($idembargo !== '') {
                                try {
                                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                                    $sql = "SELECT id_embargo, SUM(val_mes_embargo) AS tot_pagado_embargo
                                    FROM nom_liq_embargo
                                    WHERE id_embargo = '$idembargo'";
                                    $rs = $cmd->query($sql);
                                    $resemb = $rs->fetch();
                                    $cmd = null;
                                } catch (PDOException $e) {
                                    $res = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                                }
                                $caso = intval($detembargos['tipo_embargo']);
                                $dctoemb = '0';
                                if ($caso === 1 || $caso === 2) {
                                    if (isset($resemb)) {
                                        $abonado = $resemb['tot_pagado_embargo'];
                                    } else {
                                        $abonado = 0;
                                    }
                                    $saldo = $detembargos['valor_total'] - $abonado;
                                    if ($saldo > 0) {
                                        if ($saldo > $salbase * $detembargos['porcentaje']) {
                                            $dctoemb = $salbase * $detembargos['porcentaje'];
                                        } else {
                                            $dctoemb = $saldo;
                                            desactivaremb($idembargo, $date);
                                        }
                                    } else {
                                        desactivaremb($idembargo, $date);
                                    }
                                } else {
                                    $fecliq = $anio . '-' . $mes;
                                    try {
                                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                                        $sql = "SELECT *
                                        FROM
                                        (SELECT *,  SUBSTRING(fec_fin,1,7) AS comparar
                                        FROM
                                        nom_embargos
                                        WHERE estado = '1' AND tipo_embargo = '3' AND id_empleado = '$empleado') AS t
                                        WHERE comparar >= '$fecliq'";
                                        $rs = $cmd->query($sql);
                                        $finalim = $rs->fetch();
                                        if ($finalim) {
                                            $dctoemb = $salbase * $detembargos['porcentaje'];
                                            if ($fecliq === $finalim['comparar']) {
                                                desactivaremb($idembargo, $date);
                                            }
                                        } else {
                                            $dctoemb = '0';
                                            desactivaremb($idembargo, $date);
                                        }
                                        $cmd = null;
                                    } catch (PDOException $e) {
                                        $res = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                                    }
                                }
                                if ($dctoemb !== '0') {
                                    try {
                                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                                        $sql = "INSERT INTO nom_liq_embargo (id_embargo, val_mes_embargo, mes_embargo, anio_embargo, fec_reg) VALUES (?, ?, ?, ?, ?)";
                                        $sql = $cmd->prepare($sql);
                                        $sql->bindParam(1, $idembargo, PDO::PARAM_INT);
                                        $sql->bindParam(2, $dctoemb, PDO::PARAM_STR);
                                        $sql->bindParam(3, $mes, PDO::PARAM_STR);
                                        $sql->bindParam(4, $anio, PDO::PARAM_STR);
                                        $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                                        $sql->execute();
                                        $cmd = null;
                                    } catch (PDOException $e) {
                                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                                    }
                                }
                            }
                        }
                    }
                }
                //liquidar cuota sindical
                if ($lic === 0) {
                    $idcuotsind = $_POST['numIdCuotaSind_' . $i];
                    $valcuotsind = $devtotal * floatval($_POST['txtPorcCuotaSind_' . $i]);
                } else {
                    $idcuotsind = '0';
                    $valcuotsind = 0;
                }
                if ($idcuotsind !== '0') {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                        $sql = "INSERT INTO nom_liq_sindicato_aportes (id_cuota_sindical, val_aporte, mes_aporte, anio_aporte, fec_reg) VALUES (?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $idcuotsind, PDO::PARAM_INT);
                        $sql->bindParam(2, $valcuotsind, PDO::PARAM_STR);
                        $sql->bindParam(3, $mes, PDO::PARAM_STR);
                        $sql->bindParam(4, $anio, PDO::PARAM_STR);
                        $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                        $sql->execute();
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
                //neto a pagar
                $salarioneto = $devhe + (($salbase / 30) * $diaslab) + ($auxtransp * $diaslab) + $vallic + $valvac + $valincap - $saludempleado - $pensionempleado - $solidpension - $valcuotsind - $dctoemb - $dctolib;
                //echo 'HoEX: '.$devhe.' Base: '.(($salbase / 30) * $diaslab).' AuxT: '.($auxtransp * $diaslab).' ValLic: '.$vallic .' ValVac: '. $valvac.' ValIncap: '. $valincap .' ValSalud: '.$saludempleado .' ValPension: '. $pensionempleado .' ValSolidaria: '. $solidpension .' ValSindic: '. $valcuotsind .' ValEmbar: '. $dctoemb .' ValLib: '. $dctolib;
                $fpag = '1';
                $mpag = $_POST['slcMetPag' . $i];
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO nom_liq_salario (id_empleado, val_liq, forma_pago, metodo_pago, mes, anio, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $i, PDO::PARAM_INT);
                    $sql->bindParam(2, $salarioneto, PDO::PARAM_STR);
                    $sql->bindParam(3, $fpag, PDO::PARAM_STR);
                    $sql->bindParam(4, $mpag, PDO::PARAM_STR);
                    $sql->bindParam(5, $mes, PDO::PARAM_STR);
                    $sql->bindParam(6, $anio, PDO::PARAM_STR);
                    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                $key = array_search($i, array_column($emple, 'id_empleado'));
                if (false !== $key) {
                    $cc = $emple[$key]['no_documento'];
                    $nomempleado = $emple[$key]['nombre'];
                } else {
                    $cc = 'aaa';
                    $nomempleado = 'sss';
                }
                $er .= '<tr>'
                    . '<td>' . $cc . '</td>'
                    . '<td>' . mb_strtoupper($nomempleado) . '</td>'
                    . '<td>Mes liquidado</td>'
                    . '</tr>';
                $mesliq++;
            }
        }
    }
}
$er .= '</tbody>
</table> 
<center><a class="btn btn-link" href="detalles_nomina.php?mes=' . $mes . '" >Detalles Nómina</a></center></div>';
if ($mesliq === 0) {
    echo '0';
} else {
    echo $er;
}
function desactivaremb($a, $d)
{
    $idembargo = $a;
    $date = $d;
    $estado = '0';
    try {
        include '../../conexion.php';
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "UPDATE nom_embargos SET estado = ? WHERE id_embargo = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_STR);
        $sql->bindParam(2, $idembargo, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "UPDATE nom_embargos SET  fec_act = ? WHERE id_embargo = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(2, $idembargo, PDO::PARAM_INT);
            $sql->execute();
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function desactivarlib($a, $d)
{
    $idlibr = $a;
    $date = $d;
    $estado = '0';
    try {
        include '../../conexion.php';
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "UPDATE nom_libranzas SET estado = ? WHERE id_libranza = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_STR);
        $sql->bindParam(2, $idlibr, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "UPDATE nom_libranzas SET  fec_act = ? WHERE id_libranza = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(2, $idlibr, PDO::PARAM_INT);
            $sql->execute();
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
