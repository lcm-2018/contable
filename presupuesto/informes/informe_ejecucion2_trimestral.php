<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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
                , `pto_cpc`.`codigo` AS `codigo_cpc`
                , `pto_fuente`.`codigo` AS `codigo_fte`
                , `pto_politica`.`codigo` AS `codigo_pol`
                , `pto_terceros`.`codigo` AS `codigo_ter`
                , `pto_situacion`.`id_situacion` AS `codigo_sit`
                , `pto_vigencias`.`id_vigencia` AS `codigo_vig`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`
                , SUM(`recaudo`) AS `recaudo`
            FROM
                (SELECT
                    `pto_cargue`.`cod_pptal`
                    , `pto_cargue`.`nom_rubro`
                    , CASE `pto_cargue`.`tipo_dato` WHEN 1 THEN 'D' WHEN 0 THEN 'M' END AS `tipo_dato`
                    , IFNULL(`recaudo`.`valor`,0) AS `recaudo`
                FROM
                    `pto_cargue`
                    LEFT JOIN (
                        SELECT 
                            `cod_pptal`
                            , `nom_rubro`
                            , SUM(`valor`) AS `valor` 
                        FROM (	
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
                LEFT JOIN `pto_cpc` 
                    ON (`pto_homologa_ingresos`.`id_cpc` = `pto_cpc`.`id_cpc`)
                LEFT JOIN `pto_fuente` 
                    ON (`pto_homologa_ingresos`.`id_fuente` = `pto_fuente`.`id_fuente`)
                LEFT JOIN `pto_politica` 
                    ON (`pto_homologa_ingresos`.`id_politica` = `pto_politica`.`id_politica`)
                LEFT JOIN `pto_terceros` 
                    ON (`pto_homologa_ingresos`.`id_tercero` = `pto_terceros`.`id_tercero`)
                LEFT JOIN `pto_situacion` 
                    ON (`pto_homologa_ingresos`.`id_situacion` = `pto_situacion`.`id_situacion`)
                LEFT JOIN `pto_vigencias` 
                    ON (`pto_homologa_ingresos`.`id_vigencia` = `pto_vigencias`.`id_vigencia`)
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
    $id_cgr = $fila['codigo_cgr'];
    $id_cpc = $fila['codigo_cpc'];
    $id_fte = $fila['codigo_fte'];
    $id_pol = $fila['codigo_pol'];
    $id_ter = $fila['codigo_ter'];
    $id_sit = $fila['codigo_sit'];
    $id_vig = $fila['codigo_vig'];
    $recaudo = $fila['recaudo'];
    if (isset($data[$id_cgr . $id_cpc . $id_fte . $id_pol . $id_ter])) {
        $val_acumulado = $data[$id_cgr . $id_cpc . $id_fte . $id_pol . $id_ter]['recaudo'];
        $val_rec = $recaudo + $val_acumulado;
    } else {
        $val_rec = $recaudo;
    }
    $data[$id_cgr . $id_cpc . $id_fte . $id_pol . $id_ter] = [
        'codigo_cgr' => $id_cgr,
        'codigo_cpc' => $id_cpc,
        'codigo_fte' => $id_fte,
        'codigo_pol' => $id_pol,
        'codigo_ter' => $id_ter,
        'codigo_sit' => $id_sit,
        'codigo_vig' => $id_vig,
        'recaudo' => $val_rec,
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
            <td colspan="11" style="text-align:center"><?php echo 'EJECUCIÓN - INGRESOS'; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;">
            <td>-</td>
            <td colspan="3">Codigo CGR</td>
            <td>CPC</td>
            <td>Fuente</td>
            <td>Terceros</td>
            <td>Política Pública</td>
            <td>V. Actual Con Situación</td>
            <td>V. Actual Sin Situación</td>
            <td>V. Anterior Con Situación</td>
            <td>V. Anterior Sin Situación</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align:center">S</td>
            <td colspan="3" style="text-align:center">84300000</td>
            <td style="text-align:center"><?php echo $codigo; ?></td>
            <td style="text-align:center"><?php echo $vigencia; ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php
        foreach ($data as $key => $d) {

            if ($key != '') {
                if ($d['codigo_vig'] == 1 && $d['codigo_sit'] == 2) {
                    $vacss = $d['recaudo'];
                    $vaccs = '0';
                    $vanss = '0';
                    $vancs = '0';
                } else if ($d['codigo_vig'] == 1 && $d['codigo_sit'] == 1) {
                    $vacss = '0';
                    $vaccs = $d['recaudo'];
                    $vanss = '0';
                    $vancs = '0';
                } else if ($d['codigo_vig'] == 2 && $d['codigo_sit'] == 2) {
                    $vacss = '0';
                    $vaccs = '0';
                    $vanss = $d['recaudo'];
                    $vancs = '0';
                } else if ($d['codigo_vig'] == 2 && $d['codigo_sit'] == 1) {
                    $vacss = '0';
                    $vaccs = '0';
                    $vanss = '0';
                    $vancs = $d['recaudo'];
                }
                echo '<tr class="resaltar">';
                echo '<td >D</td>';
                echo '<td colspan="3">' . $d['codigo_cgr'] . '</td>';
                echo '<td>' . $d['codigo_cpc'] . '</td>';
                echo '<td>' . $d['codigo_fte'] . '</td>';
                echo '<td>' . $d['codigo_pol'] . '</td>';
                echo '<td>' . $d['codigo_ter'] . '</td>';
                echo '<td style="text-align:right">' . $vacss . '</td>';
                echo '<td style="text-align:right">' . $vaccs . '</td>';
                echo '<td style="text-align:right">' . $vanss . '</td>';
                echo '<td style="text-align:right">' . $vancs . '</td>';
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>