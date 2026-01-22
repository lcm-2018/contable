<?php
session_start();
set_time_limit(5600);
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
    /*
    header("Content-type: application/vnd.ms-excel charset=utf-8");
    header("Content-Disposition: attachment; filename=FORMATO_201101_F07_AGR.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    */
    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
$fecha_corte = $_POST['fecha'];
$fecha_ini = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
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
    , IF(pto_cargue.tipo_dato = 1, pto_cargue.valor_aprobado, 0) AS inicial
    , IFNULL(adicion.valor,0) AS adicion
    , IFNULL(adicion_mes.valor,0) AS adicion_mes
    , IFNULL(reduccion.valor,0) AS reduccion
    , IFNULL(reduccion_mes.valor,0) AS reduccion_mes
    , IFNULL(credito.valor,0) AS credito
    , IFNULL(credito_mes.valor,0) AS credito_mes
    , IFNULL(contracredito.valor,0) AS contracredito
    , IFNULL(contracredito_mes.valor,0) AS contracredito_mes
    , IFNULL(compromiso_cdp.valor,0) AS compromiso_cdp
    , IFNULL(compromiso_cdp_mes.valor,0) AS compromiso_cdp_mes
    , IFNULL(compromiso_crp.valor,0) AS compromiso_crp
    , IFNULL(compromiso_crp_mes.valor,0) AS compromiso_crp_mes
    , IFNULL(obligacion.valor,0) AS obligacion
    , IFNULL(obligacion_mes.valor,0) AS obligacion_mes
    , IFNULL(pagos.valor,0) AS pagos
    , IFNULL(pagos_mes.valor,0) AS pagos_mes
