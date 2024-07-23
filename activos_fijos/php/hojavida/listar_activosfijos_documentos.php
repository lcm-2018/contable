<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../common/funciones_generales.php';

$id_hv =  $_POST['id_hv'];


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM acf_hojavida_documentos
            WHERE id_activo_fijo=" . $id_hv;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];


    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT
            HVD.id_documento,
            HV.placa,
            CASE HVD.tipo WHEN 1 THEN 'FICHA TECNICA' WHEN 2 THEN 'MANUAL' WHEN 3 THEN 'OTRO' END AS tipo, 
            HVD.descripcion,
            HVD.archivo,
            U.login
        FROM acf_hojavida_documentos HVD
            INNER JOIN acf_hojavida HV ON HV.id = HVD.id_activo_fijo
            INNER JOIN seg_usuarios_sistema U ON U.id_usuario = HVD.id_usuario_crea
            WHERE HV.id=" . $id_hv;
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id_documento = $obj['id_documento'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5703, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id_documento . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5703, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id_documento . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id" => $obj['id_documento'],
            "placa" => $obj['placa'],
            "tipo" => $obj['tipo'],
            "descripcion" => $obj['descripcion'],
            "archivo" => $obj['archivo'],
            "usuario" => $obj['login'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $eliminar . '</div>',
        ];
    }    
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords
];

echo json_encode($datos);

   