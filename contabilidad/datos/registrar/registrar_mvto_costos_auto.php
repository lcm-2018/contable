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
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$iduser = $_SESSION['id_user'];
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $fecha->format('Y-m-d H:i:s');
$response['status'] = 'error';

try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `crp`.`id_tercero_api`
                , IFNULL(`crp`.`valor`,0) - IFNULL(`crp`.`valor_liberado`,0) AS `valor_crp` 
                , `pto_cdp_detalle`.`id_rubro`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , IFNULL(`t1`.`valor`,0) - IFNULL(`t1`.`valor_liberado`,0) AS `valor_cop`
                , `crp`.`id_pto_crp_det`

            FROM
                `ctb_doc`
                INNER JOIN `pto_crp` 
                    ON (`ctb_doc`.`id_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN 
                    (SELECT 
                        `pto_crp_detalle`.`id_pto_crp_det`
                        , `pto_crp_detalle`.`id_pto_crp`
                        , `pto_crp_detalle`.`id_pto_cdp_det`
                        , `pto_crp_detalle`.`id_tercero_api`
                        , SUM(`pto_crp_detalle`.`valor`) AS `valor`
                        , SUM(`pto_crp_detalle`.`valor_liberado`) AS `valor_liberado`
                    FROM `pto_crp_detalle`
                    GROUP BY `id_pto_crp`, `id_pto_cdp_det`) AS `crp`
                    ON (`crp`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`crp`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `id_pto_crp_det`
                        , IFNULL(SUM(`valor`),0) AS `valor`
                        , IFNULL(SUM(`valor_liberado`),0) AS `valor_liberado`
                    FROM
                        `pto_cop_detalle`
                    INNER JOIN `ctb_doc` 
                        ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`ctb_doc`.`estado` > 0)
                    GROUP BY `id_pto_crp_det`) AS `t1`  
                    ON (`t1`.`id_pto_crp_det` = `crp`.`id_pto_crp_det`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

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
    $centros = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT 
                `tt`.`id_ctb_doc`
                , `cop`.`valor`
            FROM
                (SELECT 
                    MAX(`id_ctb_doc`) AS `id_ctb_doc` 
                FROM `ctb_doc` 
                WHERE `id_crp` = $id_crp AND `id_ctb_doc` <> $id_doc AND `estado` = 2) AS `tt`
                INNER JOIN 
                    (SELECT
                        `id_ctb_doc`
                        , SUM(IFNULL(`valor`,0)- IFNULL(`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_cop_detalle`
                    GROUP BY `id_ctb_doc`) AS `cop`
                    ON(`tt`.`id_ctb_doc`=`cop`.`id_ctb_doc`)";
    $rs = $cmd->query($sql);
    $doc_anterior = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$bandera = false;
$val_ant = 0;
$impuestos = [];
if (empty($centros) && !empty($datos)) {
    $bandera = true;
    $val_ant = $doc_anterior['valor'];
    $id_ant = $doc_anterior['id_ctb_doc'];
    try {
        $sql = "SELECT `id_area_cc`,`valor`/$val_ant AS `porcentaje`
                FROM `ctb_causa_costos` WHERE `id_ctb_doc` = $id_ant";
        $rs = $cmd->query($sql);
        $centros = $rs->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $sql = "SELECT `id_rango`,`valor_base`,`tarifa`,`valor_retencion`,`id_terceroapi` 
                FROM `ctb_causa_retencion` WHERE `id_ctb_doc` = $id_ant";
        $rs = $cmd->query($sql);
        $impuestos = $rs->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
$cmd = null;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $cmd->beginTransaction();
    $cambios = 0;
    // insertar los datos de la imputación
    $sql = "INSERT INTO `pto_cop_detalle`
                (`id_ctb_doc`, `id_pto_crp_det`, `id_tercero_api`, `valor`, `valor_liberado`, `id_user_reg`, `fecha_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->bindParam(2, $id_crp_det, PDO::PARAM_INT);
    $sql->bindParam(3, $id_tercero_api, PDO::PARAM_INT);
    $sql->bindParam(4, $valor, PDO::PARAM_STR);
    $sql->bindParam(5, $liberado, PDO::PARAM_STR);
    $sql->bindParam(6, $id_user, PDO::PARAM_INT);
    $sql->bindParam(7, $fecha2, PDO::PARAM_STR);
    foreach ($datos as $dato) {
        $saldo =  $dato['valor_crp'] - $dato['valor_cop'] - $valor;
        if ($saldo < 0) {
            $response['msg'] = 'El valor a imputar supera el saldo disponible del contrato';
            echo json_encode($response);
            exit();
        } else {
            $id_crp_det = $dato['id_pto_crp_det'];
            $id_tercero_api = $dato['id_tercero_api'];
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $cambios++;
            } else {
                echo $sql->errorInfo()[2];
            }
        }
    }
    if ($cambios == 0) {
        $cmd->rollBack();
        $response['msg'] = 'Problemas al registrar la imputación, no se realizaron cambios';
        echo json_encode($response);
        exit();
    }
    $cc = 0;
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
                if ($bandera) {
                    $valor_cc = $valor * $centro['porcentaje'];
                } else {
                    $valor_cc = $centro['horas_mes'] * $valor / $total_horas;
                }
                $sql->execute();
                if ($cmd->lastInsertId() > 0) {
                    $cc++;
                } else {
                    $response['msg'] = $sql->errorInfo()[2];
                    break;
                }
            }
        } else {
            $response['msg'] = 'No se encontró el centro de costo relacionado con el contrato, registre el centro de costo manualmente';
        }
    }
    if ($cc == 0) {
        $cmd->rollBack();
        $response['msg'] = 'No se realizaron cambios';
    } else {
        if ($valor == $val_ant) {
            $imp = 0;
            $quer2 = "INSERT INTO `ctb_causa_retencion`
                        (`id_ctb_doc`,`id_rango`,`valor_base`,`tarifa`,`valor_retencion`,`id_terceroapi`,`id_user_reg`,`fecha_reg`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $quer2 = $cmd->prepare($quer2);
            $quer2->bindParam(1, $id_doc, PDO::PARAM_INT);
            $quer2->bindParam(2, $id_rango, PDO::PARAM_INT);
            $quer2->bindParam(3, $base, PDO::PARAM_STR);
            $quer2->bindParam(4, $tarifa, PDO::PARAM_STR);
            $quer2->bindParam(5, $valor_rte, PDO::PARAM_STR);
            $quer2->bindParam(6, $id_terceroapi, PDO::PARAM_INT);
            $quer2->bindParam(7, $iduser, PDO::PARAM_INT);
            $quer2->bindValue(8, $fecha2);
            foreach ($impuestos as $im) {
                $id_rango = $im['id_rango'];
                $base = $im['valor_base'];
                $tarifa = $im['tarifa'];
                $valor_rte = $im['valor_retencion'];
                $id_terceroapi = $im['id_terceroapi'];
                $quer2->execute();
                $insert = $cmd->lastInsertId();
                if ($insert > 0) {
                    $imp++;
                } else {
                    $response['msg'] = 'Error al registrar la retención: ' . $quer2->errorInfo()[2];
                    break;
                }
            }
            if ($imp > 0) {
                $cmd->commit();
                $response['status'] = 'ok';
                $response['msg'] = 'imp';
            } else {
                $cmd->rollBack();
                $response['msg'] = 'No se realizaron cambios';
            }
        } else {
            $cmd->commit();
            $response['status'] = 'ok';
            $response['msg'] = 'cc';
        }
    }
} catch (PDOException $e) {
    $cmd->rollBack();
    $response['msg'] = $e->getMessage();
}
$acumulado = GetValoresCxP($id_doc, $cmd);
$acumulado = $acumulado['val_ccosto'];
$response['acumulado'] = pesos($acumulado);
echo json_encode($response);
