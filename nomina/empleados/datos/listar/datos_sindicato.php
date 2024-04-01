<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                nom_cuota_sindical
            INNER JOIN nom_sindicatos 
                ON (nom_cuota_sindical.id_sindicato = nom_sindicatos.id_sindicato)
            WHERE id_empleado ='$id'";
    $rs = $cmd->query($sql);
    $sindicatos = $rs->fetchAll();
    $sql = "SELECT id_cuota_sindical, SUM(val_aporte) AS aportes, COUNT(val_aporte) AS cant_aportes
            FROM
                nom_liq_sindicato_aportes
            GROUP BY id_cuota_sindical";
    $rs = $cmd->query($sql);
    $tot_aportes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
if (!empty($sindicatos)) {
    foreach ($sindicatos as $s) {
        $idSind = $s['id_cuota_sindical'];
        $editar = $borrar = null;
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $idSind . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $idSind . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        }
        if ($s['estado'] > 1) {
            $borrar = null;
        }
        $key = array_search($idSind, array_column($tot_aportes, 'id_cuota_sindical'));
        if (false !== $key) {
            $aportes = $tot_aportes[$key]['aportes'];
            $cant_aportes = $tot_aportes[$key]['cant_aportes'];
        } else {
            $aportes = '0';
            $cant_aportes = '0';
        }
        $data[] = [
            'id_aporte' => $idSind,
            'sindicato' => mb_strtoupper($s['nom_sindicato']),
            'porcentaje' => $s['porcentaje_cuota'] . '%',
            'cantidad_aportes' => $cant_aportes,
            'total_aportes' => pesos($aportes),
            'fec_inicio' => $s['fec_inicio'],
            'fec_fin' => $s['fec_fin'],
            'val_sind' => '<div class="text-right">' . pesos($s['val_sidicalizacion']) . '</div>',
            'botones' => '<div class="center-block">' . $editar . $borrar . '<button value= "' . $idSind . '" class="btn btn-outline-warning btn-sm btn-circle detalles" title="Detalles Sindicato"><span class="far fa-eye fa-lg"></span></button></div>'
        ];
    }
} else {
    $data = [
        'id_aporte' => '',
        'sindicato' => '',
        'porcentaje' => '',
        'cantidad_aportes' => '',
        'total_aportes' => '',
        'fecha_inicio' => '',
        'fecha_fin' => '',
        'botones' => '',
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
