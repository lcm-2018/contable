<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
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

    if ((PermisosUsuario($permisos, 5006, 2) && $oper == 'add' ||
        (PermisosUsuario($permisos, 5006, 4) && $oper == 'del') || $id_rol == 1)
    ) {

        $id_componente = isset($_POST['id_componente']) ? $_POST['id_componente'] : -1;
        $id_hv = isset($_POST['id_hv']) ? $_POST['id_hv'] : -1;
        
        $rs = $cmd->query($sql);
        $obj_ingreso = $rs->fetch();

        if ($oper == 'add') {

            if ($id_componente == -1) {

                $sql = "INSERT INTO acf_hojavida_componente 
                        (id_activo_fijo, id_articulo, serial, id_marca, modelo, id_usuario_crea, fecha_creacion) 
                        VALUES 
                        (:id_activo_fijo, :id_articulo, :serial, :id_marca, :modelo, :id_usuario_crea, :fecha_creacion)";
                
                $sql = $cmd->prepare($sql);
                $sql->bindParam(':id_activo_fijo', $id_hv, PDO::PARAM_INT);
                $sql->bindParam(':id_articulo', $_POST['id_articulo'], PDO::PARAM_INT);
                $sql->bindParam(':serial', $_POST['serial'], PDO::PARAM_STR);
                $sql->bindParam(':id_marca', $_POST['id_marca'], PDO::PARAM_INT);
                $sql->bindParam(':modelo', $_POST['modelo'], PDO::PARAM_STR);
                $sql->bindParam(':id_usuario_crea', $id_usr_crea, PDO::PARAM_INT);
                $sql->bindParam(':fecha_creacion', $fecha_crea, PDO::PARAM_STR);

                $inserted = $sql->execute();

                if ($inserted) {
                    $res['mensaje'] = 'ok';
                    $res['id_hv'] = $id_hv;
                    $res['id_componente'] = $id_componente;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
               
            }  else {
                
                $sql = "UPDATE acf_hojavida_componente 
                        SET id_articulo = :id_articulo,
                            serial = :serial,
                            id_marca = :id_marca,
                            modelo = :modelo,
                            id_usuario_crea = :id_usuario_crea,
                            fecha_creacion = :fecha_creacion
                        WHERE id_componente = :id_componente";
    
                $sql = $cmd->prepare($sql);

                $sql->bindParam(':id_componente', $id_componente, PDO::PARAM_INT);
                $sql->bindParam(':id_articulo', $_POST['id_articulo'], PDO::PARAM_INT);
                $sql->bindParam(':serial', $_POST['serial'], PDO::PARAM_STR);
                $sql->bindParam(':id_marca', $_POST['id_marca'], PDO::PARAM_INT);
                $sql->bindParam(':modelo', $_POST['modelo'], PDO::PARAM_STR);
                $sql->bindParam(':id_usuario_crea', $id_usr_crea, PDO::PARAM_INT);
                $sql->bindParam(':fecha_creacion', $fecha_crea, PDO::PARAM_STR);

                $updated = $sql->execute();

                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id_hv'] = $id_hv;
                    $res['id_componente'] = $id_componente;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
                
            }

        } 

        if ($oper == 'del') {
            $sql = "DELETE FROM acf_hojavida_componente WHERE id_componente=" . $id_componente;
            $rs = $cmd->query($sql);
            if ($rs) {
                $res['mensaje'] = 'ok';
            } else {
                $res['mensaje'] = $cmd->errorInfo()[2];
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
