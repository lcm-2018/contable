<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../simpleXLSX.php';
$id_nomina = isset($_POST['id_nomina']) ? $_POST['id_nomina'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_empleado`, `no_documento` FROM `nom_empleado`";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`
                , `aporte_salud_emp`
                , `aporte_pension_emp`
                , `aporte_solidaridad_pensional`
            FROM
                `nom_liq_segsocial_empdo`
            WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $patronales = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$file_tmp = $_FILES['filePlanilla']['tmp_name'];

move_uploaded_file($file_tmp, "planilla.xlsx");
$t = 0;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if (!empty($empleados)) {
    if (file_exists('planilla.xlsx')) {
        $xlsx = new SimpleXLSX('planilla.xlsx');
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `nom_liq_parafiscales` 
                    SET `val_sena` = ?, `val_icbf` = ?, `val_comfam` = ? 
                    WHERE  `id_nomina` = ? AND  `id_empleado` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $sena, PDO::PARAM_STR);
            $sql->bindParam(2, $icbf, PDO::PARAM_STR);
            $sql->bindParam(3, $caja, PDO::PARAM_STR);
            $sql->bindParam(4, $id_nomina, PDO::PARAM_INT);
            $sql->bindParam(5, $id_empleado, PDO::PARAM_INT);
            $query = "UPDATE `nom_liq_segsocial_empdo` 
                    SET `aporte_salud_emp` = ?, `aporte_salud_empresa` = ?, `aporte_pension_emp` = ?, `aporte_pension_empresa` = ?, `aporte_solidaridad_pensional` = ?,`aporte_rieslab` = ? 
                    WHERE `id_nomina` = ? AND `id_empleado` = ?";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $salud_empleado, PDO::PARAM_STR);
            $query->bindParam(2, $salud_patronal, PDO::PARAM_STR);
            $query->bindParam(3, $pension_empleado, PDO::PARAM_STR);
            $query->bindParam(4, $pension_pantronal, PDO::PARAM_STR);
            $query->bindParam(5, $pension_solidaria, PDO::PARAM_STR);
            $query->bindParam(6, $riesgo, PDO::PARAM_STR);
            $query->bindParam(7, $id_nomina, PDO::PARAM_INT);
            $query->bindParam(8, $id_empleado, PDO::PARAM_INT);
            foreach ($xlsx->rows() as $fila => $campo) {
                if ($fila < 1) {
                    continue;
                }

                $cedula = $campo[0];
                $key = array_search($cedula, array_column($empleados, 'no_documento'));
                if (false !== $key) {
                    $id_empleado = $empleados[$key]['id_empleado'];
                    $pension_empleado = $campo[1];
                    $pension_pantronal = $campo[2];
                    $pension_solidaria = $campo[3];
                    $salud_empleado = $campo[4];
                    $salud_patronal = $campo[5];
                    $caja = $campo[6];
                    $riesgo = $campo[7];
                    $sena = $campo[8];
                    $icbf = $campo[9];
                    if (!($sql->execute())) {
                        echo $sql->errorInfo()[2] . '<br>';
                        exit();
                    } else {
                        if ($sql->rowCount() > 0) {
                            $t++;
                        }
                    }
                    $key = array_search($id_empleado, array_column($patronales, 'id_empleado'));
                    if (false !== $key) {
                        if (!($query->execute())) {
                            echo $query->errorInfo()[2] . '<br>';
                            exit();
                        } else {
                            if ($query->rowCount() > 0) {
                                $t++;
                            }
                        }
                    }
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        if ($t > 0) {
            unlink('planilla.xlsx');
            echo 'ok';
        } else {
            unlink('planilla.xlsx');
            echo 'No se registró ningun cambio a la Planilla';
        }
    } else {
        echo "Archivo no encontrado";
    }
} else {
    echo 'No se econtró ningún empleado';
}
