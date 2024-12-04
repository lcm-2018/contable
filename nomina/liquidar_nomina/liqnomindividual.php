<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
$anio = $_SESSION['vigencia'];
$mes = isset($_POST['slcMesLiqNomEmp']) ? $_POST['slcMesLiqNomEmp'] : exit('Acción no permitida');
$dia = '01';
$id = $_POST['slcLiqEmpleado'];
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
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_liq_salario
            WHERE mes = '$mes' AND anio = '$anio' AND id_empleado = $id";
    $rs = $cmd->query($sql);
    $nomliq = $rs->fetch();
    $cmd = null;
    if (!isset($nomliq['id_empleado'])) {
        //Embargo
        if (isset($_POST['slcEmbargos'])) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_liq_embargo (id_embargo, val_mes_embargo, mes_embargo, anio_embargo, fec_reg) VALUES (?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $idembargo, PDO::PARAM_INT);
                $sql->bindParam(2, $dctoemb, PDO::PARAM_STR);
                $sql->bindParam(3, $mes, PDO::PARAM_STR);
                $sql->bindParam(4, $anio, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $dctoemb = str_replace(',', '', $_POST['numDeduccionesEmb']);
                $idembargo = $_POST['slcEmbargos'];
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    print_r($cmd->errorInfo()[2]);
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        //Seguridad social
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO nom_liq_segsocial_empdo (id_empleado, aporte_salud_emp, aporte_pension_emp, aporte_solidaridad_pensional, aporte_salud_empresa, aporte_pension_empresa, aporte_rieslab, mes, anio, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $saludempleado, PDO::PARAM_STR);
            $sql->bindParam(3, $pensionempleado, PDO::PARAM_STR);
            $sql->bindParam(4, $solidpension, PDO::PARAM_STR);
            $sql->bindParam(5, $saludempresa, PDO::PARAM_STR);
            $sql->bindParam(6, $pensionempresa, PDO::PARAM_STR);
            $sql->bindParam(7, $rieslab, PDO::PARAM_STR);
            $sql->bindParam(8, $mes, PDO::PARAM_STR);
            $sql->bindParam(9, $anio, PDO::PARAM_STR);
            $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
            $saludempleado = str_replace(',', '', $_POST['numAportSalud']);
            $pensionempleado = str_replace(',', '', $_POST['numAportPension']);
            if (!isset($_POST['numAportPenSolid'])) {
                $solidpension =  str_replace(',', '', $_POST['numAportPenSolid']);
            } else {
                $solidpension = '0';
            }
            $saludempresa = str_replace(',', '', $_POST['numProvSalud']);
            $pensionempresa = str_replace(',', '', $_POST['numProvPension']);
            $rieslab = str_replace(',', '', $_POST['numProvARL']);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                print_r($cmd->errorInfo()[2]);
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //Prestaciones Sociales
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO nom_liq_prestaciones_sociales (id_empleado, val_vacacion, val_cesantia, val_interes_cesantia, val_prima, mes_prestaciones, anio_prestaciones, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $vacacion, PDO::PARAM_STR);
            $sql->bindParam(3, $cesantia, PDO::PARAM_STR);
            $sql->bindParam(4, $icesant, PDO::PARAM_STR);
            $sql->bindParam(5, $prima, PDO::PARAM_STR);
            $sql->bindParam(6, $mes, PDO::PARAM_STR);
            $sql->bindParam(7, $anio, PDO::PARAM_STR);
            $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
            $vacacion = str_replace(',', '', $_POST['numProvVac']);
            $cesantia = str_replace(',', '', $_POST['numProvCesan']);
            $icesant = str_replace(',', '', $_POST['numProvIntCesan']);
            $prima = str_replace(',', '', $_POST['numProvPrima']);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                print_r($cmd->errorInfo()[2]);
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //Parafiscales
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO nom_liq_parafiscales (id_empleado, val_sena, val_icbf, val_comfam, mes_pfis, anio_pfis, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $sena, PDO::PARAM_STR);
            $sql->bindParam(3, $icbf, PDO::PARAM_STR);
            $sql->bindParam(4, $comfam, PDO::PARAM_STR);
            $sql->bindParam(5, $mes, PDO::PARAM_STR);
            $sql->bindParam(6, $anio, PDO::PARAM_STR);
            $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
            $sena = str_replace(',', '', $_POST['numProvSENA']);
            $icbf = str_replace(',', '', $_POST['numProvICBF']);
            $comfam = str_replace(',', '', $_POST['numProvCOMFAM']);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                print_r($cmd->errorInfo()[2]);
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //Libranza
        if (isset($_POST['slcLibranzas'])) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_liq_libranza (id_libranza, val_mes_lib, mes_lib, anio_lib, fec_reg) VALUES (?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $idlib, PDO::PARAM_INT);
                $sql->bindParam(2, $dctolib, PDO::PARAM_STR);
                $sql->bindParam(3, $mes, PDO::PARAM_STR);
                $sql->bindParam(4, $anio, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $dctolib = str_replace(',', '', $_POST['numDeduccionesLib']);
                $idlib = $_POST['slcLibranzas'];
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    print_r($cmd->errorInfo()[2]);
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        //Sindicato

        if (isset($_POST['slcSindicato'])) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_liq_sindicato_aportes (id_cuota_sindical, val_aporte, mes_aporte, anio_aporte, fec_reg) VALUES (?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $idcuotsind, PDO::PARAM_INT);
                $sql->bindParam(2, $dctosind, PDO::PARAM_STR);
                $sql->bindParam(3, $mes, PDO::PARAM_STR);
                $sql->bindParam(4, $anio, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $dctosind = str_replace(',', '', $_POST['numDeduccionesSind']);
                $idcuotsind = $_POST['slcSindicato'];
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    print_r($cmd->errorInfo()[2]);
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        //Auxilio de transporte
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO nom_liq_dlab_auxt (id_empleado, dias_liq, val_liq_dias, val_liq_auxt, mes_liq, anio_liq, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $diaslab, PDO::PARAM_INT);
            $sql->bindParam(3, $valdiaslab, PDO::PARAM_STR);
            $sql->bindParam(4, $valauxtr, PDO::PARAM_STR);
            $sql->bindParam(5, $mes, PDO::PARAM_STR);
            $sql->bindParam(6, $anio, PDO::PARAM_STR);
            $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
            $diaslab = $_POST['numDiasLab'];
            $valdiaslab = str_replace(',', '', $_POST['numValDiasLab']);
            if (!isset($_POST['numAuxTransp'])) {
                $valauxtr = str_replace(',', '', $_POST['numAuxTransp']);
            } else {
                $valauxtr = '0';
            }
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                print_r($cmd->errorInfo()[2]);
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //Incapacidades
        if (isset($_POST['slcIncapacidad'])) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
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
                $idinc = $_POST['slcIncapacidad'];
                $inincap = $_POST['datFecInicioInc'];
                $fec_final = $_POST['datFecFinInc'];
                $days = $_POST['numDiasIncap'];
                $pagoempre = str_replace(',', '', $_POST['numValIncapEmpresa']);
                $pagoeps = str_replace(',', '', $_POST['numValIncapEPS']);
                $pagoarl = str_replace(',', '', $_POST['numValIncapARL']);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    print_r($cmd->errorInfo()[2]);
                }
                $cmd = null;
            } catch (Exception $ex) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        //Vacaciones
        if (isset($_POST['slcVacaciones'])) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
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
                $idvac = $_POST['slcVacaciones'];
                $invac = $_POST['datFecInicioVacs'];
                $finvac = $_POST['datFecFinVacs'];
                $dayvac = $_POST['numDiasVac'];
                $valvacac = str_replace(',', '', $_POST['numValVac']);
                $valdiavac = $valvacac / $dayvac;
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    print_r($cmd->errorInfo()[2]);
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        //Licencia
        if (isset($_POST['slcLicencias'])) {
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
                $idlc = $_POST['slcLicencias'];
                $inlic = $_POST['datFecInicioLics'];
                $finlic = $_POST['datFecFinLics'];
                $daylc = $_POST['numDiasLic'];
                $vallicen = str_replace(',', '', $_POST['numValLica']);
                $valdialc = $vallicen / $daylc;
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    print_r($cmd->errorInfo()[2]);
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        //Salario
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO nom_liq_salario (id_empleado, val_liq, forma_pago, metodo_pago, mes, anio, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $salarioneto, PDO::PARAM_STR);
            $sql->bindParam(3, $fpag, PDO::PARAM_STR);
            $sql->bindParam(4, $mpag, PDO::PARAM_STR);
            $sql->bindParam(5, $mes, PDO::PARAM_STR);
            $sql->bindParam(6, $anio, PDO::PARAM_STR);
            $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
            $salarioneto = str_replace(',', '', $_POST['numSalNeto']);
            $fpag = '1';
            $mpag = $_POST['slcMetPag'];
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo print_r($cmd->errorInfo());
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        echo '1';
    } else {
        echo  'Periodo de empleado ya liquidado';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
