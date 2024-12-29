<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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

$where = "WHERE tb_homologacion.id_homo<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND CONCAT(tb_regimenes.descripcion_reg,tb_cobertura.nom_cobertura,tb_modalidad.nom_modalidad) LIKE '%" . $_POST['nombre'] . "%'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM tb_homologacion WHERE id_homo<>0";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM tb_homologacion
            INNER JOIN tb_regimenes ON (tb_regimenes.id_regimen=tb_homologacion.id_regimen)
            INNER JOIN tb_cobertura ON (tb_cobertura.id_cobertura=tb_homologacion.id_cobertura)
            INNER JOIN tb_modalidad ON (tb_modalidad.id_modalidad=tb_homologacion.id_modalidad) $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT tb_homologacion.id_homo,tb_regimenes.descripcion_reg AS nom_regimen,
	            tb_cobertura.nom_cobertura,tb_modalidad.nom_modalidad,tb_homologacion.fecha_vigencia,
                c_presto.cod_pptal AS cta_presupuesto,
                c_presto_ant.cod_pptal AS cta_presupuesto_ant,
                c_debito.cuenta AS cta_debito,
                c_credito.cuenta AS cta_credito,
                c_copago.cuenta AS cta_copago,
                c_copago_cap.cuenta AS cta_copago_capitado,
                c_gloini_deb.cuenta AS cta_glosaini_debito,
                c_gloini_cre.cuenta AS cta_glosaini_credito,
                c_glo_def.cuenta AS cta_glosadefinitiva,
                c_devol.cuenta AS cta_devolucion,
                c_caja.cuenta AS cta_caja,
                c_fac_glo.cuenta AS cta_fac_global,
                c_x_ide.cuenta AS cta_x_ident,
                IF(c.id IS NULL,'','X') AS vigente,
	            IF(tb_homologacion.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM tb_homologacion
            INNER JOIN tb_regimenes ON (tb_regimenes.id_regimen=tb_homologacion.id_regimen)
            INNER JOIN tb_cobertura ON (tb_cobertura.id_cobertura=tb_homologacion.id_cobertura)
            INNER JOIN tb_modalidad ON (tb_modalidad.id_modalidad=tb_homologacion.id_modalidad)
            LEFT JOIN pto_cargue  AS c_presto ON (c_presto.id_cargue=tb_homologacion.id_cta_presupuesto)
            LEFT JOIN pto_cargue  AS c_presto_ant ON (c_presto_ant.id_cargue=tb_homologacion.id_cta_presupuesto_ant)
            LEFT JOIN ctb_pgcp AS c_debito ON (c_debito.id_pgcp=tb_homologacion.id_cta_debito)
            LEFT JOIN ctb_pgcp AS c_credito ON (c_credito.id_pgcp=tb_homologacion.id_cta_credito)
            LEFT JOIN ctb_pgcp AS c_copago ON (c_copago.id_pgcp=tb_homologacion.id_cta_copago)
            LEFT JOIN ctb_pgcp AS c_copago_cap ON (c_copago_cap.id_pgcp=tb_homologacion.id_cta_copago_capitado)
            LEFT JOIN ctb_pgcp AS c_gloini_deb ON (c_gloini_deb.id_pgcp=tb_homologacion.id_cta_glosaini_debito)
            LEFT JOIN ctb_pgcp AS c_gloini_cre ON (c_gloini_cre.id_pgcp=tb_homologacion.id_cta_glosaini_credito)
            LEFT JOIN ctb_pgcp AS c_glo_def ON (c_glo_def.id_pgcp=tb_homologacion.id_cta_glosadefinitiva)        
            LEFT JOIN ctb_pgcp AS c_devol ON (c_devol.id_pgcp=tb_homologacion.id_cta_devolucion)
            LEFT JOIN ctb_pgcp AS c_caja ON (c_caja.id_pgcp=tb_homologacion.id_cta_caja)
            LEFT JOIN ctb_pgcp AS c_fac_glo ON (c_fac_glo.id_pgcp=tb_homologacion.id_cta_fac_global)
            LEFT JOIN ctb_pgcp AS c_x_ide ON (c_x_ide.id_pgcp=tb_homologacion.id_cta_x_ident)
            LEFT JOIN (SELECT MAX(id_homo) AS id FROM tb_homologacion
                        WHERE estado=1 AND fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d')
                        GROUP BY id_regimen,id_cobertura,id_modalidad) AS c ON (c.id=tb_homologacion.id_homo)
            $where ORDER BY $col $dir $limit";

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
        $id = $obj['id_homo'];
        /*Permisos del usuario
            5507-Opcion [Otros][Cuentas Facturación]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
        */    
        if (PermisosUsuario($permisos, 5507, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5507, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_homo" => $id,
            "nom_regimen" => mb_strtoupper($obj['nom_regimen']),
            "nom_cobertura" => mb_strtoupper($obj['nom_cobertura']),
            "nom_modalidad" => mb_strtoupper($obj['nom_modalidad']),
            "fecha_vigencia" => $obj['fecha_vigencia'],
            "cta_presupuesto" => $obj['cta_presupuesto'],
            "cta_presupuesto_ant" => $obj['cta_presupuesto_ant'],
            "cta_debito" => $obj['cta_debito'],
            "cta_credito" => $obj['cta_credito'],
            "cta_copago" => $obj['cta_copago'],
            "cta_copago_capitado" => $obj['cta_copago_capitado'],
            "cta_glosaini_debito" => $obj['cta_glosaini_debito'],
            "cta_glosaini_credito" => $obj['cta_glosaini_credito'],
            "cta_glosadefinitiva" => $obj['cta_glosadefinitiva'],
            "cta_devolucion" => $obj['cta_devolucion'],
            "cta_caja" => $obj['cta_caja'],
            "cta_fac_global" => $obj['cta_fac_global'],
            "cta_x_ident" => $obj['cta_x_ident'],
            "vigente" => $obj['vigente'],
            "estado" => $obj['estado'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $eliminar . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
