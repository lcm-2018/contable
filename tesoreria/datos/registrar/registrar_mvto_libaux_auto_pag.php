<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
//Recibir variables por POST
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
$id_doc = $_post['id'];
$id_crp = $_post['id_crp'];
$id_cop = $_post['id_cop'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$response['status'] = 'error';
$registros = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "DELETE FROM `ctb_libaux` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $query = "SELECT `id_tercero` FROM `ctb_doc` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $datos = $query->fetch();
    $id_tercero = $datos['id_tercero'];
    $sq2 = "SELECT
                `tes_cuentas`.`id_cuenta` AS `cta_contable`
                , `tes_detalle_pago`.`valor`
            FROM
                `tes_detalle_pago`
                INNER JOIN `tes_cuentas` 
                    ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
            WHERE (`tes_detalle_pago`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sq2);
    $formapago = $rs->fetchAll();
    $sql = "SELECT
                `ctb_referencia`.`id_cuenta` AS `cuenta`
                , `ctb_referencia`.`accion`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_referencia` 
                    ON (`ctb_doc`.`id_ref_ctb` = `ctb_referencia`.`id_ctb_referencia`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $cuenta_ctb = $rs->fetch();
    $sql = "INSERT INTO `ctb_libaux`
                (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->bindParam(2, $id_tercero, PDO::PARAM_INT);
    $sql->bindParam(3, $id_cuenta, PDO::PARAM_INT);
    $sql->bindParam(4, $debito, PDO::PARAM_STR);
    $sql->bindParam(5, $credito, PDO::PARAM_STR);
    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
    $sql->bindParam(7, $fecha2);
    $debito = 0;
    $total = 0;
    foreach ($formapago as $fp) {
        $id_cuenta = $fp['cta_contable'];
        $credito = $fp['valor'];
        $total += $credito;
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $registros++;
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    $credito = 0;
    if (empty($cuenta_ctb)) {
        $id_cuenta = NULL;
    } else {
        $id_cuenta = $cuenta_ctb['cuenta'];
    }
    $debito = $total;
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $registros++;
    } else {
        echo $sql->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($registros > 0) {
    $response['status'] = 'ok';
} else {
    $response['msg'] = 'No se registraron los movimientos contables';
}
echo json_encode($response);
exit();
//
if (isset($_POST)) {

    // Consulto el numero de documentos asociados al pago  para COMPROBANTES DE EGRESO
    try {
        $sql = "SELECT `id_ctb_cop` FROM `pto_documento_detalles` WHERE (`id_ctb_doc` = $id_doc) GROUP BY `id_ctb_cop`;";
        $rs = $cmd->query($sql);
        $documentos = $rs->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Consulto la cuenta asociada la documento relacionado



    if ($cuenta_ctb != null) {
        // Si la accion del documento es 1 la cuenta es la de debito si es 2 la cuenta es la de credito
        if ($cuenta_ctb['accion'] == 1) {

            // El documento suma por la cuenta de banco al debito
            $valor = 0;
            $otro = 0;
            $id_rte = 0;
            $query = $cmd->prepare("INSERT INTO ctb_libaux (id_ctb_doc,id_tercero,cuenta,debito,credito,id_sede,id_cc,id_crp,id_rte,id_fac,id_tipo_ad,id_user_reg,fec_reg) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $query->bindParam(1, $id_doc, PDO::PARAM_INT);
            $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(3, $cuenta, PDO::PARAM_STR);
            $query->bindParam(4, $valor, PDO::PARAM_STR);
            $query->bindParam(5, $credito, PDO::PARAM_STR);
            $query->bindParam(6, $id_sede, PDO::PARAM_INT);
            $query->bindParam(7, $id_cc, PDO::PARAM_INT);
            $query->bindParam(8, $id_crp, PDO::PARAM_INT);
            $query->bindParam(9, $id_rte, PDO::PARAM_INT);
            $query->bindParam(10, $id_fac, PDO::PARAM_INT);
            $query->bindParam(11, $id_tipo_bn_sv, PDO::PARAM_INT);
            $query->bindParam(12, $iduser, PDO::PARAM_INT);
            $query->bindParam(13, $fecha2);
            // Consulto la cuenta registrada como referencia en la tabla ctb_referencia
            $sql = $cmd->prepare("SELECT
                                    SUM(`valor`) as valor
                                FROM
                                    `tes_detalle_pago`
                                WHERE `id_ctb_doc` = ?");
            $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
            $sql->execute();
            $datos = $sql->fetch();
            $cuenta = $cuenta_ctb['cuenta'];
            $credito = $datos['valor'];
            $id_fac = 0;
            $id_sede = 1;
            $id_cc = 0;
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $response[] = array("value" => 'ok');
            } else {
                $response[] = array("value" => 'Error del insert');
                print_r($query->errorInfo()[2]);
            }
            // Recorro la forma de pago para realizar el registro de las cuentas bancarias
            foreach ($formapago as $key => $value) {
                $cuenta = $value['cta_contable'];
                $credito = 0;
                $valor = $value['valor'];
                $query->execute();
                if ($cmd->lastInsertId() > 0) {
                    $response[] = array("value" => 'ok');
                } else {
                    $response[] = array("value" => 'Error del insert');
                    print_r($query->errorInfo()[2]);
                }
            }
        } else {
            // recorro los datos para hacer cuando es una nota dbito
            $credito = 0;
            $otro = 0;
            $id_rte = 0;
            $query = $cmd->prepare("INSERT INTO ctb_libaux (id_ctb_doc,id_tercero,cuenta,debito,credito,id_sede,id_cc,id_crp,id_rte,id_fac,id_tipo_ad,id_user_reg,fec_reg) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $query->bindParam(1, $id_doc, PDO::PARAM_INT);
            $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(3, $cuenta, PDO::PARAM_STR);
            $query->bindParam(4, $valor, PDO::PARAM_STR);
            $query->bindParam(5, $credito, PDO::PARAM_STR);
            $query->bindParam(6, $id_sede, PDO::PARAM_INT);
            $query->bindParam(7, $id_cc, PDO::PARAM_INT);
            $query->bindParam(8, $id_crp, PDO::PARAM_INT);
            $query->bindParam(9, $id_rte, PDO::PARAM_INT);
            $query->bindParam(10, $id_fac, PDO::PARAM_INT);
            $query->bindParam(11, $id_tipo_bn_sv, PDO::PARAM_INT);
            $query->bindParam(12, $iduser, PDO::PARAM_INT);
            $query->bindParam(13, $fecha2);
            // Consulto las facturas causadas de acuerdo a los documentos relacionados para hacer insert por cada valor y factura
            // Consulto la cuenta registrada en ctb_libaux cuando id_factura > 0
            $sql = $cmd->prepare("SELECT
                                    SUM(`valor`) as creditos
                                FROM
                                    `tes_detalle_pago`
                                WHERE `id_ctb_doc` = ?;");
            $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
            $sql->execute();
            $datos = $sql->fetch();
            $cuenta = $cuenta_ctb['cuenta'];
            $valor = $datos['creditos'];
            $id_fac = 0;
            $id_sede = 1;
            $id_cc = 0;
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $response[] = array("value" => 'ok');
            } else {
                $response[] = array("value" => 'Error del insert');
                print_r($query->errorInfo()[2]);
            }
            // Recorro la forma de pago para realizar el registro de las cuentas bancarias
            foreach ($formapago as $key => $value) {
                $cuenta = $value['cta_contable'];
                $valor = 0;
                $credito = $value['valor'];
                $query->execute();
                if ($cmd->lastInsertId() > 0) {
                    $response[] = array("value" => 'ok');
                } else {
                    $response[] = array("value" => 'Error del insert');
                    print_r($query->errorInfo()[2]);
                }
            }
        }
    } else {
        // recorro los datos para hacer un insert
        $credito = 0;
        $otro = 0;
        $id_rte = 0;
        $query = $cmd->prepare("INSERT INTO ctb_libaux (id_ctb_doc,id_tercero,cuenta,debito,credito,id_sede,id_cc,id_crp,id_rte,id_fac,id_tipo_ad,id_user_reg,fec_reg) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(3, $cuenta, PDO::PARAM_STR);
        $query->bindParam(4, $valor, PDO::PARAM_STR);
        $query->bindParam(5, $credito, PDO::PARAM_STR);
        $query->bindParam(6, $id_sede, PDO::PARAM_INT);
        $query->bindParam(7, $id_cc, PDO::PARAM_INT);
        $query->bindParam(8, $id_crp, PDO::PARAM_INT);
        $query->bindParam(9, $id_rte, PDO::PARAM_INT);
        $query->bindParam(10, $id_fac, PDO::PARAM_INT);
        $query->bindParam(11, $id_tipo_bn_sv, PDO::PARAM_INT);
        $query->bindParam(12, $iduser, PDO::PARAM_INT);
        $query->bindParam(13, $fecha2);
        // Consulto las facturas causadas de acuerdo a los documentos relacionados para hacer insert por cada valor y factura
        foreach ($documentos as $des) {
            $id_cop = $des['id_ctb_cop'];
            // Consulto la cuenta registrada en ctb_libaux cuando id_factura > 0
            $sql = $cmd->prepare("SELECT cuenta,credito,id_fac,id_sede,id_cc FROM ctb_libaux WHERE id_fac >0 AND id_ctb_doc= ?;");
            $sql->bindParam(1, $id_cop, PDO::PARAM_INT);
            $sql->execute();
            $datos = $sql->fetch();
            $cuenta = $datos['cuenta'];
            $valor = $datos['credito'];
            $id_fac = $datos['id_fac'];
            $id_sede = $datos['id_sede'];
            $id_cc = $datos['id_cc'];
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $response[] = array("value" => 'ok');
            } else {
                $response[] = array("value" => 'Error del insert');
                print_r($query->errorInfo()[2]);
            }
        }
        // Recorro la forma de pago para realizar el registro de las cuentas bancarias
        foreach ($formapago as $key => $value) {
            $cuenta = $value['cta_contable'];
            $credito = $value['valor'];
            $valor = 0;
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $response[] = array("value" => 'ok');
            } else {
                $response[] = array("value" => 'Error del insert');
                print_r($query->errorInfo()[2]);
            }
        }
    }

    echo json_encode($response);
}
