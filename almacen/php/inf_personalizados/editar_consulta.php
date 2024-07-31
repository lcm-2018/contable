<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir

$oper = isset($_POST['txt_id_con']) ? $_POST['txt_id_con'] : exit('Acción no permitida');
$fecha_crea = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ($id_rol == 1) {
        $sensql = array();
        $sensql[0]="alter";
        $sensql[1]="analyze";
        $sensql[2]="create";
        $sensql[3]="delete";
        $sensql[4]="drop";
        $sensql[5]="explain";
        $sensql[6]="grant";
        $sensql[7]="revoke";
        $sensql[8]="handler";
        $sensql[9]="insert";
        $sensql[10]="kill";
        $sensql[11]="lock";
        $sensql[12]="rename";
        $sensql[13]="replace";
        $sensql[14]="reset";
        $sensql[15]="revoke";
        $sensql[16]="show";
        $sensql[17]="truncate";
        $sensql[18]="update";
        $sensql[19]="use ";

        $sentencia = $_POST['txt_con_sql'];
        $error = '';
        for($x=0; $x<=19; $x++){
            if (strpos($sentencia, $sensql[$x]) != false){ 
                $error .= ' - ' . $sensql[$x];
            }    
        }
        
        if ($error == ''){
            $sql = "UPDATE tb_consultas_sql SET
                            nom_consulta = :nom_con,
                            id_opcion = :id_opcion,
                            des_consulta = :des_con,
                            consulta = :con_sql,
                            parametros = :par_con                            
                    WHERE id_consulta = :id_con";
            $sql = $cmd->prepare($sql);
            $sql->bindValue(':nom_con', $_POST['txt_nom_con']);
            $sql->bindValue(':id_opcion', $_POST['sl_opcion']);
            $sql->bindValue(':des_con', $_POST['txt_des_con']);
            $sql->bindValue(':con_sql', $_POST['txt_con_sql']);
            $sql->bindValue(':par_con', $_POST['txt_par_con']);
            $sql->bindValue(':id_con', $_POST['txt_id_con']);
            $rs = $sql->execute();
            if ($rs) {
                $res['mensaje'] = 'ok';
                $res['nom_consulta'] = $_POST['txt_nom_con'];
                $res['des_consulta'] = $_POST['txt_des_con'];
            } else {
                $res['mensaje'] = $cmd->errorInfo()[2];
            } 
        } else {
            $res['mensaje'] = 'Sentencia incorrecta ' . $error;
        }
    } else {
        $res['mensaje'] = 'El Usuario de Sistema no tienen permiso para esta Acción';
    }
    
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