FROM pto_cargue
INNER JOIN pto_presupuestos ON (pto_cargue.id_pto_presupuestos = pto_presupuestos.id_pto_presupuestos)
LEFT JOIN (
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini' AND '$fecha_corte' AND pto_documento_detalles.tipo_mov ='ADI' AND pto_documento_detalles.mov =0
	GROUP BY pto_documento_detalles.rubro
) AS adicion ON (adicion.rubro=pto_cargue.cod_pptal) 
LEFT JOIN (
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND pto_documento_detalles.tipo_mov ='ADI' AND pto_documento_detalles.mov =0
	GROUP BY pto_documento_detalles.rubro
) AS adicion_mes ON (adicion_mes.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini' AND' $fecha_corte' AND pto_documento_detalles.tipo_mov ='RED' AND pto_documento_detalles.mov =0
        GROUP BY pto_documento_detalles.rubro
) AS reduccion ON (reduccion.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini_mes'AND'$fecha_corte' AND pto_documento_detalles.tipo_mov ='RED' AND pto_documento_detalles.mov =0 
        GROUP BY pto_documento_detalles.rubro
) AS reduccion_mes ON (reduccion_mes.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini'AND'$fecha_corte' AND pto_documento_detalles.tipo_mov ='TRA' AND pto_documento_detalles.mov =1 
        GROUP BY pto_documento_detalles.rubro
) AS credito ON (credito.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini_mes'AND'$fecha_corte' AND pto_documento_detalles.tipo_mov ='TRA' AND pto_documento_detalles.mov =1 
        GROUP BY pto_documento_detalles.rubro
) AS credito_mes ON (credito_mes.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini'AND'$fecha_corte' AND pto_documento_detalles.tipo_mov ='TRA' AND pto_documento_detalles.mov =0 
        GROUP BY pto_documento_detalles.rubro
) AS contracredito ON (contracredito.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini_mes'AND'$fecha_corte' AND pto_documento_detalles.tipo_mov ='TRA' AND pto_documento_detalles.mov =0 
        GROUP BY pto_documento_detalles.rubro
) AS contracredito_mes ON (contracredito_mes.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini'AND'$fecha_corte' AND (pto_documento_detalles.tipo_mov ='CDP' OR pto_documento_detalles.tipo_mov ='LCD') AND pto_documento_detalles.mov =0 
        GROUP BY pto_documento_detalles.rubro
) AS compromiso_cdp ON (compromiso_cdp.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini_mes'AND'$fecha_corte' AND (pto_documento_detalles.tipo_mov ='CDP' OR pto_documento_detalles.tipo_mov ='LCD') AND pto_documento_detalles.mov =0 
        GROUP BY pto_documento_detalles.rubro
) AS compromiso_cdp_mes ON (compromiso_cdp_mes.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini'AND'$fecha_corte' AND (pto_documento_detalles.tipo_mov ='CRP' OR pto_documento_detalles.tipo_mov ='LRP') AND pto_documento_detalles.mov =0 
        GROUP BY pto_documento_detalles.rubro
) AS compromiso_crp ON (compromiso_crp.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT pto_documento_detalles.rubro,SUM(pto_documento_detalles.valor) AS valor
	FROM pto_documento_detalles
	INNER JOIN pto_cargue ON (pto_documento_detalles.rubro = pto_cargue.cod_pptal)
	INNER JOIN pto_documento  ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
	WHERE pto_documento.estado =0 AND pto_documento.fecha BETWEEN '$fecha_ini_mes'AND'$fecha_corte' AND (pto_documento_detalles.tipo_mov ='CRP' OR pto_documento_detalles.tipo_mov ='LRP')  AND pto_documento_detalles.mov =0 
        GROUP BY pto_documento_detalles.rubro
) AS compromiso_crp_mes ON (compromiso_crp_mes.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT `pto_documento_detalles`.`rubro`, SUM(`pto_documento_detalles`.`valor`) AS valor
	FROM `ctb_doc`
	INNER JOIN `pto_documento_detalles` ON (`ctb_doc`.`id_ctb_doc` = `pto_documento_detalles`.`id_ctb_doc`)
	WHERE `ctb_doc`.`estado`=1 AND `ctb_doc`.`fecha`  BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_documento_detalles`.`tipo_mov` ='COP'
	GROUP BY pto_documento_detalles.rubro
) AS obligacion ON (obligacion.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT `pto_documento_detalles`.`rubro`, SUM(`pto_documento_detalles`.`valor`) AS valor
	FROM `ctb_doc`
	INNER JOIN `pto_documento_detalles` ON (`ctb_doc`.`id_ctb_doc` = `pto_documento_detalles`.`id_ctb_doc`)
	WHERE `ctb_doc`.`estado`=1 AND `ctb_doc`.`fecha`  BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_documento_detalles`.`tipo_mov` ='COP'
	GROUP BY pto_documento_detalles.rubro
) AS obligacion_mes ON (obligacion_mes.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT `pto_documento_detalles`.`rubro`, SUM(`pto_documento_detalles`.`valor`) AS valor
	FROM `ctb_doc`
	INNER JOIN `pto_documento_detalles` ON (`ctb_doc`.`id_ctb_doc` = `pto_documento_detalles`.`id_ctb_doc`)
	WHERE `ctb_doc`.`estado`=1 AND `ctb_doc`.`fecha`  BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_documento_detalles`.`tipo_mov` ='PAG'
	GROUP BY pto_documento_detalles.rubro
) AS pagos ON (pagos.rubro=pto_cargue.cod_pptal)
LEFT JOIN(
	SELECT `pto_documento_detalles`.`rubro`, SUM(`pto_documento_detalles`.`valor`) AS valor
	FROM `ctb_doc`
	INNER JOIN `pto_documento_detalles` ON (`ctb_doc`.`id_ctb_doc` = `pto_documento_detalles`.`id_ctb_doc`)
	WHERE `ctb_doc`.`estado`=1 AND `ctb_doc`.`fecha`  BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_documento_detalles`.`tipo_mov` ='PAG'
	GROUP BY pto_documento_detalles.rubro
) AS pagos_mes ON (pagos_mes.rubro=pto_cargue.cod_pptal)
WHERE pto_cargue.vigencia =2023 AND pto_presupuestos.id_pto_tipo =2 
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
            $val_credito_mes = $f['credito_mes'];
            $val_credito = $f['credito'];
            $val_contracredito_mes = $f['contracredito_mes'];
            $val_contracredito = $f['contracredito'];
            $val_compromiso_cdp_mes = $f['compromiso_cdp_mes'];
            $val_compromiso_cdp = $f['compromiso_cdp'];
            $val_compromiso_crp_mes = $f['compromiso_crp_mes'];
            $val_compromiso_crp = $f['compromiso_crp'];
            $val_obligacion_mes = $f['obligacion_mes'];
            $val_obligacion = $f['obligacion'];
            $val_pagos_mes = $f['pagos_mes'];
            $val_pagos = $f['pagos'];
            $val_ini = isset($acum[$rubro]['inicial']) ? $acum[$rubro]['inicial'] : 0;
            $val_ad_mes = isset($acum[$rubro]['adicion_mes']) ? $acum[$rubro]['adicion_mes'] : 0;
            $val_ad = isset($acum[$rubro]['adicion']) ? $acum[$rubro]['adicion'] : 0;
            $val_red_mes = isset($acum[$rubro]['reduccion_mes']) ? $acum[$rubro]['reduccion_mes'] : 0;
            $val_red = isset($acum[$rubro]['reduccion']) ? $acum[$rubro]['reduccion'] : 0;
            $val_cre_mes = isset($acum[$rubro]['credito_mes']) ? $acum[$rubro]['credito_mes'] : 0;
            $val_cre = isset($acum[$rubro]['credito']) ? $acum[$rubro]['credito'] : 0;
            $val_ccre_mes = isset($acum[$rubro]['contracredito_mes']) ? $acum[$rubro]['contracredito_mes'] : 0;
            $val_ccre = isset($acum[$rubro]['contracredito']) ? $acum[$rubro]['contracredito'] : 0;
            $val_cdp_mes = isset($acum[$rubro]['compromiso_cdp_mes']) ? $acum[$rubro]['compromiso_cdp_mes'] : 0;
            $val_cdp = isset($acum[$rubro]['compromiso_cdp']) ? $acum[$rubro]['compromiso_cdp'] : 0;
            $val_crp_mes = isset($acum[$rubro]['compromiso_crp_mes']) ? $acum[$rubro]['compromiso_crp_mes'] : 0;
            $val_crp = isset($acum[$rubro]['compromiso_crp']) ? $acum[$rubro]['compromiso_crp'] : 0;
            $val_cop_mes = isset($acum[$rubro]['obligacion_mes']) ? $acum[$rubro]['obligacion_mes'] : 0;
            $val_cop = isset($acum[$rubro]['obligacion']) ? $acum[$rubro]['obligacion'] : 0;
            $val_pag_mes = isset($acum[$rubro]['pagos_mes']) ? $acum[$rubro]['pagos_mes'] : 0;
            $val_pag = isset($acum[$rubro]['pagos']) ? $acum[$rubro]['pagos'] : 0;
            $acum[$rubro] = [
                'inicial' => $val_ini + $val_inicial,
                'adicion_mes' => $val_adicion_mes + $val_ad_mes,
                'adicion' => $val_adicion + $val_ad,
                'reduccion_mes' => $val_reduccion_mes + $val_red_mes,
                'reduccion' => $val_reduccion + $val_red,
                'credito_mes' => $val_credito_mes + $val_cre_mes,
                'credito' => $val_credito + $val_cre,
                'contracredito_mes' => $val_contracredito_mes + $val_ccre_mes,
                'contracredito' => $val_contracredito + $val_ccre,
                'compromiso_cdp_mes' => $val_compromiso_cdp_mes + $val_cdp_mes,
                'compromiso_cdp' => $val_compromiso_cdp + $val_cdp,
                'compromiso_crp_mes' => $val_compromiso_crp_mes + $val_crp_mes,
                'compromiso_crp' => $val_compromiso_crp + $val_crp,
                'obligacion_mes' => $val_obligacion_mes + $val_cop_mes,
                'obligacion' => $val_obligacion + $val_cop,
                'pagos_mes' => $val_pagos_mes + $val_pag_mes,
                'pagos' => $val_pagos + $val_pag
            ];
        }
    }
}
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
<button onclick="generarPDF3()">Generar PDF2</button>
<script>
    const generarPDF3 = () => {
        const doc = new jsPDF({
            orientation: "landscape", // Puedes cambiar a 'portrait' para orientación vertical
            unit: "mm", // Unidad de medida: milímetros
            format: "legal", // Tamaño del papel: 'letter', 'a4', etc.
            marginLeft: 20, // Margen izquierdo en milímetros
            marginRight: 20, // Margen derecho en milímetros
            marginTop: 10, // Margen superior en milímetros
            marginBottom: 10, // Margen inferior en milímetros
        });

        const contenido = document.getElementById("areaImprimir"); // Cambia 'contenido' por el ID del elemento HTML que deseas convertir a PDF
        doc.fromHTML(contenido, 15, 15); // Genera el PDF a partir del contenido HTML
        doc.save("archivo.pdf"); // Descarga el archivo PDF
    };
    const generarPDF4 = () => {
        // Crear un objeto jsPDF
        var doc = new jsPDF({
            orientation: "landscape",
            unit: "mm",
            format: "legal",
            marginLeft: 20,
            marginRight: 20,
            marginTop: 10,
            marginBottom: 10,
        });

        // Obtener el contenido HTML de la tabla
        var tabla = document.getElementById("tablaImpresion");
        var tablaHTML = tabla.outerHTML;

        // Definir las opciones para la inserción de HTML
        var options = {
            html: tablaHTML,
            x: 10,
            y: 20,
        };

        // Función para generar el PDF con la tabla HTML y mantener el formato

        // Agregar el contenido HTML de la tabla al PDF utilizando la extensión autoTable
        doc.autoTable(options);

        // Guardar o mostrar el PDF
        doc.save("tabla.pdf");

    };
