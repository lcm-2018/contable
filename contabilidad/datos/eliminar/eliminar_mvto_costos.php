<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];
$id_doc = $_post['id_doc'];
include '../../../conexion.php';
include_once '../../../financiero/consultas.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}
try {
    $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = $pdo->prepare("DELETE FROM ctb_causa_costos WHERE id = ?");
    $query->bindParam(1, $id);
    $query->execute();
    $acumulado = GetValoresCxP($id_doc, $pdo);
    $acumulado = pesos($acumulado['val_ccosto']);
    $response[] = array("value" => 'ok', "id" => $id, "acumulado" => $acumulado);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
