<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
$data = str_replace("|", ",", $data);
$data = str_replace("'", "", $data);
// Incio la transaccion
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `ctb_libaux`.`id_ctb_doc`, `id_manu`,SUM(`debito`) as `debito`, SUM(`credito`) as `credito` 
            FROM 
                `ctb_libaux` 
            INNER JOIN `ctb_doc` ON `ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
            WHERE (`ctb_libaux`.`id_ctb_doc` IN ($data))
            GROUP BY `id_ctb_doc`";
    $rs = $cmd->query($sql);
    $sumaMovimientos = $rs->fetchAll();
    $total = count($sumaMovimientos);
    $cerrados = 0;
    $errores = '';
    foreach ($sumaMovimientos as $sumaMov) {
        $id_ctb_doc = $sumaMov['id_ctb_doc'];
        $dif = $sumaMov['debito'] - $sumaMov['credito'];
        $sql = "SELECT `id_cuenta`, `id_ctb_doc` FROM `ctb_libaux` WHERE (`id_ctb_doc` = $id_ctb_doc)";
        $rs = $cmd->query($sql);
        $cuentas = $rs->fetchAll();
        if ($sumaMov['debito'] == 0 || $sumaMov['credito'] == 0) {
            $dif = 3;
        }
        foreach ($cuentas as $rp) {
            if ($rp['id_cuenta'] == '') {
                $dif = 3;
                break;
            }
        }
        $sql = "SELECT `id_tipo_doc` FROM `ctb_doc` WHERE (`id_ctb_doc` = $id_ctb_doc)";
        $rs = $cmd->query($sql);
        $tipo = $rs->fetch(PDO::FETCH_ASSOC);
        $dif = $tipo['id_tipo_doc'] == '14' ? 0 : $dif;

        if ($dif == 0) {
            $estado = 2;
            $query = "UPDATE `ctb_doc` SET `estado`= ? WHERE `id_ctb_doc` = ?";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $estado, PDO::PARAM_INT);
            $query->bindParam(2, $id_ctb_doc, PDO::PARAM_INT);
            $query->execute();
            $cerrados++;
        } else {
            $errores .= $sumaMov['id_manu'] . " ";
        }
    }
    $cmd = null;
} catch (Exception $e) {
    $response['msg'] = $e->getMessage();
}
if ($cerrados == $total) {
    $response['status'] = 'ok';
} else {
    $response['status'] = 'error';
    $response['msg'] = "No se han cerrado los documentos: " . $errores;
}
echo json_encode($response);