</script>
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
                <td colspan="15" style="text-align:center"><?php echo 'EJECUCION PRESUPUESTAL DE GASTOS'; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1 id="tablaImpresion">
            <tr>
                <td>Rubro</td>
                <td>Descripcion</td>
                <td>Tipo</td>
                <td>Presupuesto inicial</td>
                <td>Adiciones mes</td>
                <td>Adiciones</td>
                <td>Reducciones mes</td>
                <td>Reducciones</td>
                <td>Cr&eacute;ditos mes</td>
                <td>Cr&eacute;ditos</td>
                <td>Contracreditos mes</td>
                <td>Contracreditos</td>
                <td>Presupuesto definitivo</td>
                <td>Disponibilidades mes</td>
                <td>Disponibilidades</td>
                <td>Compromisos mes</td>
                <td>Compromisos</td>
                <td>Obligaciones mes</td>
                <td>Obligación</td>
                <td>Pagos mes</td>
                <td>Pagos</td>
                <td>Saldo presupuestal</td>
                <td>Cuentas por pagar</td>
            </tr>
            <?php
            foreach ($acum as $key => $value) {
                $keyrb = array_search($key, array_column($rubros, 'cod_pptal'));
                if ($keyrb !== false) {
                    $nomrb = $rubros[$keyrb]['nom_rubro'];
                    $tipo = $rubros[$keyrb]['tipo_dato'];
                } else {
                    $nomrb = '';
                }
                if ($tipo == '0') {
                    $tipo_dat = 'M';
                } else {
                    $tipo_dat = 'D';
                }
                echo '<tr>';
                echo '<td class="text">' . $key . '</td>';
                echo '<td class="text">' . $nomrb . '</td>';
                echo '<td class="text">' . $tipo_dat . '</td>';
                echo '<td>' . number_format($value['inicial'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['adicion_mes'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['adicion'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['reduccion_mes'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['reduccion'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['credito_mes'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['credito'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['contracredito_mes'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['contracredito'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format(($value['inicial'] + $value['adicion'] - $value['reduccion'] + $value['credito'] - $value['contracredito']), 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['compromiso_cdp_mes'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['compromiso_cdp'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['compromiso_crp_mes'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['compromiso_crp'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['obligacion_mes'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['obligacion'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['pagos_mes'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format($value['pagos'], 2, ".", ",") . '</td>';
                echo '<td>' . number_format((($value['inicial'] + $value['adicion'] - $value['reduccion'] + $value['credito'] - $value['contracredito']) - $value['compromiso_cdp']), 2, ".", ",") . '</td>';
                echo '<td>' . number_format(($value['obligacion'] - $value['pagos']), 2, ".", ",") . '</td>';
                echo '</tr>';
            }
            ?>
        </table>
        </br>
        </br>
        </br>
    </div>
</div>