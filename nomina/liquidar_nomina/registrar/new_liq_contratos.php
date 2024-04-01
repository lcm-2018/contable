<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = " SELECT id_empleado, id_contrato, SUM(cant_dias) AS tot_dias FROM 
                (SELECT id_empleado, id_contrato, cant_dias FROM nom_liq_dias_lab WHERE anio = '$vigencia') AS t
            GROUP BY id_contrato";
    $rs = $cmd->query($sql);
    $diaslab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, vigencia, salario_basico
            FROM nom_salarios_basico
            WHERE  vigencia = '$vigencia'";
    $rs = $cmd->query($sql);
    $salarios = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, vigencia, salario_basico
            FROM nom_salarios_basico
            WHERE  vigencia = '$vigencia'";
    $rs = $cmd->query($sql);
    $salarios = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
            FROM
                nom_valxvigencia
            INNER JOIN nom_conceptosxvigencia 
                ON (nom_valxvigencia.id_concepto = nom_conceptosxvigencia.id_concp)
            INNER JOIN tb_vigencias 
                ON (nom_valxvigencia.id_vigencia = tb_vigencias.id_vigencia)
            WHERE anio = '$vigencia';";
    $rs = $cmd->query($sql);
    $valxvig = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

foreach ($valxvig as $vxv) {
    if ($vxv['id_concepto'] === '1') {
        $smmlv = $vxv['valor'];
    }
    if ($vxv['id_concepto'] === '2') {
        $auxt = $vxv['valor'];
    }
}
$dossmmlv = $smmlv * 2;
$iduser = $_SESSION['id_user'];
$estado = 1;
$cont = 0;
if (isset($_REQUEST['check'])) {
    $list_contratos = $_REQUEST['check'];
    foreach ($list_contratos as $lc) {
        $key = array_search($lc, array_column($diaslab, 'id_contrato'));
        if (false !== $key) {
            $tdiaslab = $diaslab[$key]['tot_dias'];
            $id_emp = $diaslab[$key]['id_empleado'];
            $key = array_search($id_emp, array_column($salarios, 'id_empleado'));
            if (false !== $key) {
                $salbase = $salarios[$key]['salario_basico'];
                if ($salbase <=  $dossmmlv) {
                    $auxtransp = $auxt;
                } else {
                    $auxtransp = 0;
                }
                $cesantias = ($salbase + $auxtransp) * $tdiaslab / 360;
                $icesantias = $cesantias * $tdiaslab * 0.12 / 360;
                $vacaciones = $salbase * $tdiaslab / 720;
                $prima = ($salbase + $auxtransp) * $tdiaslab / 360;
                $tdiasvac = ($tdiaslab * 15) / 360;
                $date = new DateTime('now', new DateTimeZone('America/Bogota'));
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO nom_liq_contrato_emp (id_contrato, sal_base, aux_transp, val_cesantias, val_icesantias, val_vacaciones, val_prima, tot_dias_lab, tot_dias_vac, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $lc, PDO::PARAM_INT);
                    $sql->bindParam(2, $salbase, PDO::PARAM_STR);
                    $sql->bindParam(3, $auxtransp, PDO::PARAM_STR);
                    $sql->bindParam(4, $cesantias, PDO::PARAM_STR);
                    $sql->bindParam(5, $icesantias, PDO::PARAM_STR);
                    $sql->bindParam(6, $vacaciones, PDO::PARAM_STR);
                    $sql->bindParam(7, $prima, PDO::PARAM_STR);
                    $sql->bindParam(8, $tdiaslab, PDO::PARAM_STR);
                    $sql->bindParam(9, $tdiasvac, PDO::PARAM_STR);
                    $sql->bindParam(10, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(11, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                            $sql = "UPDATE nom_contratos_empleados SET estado = ?, id_user_act = ?, fec_act = ? WHERE id_contrato_emp = ?";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $estado, PDO::PARAM_INT);
                            $sql->bindParam(2, $iduser, PDO::PARAM_INT);
                            $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
                            $sql->bindParam(4, $lc, PDO::PARAM_INT);
                            $sql->execute();
                            $cmd = null;
                            $cont++;
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            } else {
                echo 'Error, No existe salario';
            }
        } else {
            echo 'Error en datos de contrato: ' . $diaslab[$key]['id_contrato'];
        }
    }
} else {
    echo 'No se ha seleccionado ninugún contrato';
}
if ($cont > 0) {
    echo '1';
}
