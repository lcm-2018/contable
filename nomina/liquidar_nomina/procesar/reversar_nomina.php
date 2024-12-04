<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_nomina = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `tipo` FROM  `nom_nominas` WHERE (`id_nomina` = $id_nomina) LIMIT 1";
    $rs = $cmd->query($sql);
    $nomina = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_vac` FROM `nom_liq_vac` WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $idVacs = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ids = [];
if (!empty($idVacs)) {
    foreach ($idVacs as $iv) {
        $ids[] = $iv['id_vac'] != '' ? $iv['id_vac'] : 0;
    }
}
$idVacs = implode(',', $ids);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_indemnizacion` FROM `nom_liq_indemniza_vac` WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $idIndemVac = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ids = [];
if (!empty($idIndemVac)) {
    foreach ($idIndemVac as $iv) {
        $ids[] = $iv['id_indemnizacion'] != '' ? $iv['id_indemnizacion'] : 0;
    }
}
$id_inom = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `nom_liq_compesatorio` SET `estado` = 1, `id_nomina` = 0 WHERE `id_nomina` = $id_nomina";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_he_lab` FROM `nom_liq_horex` WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $horas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $idhe = [];
    foreach ($horas as $h) {
        $idhe[] = $h['id_he_lab'] != '' ? $h['id_he_lab'] : 0;
    }
    if (!empty($idhe)) {
        $idhe = implode(',', $idhe);
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            if ($nomina['tipo'] == 'N') {
                $estado = 1;
            } else {
                $estado = 2;
            }
            $sql = "UPDATE `nom_horas_ex_trab` SET `tipo` = $estado WHERE `id_he_trab` IN ($idhe)";
            $sql = $cmd->prepare($sql);
            $sql->execute();
            if (!($sql->rowCount() > 0)) {
                echo $sql->errorInfo()[2];
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$idsIndemVac = implode(',', $ids);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `nom_nominas`  WHERE `id_nomina` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_nomina, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        if (!empty($idVacs)) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                if ($nomina['tipo'] == 'N' || $nomina['tipo'] == 'VC') {
                    $sql = "UPDATE `nom_vacaciones` SET `estado` = '1' WHERE `id_vac` IN ($idVacs)";
                } else {
                    $sql = "DELETE FROM `nom_vacaciones` WHERE `id_vac` IN ($idVacs)";
                }
                if ($nomina['tipo'] != 'RA') {
                    $sql = $cmd->prepare($sql);
                    $sql->execute();
                    if (!($sql->rowCount() > 0)) {
                        echo $sql->errorInfo()[2];
                    }
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        if (!empty($idsIndemVac)) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `nom_indemniza_vac` SET `estado` = '1' WHERE `id_indemniza` IN ($idsIndemVac)";
                $sql = $cmd->prepare($sql);
                $sql->execute();
                if (!($sql->rowCount() > 0)) {
                    echo $sql->errorInfo()[2];
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "SELECT nombre, IFNULL(id,0) AS id  FROM 
                        (SELECT 'nom_nominas' AS nombre, MAX(IFNULL(id_nomina,0)) AS id FROM nom_nominas
                        UNION
                        SELECT 'nom_liq_bsp' AS nombre, MAX(IFNULL(id_bonificaciones,0)) AS id FROM nom_liq_bsp
                        UNION
                        SELECT 'nom_liq_horex' AS nombre, MAX(IFNULL(id_liq_he,0)) AS id FROM nom_liq_horex
                        UNION
                        SELECT 'nom_liq_licmp' AS nombre, MAX(IFNULL(id_liqlicmp,0)) AS id FROM nom_liq_licmp
                        UNION
                        SELECT 'nom_liq_licnr' AS nombre, MAX(IFNULL(id_liqlicnr,0)) AS id FROM nom_liq_licnr
                        UNION
                        SELECT 'nom_vacaciones' AS nombre, MAX(IFNULL(id_vac,0)) AS id FROM nom_vacaciones
                        UNION
                        SELECT 'nom_liq_vac' AS nombre, MAX(IFNULL(id_liq_vac,0)) AS id FROM nom_liq_vac
                        UNION
                        SELECT 'nom_liq_incap' AS nombre, MAX(IFNULL(id_liq_incap,0)) AS id FROM nom_liq_incap
                        UNION
                        SELECT 'nom_liq_dias_lab' AS nombre, MAX(IFNULL(id_diatrab,0)) AS id FROM nom_liq_dias_lab
                        UNION
                        SELECT 'nom_liq_parafiscales' AS nombre, MAX(IFNULL(id_liq_pfis,0)) AS id FROM nom_liq_parafiscales
                        UNION
                        SELECT 'nom_liq_segsocial_empdo' AS nombre, MAX(IFNULL(id_liq_empdo,0)) AS id FROM nom_liq_segsocial_empdo
                        UNION
                        SELECT 'nom_liq_dlab_auxt' AS nombre, MAX(IFNULL(id_liq_dlab_auxt,0)) AS id FROM nom_liq_dlab_auxt
                        UNION
                        SELECT 'nom_liq_prestaciones_sociales' AS nombre, MAX(IFNULL(id_liqpresoc,0)) AS id FROM nom_liq_prestaciones_sociales
                        UNION
                        SELECT 'nom_liq_libranza' AS nombre, MAX(IFNULL(id_lid_lib,0)) AS id FROM nom_liq_libranza
                        UNION
                        SELECT 'nom_liq_embargo' AS nombre, MAX(IFNULL(id_liq_embargo,0)) AS id FROM nom_liq_embargo
                        UNION
                        SELECT 'nom_liq_sindicato_aportes' AS nombre, MAX(IFNULL(id_aporte,0)) AS id FROM nom_liq_sindicato_aportes
                        UNION
                        SELECT 'nom_retencion_fte' AS nombre, MAX(IFNULL(id_rte_fte,0)) AS id FROM nom_retencion_fte
                        UNION
                        SELECT 'nom_liq_salario' AS nombre, MAX(IFNULL(id_sal_liq,0)) AS id FROM nom_liq_salario
                        UNION
                        SELECT 'nom_liq_indemniza_vac' AS nombre, MAX(IFNULL(id_indemnizacion,0)) AS id FROM nom_liq_indemniza_vac
                        UNION
                        SELECT 'nom_liq_prima' AS nombre, MAX(IFNULL(id_liq_prima,0)) AS id FROM nom_liq_prima
                        UNION
                        SELECT 'nom_empleados_retirados' AS nombre, MAX(IFNULL(id_liq,0)) AS id FROM nom_empleados_retirados
                        UNION
                        SELECT 'nom_liq_prima_nav' AS nombre, MAX(IFNULL(id_liq_privac,0)) AS id FROM nom_liq_prima_nav) AS t";
            $rs = $cmd->query($sql);
            $incremento = $rs->fetchAll(PDO::FETCH_ASSOC);
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        foreach ($incremento as $t) {
            $nombre  = $t['nombre'];
            $consecutivo = $t['id'] + 1;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "ALTER TABLE `$nombre` AUTO_INCREMENT = $consecutivo";
                $sql = $cmd->prepare($sql);
                $sql->execute();
                if (!($sql->rowCount() > 0)) {
                    echo $sql->errorInfo()[2];
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        echo '1';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
