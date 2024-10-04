<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_nominas`.`id_nomina`
                , `nom_nominas`.`descripcion`
                , `nom_nominas`.`mes`
                , `nom_nominas`.`vigencia`
                , `nom_nominas`.`tipo`
                , `nom_nominas`.`estado`
                , `nom_meses`.`nom_mes`
            FROM
                `nom_nominas`
                LEFT JOIN `nom_meses` 
                    ON (`nom_nominas`.`mes` = `nom_meses`.`codigo`)
            WHERE `nom_nominas`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $nominas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($nominas)) {
    foreach ($nominas as $n) {
        $detalle = '<button value="' . $n['id_nomina'] . '" type="button" class="btn btn-outline-warning btn-sm btn-circle detalle"><i class="fa fa-eye"></i></button>';
        $compare = '<button value="' . $n['id_nomina'] . '" type="button" class="btn btn-outline-light btn-sm btn-circle comparePatronal" title="Comparar Seguridad Social y Parafiscales"><i class="fas fa-not-equal"></i></button>';
        if (PermisosUsuario($permisos, 5104, 3) || $id_rol == 1) {
            $cargue_patron = '<button value="' . $n['id_nomina'] . '" type="button" class="btn btn-outline-primary btn-sm btn-circle carguePatronal" title="Cargar aportes patronales"><i class="fas fa-upload"></i></button>';
        } else {
            $cargue_patron = null;
        }
        if ($n['estado'] == 1) {
            $estado = '<span class="badge badge-bill badge-secondary">PENDIENTE</span>';
            $solcdp = $cdpPatron = null;
        } else {
            $estado = '<span class="badge badge-bill badge-success">DEFINITIVA</span>';
            $solcdp = '<button value="' . $n['id_nomina'] . '" type="button" class="btn btn-outline-success btn-sm btn-circle solcdp" title="Imprimir solicitud de CDP"><i class="fa fa-print"></i></button>';
            $cdpPatron = '<button value="' . $n['id_nomina'] . '" type="button" class="btn btn-outline-info btn-sm btn-circle cpdPatronal" title="Imprimir solicitud de CDP Patronal"><i class="fa fa-print"></i></button>';
        }
        if ($n['estado'] == 0) {
            $estado = '<span class="badge badge-bill badge-secondary">OTRO</span>';
            $solcdp = $cdpPatron = $pdf =  $compare = $cargue_patron = NULL;
        }
        if ($n['estado'] > 1) {
            $compare = $cargue_patron = null;
        }
        $pdf = '<button value="' . $n['id_nomina'] . '" type="button" class="btn btn-outline-danger btn-sm btn-circle impPDF" title="Exportar a PDF"><i class="far fa-file-pdf"></i></button>';
        $data[] = [
            'id_nomina' => $n['id_nomina'],
            'descripcion' => $n['descripcion'],
            'mes' => $n['nom_mes'],
            'tipo' => $n['tipo'],
            'estado' => '<div class="text-center">' . $estado . '</div>',
            'botones' => '<div class="text-center">' . $detalle . $solcdp . $cdpPatron . $pdf .  $compare . $cargue_patron . '</div>'
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);