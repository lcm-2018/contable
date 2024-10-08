<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>CONTAFACIL</title>
    <style>
        .text {
            mso-number-format: "\@"
        }
    </style>
    <?php
    header("Content-type: application/vnd.ms-excel charset=utf-8");
    header("Content-Disposition: attachment; filename=FORMATO_201101_F07_AGR.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
</head>

<?php
$vigencia = $_SESSION['vigencia'];
$fecha_corte = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
// establecer fecha de inicio como 2023-01-01 a partir de varable se sesion vigencia
$fecha_ini = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
// extraer el mes de $fecha_corte
$mes = date("m", strtotime($fecha_corte));
$fecha_ini_mes = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-' . $mes . '-01'));
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT 
    pto_cargue.cod_pptal
    , pto_cargue.nom_rubro
    , pto_cargue.tipo_dato  
    , SUM(inicial) AS inicial
    , SUM(adicion_mes) AS adicion_mes
    , SUM(adicion) AS adicion
    , SUM(reduccion_mes) AS reduccion_mes
    , SUM(reduccion) AS reduccion
    , SUM(reconocimiento_mes) AS reconocimiento_mes
    , SUM(reconocimiento) AS reconocimiento
    , SUM(recaudo_mes1) AS recaudo_mes1
    , SUM(recaudo_mes2) AS recaudo_mes2
    , SUM(recaudo_mes3) AS recaudo_mes3
    , SUM(recaudo) AS recaudo   
FROM (
    SELECT
        pto_cargue.cod_pptal
        , pto_cargue.nom_rubro
        , CASE pto_cargue.tipo_dato WHEN 1 THEN 'D' WHEN 0 THEN 'M' END AS tipo_dato
        , pto_cargue.valor_aprobado AS inicial
        , IFNULL(adicion_mes.valor,0) AS adicion_mes
        , IFNULL(adicion.valor,0) AS adicion
        , IFNULL(reduccion_mes.valor,0) AS reduccion_mes
        , IFNULL(reduccion.valor,0) AS reduccion
        , IFNULL(reconocimiento_mes.valor,0) AS reconocimiento_mes
        , IFNULL(reconocimiento.valor,0) AS reconocimiento
        , IFNULL(recaudo_mes1.valor,0) AS recaudo_mes1
        , IFNULL(recaudo_mes2.valor,0) AS recaudo_mes2
        , IFNULL(recaudo_mes3.valor,0) AS recaudo_mes3
        , IFNULL(recaudo.valor,0) AS recaudo   
    FROM
        pto_cargue
        LEFT JOIN (
        SELECT
            pto_cargue.cod_pptal
            , pto_cargue.nom_rubro    
            , SUM(pto_documento_detalles.valor) AS valor    
        FROM
            pto_cargue
            INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
            INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
            INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
        WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'ADI' AND pto_documento.fecha BETWEEN '$fecha_ini' AND '$fecha_corte' 
        GROUP BY pto_cargue.cod_pptal
        ) AS adicion ON (pto_cargue.cod_pptal=adicion.cod_pptal)
        LEFT JOIN (
        SELECT
            pto_cargue.cod_pptal
            , pto_cargue.nom_rubro    
            , SUM(pto_documento_detalles.valor) AS valor    
        FROM
            pto_cargue
            INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
            INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
            INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
        WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'ADI' AND pto_documento.fecha BETWEEN '$fecha_ini' AND '$fecha_corte' 
        GROUP BY pto_cargue.cod_pptal
        ) AS adicion_mes ON (pto_cargue.cod_pptal=adicion_mes.cod_pptal)
        LEFT JOIN (
        SELECT
            pto_cargue.cod_pptal
            , pto_cargue.nom_rubro    
            , SUM(pto_documento_detalles.valor) AS valor    
        FROM
            pto_cargue
            INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
            INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
            INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
        WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'RED' AND pto_documento.fecha BETWEEN '$fecha_ini' AND '$fecha_corte' 
        GROUP BY pto_cargue.cod_pptal
        ) AS reduccion ON (pto_cargue.cod_pptal=reduccion.cod_pptal)
        LEFT JOIN (
        SELECT
            pto_cargue.cod_pptal
            , pto_cargue.nom_rubro    
            , SUM(pto_documento_detalles.valor) AS valor    
        FROM
            pto_cargue
            INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
            INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
            INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
        WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'RED' AND pto_documento.fecha BETWEEN '$fecha_ini_mes' AND '$fecha_corte' 
        GROUP BY pto_cargue.cod_pptal
        ) AS reduccion_mes ON (pto_cargue.cod_pptal=reduccion_mes.cod_pptal)
        LEFT JOIN (
        SELECT cod_pptal,nom_rubro,SUM(valor) AS valor FROM (	
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , pto_documento_detalles.valor AS valor    
            FROM
                pto_cargue
                INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
                INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
                INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
            WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'RAD' AND date_format(pto_documento.fecha,'%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_corte'
            UNION ALL
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , vista_ctb_libaux.valordeb AS valor    
            FROM
                pto_cargue
                INNER JOIN vista_ctb_libaux ON (vista_ctb_libaux.cuenta=pto_cargue.cod_pptal)
            WHERE vista_ctb_libaux.fecha BETWEEN '$fecha_ini' AND '$fecha_corte' AND vista_ctb_libaux.tipo = 'RAD'
        ) AS rec GROUP BY cod_pptal	 
        ) AS reconocimiento ON (pto_cargue.cod_pptal=reconocimiento.cod_pptal)
        LEFT JOIN (
        SELECT cod_pptal,nom_rubro,SUM(valor) AS valor FROM (	
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , pto_documento_detalles.valor AS valor    
            FROM
                pto_cargue
                INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
                INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
                INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
            WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'RAD' AND date_format(pto_documento.fecha,'%Y-%m-%d') BETWEEN '$fecha_ini_mes' AND '$fecha_corte'
            UNION ALL
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , vista_ctb_libaux.valordeb AS valor    
            FROM
                pto_cargue
                INNER JOIN vista_ctb_libaux ON (vista_ctb_libaux.cuenta=pto_cargue.cod_pptal)
            WHERE vista_ctb_libaux.fecha BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND vista_ctb_libaux.tipo = 'RAD'
        ) AS rec GROUP BY cod_pptal	 
        ) AS reconocimiento_mes ON (pto_cargue.cod_pptal=reconocimiento_mes.cod_pptal)
        LEFT JOIN (
         SELECT cod_pptal,nom_rubro,SUM(valor) AS valor FROM (	
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , pto_documento_detalles.valor AS valor    
            FROM
                pto_cargue
                INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
                INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
                INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
            WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'REC' AND pto_documento.fecha BETWEEN '$fecha_ini' AND '$fecha_corte'
            UNION ALL
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , vista_ctb_libaux.valordeb AS valor    
            FROM
                pto_cargue
                INNER JOIN vista_ctb_libaux ON (vista_ctb_libaux.cuenta=pto_cargue.cod_pptal)
            WHERE vista_ctb_libaux.fecha BETWEEN '$fecha_ini' AND '$fecha_corte' AND vista_ctb_libaux.tipo = 'REC'
        ) AS rec GROUP BY cod_pptal	 
        ) AS recaudo ON (pto_cargue.cod_pptal=recaudo.cod_pptal)
        LEFT JOIN (
        SELECT cod_pptal,nom_rubro,SUM(valor) AS valor FROM (	
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , pto_documento_detalles.valor AS valor    
            FROM
                pto_cargue
                INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
                INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
                INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
            WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'REC' AND pto_documento.fecha BETWEEN '2023-01-01' AND '2023-01-31'
            UNION ALL
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , vista_ctb_libaux.valordeb AS valor    
            FROM
                pto_cargue
                INNER JOIN vista_ctb_libaux ON (vista_ctb_libaux.cuenta=pto_cargue.cod_pptal)
            WHERE vista_ctb_libaux.fecha BETWEEN '2023-01-01' AND '2023-01-31' AND vista_ctb_libaux.tipo = 'REC'
        ) AS rec GROUP BY cod_pptal	 
        ) AS recaudo_mes1 ON (pto_cargue.cod_pptal=recaudo_mes1.cod_pptal)
        LEFT JOIN (
        SELECT cod_pptal,nom_rubro,SUM(valor) AS valor FROM (	
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , pto_documento_detalles.valor AS valor    
            FROM
                pto_cargue
                INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
                INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
                INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
            WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'REC' AND pto_documento.fecha BETWEEN '2023-02-01' AND '2023-02-28'
            UNION ALL
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , vista_ctb_libaux.valordeb AS valor    
            FROM
                pto_cargue
                INNER JOIN vista_ctb_libaux ON (vista_ctb_libaux.cuenta=pto_cargue.cod_pptal)
            WHERE vista_ctb_libaux.fecha BETWEEN '2023-02-01' AND '2023-02-28' AND vista_ctb_libaux.tipo = 'REC'
        ) AS rec GROUP BY cod_pptal	 
        ) AS recaudo_mes2 ON (pto_cargue.cod_pptal=recaudo_mes2.cod_pptal)
        LEFT JOIN (
        SELECT cod_pptal,nom_rubro,SUM(valor) AS valor FROM (	
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , pto_documento_detalles.valor AS valor    
            FROM
                pto_cargue
                INNER JOIN pto_documento_detalles ON (pto_cargue.cod_pptal = pto_documento_detalles.rubro)
                INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
                INNER JOIN pto_presupuestos ON (pto_documento.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
            WHERE pto_presupuestos.id_pto_tipo = 1 AND pto_documento_detalles.tipo_mov = 'REC' AND pto_documento.fecha BETWEEN '2023-03-01' AND '2023-03-31'
            UNION ALL
            SELECT
                pto_cargue.cod_pptal
                , pto_cargue.nom_rubro    
                , vista_ctb_libaux.valordeb AS valor    
            FROM
                pto_cargue
                INNER JOIN vista_ctb_libaux ON (vista_ctb_libaux.cuenta=pto_cargue.cod_pptal)
            WHERE vista_ctb_libaux.fecha BETWEEN '2023-03-01' AND '2023-03-31' AND vista_ctb_libaux.tipo = 'REC'
        ) AS rec GROUP BY cod_pptal	 
        ) AS recaudo_mes3 ON (pto_cargue.cod_pptal=recaudo_mes3.cod_pptal)                    
        
    WHERE vigencia = 2023 ) 
AS ejecucion  
INNER JOIN pto_cargue ON(pto_cargue.cod_pptal=ejecucion.cod_pptal)
WHERE pto_cargue.id_pto_presupuestos = 1 
GROUP BY   pto_cargue.cod_pptal , pto_cargue.nom_rubro , pto_cargue.tipo_dato
ORDER BY pto_cargue.cod_pptal";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$acum = [];
foreach ($rubros as $rb) {
    $rubro = $rb['cod_pptal'];
    $acum[$rubro] = $rb['cod_pptal'];
    $filtro = [];
    $filtro = array_filter($rubros, function ($rubros) use ($rubro) {
        return (strpos($rubros['cod_pptal'], $rubro) === 0);
    });
    if (!empty($filtro)) {
        foreach ($filtro as $f) {
            $val_inicial = $f['inicial'];
            $val_adicion_mes = $f['adicion_mes'];
            $val_adicion = $f['adicion'];
            $val_reduccion_mes = $f['reduccion_mes'];
            $val_reduccion = $f['reduccion'];
            $val_reconocimiento_mes = $f['reconocimiento_mes'];
            $val_reconocimiento = $f['reconocimiento'];
            $val_recaudo_mes1 = $f['recaudo_mes1'];
            $val_recaudo_mes2 = $f['recaudo_mes2'];
            $val_recaudo_mes3 = $f['recaudo_mes3'];
            $val_recaudo = $f['recaudo'];
            $val_ini = isset($acum[$rubro]['inicial']) ? $acum[$rubro]['inicial'] : 0;
            $val_ad_mes = isset($acum[$rubro]['adicion_mes']) ? $acum[$rubro]['adicion_mes'] : 0;
            $val_ad = isset($acum[$rubro]['adicion']) ? $acum[$rubro]['adicion'] : 0;
            $val_red_mes = isset($acum[$rubro]['reduccion_mes']) ? $acum[$rubro]['reduccion_mes'] : 0;
            $val_red = isset($acum[$rubro]['reduccion']) ? $acum[$rubro]['reduccion'] : 0;
            $val_rec_mes = isset($acum[$rubro]['reconocimiento_mes']) ? $acum[$rubro]['reconocimiento_mes'] : 0;
            $val_rec = isset($acum[$rubro]['reconocimiento']) ? $acum[$rubro]['reconocimiento'] : 0;
            $val_reca_mes1 = isset($acum[$rubro]['recaudo_mes1']) ? $acum[$rubro]['recaudo_mes1'] : 0;
            $val_reca_mes2 = isset($acum[$rubro]['recaudo_mes2']) ? $acum[$rubro]['recaudo_mes2'] : 0;
            $val_reca_mes3 = isset($acum[$rubro]['recaudo_mes3']) ? $acum[$rubro]['recaudo_mes3'] : 0;
            $val_reca = isset($acum[$rubro]['recaudo']) ? $acum[$rubro]['recaudo'] : 0;
            $acum[$rubro] = [
                'inicial' => $val_ini + $val_inicial,
                'adicion_mes' => $val_adicion_mes + $val_ad_mes,
                'adicion' => $val_adicion + $val_ad,
                'reduccion_mes' => $val_reduccion_mes + $val_red_mes,
                'reduccion' => $val_reduccion + $val_red,
                'reconocimiento_mes' => $val_reconocimiento_mes + $val_rec_mes,
                'reconocimiento' => $val_reconocimiento + $val_rec,
                'recaudo_mes' =>  $val_recaudo_mes + $val_reca_mes,
                'recaudo_mes1' =>  $val_recaudo_mes1 + $val_reca_mes1,
                'recaudo_mes2' =>  $val_recaudo_mes2 + $val_reca_mes2,
                'recaudo_mes3' =>  $val_recaudo_mes3 + $val_reca_mes3,
                'recaudo' =>  $val_recaudo + $val_reca
            ];
        }
    }
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT
    `nombre`
    , `nit`
    , `dig_ver`
FROM
    `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="15" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="15" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo 'EJECUCION PRESUPUESTAL DE INGRESOS'; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>C&oacute;digo</td>
                <td>Nombre</td>
                <td>Inicial</td>
                <td>Adiciones mes</td>
                <td>adicion acumulada</td>
                <td>Reducción mes</td>
                <td>Reducción acumulada</td>
                <td>Definitivo</td>
                <td>Reconocimiento mes</td>
                <td>Reconocimiento acumulado</td>
                <td>Recaudo mes ene</td>
                <td>Recaudo mes feb</td>
                <td>Recaudo mes mar</td>
                <td>Recaudo acumulado</td>
                <td>Saldo por recaudar</td>
                <td>Cuentas por cobrar</td>
            </tr>
            <?php
            foreach ($acum as $key => $value) {
                $keyrb = array_search($key, array_column($rubros, 'cod_pptal'));
                if ($keyrb !== false)
                    $nomrb = $rubros[$keyrb]['nom_rubro'];
                else
                    $nomrb = '';
                echo '<tr>';
                echo '<td class="text">' . $key . '</td>';
                echo '<td>' . $nomrb . '</td>';
                echo '<td>' . $value['inicial'] . '</td>';
                echo '<td>' . $value['adicion_mes'] . '</td>';
                echo '<td>' . $value['adicion'] . '</td>';
                echo '<td>' . $value['reduccion_mes'] . '</td>';
                echo '<td>' . $value['reduccion'] . '</td>';
                echo '<td>' . ($value['inicial'] + $value['adicion'] - $value['reduccion']) . '</td>';
                echo '<td>' . $value['reconocimiento_mes'] . '</td>';
                echo '<td>' . $value['reconocimiento'] . '</td>';
                echo '<td>' . $value['recaudo_mes1'] . '</td>';
                echo '<td>' . $value['recaudo_mes2'] . '</td>';
                echo '<td>' . $value['recaudo_mes3'] . '</td>';
                echo '<td>' . $value['recaudo'] . '</td>';
                echo '<td>' . (($value['inicial'] + $value['adicion'] - $value['reduccion'])) - $value['recaudo'] . '</td>';
                echo '<td>' . $value['reconocimiento'] - $value['recaudo'] . '</td>';
                echo '</tr>';
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>