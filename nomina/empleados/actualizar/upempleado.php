<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$tipodoc = isset($_POST['slcTipoDocEmp']) ? $_POST['slcTipoDocEmp'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
$tipoemp = $_POST['slcTipoEmp'];
$subtipemp = $_POST['slcSubTipoEmp'];
$slcaltriesg = $_POST['slcAltoRiesgo'];
$tipocontrat = $_POST['slcTipoContratoEmp'];
if ($_POST['txtCCempleado'] === '') {
    $cc = null;
} else {
    $cc = $_POST['txtCCempleado'];
}
$paisExp = $_POST['slcPaisExp'];
$dptoExp = $_POST['slcDptoExp'];
$ciudadExp = $_POST['slcMunicipioExp'];
$fechaExp = $_POST['datFecExp'];
$paisNac = $_POST['slcPaisNac'];
$dptoNac = $_POST['slcDptoNac'];
$ciudadNac = $_POST['slcMunicipioNac'];
$fechaNac = $_POST['datFecNac'];
$gen = $_POST['slcGenero'];
$nomb1 = $_POST['txtNomb1Emp'];
$nomb2 = $_POST['txtNomb2Emp'];
$ape1 = $_POST['txtApe1Emp'];
$ape2 = $_POST['txtApe2Emp'];
$fecha = date('Y-m-d', strtotime($_POST['datInicio']));
if ($_POST['datFecRetiro'] !== "") {
    $fecretiro = date('Y-m-d', strtotime($_POST['datFecRetiro']));
} else {
    $fecretiro = NULL;
}
$salintegral = $_POST['slcSalIntegral'];
$sal = str_replace(',', '', $_POST['numSalarioEmp']);
$mail = $_POST['mailEmp'];
$tel = $_POST['txtTelEmp'];
$cargo = $_POST['slcCargoEmp'];
$pais = $_POST['slcPaisEmp'];
$dpto = $_POST['slcDptoEmp'];
$municip = $_POST['slcMunicipioEmp'];
$dir = $_POST['txtDireccion'];
$banco = $_POST['slcBancoEmp'];
$tipcta = $_POST['selTipoCta'];
$numcta = $_POST['txtCuentaBanc'];
$sede = $_POST['slcSedeEmp'];
$tipo_cargo = $_POST['slcTipoCargo'];
$idemp = $_POST['idEmpleado'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE nom_empleado SET tipo_empleado = ?, subtipo_empleado = ?, alto_riesgo_pension = ?, tipo_contrato = ?, tipo_doc = ?, no_documento = ?,
        nombre1= ?, nombre2 = ?, apellido1 = ?, apellido2 = ?, fech_inicio = ?, fec_retiro = ?, salario_integral = ?,
        correo = ?, telefono = ?, cargo = ?, pais = ?, departamento = ?, municipio = ?, direccion = ?,
        id_banco = ?, tipo_cta = ?, cuenta_bancaria= ?, genero= ?, 
        `pais_exp` = ?,`dpto_exp` = ?,`city_exp` = ?,`fec_exp` = ?,`pais_nac` = ?,`dpto_nac` = ?,`city_nac` = ?,`fec_nac` = ?, `sede_emp` = ?, `tipo_cargo` = ?
        WHERE id_empleado = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $tipoemp, PDO::PARAM_INT);
    $sql->bindParam(2, $subtipemp, PDO::PARAM_INT);
    $sql->bindParam(3, $slcaltriesg, PDO::PARAM_INT);
    $sql->bindParam(4, $tipocontrat, PDO::PARAM_INT);
    $sql->bindParam(5, $tipodoc, PDO::PARAM_INT);
    $sql->bindParam(6, $cc, PDO::PARAM_STR);
    $sql->bindParam(7, $nomb1, PDO::PARAM_STR);
    $sql->bindParam(8, $nomb2, PDO::PARAM_STR);
    $sql->bindParam(9, $ape1, PDO::PARAM_STR);
    $sql->bindParam(10, $ape2, PDO::PARAM_STR);
    $sql->bindParam(11, $fecha, PDO::PARAM_STR);
    $sql->bindParam(12, $fecretiro, PDO::PARAM_STR);
    $sql->bindParam(13, $salintegral, PDO::PARAM_INT);
    $sql->bindParam(14, $mail, PDO::PARAM_STR);
    $sql->bindParam(15, $tel, PDO::PARAM_STR);
    $sql->bindParam(16, $cargo, PDO::PARAM_INT);
    $sql->bindParam(17, $pais, PDO::PARAM_INT);
    $sql->bindParam(18, $dpto, PDO::PARAM_INT);
    $sql->bindParam(19, $municip, PDO::PARAM_INT);
    $sql->bindParam(20, $dir, PDO::PARAM_STR);
    $sql->bindParam(21, $banco, PDO::PARAM_INT);
    $sql->bindParam(22, $tipcta, PDO::PARAM_INT);
    $sql->bindParam(23, $numcta, PDO::PARAM_STR);
    $sql->bindParam(24, $gen, PDO::PARAM_STR);
    $sql->bindParam(25, $paisExp, PDO::PARAM_INT);
    $sql->bindParam(26, $dptoExp, PDO::PARAM_INT);
    $sql->bindParam(27, $ciudadExp, PDO::PARAM_INT);
    $sql->bindParam(28, $fechaExp, PDO::PARAM_STR);
    $sql->bindParam(29, $paisNac, PDO::PARAM_INT);
    $sql->bindParam(30, $dptoNac, PDO::PARAM_INT);
    $sql->bindParam(31, $ciudadNac, PDO::PARAM_INT);
    $sql->bindParam(32, $fechaNac, PDO::PARAM_STR);
    $sql->bindParam(33, $sede, PDO::PARAM_INT);
    $sql->bindParam(34, $tipo_cargo, PDO::PARAM_INT);
    $sql->bindParam(35, $idemp, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $updatemp = 1;
    } else {
        $updatemp = 0;
    }
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    }
    $upsalemp = 0;
    if ($_POST['salAnt'] != $_POST['numSalarioEmp']) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO nom_salarios_basico (id_empleado, vigencia, salario_basico, fec_reg) VALUES (?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idemp, PDO::PARAM_INT);
        $sql->bindParam(2, $vigencia, PDO::PARAM_STR);
        $sql->bindParam(3, $sal, PDO::PARAM_STR);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            $upsalemp = 1;
        }
    }
    if (isset($_POST['checkDependientes'])) {
        $pago = 1;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `nom_pago_dependiente`
                    (`id_empleado`, `val_pagoxdep`)
                VALUES (?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idemp, PDO::PARAM_INT);
        $sql->bindParam(2, $pago, PDO::PARAM_INT);
        $sql->execute();
        $dpt = $sql->rowCount() > 0 ? 1 : 0;
    } else {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "DELETE FROM  `nom_pago_dependiente` WHERE `id_empleado` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idemp, PDO::PARAM_INT);
        $sql->execute();
        $dpt = $sql->rowCount() > 0 ? 1 : 0;
    }
    if ($updatemp > 0 || $upsalemp > 0 || $dpt > 0) {
        if ($updatemp > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE nom_empleado SET  fec_actu = ? WHERE id_empleado = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(2, $idemp, PDO::PARAM_INT);
            $sql->execute();
        }
        if ($upsalemp > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $sql = "UPDATE nom_salarios_basico SET  fec_act = ? WHERE WHERE id_salario = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(2, $id_salario, PDO::PARAM_INT);
            $sql->execute();
        }
        echo 'ok';
    } else {
        echo 'No se ingresó ningún dato nuevo';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
