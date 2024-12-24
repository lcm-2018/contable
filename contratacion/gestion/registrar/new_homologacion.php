<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : exit('Acceso denegado');
$id_vigencia = $_SESSION['id_vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($tipo == '1') {
        $sql = "SELECT `id_clasificaicion`, `id_b_s`, `vigencia` FROM `ctt_clasificacion_bn_sv`";
    } else {
        $sql = "SELECT `id_escala`, `id_tipo_b_s`, `vigencia` FROM `ctt_escala_honorarios`";
    }
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`id_cargue`
            FROM
                `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE (`pto_presupuestos`.`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    exit();
}

if (!isset($_FILES['fileHomologacion']) || $_FILES['fileHomologacion']['error'] !== UPLOAD_ERR_OK) {
    exit('Error al subir el archivo');
}

$file_tmp = $_FILES['fileHomologacion']['tmp_name'];
$file_dest = 'homologacion.csv';

if (!move_uploaded_file($file_tmp, $file_dest)) {
    exit('Error al mover el archivo');
}

// Verificar que el archivo no está vacío
if (filesize($file_dest) == 0) {
    exit('El archivo está vacío');
}

$t = 0;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

if (file_exists($file_dest)) {
    if (($handle = fopen($file_dest, 'r')) !== FALSE) {
        // Leer la primera fila para obtener los encabezados
        $headers = fgetcsv($handle, 0, ';'); // Especificar el delimitador, por ejemplo, ';'

        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

            if ($tipo == '1') {
                $sql = "INSERT INTO `ctt_clasificacion_bn_sv` 
                            (`id_b_s`, `honorarios`, `horas`, `cod_unspsc`, `cod_cuipo`, `cod_siho`, `vigencia`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $query = "UPDATE `ctt_clasificacion_bn_sv` 
                            SET `honorarios`= ?, `horas` = ?, `cod_unspsc` = ?, `cod_cuipo` = ?, `cod_siho` = ? 
                        WHERE `id_clasificaicion` = ?";
                $sql = $cmd->prepare($sql);
                $query = $cmd->prepare($query);

                while (($data = fgetcsv($handle, 0, ';')) !== FALSE) { // Especificar el delimitador, por ejemplo, ';'
                    if (count($data) < 10) {
                        echo 'Fila con datos insuficientes: ' . implode(';', $data) . '<br>';
                        continue; // Salta a la siguiente fila si los datos son insuficientes
                    }
                    $id_servicio = $data[0];
                    $honorarios = $data[4] > 0 ? $data[4] : NULL;
                    $horas = $data[5] > 0 ? $data[5] : NULL;
                    $cod_unspsc = $data[6] != '' ? $data[6] : NULL;
                    $cod_cuipo = $data[7] != '' ? $data[7] : NULL;
                    $cod_siho = $data[8] != '' ? $data[8] : NULL;
                    $vigencia = $data[9] > 0 ? $data[9] : NULL;

                    $key = buscarIdServicio($datos, $id_servicio, $vigencia);
                    if ($key === false) {
                        $sql->bindParam(1, $id_servicio, PDO::PARAM_INT);
                        $sql->bindParam(2, $honorarios, PDO::PARAM_STR);
                        $sql->bindParam(3, $horas, PDO::PARAM_INT);
                        $sql->bindParam(4, $cod_unspsc, PDO::PARAM_STR);
                        $sql->bindParam(5, $cod_cuipo, PDO::PARAM_STR);
                        $sql->bindParam(6, $cod_siho, PDO::PARAM_STR);
                        $sql->bindParam(7, $vigencia, PDO::PARAM_INT);
                        $sql->execute();
                        if ($cmd->lastInsertId() > 0) {
                            $t++;
                        } else {
                            echo $sql->errorInfo()[2];
                        }
                    } else {
                        $id_clas = $key;
                        $query->bindParam(1, $honorarios, PDO::PARAM_STR);
                        $query->bindParam(2, $horas, PDO::PARAM_INT);
                        $query->bindParam(3, $cod_unspsc, PDO::PARAM_STR);
                        $query->bindParam(4, $cod_cuipo, PDO::PARAM_STR);
                        $query->bindParam(5, $cod_siho, PDO::PARAM_STR);
                        $query->bindParam(6, $id_clas, PDO::PARAM_INT);
                        $query->execute();
                        if ($query->rowCount() > 0) {
                            $t++;
                        } else {
                            echo $query->errorInfo()[2];
                        }
                    }
                }
            } else {
                $sql = "INSERT INTO `ctt_escala_honorarios` (`id_tipo_b_s`,`cod_pptal`,`vigencia`) VALUES (?, ?, ?)";
                $query = "UPDATE `ctt_escala_honorarios` SET `cod_pptal` = ?, `vigencia` = ? WHERE `id_escala` = ?";
                $sql = $cmd->prepare($sql);
                $query = $cmd->prepare($query);

                while (($data = fgetcsv($handle, 0, ';')) !== FALSE) { // Especificar el delimitador, por ejemplo, ';'
                    if (count($data) < 6) {
                        echo 'Fila con datos insuficientes: ' . implode(';', $data) . '<br>';
                        continue; // Salta a la siguiente fila si los datos son insuficientes
                    }

                    $id_tipo_s = $data[0];
                    $key = array_search($data[4], array_column($rubros, 'cod_pptal'));
                    $cod_pptal = $key !== false ? $rubros[$key]['id_cargue'] : NULL;
                    $vigencia = $data[5];

                    $id_escala = buscarIdTipoBS($datos, $id_tipo_s, $vigencia);
                    if ($id_escala === false) {
                        $sql->bindParam(1, $id_tipo_s, PDO::PARAM_INT);
                        $sql->bindParam(2, $cod_pptal, PDO::PARAM_STR);
                        $sql->bindParam(3, $vigencia, PDO::PARAM_STR);
                        $sql->execute();
                        if ($cmd->lastInsertId() > 0) {
                            $t++;
                        } else {
                            echo $sql->errorInfo()[2];
                        }
                    } else {
                        $query->bindParam(1, $cod_pptal, PDO::PARAM_STR);
                        $query->bindParam(2, $vigencia, PDO::PARAM_STR);
                        $query->bindParam(3, $id_escala, PDO::PARAM_INT);
                        $query->execute();
                        if ($query->rowCount() > 0) {
                            $t++;
                        } else {
                            echo $query->errorInfo()[2];
                        }
                    }
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            exit();
        }
        fclose($handle);
        unlink($file_dest);
        if ($t > 0) {
            echo '1';
        } else {
            echo 'No se realizó ninguna operación';
        }
    }
} else {
    echo "Archivo no encontrado";
}

function buscarIdTipoBS($datos, $id_tipo_s, $vigencia)
{
    foreach ($datos as $dato) {
        if ($dato['id_tipo_b_s'] == $id_tipo_s && $dato['vigencia'] == $vigencia) {
            return $dato['id_escala'];
        }
    }
    return false;
}

function buscarIdServicio($datos, $id_servicio, $id_vigencia)
{
    foreach ($datos as $dato) {
        if ($dato['id_b_s'] == $id_servicio && $dato['vigencia'] == $id_vigencia) {
            return $dato['id_clasificaicion'];
        }
    }
    return false;
}
