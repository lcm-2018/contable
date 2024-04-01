<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_pto_presupuestos = $_POST['id_cpto'];
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_cargue`
                , `cod_pptal`
                , `nom_rubro`
                , `tipo_dato`
            FROM
                `pto_cargue`
            WHERE `id_pto` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `estado` FROM `pto_presupuestos` WHERE `id_pto` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $estado = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $id_pto = $lp['id_cargue'];
        $tipo_dato = $lp['tipo_dato'] == 0 ? 'M' : 'D';
        $editar =  $detalles =  $borrar =  null;
        //Consulto el valor cargado a presupuestos por cada rubro y presupuesto seleccionado
        $sql = "SELECT sum(valor_aprobado) as valor  FROM pto_cargue where cod_pptal like '$lp[cod_pptal]%' and id_pto ='$id_pto_presupuestos' and tipo_dato=1";
        $rs = $cmd->query($sql);
        $valor = $rs->fetch();
        $valor_ppto = number_format($valor['valor'], 2, '.', ',');
        if ($estado['estado'] == '1') {
            if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
                $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
            if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
                $borrar = '<a value="' . $id_pto . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        }
        $data[] = [

            'rubro' => $lp['cod_pptal'],
            'nombre' => $lp['nom_rubro'],
            'tipo_dato' => $tipo_dato,
            'valor' => '<div class="text-right">' . $valor_ppto . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $detalles . '</div>',

        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
