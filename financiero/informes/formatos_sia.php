<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT `id`,`formato`,`nombre` FROM `fin_informes`";
    $res = $cmd->query($sql);
    $formatos = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$opcion = $_POST['periodo'];
$tabla = '';
$body = '';
foreach ($formatos as $formato) {
    $body .= "<tr>
            <td>{$formato['id']}</td>
            <td>{$formato['formato']}</td>
            <td>{$formato['nombre']}</td>
            <td class='text-center'>
                <button class='btn btn-outline-success btn-sm' onclick='LoadInforme({$formato['id']})' title='{$formato['nombre']}'><i class='fas fa-file-excel fa-lg'></i></button>
            </td>
        </tr>";
}
$tabla = '<table class="table table-sm table-striped table-bordered table-hover" id="tablaInformesFinancieros" style="width:100%">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Formato</th>
                    <th>Nombre</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>' . $body . '
            </tbody>
        </table>';
echo $tabla;
