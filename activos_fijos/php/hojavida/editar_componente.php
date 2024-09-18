<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = date('Y-m-d H:i:s');
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5704, 3) && $oper == 'add' || $id_rol == 1)) {

        $id_hv = $_POST['id_hv'];

        if ($id_hv > 0) {
            if ($oper == 'add') {
                $id = isset($_POST['id_componente']) ? $_POST['id_componente'] : -1;
                if ($id == -1) {
                    
                    $sql = "INSERT INTO acf_hojavida_componentes (id_activo_fijo,id_articulo,num_serial,id_marca,modelo,id_usr_crea,fec_creacion) 
                            VALUES (:id_activo_fijo,:id_articulo,:num_serial,:id_marca,:modelo,:id_usr_crea,:fec_creacion)";                    
                    $sql = $cmd->prepare($sql);

                    $sql->bindParam(':id_activo_fijo', $id_hv, PDO::PARAM_INT);
                    $sql->bindParam(':id_articulo', $_POST['id_articulo'], PDO::PARAM_INT);
                    $sql->bindParam(':num_serial', $_POST['num_serial'], PDO::PARAM_STR);
                    $sql->bindParam(':id_marca', $_POST['id_marca'], PDO::PARAM_INT);
                    $sql->bindParam(':modelo', $_POST['modelo'], PDO::PARAM_STR);
                    $sql->bindParam(':id_usr_crea', $id_usr_crea, PDO::PARAM_INT);
                    $sql->bindParam(':fec_creacion', $fecha_crea, PDO::PARAM_STR);

                    $inserted = $sql->execute();

                    if ($inserted) {
                        $id = $cmd->lastInsertId();
                        $res['mensaje'] = 'ok';
                        $res['id_componente'] = $id;
                    } else {
                        $res['mensaje'] = $sql->errorInfo()[2];
                    }               
                }  else {
                
                    $sql = "UPDATE acf_hojavida_componentes 
                            SET id_articulo=:id_articulo,num_serial=:num_serial,id_marca=:id_marca,modelo=:modelo
                            WHERE id_componente=:id_componente";        
                    $sql = $cmd->prepare($sql);
                    
                    $sql->bindParam(':id_articulo', $_POST['id_articulo'], PDO::PARAM_INT);
                    $sql->bindParam(':num_serial', $_POST['num_serial'], PDO::PARAM_STR);
                    $sql->bindParam(':id_marca', $_POST['id_marca'], PDO::PARAM_INT);
                    $sql->bindParam(':modelo', $_POST['modelo'], PDO::PARAM_STR);
                    $sql->bindParam(':id_componente', $id, PDO::PARAM_INT);

                    $updated = $sql->execute();

                    if ($updated) {
                        $res['mensaje'] = 'ok';
                        $res['id_componente'] = $id;
                    } else {
                        $res['mensaje'] = $sql->errorInfo()[2];
                    }                
                }
            } 

            if ($oper == 'del') {
                $id = $_POST['id'];
                $sql = "DELETE FROM acf_hojavida_componentes WHERE id_componente=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                    $res['mensaje1'] = 'ok1';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar la Hoja de Vida';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }
    $cmd = null;

} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
