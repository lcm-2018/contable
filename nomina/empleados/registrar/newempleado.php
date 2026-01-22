<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$cc = isset($_POST['txtCCempleado']) ? $_POST['txtCCempleado'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
$tipoemp = $_POST['slcTipoEmp'];
$subtipemp = $_POST['slcSubTipoEmp'];
$slcaltriesg = $_POST['slcAltoRiesgo'];
$tipocontrat = $_POST['slcTipoContratoEmp'];
$tipodoc = $_POST['slcTipoDocEmp'];
$paisExp = $_POST['slcPaisExp'];
$dptoExp = $_POST['slcDptoExp'];
$ciudadExp = $_POST['slcMunicipioExp'];
$fechaExp = $_POST['datFecExp'];
$paisNac = $_POST['slcPaisNac'];
$dptoNac = $_POST['slcDptoNac'];
$ciudadNac = $_POST['slcMunicipioNac'];
$fechaNac = $_POST['datFecNac'];
$genero = $_POST['slcGenero'];
$nomb1 = $_POST['txtNomb1Emp'];
$nomb2 = $_POST['txtNomb2Emp'];
$ape1 = $_POST['txtApe1Emp'];
$ape2 = $_POST['txtApe2Emp'];
$fecha = date('Y-m-d', strtotime($_POST['datInicio']));
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
$est = $_POST['numEstadoEmp'];
$ccosto = $_POST['slcCCostoEmp'];
$eps = $_POST['slcEps'];
$afileps = date('Y-m-d', strtotime($_POST['datFecAfilEps']));
if ($_POST['datFecRetEps'] === '') {
    $reteps;
} else {
    $reteps = date('Y-m-d', strtotime($_POST['datFecRetEps']));
}
$arl = $_POST['slcArl'];
$afilarl = date('Y-m-d', strtotime($_POST['datFecAfilArl']));
if ($_POST['datFecRetArl'] === '') {
    $retarl;
} else {
    $retarl = date('Y-m-d', strtotime($_POST['datFecRetArl']));
}
$rl = $_POST['slcRiesLab'];
$afp = $_POST['slcAfp'];
$afilafp = date('Y-m-d', strtotime($_POST['datFecAfilAfp']));
if ($_POST['datFecRetAfp'] === '') {
    $retafp;
} else {
    $retafp = date('Y-m-d', strtotime($_POST['datFecRetAfp']));
}
$fc = $_POST['slcFc'];
$afilfc = date('Y-m-d', strtotime($_POST['datFecAfilFc']));
if ($_POST['datFecRetFc'] === '') {
    $retfc;
} else {
    $retfc = date('Y-m-d', strtotime($_POST['datFecRetFc']));
}
$sede = $_POST['slcSedeEmp'];
$tipo_cargo = $_POST['slcTipoCargo'];
$idus = $_SESSION['id_user'];
$nit_crea = $_SESSION['nit_emp'];
$pass = $_POST['pasT'];
$bsp = isset($_POST['checkBsp']) ? 1 : 0;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT no_documento FROM nom_empleado WHERE no_documento = '$cc'";
    $rs = $cmd->query($sql);
    if ($rs->rowCount() > 0) {
        echo '0';
    } else {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `nom_empleado`(`tipo_empleado`, `subtipo_empleado`, `alto_riesgo_pension`, `tipo_contrato`, `tipo_doc`
                                        , `no_documento`, `nombre1`, `nombre2`, `apellido1`, `apellido2`, `fech_inicio`, `salario_integral`, `correo`
                                        , `telefono`, `cargo`, `pais`, `departamento`, `municipio`, `direccion`, `id_banco`, `tipo_cta`, `cuenta_bancaria`
                                        , `estado`, `genero`, `fec_reg`, `sede_emp`, `tipo_cargo`, `pais_exp`,`dpto_exp`,`city_exp`,`fec_exp`,`pais_nac`
                                        ,`dpto_nac`,`city_nac`,`fec_nac`,`bsp`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
        $sql->bindParam(12, $salintegral, PDO::PARAM_INT);
        $sql->bindParam(13, $mail, PDO::PARAM_STR);
        $sql->bindParam(14, $tel, PDO::PARAM_STR);
        $sql->bindParam(15, $cargo, PDO::PARAM_INT);
        $sql->bindParam(16, $pais, PDO::PARAM_INT);
        $sql->bindParam(17, $dpto, PDO::PARAM_INT);
        $sql->bindParam(18, $municip, PDO::PARAM_INT);
        $sql->bindParam(19, $dir, PDO::PARAM_STR);
        $sql->bindParam(20, $banco, PDO::PARAM_INT);
        $sql->bindParam(21, $tipcta, PDO::PARAM_INT);
        $sql->bindParam(22, $numcta, PDO::PARAM_STR);
        $sql->bindParam(23, $est, PDO::PARAM_INT);
        $sql->bindParam(24, $genero, PDO::PARAM_STR);
        $sql->bindValue(25, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(26, $sede, PDO::PARAM_INT);
        $sql->bindParam(27, $tipo_cargo, PDO::PARAM_INT);
        $sql->bindParam(28, $paisExp, PDO::PARAM_STR);
        $sql->bindParam(29, $dptoExp, PDO::PARAM_STR);
        $sql->bindParam(30, $ciudadExp, PDO::PARAM_STR);
        $sql->bindParam(31, $fechaExp, PDO::PARAM_STR);
        $sql->bindParam(32, $paisNac, PDO::PARAM_STR);
        $sql->bindParam(33, $dptoNac, PDO::PARAM_STR);
        $sql->bindParam(34, $ciudadNac, PDO::PARAM_STR);
        $sql->bindParam(35, $fechaNac, PDO::PARAM_STR);
        $sql->bindParam(36, $bsp, PDO::PARAM_INT);
        $sql->execute();
        $idinsert = $cmd->lastInsertId();
        if ($idinsert > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `nom_ccosto_empleado`
                        (`id_empleado`,`id_ccosto`,`id_user_reg`,`fec_reg`)
                    VALUES (?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $idinsert, PDO::PARAM_INT);
            $sql->bindParam(2, $ccosto, PDO::PARAM_INT);
            $sql->bindParam(3, $idus, PDO::PARAM_INT);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_novedades_eps (id_empleado, id_eps, fec_afiliacion, fec_retiro, fec_reg) VALUES (?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $idinsert, PDO::PARAM_INT);
                $sql->bindParam(2, $eps, PDO::PARAM_INT);
                $sql->bindParam(3, $afileps, PDO::PARAM_STR);
                $sql->bindParam(4, $reteps, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $sql->execute();
                if ($cmd->lastInsertId() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO nom_novedades_arl (id_empleado, id_arl, id_riesgo, fec_afiliacion, fec_retiro, fec_reg) VALUES (?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $idinsert, PDO::PARAM_INT);
                    $sql->bindParam(2, $arl, PDO::PARAM_INT);
                    $sql->bindParam(3, $rl, PDO::PARAM_INT);
                    $sql->bindParam(4, $afilarl, PDO::PARAM_STR);
                    $sql->bindParam(5, $retarl, PDO::PARAM_STR);
                    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO nom_novedades_afp (id_empleado, id_afp, fec_afiliacion, fec_retiro, fec_reg) VALUES (?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $idinsert, PDO::PARAM_INT);
                        $sql->bindParam(2, $afp, PDO::PARAM_INT);
                        $sql->bindParam(3, $afilafp, PDO::PARAM_STR);
                        $sql->bindParam(4, $retafp, PDO::PARAM_STR);
                        $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                        $sql->execute();
                        if ($cmd->lastInsertId() > 0) {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "INSERT INTO `nom_novedades_fc`
                                    (`id_empleado`, `id_fc`, `fec_afiliacion`, `fec_retiro`, `fec_reg`)
                                VALUES (?, ?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idinsert, PDO::PARAM_INT);
                            $sql->bindParam(2, $fc, PDO::PARAM_INT);
                            $sql->bindParam(3, $afilfc, PDO::PARAM_STR);
                            $sql->bindParam(4, $retfc, PDO::PARAM_STR);
                            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                            $sql->execute();
                            if (isset($_POST['checkDependientes'])) {
                                $pago = 1;
                                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                                $sql = "INSERT INTO `nom_pago_dependiente`
                                        (`id_empleado`, `val_pagoxdep`)
                                    VALUES (?, ?)";
                                $sql = $cmd->prepare($sql);
                                $sql->bindParam(1, $idinsert, PDO::PARAM_INT);
                                $sql->bindParam(2, $pago, PDO::PARAM_INT);
                                $sql->execute();
                            }
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "INSERT INTO `nom_salarios_basico` 
                                    (`id_empleado`, `vigencia`, `salario_basico`, `fec_reg`) 
                                VALUES (?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idinsert, PDO::PARAM_INT);
                            $sql->bindParam(2, $vigencia, PDO::PARAM_STR);
                            $sql->bindParam(3, $sal, PDO::PARAM_STR);
                            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                            $sql->execute();
                            if ($cmd->lastInsertId() > 0) {
                                try {
                                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                                    $sql = "INSERT INTO `nom_contratos_empleados` 
                                            (`id_empleado`, `fec_inicio`, `vigencia`, `id_user_reg`, `fec_reg`) 
                                        VALUES (?, ?, ?, ?, ?)";
                                    $sql = $cmd->prepare($sql);
                                    $sql->bindParam(1, $idinsert, PDO::PARAM_INT);
                                    $sql->bindParam(2, $fecha, PDO::PARAM_STR);
                                    $sql->bindParam(3, $vigencia, PDO::PARAM_STR);
                                    $sql->bindParam(4, $idus, PDO::PARAM_INT);
                                    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                                    $sql->execute();
                                    if ($cmd->lastInsertId() > 0) {
                                        //API URL
                                        $url = $api . 'terceros/datos/res/lista/' . $cc;
                                        $ch = curl_init($url);
                                        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                                        $result = curl_exec($ch);
                                        curl_close($ch);
                                        $terceros = json_decode($result, true);
                                        $regAtTerc = 'NO';
                                        $res = '';
                                        $id_ter_api = NULL;
                                        if ($terceros != '0') {
                                            $regAtTerc = 'SI';
                                            $id_ter_api = $terceros[0]['id_tercero'];
                                        } else {
                                            //API URL
                                            $url = $api . 'terceros/datos/res/nuevo';
                                            $ch = curl_init($url);
                                            $data = [
                                                "slcTipoTercero" => '1',
                                                "slcGenero" => $genero,
                                                "datFecNacimiento" => '',
                                                "slcTipoDocEmp" => $tipodoc,
                                                "txtCCempleado" => $cc,
                                                "txtNomb1Emp" => $nomb1,
                                                "txtNomb2Emp" => $nomb2,
                                                "txtApe1Emp" => $ape1,
                                                "txtApe2Emp" => $ape2,
                                                "txtRazonSocial" => '',
                                                "slcPaisEmp" => $pais,
                                                "slcDptoEmp" => $dpto,
                                                "slcMunicipioEmp" => $municip,
                                                "txtDireccion" => $dir,
                                                "mailEmp" => $mail,
                                                "txtTelEmp" => $tel,
                                                "id_user" => $idus,
                                                "nit_emp" => $nit_crea,
                                                "pass" => $pass,
                                            ];
                                            $payload = json_encode($data);
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                                            $result = curl_exec($ch);
                                            curl_close($ch);
                                            $res = json_decode($result, true);
                                            $id_ter_api = $res;
                                        }
                                        if ($res > 1 || $regAtTerc = 'SI') {
                                            try {
                                                $estado = 1;
                                                $nombre = trim($nomb1 . ' ' . $nomb2 . ' ' . $ape1 . ' ' . $ape2);
                                                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                                                $sql = "INSERT INTO `tb_terceros`
                                                        (`tipo_doc`, `id_tercero_api`, `nit_tercero`, `estado`, `fec_inicio`, `id_usr_crea`, `genero`, `nom_tercero`,`id_municipio`,`dir_tercero`, `tel_tercero`,`email`) 
                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                                $sql = $cmd->prepare($sql);
                                                $sql->bindParam(1, $tipodoc, PDO::PARAM_INT);
                                                $sql->bindParam(2, $id_ter_api, PDO::PARAM_INT);
                                                $sql->bindParam(3, $cc, PDO::PARAM_STR);
                                                $sql->bindParam(4, $estado, PDO::PARAM_STR);
                                                $sql->bindParam(5, $fecha, PDO::PARAM_STR);
                                                $sql->bindParam(6, $idus, PDO::PARAM_INT);
                                                $sql->bindValue(7, $genero, PDO::PARAM_STR);
                                                $sql->bindParam(8, $nombre, PDO::PARAM_STR);
                                                $sql->bindParam(9, $municip, PDO::PARAM_INT);
                                                $sql->bindParam(10, $dir, PDO::PARAM_STR);
                                                $sql->bindParam(11, $tel, PDO::PARAM_STR);
                                                $sql->bindParam(12, $mail, PDO::PARAM_STR);
                                                $sql->execute();
                                                if ($cmd->lastInsertId() > 0) {
                                                    $cmd = null;
                                                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                                                    $tipo_tercero = 1;
                                                    $query = "INSERT INTO `tb_rel_tercero` 
                                                                (`id_tercero_api`, `id_tipo_tercero`, `id_user_reg`, `fec_reg`) 
                                                        VALUES (?, ?, ?, ?)";
                                                    $query = $cmd->prepare($query);
                                                    $query->bindParam(1, $id_ter_api, PDO::PARAM_INT);
                                                    $query->bindParam(2, $tipo_tercero, PDO::PARAM_STR);
                                                    $query->bindParam(3, $idus, PDO::PARAM_INT);
                                                    $query->bindValue(4, $date->format('Y-m-d H:i:s'));
                                                    $query->execute();
                                                    if ($cmd->lastInsertId() > 0) {
                                                        echo '1';
                                                    } else {
                                                        echo $query->errorInfo()[2];
                                                    }
                                                } else {
                                                    echo $sql->errorInfo()[2];
                                                }
                                                $cmd = null;
                                            } catch (PDOException $e) {
                                                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                                            }
                                        } else {
                                            echo 'No se pudo Registrar';
                                        }
                                    } else {
                                        echo $sql->errorInfo()[2];
                                    }
                                    $cmd = null;
                                } catch (PDOException $e) {
                                    echo  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                                }
                            } else {
                                echo $sql->errorInfo()[2];
                            }
                        } else {
                            echo $sql->errorInfo()[2];
                        }
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo $sql->errorInfo()[2];
                }
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
