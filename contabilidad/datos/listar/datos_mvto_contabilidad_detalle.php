<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../terceros.php';

// Div de acciones de la lista
$id_ctb_doc = $_POST['id_doc'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_libaux`.`id_ctb_libaux`
                , `ctb_libaux`.`id_ctb_doc`
                , `ctb_libaux`.`id_cuenta`
                , `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , `ctb_libaux`.`id_tercero_api`
                , `ctb_doc`.`estado`
            FROM
                `ctb_libaux`
                LEFT JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (`ctb_libaux`.`id_ctb_doc` = $id_ctb_doc)";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `estado`
            FROM `ctb_doc`
            WHERE (`id_ctb_doc` = $id_ctb_doc)";
    $rs = $cmd->query($sql);
    $estado = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$estado = !empty($estado) ? $estado['estado'] : 1;
$data = [];
$totDebito = 0;
$totCredito = 0;
if (!empty($listappto)) {
    $id_t = [];
    foreach ($listappto as $lp) {
        if ($lp['id_tercero_api'] != '') {
            $id_t[] = $lp['id_tercero_api'];
        }
    }
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);

    foreach ($listappto as $lp) {
        $id = $lp['id_ctb_libaux'];
        $id_ctb = $lp['id_ctb_doc'];
        $cuenta = $lp['cuenta'] . ' - ' . $lp['nombre'];
        $deb = $lp['debito'];
        $cred = $lp['credito'];
        $totDebito += $deb;
        $totCredito += $cred;
        $valorDebito =  number_format($deb, 2, '.', ',');
        $valorCredito =  number_format($cred, 2, '.', ',');
        $key = array_search($lp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
        $tercero = $key !== false ? $terceros[$key]['nom_tercero'] : '';
        $borrar = $editar = $detalles = $registrar = null;
        if ($estado == 1) {
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            if (PermisosUsuario($permisos, 5501, 2) || $id_rol == 1) {
                $registrar = '<a value="' . $id_ctb . '" onclick="CargarFormularioCrpp(' . $id_ctb . ')" class="text-blue " role="button" title="Detalles"><span>Registrar</span></a>';
            }
            if (PermisosUsuario($permisos, 5501, 3) || $id_rol == 1) {
                $editar = '<a text="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                /*
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_ctb . '" class="dropdown-item sombra carga" href="#">Cargar presupuesto</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Another action</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Something else here</a>
            </div>';*/
            }
            if (PermisosUsuario($permisos, 5501, 4) || $id_rol == 1) {
                $borrar = '<a value="' . $id . '" onclick="eliminarRegistroDetalle(' . $id . ')"class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Borrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        }
        $data[] = [
            'cuenta' => $cuenta,
            'tercero' => ltrim($tercero),
            'debito' => '<div class="text-right">' . $valorDebito . '</div>',
            'credito' => '<div class="text-right">' . $valorCredito . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . '</div>',
        ];
    }
}
$debe = number_format($totDebito, 2, '.', ',');
$haber = number_format($totCredito, 2, '.', ',');
$valor = number_format($totDebito, 2, '.', '') - number_format($totCredito, 2, '.', '');
$msg = $valor == 0 ? '<span class="badge badge-success">Correcto</span>' : '<span class="badge badge-danger">Incorrecto</span>';
$tfoot = [
    'cuenta' => '1',
    'tercero' => '<div class="text-center"><b>TOTAL</b> (Sumas iguales)</div>',
    'debito' => '<div class="text-right">' . $debe . '</div>',
    'credito' => '<div class="text-right">' . $haber . '</div>',
    'botones' => '<div class="text-center" style="position:relative">' . $msg . '<input type="hidden" id="total" value="' . $valor . '"></div>',
];
$cmd = null;
$datos = [
    'data' => $data,
    'tfoot' => $tfoot
];

echo json_encode($datos);
