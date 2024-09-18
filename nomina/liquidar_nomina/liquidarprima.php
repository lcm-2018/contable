<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
$anio = $_SESSION['vigencia'];
$mes = isset($_POST['slcMesLiqNom']) ? $_POST['slcMesLiqNom'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, salario_integral, no_documento, CONCAT(nombre1, ' ', nombre2, ' ', apellido1, ' ', apellido2) as nombre 
            FROM nom_empleado";
    $rs = $cmd->query($sql);
    $empleado = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($mes === '06') {
    $ini = '01';
    $fin = '05';
    $perido = '1';
} else {
    $ini = '07';
    $fin = '11';
    $perido = '2';
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, CONCAT(anio, periodo) AS periodo
            FROM nom_liq_prima
            WHERE anio = '$anio' AND periodo = '$perido'";
    $rs = $cmd->query($sql);
    $primliq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, SUM(cant_dias) AS diasxsem
            FROM
                (SELECT id_empleado, cant_dias, mes, anio
                FROM nom_liq_dias_lab
                WHERE mes BETWEEN '$ini' AND '$fin') AS t
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $diaslab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, COUNT(val_liq) AS cant_pagos
                FROM 
                (SELECT id_empleado,val_liq
                FROM nom_liq_salario
                WHERE mes BETWEEN '$ini' AND '$fin' AND anio = '$anio') AS t
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $cantpag = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT anio, id_concepto, valor
            FROM
                nom_valxvigencia
            INNER JOIN tb_vigencias 
                ON (nom_valxvigencia.id_vigencia = tb_vigencias.id_vigencia)
            WHERE anio = '$anio'";
    $rs = $cmd->query($sql);
    $valxvig = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, SUM(val_liq) AS tot_he
            FROM 
                (SELECT id_empleado, val_liq, mes_he, anio_he
                FROM
                    nom_liq_horex
                    INNER JOIN nom_horas_ex_trab 
                        ON (nom_liq_horex.id_he_lab = nom_horas_ex_trab.id_he_trab)
                WHERE mes_he BETWEEN '$ini' AND '$fin' AND anio_he = '$anio') AS t
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $hoex = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
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
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$liquidados = 0;
if (isset($_REQUEST['check'])) {
    $list_liquidar = $_REQUEST['check'];
    foreach ($list_liquidar as $i) {
        $key = array_search($i, array_column($empleado, 'id_empleado'));
        if (false !== $key) {
            $sal_integ = $empleado[$key]['salario_integral'];
        } else {
            $sal_integ = null;
        }
        if ($sal_integ != 1) {
            $key = array_search($i, array_column($primliq, 'id_empleado'));
            if (false !== $key) {
            } else {
                $salbase = $_POST['numSalBas_' . $i];
                $dlab = $_POST['numDiaLab_' . $i];
                $key = array_search($i, array_column($diaslab, 'id_empleado'));
                if (false !== $key) {
                    $diaxsem = $diaslab[$key]['diasxsem'];
                } else {
                    $diaxsem = 0;
                }
                $key = array_search('1', array_column($valxvig, 'id_concepto'));
                if (false !== $key) {
                    $smmlv = $valxvig[$key]['valor'];
                }
                $key = array_search('2', array_column($valxvig, 'id_concepto'));
                if (false !== $key) {
                    $auxtrans = $valxvig[$key]['valor'];
                }
                if ($salbase >= $smmlv * 2) {
                    $auxtrans = 0;
                }
                $key = array_search($i, array_column($cantpag, 'id_empleado'));
                if (false !== $key) {
                    $totpagos = $cantpag[$key]['cant_pagos'];
                } else {
                    $totpagos = '1';
                }
                $key = array_search($i, array_column($hoex, 'id_empleado'));
                if (false !== $key) {
                    $tothoex = $hoex[$key]['tot_he'];
                } else {
                    $tothoex = '0';
                }
                $promhe = $tothoex / $totpagos;
                $totdias = $diaxsem + $dlab;
                $prima = $totdias * ($salbase + $promhe + $auxtrans) / 360;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO nom_liq_prima (id_empleado, cant_dias, val_liq_ps, periodo, anio, fec_reg) VALUES (?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $i, PDO::PARAM_INT);
                    $sql->bindParam(2, $totdias, PDO::PARAM_STR);
                    $sql->bindParam(3, $prima, PDO::PARAM_STR);
                    $sql->bindParam(4, $perido, PDO::PARAM_STR);
                    $sql->bindParam(5, $anio, PDO::PARAM_STR);
                    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    $key = array_search($i, array_column($empleado, 'id_empleado'));
                    if (false !== $key) {
                        $cc = $empleado[$key]['no_documento'];
                        $nombre = $empleado[$key]['nombre'];
                    } else {
                        $cc = '';
                        $nombre = '';
                    }
                    $er .= '<tr>'
                        . '<td>' . $cc . '</td>'
                        . '<td>' . mb_strtoupper($nombre) . '</td>';
                    if ($cmd->lastInsertId() > 0) {
                        $liquidados++;
                        $er .= '<td>Liquidado</td>';
                    } else {
                        $er .=  '<td>' . print_r($cmd->errorInfo()) . '</td>';
                    }
                    $er .= '</tr>';
                    $cmd = null;
                } catch (PDOException $e) {
                    $res = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            }
        }
    }
}
$er .= '</tbody>
        </table> 
        <center><a id="btnDetallesLiqs" class="btn btn-link" href="detalles_prima.php?per=' . $perido . '">Detalles</a></center>';
if ($liquidados == 0) {
    echo '0';
} else {
    echo $er;
}
