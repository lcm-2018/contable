<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

$id_t = isset($_POST['id_t']) ? $_POST['id_t'] : exit('Acción no permitida');


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `ctt_documentos`.`fec_inicio`
                , `ctt_documentos`.`fec_vig`
                , `ctt_documentos`.`id_soportester`
                , `ctt_soportes_contrato`.`descripcion`
            FROM
                `ctt_documentos`
                INNER JOIN `ctt_soportes_contrato` 
                    ON (`ctt_documentos`.`id_soporte` = `ctt_soportes_contrato`.`id_soporte`)
            WHERE (`ctt_documentos`.`id_tercero` = $id_t)";
    $rs = $cmd->query($sql);
    $docs = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

if (!empty($docs)) {
    foreach ($docs as $d) {
        $id_doc = $d['id_soportester'];
        $borrar = '';
        if ($d['fec_vig'] > date('Y-m-d')) {
            $estado = '<span class="fas fa-toggle-on fa-lg estado activo" ></span>';
        } else {
            $estado = '<span class="fas fa-toggle-off fa-lg estado inactivo"></span>';
        }
        if (PermisosUsuario($permisos, 5201, 4) || $id_rol == 1) {
            $borrar = '<a onclick="BorrarDocumentoTercero(' . $id_doc . ')" class="btn btn-outline-warning btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            'id_doc' => $id_doc,
            'tipo' => mb_strtoupper($d['descripcion']),
            'fec_inicio' => $d['fec_inicio'],
            'fec_vigencia' => $d['fec_vig'],
            'vigente' => '<div class="text-center">' . $estado . '</div>',
            'doc' => '<div class="text-center"><button text="' . $id_doc . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb descargar" title="Descargar"><span class="far fa-file-pdf fa-lg"></span></button>' . $borrar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
