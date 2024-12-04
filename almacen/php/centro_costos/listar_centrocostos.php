<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1){
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column']+1;
$dir = $_POST['order'][0]['dir'];

$where = "WHERE tb_centrocostos.id_centro<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND tb_centrocostos.nom_centro LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM tb_centrocostos WHERE id_centro<>0";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM tb_centrocostos $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT tb_centrocostos.id_centro,tb_centrocostos.nom_centro,                
                IF(tb_centrocostos.es_clinico=1,'SI','NO') AS es_clinico,
                CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_respon
            FROM tb_centrocostos    
            INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=tb_centrocostos.id_responsable)
            $where ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_centro'];
        /*Permisos del usuario
           5010-Opcion [General][Centros Costo]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
        */    
        if (PermisosUsuario($permisos, 5010, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5010, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }

        $sql = "SELECT CONCAT_WS(' - ',ctb_pgcp.cuenta,ctb_pgcp.nombre) AS cuenta
                FROM tb_centrocostos_cta    
                INNER JOIN ctb_pgcp ON (ctb_pgcp.id_pgcp=tb_centrocostos_cta.id_cuenta)            
                WHERE tb_centrocostos_cta.estado=1 AND tb_centrocostos_cta.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND tb_centrocostos_cta.id_cencos=$id
                ORDER BY tb_centrocostos_cta.fecha_vigencia DESC LIMIT 1";
        $rs = $cmd->query($sql);
        $objs_cta = $rs->fetch();
        $cuenta = isset($objs_cta['cuenta']) ? $objs_cta['cuenta'] : '';

        $data[] = [
            "id_centro" => $id,          
            "nom_centro" => mb_strtoupper($obj['nom_centro']),             
            "cuenta" => $cuenta,
            "es_clinico" => $obj['es_clinico'],
            "usr_respon" => mb_strtoupper($obj['usr_respon']), 
            "botones" => '<div class="text-center centro-vertical">' . $editar . $eliminar . '</div>',
        ];
    }
}
$cmd = null;
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
