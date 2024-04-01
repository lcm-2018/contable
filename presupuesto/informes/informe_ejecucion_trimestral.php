<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$data = explode(',', file_get_contents("php://input"));
$tipo_pto = $data['0'];
$id_corte = $data['1'];
$fecha_ini = $vigencia . '-01-01';
switch ($id_corte) {
    case 1:
        $fecha_corte = $vigencia . '-03-31';
        $codigo = '10303';
        break;
    case 2:
        $fecha_corte = $vigencia . '-06-30';
        $codigo = '10606';
        break;
    case 3:
        $fecha_corte = $vigencia . '-09-30';
        $codigo = '10909';
        break;
    case 4:
        $fecha_corte = $vigencia . '-03-31';
        $codigo = '11212';
        break;
    default:
        exit();
        break;
}
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT
                `nombre`
                , `nit`
                , `dig_ver`
            FROM
                `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$sqlDelete = "DELETE FROM `tmp_ctb_libaux`";
$deleteResult = $cmd->query($sqlDelete);
// Cargo la informacion de la tabla tmp_ctb_libaux
// Cargar la información del archivo SQL en la tabla tmp_ctb_libaux
$sqlFilePath = "C:/Users/LCM01/Downloads/in2.sql"; // Ruta al archivo SQL
//$sqlFilePath = "/home/admin/in2.sql"; // Ruta al archivo SQL
$sqlContent = file_get_contents($sqlFilePath); // Leer el contenido del archivo

if ($sqlContent !== false) {
    // Ejecutar el contenido del archivo SQL como consulta
    $sqlin = $cmd->exec($sqlContent);

    if ($sqlin === false) {
        echo "Error al cargar datos: " . implode(" ", $cmd->errorInfo());
    }
} else {
    echo "Error al leer el archivo SQL.";
}
try {
    $sql = "SELECT
                `pto_homologa_ingresos`.`id_cgr`
                , `pto_codigo_cgr`.`codigo` AS `codigo_cgr`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`  
                , SUM(`inicial`) AS `inicial`
                , SUM(`adicion`) AS `adicion`
                , SUM(`reduccion`) AS `reduccion` 
            FROM
                (SELECT
                    `pto_cargue`.`cod_pptal`
                    , `pto_cargue`.`nom_rubro`
                    , CASE `pto_cargue`.`tipo_dato` WHEN 1 THEN 'D' WHEN 0 THEN 'M' END AS `tipo_dato`
                    , `pto_cargue`.`valor_aprobado` AS `inicial`
                    , IFNULL(`adicion`.`valor`,0) AS `adicion`
                    , IFNULL(`reduccion`.`valor`,0) AS `reduccion`  
                FROM
                    `pto_cargue`
                    LEFT JOIN (
                        SELECT
                            `pto_cargue`.`cod_pptal`
                            , `pto_cargue`.`nom_rubro`    
                            , SUM(`pto_documento_detalles`.`valor`) AS `valor`    
                        FROM
                            `pto_cargue`
                            INNER JOIN `pto_documento_detalles` 
                                ON (`pto_cargue`.`cod_pptal` = `pto_documento_detalles`.`rubro`)
                            INNER JOIN `pto_documento` 
                                ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
                        WHERE `pto_documento_detalles`.`tipo_mov` = 'ADI' AND `pto_documento`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' 
                        GROUP BY `pto_cargue`.`cod_pptal`
                    ) AS adicion 
                        ON (`pto_cargue`.`cod_pptal` = `adicion`.`cod_pptal`)
                    LEFT JOIN (
                        SELECT
                            `pto_cargue`.`cod_pptal`
                            , `pto_cargue`.`nom_rubro`    
                            , SUM(`pto_documento_detalles`.`valor`) AS `valor`    
                        FROM
                            `pto_cargue`
                            INNER JOIN `pto_documento_detalles` ON (`pto_cargue`.`cod_pptal` = `pto_documento_detalles`.`rubro`)
                            INNER JOIN `pto_documento` ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
                            INNER JOIN `pto_presupuestos` ON (`pto_documento`.`id_pto_presupuestos` = `pto_presupuestos`.`id_pto`)
                        WHERE `pto_presupuestos`.`id_tipo` = 2 AND `pto_documento_detalles`.`tipo_mov` = 'RED' AND `pto_documento`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' 
                        GROUP BY `pto_cargue`.`cod_pptal`
                    ) AS `reduccion` 
                        ON (`pto_cargue`.`cod_pptal` = `reduccion`.`cod_pptal`)
                    LEFT JOIN (
                        SELECT `cod_pptal`,`nom_rubro`,SUM(`valor`) AS `valor` FROM (	
                            SELECT
                                `pto_cargue`.`cod_pptal`
                                , `pto_cargue`.`nom_rubro`    
                                , `pto_documento_detalles`.`valor` AS `valor`    
                            FROM
                                `pto_cargue`
                                INNER JOIN `pto_documento_detalles` ON (`pto_cargue`.`cod_pptal` = `pto_documento_detalles`.`rubro`)
                                INNER JOIN `pto_documento` ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
                                INNER JOIN `pto_presupuestos` ON (`pto_documento`.`id_pto_presupuestos` = `pto_presupuestos`.`id_pto`)
                            WHERE `pto_presupuestos`.`id_tipo` = 1 AND `pto_documento_detalles`.`tipo_mov` = 'RAD' AND date_format(`pto_documento`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_corte'
                            UNION ALL
                            SELECT
                                `pto_cargue`.`cod_pptal`
                                , `pto_cargue`.`nom_rubro`    
                                , `tmp_ctb_libaux`.`valordeb` AS `valor`    
                            FROM
                                `pto_cargue`
                                INNER JOIN `tmp_ctb_libaux` ON (`tmp_ctb_libaux`.`cuenta` = `pto_cargue`.`cod_pptal`)
                            WHERE `tmp_ctb_libaux`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `tmp_ctb_libaux`.`tipo` = 'RAD'
                        ) AS `rec` GROUP BY `cod_pptal`	 
                    ) AS `reconocimiento` 
                        ON (`pto_cargue`.`cod_pptal`=`reconocimiento`.`cod_pptal`)
                    LEFT JOIN (
                        SELECT `cod_pptal`,`nom_rubro`,SUM(`valor`) AS `valor` FROM (	
                            SELECT
                                `pto_cargue`.`cod_pptal`
                                , `pto_cargue`.`nom_rubro`    
                                , `pto_documento_detalles`.`valor` AS `valor`    
                            FROM
                                `pto_cargue`
                                INNER JOIN `pto_documento_detalles` ON (`pto_cargue`.`cod_pptal` = `pto_documento_detalles`.`rubro`)
                                INNER JOIN `pto_documento` ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
                                INNER JOIN `pto_presupuestos` ON (`pto_documento`.`id_pto_presupuestos` = `pto_presupuestos`.`id_pto`)
                            WHERE `pto_presupuestos`.`id_tipo` = 1 AND `pto_documento_detalles`.`tipo_mov` = 'REC' AND `pto_documento`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte'
                            UNION ALL
                            SELECT
                                `pto_cargue`.`cod_pptal`
                                , `pto_cargue`.`nom_rubro`    
                                , `tmp_ctb_libaux`.`valordeb` AS `valor`    
                            FROM
                                `pto_cargue`
                                INNER JOIN `tmp_ctb_libaux` ON (`tmp_ctb_libaux`.`cuenta`=`pto_cargue`.`cod_pptal`)
                            WHERE `tmp_ctb_libaux`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `tmp_ctb_libaux`.`tipo` = 'REC'
                        ) AS `rec` GROUP BY `cod_pptal`	 
                    ) AS `recaudo` 
                        ON (`pto_cargue`.`cod_pptal` = `recaudo`.`cod_pptal`)                    
                WHERE `vigencia` = '$vigencia') AS `ejecucion`  
                LEFT JOIN `pto_cargue` 
                    ON (`pto_cargue`.`cod_pptal` = `ejecucion`.`cod_pptal`) 
                LEFT JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto_presupuestos` = `pto_presupuestos`.`id_pto`) 
                LEFT JOIN `pto_homologa_ingresos` 
                    ON (`pto_homologa_ingresos`.`id_pto` = `pto_cargue`.`id_pto_cargue`) 
                LEFT JOIN `pto_codigo_cgr` 
                    ON (`pto_homologa_ingresos`.`id_cgr` = `pto_codigo_cgr`.`id_cod`) 
            WHERE `pto_presupuestos`.`id_tipo` = 1 
            GROUP BY   `pto_cargue`.`cod_pptal` , `pto_cargue`.`nom_rubro` , `pto_cargue`.`tipo_dato`
            ORDER BY `pto_cargue`.`cod_pptal`";
    //echo $sql;
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$data = [];
foreach ($rubros as $fila) {
    $id = $fila['id_cgr'];
    $ini = $fila['inicial'];
    $def = $fila['inicial'] + $fila['adicion'] - $fila['reduccion'];
    if (isset($data[$id])) {
        $val_i = $data[$fila['id_cgr']]['inicial'];
        $val_d = $data[$fila['id_cgr']]['definitivo'];
        $val_ini = $val_i + $ini;
        $val_def = $val_d + $def;
    } else {
        $val_ini = $ini;
        $val_def = $def;
    }
    $data[$fila['id_cgr']] = [
        'codigo' => $fila['codigo_cgr'],
        'inicial' => $val_ini,
        'definitivo' => $val_def,
    ];
}
?>
<style>
    .resaltar:nth-child(even) {
        background-color: #F8F9F9;
    }

    .resaltar:nth-child(odd) {
        background-color: #ffffff;
    }
</style>
<table style="width:100% !important; border-collapse: collapse;">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="11" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'CUIPO - INGRESOS'; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;">
            <td colspan="3">-</td>
            <td colspan="3">Codigo CGR</td>
            <td colspan="3">Pto. Inicial</td>
            <td colspan="3">Pto. Definitivo</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3" style="text-align:center">S</td>
            <td colspan="3" style="text-align:center">84300000</td>
            <td colspan="3" style="text-align:center"><?php echo $codigo; ?></td>
            <td colspan="3" style="text-align:center"><?php echo $vigencia; ?></td>
        </tr>
        <?php
        foreach ($data as $key => $d) {
            if ($key != '') {
                echo '<tr class="resaltar">';
                echo '<td colspan="3">D</td>';
                echo '<td colspan="3">' . $d['codigo'] . '</td>';
                echo '<td colspan="3" style="text-align:right">' . $d['inicial'] . '</td>';
                echo '<td colspan="3" style="text-align:right">' . $d['definitivo'] . '</td>';
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>