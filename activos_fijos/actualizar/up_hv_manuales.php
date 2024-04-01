<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_mst = isset($_POST['id_mSerTec']) ? $_POST['id_mSerTec'] : exit('Acción no permitida');
$id_mu = isset($_POST['id_mUser']) ? $_POST['id_mUser'] : exit('Acción no permitida');
$id_mf = isset($_POST['id_mGFast']) ? $_POST['id_mGFast'] : exit('Acción no permitida');
$id_serie = $_POST['id_serie'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($id_mst == '0') {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_hv_manuales`
                    (`id_serie`, `id_tipo`, `ruta`, `nombre`, `estado`, `id_user_reg`, `fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_serie, PDO::PARAM_INT);
        $sql->bindParam(2, $tipo, PDO::PARAM_INT);
        $sql->bindParam(3, $ruta, PDO::PARAM_STR);
        $sql->bindParam(4, $nombre, PDO::PARAM_STR);
        $sql->bindParam(5, $estado, PDO::PARAM_INT);
        $sql->bindParam(6, $iduser, PDO::PARAM_INT);
        $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
        $tipo = 3;
        $estado = $_POST['servTecManual'];
        $ruta = $nombre = null;
        if (isset($_FILES['filemst']) && $estado == 1) {
            $nombre = 'MST' . '_' . date('YmdGis') . '_' . $_FILES['filemst']['name'];
            $nombre = strlen($nombre) >= 101 ? substr($nombre, 0, 100) : $nombre;
            $temporal = $_FILES['filemst']['tmp_name'];
            $ruta = '../../upload/activos_fijos/';
            if (!file_exists($ruta)) {
                $ruta = mkdir($ruta, 0777, true);
                $ruta = '../../upload/activos_fijos/';
            }
            if (!(move_uploaded_file($temporal, $ruta . $nombre))) {
                echo 'No se pudo adjuntar el archivo de <b>Servicio Técnico</b>';
                exit();
            }
        }
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $tipo = 4;
            $ruta = $nombre = null;
            $estado = $_POST['userManual'];
            if (isset($_FILES['filemu']) && $estado == 1) {
                $nombre = 'MU' . '_' . date('YmdGis') . '_' . $_FILES['filemu']['name'];
                $nombre = strlen($nombre) >= 101 ? substr($nombre, 0, 100) : $nombre;
                $temporal = $_FILES['filemu']['tmp_name'];
                $ruta = '../../upload/activos_fijos/';
                if (!file_exists($ruta)) {
                    $ruta = mkdir($ruta, 0777, true);
                    $ruta = '../../upload/activos_fijos/';
                }
                if (!(move_uploaded_file($temporal, $ruta . $nombre))) {
                    echo 'No se pudo adjuntar el archivo de <b>Usuario</b>';
                    exit();
                }
            }
            $sql->execute();
            $tipo = 5;
            $ruta = $nombre = null;
            $estado = $_POST['guiaFastManual'];
            if (isset($_FILES['filemf']) && $estado == 1) {
                $nombre = 'MU' . '_' . date('YmdGis') . '_' . $_FILES['filemf']['name'];
                $nombre = strlen($nombre) >= 101 ? substr($nombre, 0, 100) : $nombre;
                $temporal = $_FILES['filemf']['tmp_name'];
                $ruta = '../../upload/activos_fijos/';
                if (!file_exists($ruta)) {
                    $ruta = mkdir($ruta, 0777, true);
                    $ruta = '../../upload/activos_fijos/';
                }
                if (!(move_uploaded_file($temporal, $ruta . $nombre))) {
                    echo 'No se pudo adjuntar el archivo de <b>Guía de manejo rápido</b>';
                    exit();
                }
            }
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                echo '1';
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo $sql->errorInfo()[2];
        }
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    $contar = 0;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_hv_manuales`
                    SET `ruta` = ?, `nombre` = ?, `estado` = ? WHERE `id_manual` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $ruta, PDO::PARAM_STR);
        $sql->bindParam(2, $nombre, PDO::PARAM_STR);
        $sql->bindParam(3, $estado, PDO::PARAM_INT);
        $sql->bindParam(4, $id_manual, PDO::PARAM_INT);
        $conx = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $conx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $query = "UPDATE `seg_hv_manuales` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_manual` = ?";
        $query = $conx->prepare($query);
        $query->bindParam(1, $iduser, PDO::PARAM_INT);
        $query->bindValue(2, $date->format('Y-m-d H:i:s'));
        $query->bindParam(3, $id_manual, PDO::PARAM_INT);
        $id_manual = $id_mst;
        $ruta = dirname(base64_decode($_POST['ramst']));
        $ruta = $ruta . '/';
        $nombre = basename(base64_decode($_POST['ramst']));
        $estado = $_POST['servTecManual'];
        if (isset($_FILES['filemst']) && $estado == 1) {
            $nombre = 'MST' . '_' . date('YmdGis') . '_' . $_FILES['filemst']['name'];
            $nombre = strlen($nombre) >= 101 ? substr($nombre, 0, 100) : $nombre;
            $temporal = $_FILES['filemst']['tmp_name'];
            $ruta = '../../upload/activos_fijos/';
            if (!file_exists($ruta)) {
                $ruta = mkdir($ruta, 0777, true);
                $ruta = '../../upload/activos_fijos/';
            }
            if (!(move_uploaded_file($temporal, $ruta . $nombre))) {
                echo 'No se pudo adjuntar el archivo de <b>Servico Técnico</b>';
                exit();
            }
            if ($_POST['ramst'] != '') {
                unlink(base64_decode($_POST['ramst']));
            }
        }
        if ($estado != 1) {
            $ruta = $nombre = null;
            if ($_POST['ramst'] != '') {
                unlink(base64_decode($_POST['ramst']));
            }
        }
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $query->execute();
                if ($query->rowCount() > 0) {
                    $contar++;
                } else {
                    echo $query->errorInfo()[2];
                    exit();
                }
            }
            $id_manual = $id_mu;
            $ruta = dirname(base64_decode($_POST['ramu']));
            $ruta = $ruta . '/';
            $nombre = basename(base64_decode($_POST['ramu']));
            $estado = $_POST['userManual'];
            if (isset($_FILES['filemu']) && $estado == 1) {
                $nombre = 'MU' . '_' . date('YmdGis') . '_' . $_FILES['filemu']['name'];
                $nombre = strlen($nombre) >= 101 ? substr($nombre, 0, 100) : $nombre;
                $temporal = $_FILES['filemu']['tmp_name'];
                $ruta = '../../upload/activos_fijos/';
                if (!file_exists($ruta)) {
                    $ruta = mkdir($ruta, 0777, true);
                    $ruta = '../../upload/activos_fijos/';
                }
                if (!(move_uploaded_file($temporal, $ruta . $nombre))) {
                    echo 'No se pudo adjuntar el archivo de <b> Usuarios</b>';
                    exit();
                }
                if ($_POST['ramu'] != '') {
                    unlink(base64_decode($_POST['ramu']));
                }
            }
            if ($estado != 1) {
                $ruta = $nombre = null;
                if ($_POST['ramu'] != '') {
                    unlink(base64_decode($_POST['ramu']));
                }
            }
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $query->execute();
                    if ($query->rowCount() > 0) {
                        $contar++;
                    } else {
                        echo $query->errorInfo()[2];
                        exit();
                    }
                }
                $id_manual = $id_mf;
                $ruta = dirname(base64_decode($_POST['ramf']));
                $ruta = $ruta . '/';
                $nombre = basename(base64_decode($_POST['ramf']));
                $estado = $_POST['guiaFastManual'];
                if (isset($_FILES['filemf']) && $estado == 1) {
                    $nombre = 'MF' . '_' . date('YmdGis') . '_' . $_FILES['filemf']['name'];
                    $nombre = strlen($nombre) >= 101 ? substr($nombre, 0, 100) : $nombre;
                    $temporal = $_FILES['filemf']['tmp_name'];
                    $ruta = '../../upload/activos_fijos/';
                    if (!file_exists($ruta)) {
                        $ruta = mkdir($ruta, 0777, true);
                        $ruta = '../../upload/activos_fijos/';
                    }
                    if (!(move_uploaded_file($temporal, $ruta . $nombre))) {
                        echo 'No se pudo adjuntar el archivo de <b>Guía de manejo rápido</b>';
                        exit();
                    }
                    if ($_POST['ramf'] != '') {
                        unlink(base64_decode($_POST['ramf']));
                    }
                }
                if ($estado != 1) {
                    $ruta = $nombre = null;
                    if ($_POST['ramf'] != '') {
                        unlink(base64_decode($_POST['ramf']));
                    }
                }
                if (!($sql->execute())) {
                    echo $sql->errorInfo()[2];
                    exit();
                } else {
                    if ($sql->rowCount() > 0) {
                        $query->execute();
                        if ($query->rowCount() > 0) {
                            $contar++;
                        } else {
                            echo $query->errorInfo()[2];
                            exit();
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    if ($contar == 0) {
        echo 'No se ha ingresado ningun dato nuevo';
    } else if ($contar > 0) {
        echo '1';
    } else {
        echo 'Error al ingresar datos';
    }
}
