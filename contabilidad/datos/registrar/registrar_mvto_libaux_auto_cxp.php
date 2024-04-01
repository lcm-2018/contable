<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $_post = json_decode(file_get_contents('php://input'), true);
    $id_doc = $_post['id'];
    $id_crp = $_post['id_crp'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');

    //
    include '../../../conexion.php';

    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    } catch (Exception $e) {
        die("No se pudo conectar: " . $e->getMessage());
    }
    // Verifico si en la tabla ctb_libaux existe un registro con el id_ctb_doc = $id_doc
    $query = $cmd->prepare("SELECT id_ctb_libaux FROM ctb_libaux WHERE id_ctb_doc = ?;");
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $datos = $query->fetch();
    // verifico si $datos tiene registros
    if ($datos != null) {
        // eliminar todos los registros en ctb_libaux donde id_ctb_doc = id_doc
        $query = $cmd->prepare("DELETE FROM ctb_libaux WHERE id_ctb_doc = ?;");
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->execute();
    }
    // Consulto en la tabla ctb_doc cuando id_ctb_doc = $id_doc
    $query = $cmd->prepare("SELECT id_tercero FROM ctb_doc WHERE id_ctb_doc = ?;");
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $datos = $query->fetch();
    $id_tercero = $datos['id_tercero'];
    $id_tercero_ant =  $id_tercero;

    // Busco en el tipo de bien o servicio que corresponde
    $query = $cmd->prepare("SELECT
    `pto_documento`.`id_doc`
    , `ctt_adquisiciones`.`id_tipo_bn_sv`
    FROM
    `ctt_adquisiciones`
    INNER JOIN `pto_documento` 
        ON (`ctt_adquisiciones`.`id_cdp` = `pto_documento`.`id_auto`)
    WHERE (`pto_documento`.`id_doc` =$id_crp);");
    $query->bindParam(1, $id_crp, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch();
    $id_tipo_bn_sv = $result['id_tipo_bn_sv'];

    // Consulto en la tabla de costos cuantos registros tiene asociados
    $sq2 = "SELECT id_sede,id_cc,valor FROM seg_ctb_causa_costos WHERE id_ctb_doc = $id_doc";
    $rs = $cmd->query($sq2);
    $datoscostos = $rs->fetchAll();

    // Consulto las retenciones causadas en seg_ctb_causa_retencion
    $sq2 = "SELECT id_retencion,valor_retencion,id_terceroapi FROM seg_ctb_causa_retencion WHERE id_ctb_doc = $id_doc";
    $rs = $cmd->query($sq2);
    $datosretencion = $rs->fetchAll();

    // recorro los datos para hacer un insert
    $credito = 0;
    $otro = 0;
    $id_rte = 0;
    $id_fac = 0;
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
    foreach ($datoscostos as $key => $value) {
        $id_sede = $value['id_sede'];
        $id_cc = $value['id_cc'];
        $valor = $value['valor'];
        // Consultar el numero de cuenta para un registro similiar
        $sql = $cmd->prepare("SELECT cuenta FROM ctb_libaux WHERE id_tipo_ad = ? AND id_cc = ? ;");
        $sql->bindParam(1, $id_tipo_bn_sv, PDO::PARAM_INT);
        $sql->bindParam(2, $id_cc, PDO::PARAM_INT);
        $sql->execute();
        $result = $sql->fetch();
        $cuenta = $result['cuenta'];
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response[] = array("value" => 'ok');
        } else {
            $response[] = array("value" => 'Error del insert');
            print_r($query->errorInfo()[2]);
        }
    }
    // Causa todas las retenciones que se le hayan aplicado
    $valor = 0;
    foreach ($datosretencion as $key => $value) {
        $id_rte = $value['id_retencion'];
        $credito = $value['valor_retencion'];
        $id_terceroapi = $value['id_terceroapi'];
        // consultar el id del tercero refernciado por el id_terceroapi
        $sql = $cmd->prepare("SELECT id_tercero FROM seg_terceros WHERE id_tercero_api = ? ;");
        $sql->bindParam(1, $id_terceroapi, PDO::PARAM_INT);
        $sql->execute();
        $result = $sql->fetch();
        $id_tercero = $result['id_tercero'];
        // Consultar el numero de cuenta para un registro similiar
        $sql = $cmd->prepare("SELECT cuenta FROM ctb_libaux WHERE id_rte = ? ;");
        $sql->bindParam(1, $id_rte, PDO::PARAM_INT);
        $sql->execute();
        $result = $sql->fetch();
        $cuenta = $result['cuenta'];
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response[] = array("value" => 'ok');
        } else {
            $response[] = array("value" => 'Error del insert');
            print_r($query->errorInfo()[2]);
        }
    }
    // Liquido el valor de la cuenta por pagar, busco la diferencia debito credito en el libro auxiliar para el documento
    $sql = $cmd->prepare("SELECT SUM(debito) AS debito, SUM(credito) AS credito FROM ctb_libaux WHERE id_ctb_doc = ? ;");
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->execute();
    $result = $sql->fetch();
    $deb = $result['debito'];
    $cre = $result['credito'];
    $credito = $deb - $cre;
    $valor = 0;
    $id_cc = 0;
    $id_rte = 0;
    // buscar id_cta_factura
    $sql = $cmd->prepare("SELECT id_cta_factura FROM seg_ctb_factura WHERE id_ctb_doc = ? ;");
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->execute();
    $result = $sql->fetch();
    $id_fac = $result['id_cta_factura'];
    // buscar la cuenta asociada al tipo de cuenta por pagar 
    $sql = $cmd->prepare("SELECT cuenta FROM ctb_libaux WHERE id_tipo_ad = ? AND id_fac >0;");
    $sql->bindParam(1, $id_tipo_bn_sv, PDO::PARAM_INT);
    $sql->execute();
    $result = $sql->fetch();
    $cuenta = $result['cuenta'];
    $id_tercero = $id_tercero_ant;
    $query->execute();
    if ($cmd->lastInsertId() > 0) {
        $response[] = array("value" => 'ok');
    } else {
        $response[] = array("value" => 'Error del insert');
        print_r($query->errorInfo()[2]);
    }


    echo json_encode($response);
}
