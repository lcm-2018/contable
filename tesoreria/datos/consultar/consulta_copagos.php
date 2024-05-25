<?php

include '../../../conexion.php';
// Consexion a cronhis asistencial
$cmd2 = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base_f;$charset", $bd_usuario, $bd_clave);
$cmd2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
//
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$_post = json_decode(file_get_contents('php://input'), true);
$tercero = $_post['tercero'];
$fecha = $_post['fecha'];
// Sumo valores de copagos de facturas activas sin anulación
// $sql = "SELECT SUM(`valor`) as valor FROM `seg_vta_copagos` WHERE (`cc_fact` =$tercero AND `num_fact_anula` = 0 AND `fecha` ='$fecha');";
try {
    $sql = "SELECT cc_facturador,SUM(valor) AS valor FROM(
SELECT  num_documento AS cc_facturador
      , num_doc_usr AS cc_paciente
      , paciente AS nom_paciente
      , concepto
      , vista_aux_arqueo.id_factura
      , t.id_factura AS anulada
      , fec_factura
      , vista_aux_arqueo.valor
      , t.valor AS ingreso      
FROM vista_aux_arqueo
      LEFT JOIN (SELECT id_factura,valor FROM vista_aux_arqueo WHERE `estado`=0 AND fec_factura <> IFNULL(DATE_FORMAT(fec_anulacion,'%Y-%m-%d'),'1900-01-01')) AS t
      ON(vista_aux_arqueo.id_fac_anulada = t.id_factura)
WHERE fec_factura <> IFNULL(DATE_FORMAT(fec_anulacion,'%Y-%m-%d'),'1900-01-01') AND fec_factura = '$fecha') AS temp
    WHERE temp.cc_facturador ='$tercero' AND anulada IS NULL
    GROUP BY temp.cc_facturador";
    $res = $cmd2->query($sql);
    $datos = $res->fetch();
    $valor = $datos['valor'];
} catch (Exception $e) {
    echo $e->getMessage();
}
// Suma ingresos de facturas con anulación 

// $sql = "SELECT SUM(`ingreso`) as ingreso FROM `seg_vta_copagos` WHERE (`cc_fact` =$tercero AND `num_fact_anula` != 0 AND `fecha` ='$fecha');";
try {
    $sql = "SELECT cc_facturador      
, SUM(IF(valor>ingreso,valor-ingreso,0)) AS ingreso FROM(
    SELECT  num_documento AS cc_facturador
      , num_doc_usr AS cc_paciente
      , paciente AS nom_paciente
      , concepto
      , vista_aux_arqueo.id_factura
      , t.id_factura AS anulada
      , fec_factura
      , vista_aux_arqueo.valor
      , t.valor AS ingreso   
      , vista_aux_arqueo.`estado`  
FROM vista_aux_arqueo
      LEFT JOIN (SELECT id_factura,valor FROM vista_aux_arqueo WHERE `estado`=0 AND fec_factura <> IFNULL(DATE_FORMAT(fec_anulacion,'%Y-%m-%d'),'1900-01-01')) AS t
      ON(vista_aux_arqueo.id_fac_anulada = t.id_factura)
WHERE fec_factura <> IFNULL(DATE_FORMAT(fec_anulacion,'%Y-%m-%d'),'1900-01-01') AND fec_factura = '$fecha' AND `id_fac_anulada` IS NOT NULL) AS temp
    WHERE temp.cc_facturador ='$tercero'
    GROUP BY temp.cc_facturador";
    $res = $cmd2->query($sql);
    $datos = $res->fetch();
} catch (Exception $e) {
    echo $e->getMessage();
}
// Consulto el valor que el facturador ya tiene registrado en esa fecha
$sql = "SELECT SUM(`valor_fac`) as valor FROM `tes_causa_arqueo` WHERE (`id_tercero` =$tercero AND `fecha` ='$fecha');";
$res = $cmd->query($sql);
$registro = $res->fetch();

$valor = $valor + $datos['ingreso'] - $registro['valor'];

if ($valor > 0) {
    $response[] = array("valor" => $valor);
} else {
    $response[] = array("valor" => 0);
}
echo json_encode($response);
$cmd = null;
$cmd2 = null;
exit;
