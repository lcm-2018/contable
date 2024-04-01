<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$data = file_get_contents("php://input");
//consulto el tipo de documento que se esta Procesando
$sql = "SELECT tipo_doc FROM pto_documento WHERE id_pto_doc = $data";
$res = $conexion->query($sql);
$tipoDoc = $res->fetch_assoc();
$tipoDoc = $tipoDoc['tipo_doc'];
if ($tipoDoc == 'ADI') {
    $sql = "SELECT
        SUM(`pto_documento_detalles`.`valor`) as valorsum
        , `pto_cargue`.`id_pto_presupuestos`
        , `pto_documento_detalles`.`id_documento`
        FROM
        `pto_documento_detalles`
        INNER JOIN `pto_cargue` 
            ON (`pto_documento_detalles`.`rubro` = `pto_cargue`.`cod_pptal`)
        WHERE (`pto_cargue`.`id_pto_presupuestos` ='1'
        AND `pto_documento_detalles`.`id_documento` =$data)
        GROUP BY `pto_cargue`.`id_pto_presupuestos`, `pto_documento_detalles`.`id_documento`;";
    $res = $conexion->query($sql);
    $sumaMov = $res->fetch_assoc();
    $valor1 = $sumaMov['valorsum'];
    $sql = "SELECT
        SUM(`pto_documento_detalles`.`valor`) as valorsum
        , `pto_cargue`.`id_pto_presupuestos`
        , `pto_documento_detalles`.`id_documento`
        FROM
        `pto_documento_detalles`
        INNER JOIN `pto_cargue` 
            ON (`pto_documento_detalles`.`rubro` = `pto_cargue`.`cod_pptal`)
        WHERE (`pto_cargue`.`id_pto_presupuestos` ='2'
        AND `pto_documento_detalles`.`id_documento` =$data)
        GROUP BY `pto_cargue`.`id_pto_presupuestos`, `pto_documento_detalles`.`id_documento`;";
    $res = $conexion->query($sql);
    $sumaMov2 = $res->fetch_assoc();
    $valor2 = $sumaMov2['valorsum'];
}
if ($tipoDoc == 'TRA') {
    $sql = "SELECT
                SUM(`pto_documento_detalles`.`valor`) as valorsum
            FROM
                `pto_documento_detalles`
                INNER JOIN `pto_documento` 
                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
            WHERE (`pto_documento_detalles`.`tipo_mov` ='TRA'
                AND `pto_documento_detalles`.`id_documento` =$data
                AND `pto_documento_detalles`.`mov` =1)";
    $res = $conexion->query($sql);
    $sumaMov2 = $res->fetch_assoc();
    $valor2 = $sumaMov2['valorsum'];
    // Consulta segundo movimiento
    $sql = "SELECT
                SUM(`pto_documento_detalles`.`valor`) as valorsum
            FROM
                `pto_documento_detalles`
                INNER JOIN `pto_documento` 
                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
            WHERE (`pto_documento_detalles`.`tipo_mov` ='TRA'
                AND `pto_documento_detalles`.`id_documento` =$data
                AND `pto_documento_detalles`.`mov` =0)";
    $res = $conexion->query($sql);
    $sumaMov1 = $res->fetch_assoc();
    $valor1 = $sumaMov1['valorsum'];
}
$dif = $valor1 - $valor2;

if ($tipoDoc == 'APL') {
    $dif = 0;
}
if ($dif == 0) {
    // update ctb_libaux set estado='C' where id_ctb_doc=$data;
    $sql = "UPDATE pto_documento SET estado=0 WHERE id_pto_doc=$data";
    $res = $conexion->query($sql);
    $response[] = array("value" => "ok");
} else {
    $response[] = array("value" => "no");
}
echo json_encode($response);
$conexion->close();
exit;
