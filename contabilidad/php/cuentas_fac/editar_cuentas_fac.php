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
$fecha_crea = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5507, 2) && $oper == 'add' && $_POST['id_cuentafac'] == -1) ||
        (PermisosUsuario($permisos, 5507, 3) && $oper == 'add' && $_POST['id_cuentafac'] != -1) ||
        (PermisosUsuario($permisos, 5507, 4) && $oper == 'del') || $id_rol == 1
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_cuentafac'];
            $id_regimen = $_POST['sl_regimen'];
            $id_cobertura = $_POST['sl_cobertura'];
            $id_modalidad = $_POST['sl_modalidad'];
            $id_cta_pre = $_POST['id_txt_cta_pre'] ? $_POST['id_txt_cta_pre'] : 'NULL';
            $id_cta_pre_ant = $_POST['id_txt_cta_pre_ant'] ? $_POST['id_txt_cta_pre_ant'] : 'NULL';
            $id_cta_deb = $_POST['id_txt_cta_deb'] ? $_POST['id_txt_cta_deb'] : 'NULL';
            $id_cta_cre = $_POST['id_txt_cta_cre'] ? $_POST['id_txt_cta_cre'] : 'NULL';
            $id_cta_cop = $_POST['id_txt_cta_cop'] ? $_POST['id_txt_cta_cop'] : 'NULL';
            $id_cta_cop_cap = $_POST['id_txt_cta_cop_cap'] ? $_POST['id_txt_cta_cop_cap'] : 'NULL';
            $id_cta_gli_deb = $_POST['id_txt_cta_gli_deb'] ? $_POST['id_txt_cta_gli_deb'] : 'NULL';
            $id_cta_gli_cre = $_POST['id_txt_cta_gli_cre'] ? $_POST['id_txt_cta_gli_cre'] : 'NULL';
            $id_cta_glo_def = $_POST['id_txt_cta_glo_def'] ? $_POST['id_txt_cta_glo_def'] : 'NULL';
            $id_cta_dev = $_POST['id_txt_cta_dev'] ? $_POST['id_txt_cta_dev'] : 'NULL';
            $id_cta_caj = $_POST['id_txt_cta_caj'] ? $_POST['id_txt_cta_caj'] : 'NULL';
            $id_cta_fac_glo = $_POST['id_txt_cta_fac_glo'] ? $_POST['id_txt_cta_fac_glo'] : 'NULL';
            $id_cta_x_ide = $_POST['id_txt_cta_x_ide'] ? $_POST['id_txt_cta_x_ide'] : 'NULL';
            $id_cta_baja = $_POST['id_txt_cta_baja'] ? $_POST['id_txt_cta_baja'] : 'NULL';
            $fec_vig = $_POST['txt_fec_vig'] ? "'" . $_POST['txt_fec_vig'] . "'" : 'NULL';
            $estado = $_POST['sl_estado'];

            if ($id == -1) {
                $sql = "INSERT INTO tb_homologacion(id_regimen,id_cobertura,id_modalidad,
                                id_cta_presupuesto,id_cta_presupuesto_ant,
                                id_cta_debito,id_cta_credito,
                                id_cta_copago,id_cta_copago_capitado,
                                id_cta_glosaini_debito,id_cta_glosaini_credito,
                                id_cta_glosadefinitiva,id_cta_devolucion,id_cta_caja,
                                id_cta_fac_global,id_cta_x_ident,id_cta_baja,
                                fecha_vigencia,estado,id_usr_crea,fec_creacion) 
                        VALUES($id_regimen,$id_cobertura,$id_modalidad,
                                $id_cta_pre,$id_cta_pre_ant,
                                $id_cta_deb,$id_cta_cre,
                                $id_cta_cop,$id_cta_cop_cap,
                                $id_cta_gli_deb,$id_cta_gli_cre,
                                $id_cta_glo_def,$id_cta_dev,$id_cta_caj,
                                $id_cta_fac_glo,$id_cta_x_ide,$id_cta_baja,
                                $fec_vig,$estado,$id_usr_ope,'$fecha_ope')";
                $rs = $cmd->query($sql);

                if ($rs) {
                    $res['mensaje'] = 'ok';
                    $sql_i = 'SELECT LAST_INSERT_ID() AS id';
                    $rs = $cmd->query($sql_i);
                    $obj = $rs->fetch();
                    $res['id'] = $obj['id'];
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $sql = "UPDATE tb_homologacion 
                        SET id_regimen=$id_regimen,id_cobertura=$id_cobertura,id_modalidad=$id_modalidad,
                            id_cta_presupuesto=$id_cta_pre,id_cta_presupuesto_ant=$id_cta_pre_ant,
                            id_cta_debito=$id_cta_deb,id_cta_credito=$id_cta_cre,
                            id_cta_copago=$id_cta_cop,id_cta_copago_capitado=$id_cta_cop_cap,
                            id_cta_glosaini_debito=$id_cta_gli_deb,id_cta_glosaini_credito=$id_cta_gli_cre,
                            id_cta_glosadefinitiva=$id_cta_glo_def,id_cta_devolucion=$id_cta_dev,id_cta_caja=$id_cta_caj,
                            id_cta_fac_global=$id_cta_fac_glo,id_cta_x_ident=$id_cta_x_ide,id_cta_baja=$id_cta_baja,
                            fecha_vigencia=$fec_vig,estado=$estado
                        WHERE id_homo=" . $id;
                $rs = $cmd->query($sql);

                if ($rs) {
                    $res['mensaje'] = 'ok';
                    $res['id'] = $id;
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            }
        }

        if ($oper == 'del') {
            $id = $_POST['id'];
            $sql = "DELETE FROM tb_homologacion WHERE id_homo=" . $id;
            $rs = $cmd->query($sql);
            if ($rs) {
                include '../../../financiero/reg_logs.php';
                $ruta = '../../../log';
                $consulta = $sql;
                RegistraLogs($ruta, $consulta);
                $res['mensaje'] = 'ok';
            } else {
                $res['mensaje'] = $cmd->errorInfo()[2];
            }
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
