<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';
include_once '../../../financiero/consultas.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}
$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : exit('Acceso no disponible');
$id_doc = $_POST['id_doc'];
$valor = $_POST['valor'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "SELECT
                `ctt_destino_contrato`.`id_area_cc`
                , `ctt_destino_contrato`.`horas_mes`
            FROM 
                `ctt_adquisiciones`
            INNER JOIN `ctt_contratos`
                ON (`ctt_adquisiciones`.`id_adquisicion` = `ctt_contratos`.`id_compra`)
            INNER JOIN `ctt_destino_contrato`
                ON (`ctt_adquisiciones`.`id_adquisicion` = `ctt_destino_contrato`.`id_adquisicion`)
            WHERE `ctt_contratos`.`id_contrato_compra` IN
                (SELECT
                    `ctt_contratos`.`id_contrato_compra`
                FROM
                    `pto_crp`
                    INNER JOIN `ctt_adquisiciones` 
                    ON (`pto_crp`.`id_cdp` = `ctt_adquisiciones`.`id_cdp`)
                    INNER JOIN `ctt_contratos` 
                    ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                WHERE (`pto_crp`.`id_pto_crp` = $id_crp)
                UNION ALL 
                SELECT
                    `ctt_novedad_adicion_prorroga`.`id_adq` AS `id_contrato_compra`
                FROM
                    `pto_crp`
                    INNER JOIN `ctt_novedad_adicion_prorroga` 
                    ON (`pto_crp`.`id_cdp` = `ctt_novedad_adicion_prorroga`.`id_cdp`)
                WHERE (`pto_crp`.`id_pto_crp` = $id_crp))";
    $rs = $cmd->query($sql);
    $centros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$iduser = $_SESSION['id_user'];
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $fecha->format('Y-m-d H:i:s');
$response['status'] = 'error';
try {
    $query = "SELECT `id_area_cc` FROM `ctb_causa_costos` WHERE `id_ctb_doc` = $id_doc";
    $rs = $cmd->query($query);
    $rs = $rs->fetchAll();
    if (count($rs) > 0) {
        $response['msg'] = 'Ya se ha registrado el centro de costo para este documento';
    } else {
        $sql = "INSERT INTO `ctb_causa_costos`
                (`id_ctb_doc`,`id_area_cc`,`valor`,`id_user_reg`,`fecha_reg`)
            VALUES (?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
        $sql->bindParam(2, $id_cc, PDO::PARAM_INT);
        $sql->bindParam(3, $valor_cc, PDO::PARAM_STR);
        $sql->bindParam(4, $iduser, PDO::PARAM_INT);
        $sql->bindParam(5, $fecha2, PDO::PARAM_STR);
        if (!empty($centros)) {
            $total_horas = array_sum(array_column($centros, 'horas_mes'));
            foreach ($centros as $centro) {
                $id_cc = $centro['id_area_cc'];
                $valor_cc = $centro['horas_mes'] * $valor / $total_horas;
                $sql->execute();
                if ($cmd->lastInsertId() > 0) {
                    $response['status'] = 'ok';
                } else {
                    $response['msg'] = $sql->errorInfo()[2];
                    break;
                }
            }
        } else {
            $response['msg'] = 'No se encontró el centro de costo relacionado con el contrato, registre el centro de costo manualmente';
        }
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}
$acumulado = GetValoresCxP($id_doc, $cmd);
$acumulado = $acumulado['val_ccosto'];
$response['acumulado'] = pesos($acumulado);
echo json_encode($response);
