<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

ini_set('memory_limit', '-1');

include '../../../conexion.php';
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

include '../../../vendor/autoload.php';
include 'funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_cuenta_ini = isset($_POST['id_cuenta_ini']) ? $_POST['id_cuenta_ini'] : 0;
$id_cuenta_fin = isset($_POST['id_cuenta_fin']) ? $_POST['id_cuenta_fin'] : 0;
$fec_ini = isset($_POST['fec_ini']) && strlen($_POST['fec_ini'] > 0) ? "'" . $_POST['fec_ini'] . "'" : '2020-01-01';
$fec_fin = isset($_POST['fec_fin']) && strlen($_POST['fec_fin']) > 0 ? "'" . $_POST['fec_fin'] . "'" : '2050-12-31';
$id_tipo_doc = isset($_POST['id_tipo_doc']) ? $_POST['id_tipo_doc'] : 0;
$id_tercero = isset($_POST['id_tercero']) ? $_POST['id_tercero'] : 0;

$and_where = '';
if ($id_tercero > 0) {
    $and_where .= " AND ctb_libaux.id_tercero_api = $id_tercero";
}
if ($id_tipo_doc > 0) {
    $and_where .= " AND ctb_doc.id_tipo_doc = $id_tipo_doc";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                 ctb_pgcp.id_pgcp
                ,ctb_pgcp.cuenta
                ,ctb_pgcp.nombre
                ,ctb_pgcp.tipo_dato 
            FROM ctb_pgcp 
            WHERE ctb_pgcp.estado = 1
            AND ctb_pgcp.id_pgcp BETWEEN '$id_cuenta_ini' AND '$id_cuenta_fin'";
    $rs = $cmd->query($sql);
    $obj_cuentas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $rs->closeCursor();
    unset($rs);
    $sql = 'SELECT razon_social_ips,nit_ips FROM tb_datos_ips LIMIT 1';
    $rs = $cmd->query($sql);
    $obj_ent = $rs->fetch();
    $razhd = $obj_ent['razon_social_ips'];
    $nithd = $obj_ent['nit_ips'];
    $cmd = null;

    $filename = "Libro_Auxiliar_Bancos_" . date("Y-m-d_H-i-s") . ".xlsx";

    $writer = WriterEntityFactory::createXLSXWriter();
    $writer->openToBrowser($filename);

    // Encabezados del reporte
    $writer->addRow(WriterEntityFactory::createRowFromArray(["ENTIDAD", $razhd]));
    $writer->addRow(WriterEntityFactory::createRowFromArray(["NIT", $nithd]));
    $writer->addRow(WriterEntityFactory::createRowFromArray(["REPORTE", "LIBROS AUXILIARES"]));
    $writer->addRow(WriterEntityFactory::createRowFromArray(["FECHA INICIAL", $fec_ini]));
    $writer->addRow(WriterEntityFactory::createRowFromArray(["FECHA FINAL", $fec_fin]));

    // Optimización: reducir consultas repetidas por cuenta
    // Construir lista de ids de cuenta
    $accountIds = array_column($obj_cuentas, 'id_pgcp');
    if (!empty($accountIds)) {
        $inList = implode(',', array_map(function ($v) { return "'" . $v . "'"; }, $accountIds));

    // 1) Obtener saldos iniciales para todas las cuentas en una sola consulta
        $sqlSaldos = "SELECT
                        ctb_libaux.id_cuenta AS id_cuenta,
                        ctb_pgcp.cuenta AS cuenta,
                        SUM(IFNULL(ctb_libaux.debito,0)) AS debito,
                        SUM(IFNULL(ctb_libaux.credito,0)) AS credito
                    FROM ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                    INNER JOIN ctb_pgcp ON (ctb_libaux.id_cuenta = ctb_pgcp.id_pgcp)
                    WHERE ctb_doc.fecha < $fec_ini
                        AND ctb_libaux.id_cuenta IN ($inList)
                        AND ctb_doc.estado = 2
                    GROUP BY ctb_libaux.id_cuenta";

        // asegurar conexión PDO activa
        if ($cmd === null) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        }
        $rs = $cmd->query($sqlSaldos);
        if ($rs === false) {
            $arrSaldos = [];
        } else {
            $arrSaldos = $rs->fetchAll(PDO::FETCH_ASSOC);
            $rs->closeCursor();
            unset($rs);
        }

        $saldosMap = [];
        foreach ($arrSaldos as $s) {
            $primer = substr($s['cuenta'], 0, 1);
            $segundo = substr($s['cuenta'], 0, 2);
            if ($primer == 1 || $primer == 5 || $primer == 6 || $primer == 7 || $segundo == 81 || $segundo == 83 || $segundo == 99) {
                $saldo = $s['debito'] - $s['credito'];
            } else {
                $saldo = $s['credito'] - $s['debito'];
            }
            $saldosMap[$s['id_cuenta']] = $saldo;
        }

        // Crear mapa id_pgcp => 'cuenta - nombre' para búsquedas O(1)
        $cuentaMap = [];
        foreach ($obj_cuentas as $c) {
            $cuentaMap[$c['id_pgcp']] = strval($c['cuenta'] . ' - ' . $c['nombre']);
        }

        // 2) Obtener todas las transacciones en una sola consulta y procesar por cuenta (streaming)
        $sqlAll = "SELECT
                        DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha,
                        ctb_pgcp.cuenta,
                        ctb_libaux.id_tercero_api,
                        IFNULL(ctb_libaux.debito,0) AS debito,
                        IFNULL(ctb_libaux.credito,0) AS credito,
                        ctb_doc.id_tipo_doc,
                        ctb_fuente.cod AS cod_tipo_doc,
                        ctb_fuente.nombre AS nom_tipo_doc,
                        ctb_doc.id_manu,
                        CONCAT(IFNULL(facturas.num_factura,''),' - ',ctb_doc.detalle) AS detalle,
                        tes_forma_pago.forma_pago,
                        tb_terceros.nom_tercero,
                        tb_terceros.nit_tercero,
                        ctb_libaux.id_cuenta AS id_cuenta
                    FROM ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                    INNER JOIN ctb_pgcp ON (ctb_libaux.id_cuenta = ctb_pgcp.id_pgcp)
                    INNER JOIN ctb_fuente ON (ctb_doc.id_tipo_doc = ctb_fuente.id_doc_fuente)
                    LEFT JOIN tes_detalle_pago ON (tes_detalle_pago.id_ctb_doc = ctb_doc.id_ctb_doc)
                    LEFT JOIN tes_forma_pago ON (tes_detalle_pago.id_forma_pago = tes_forma_pago.id_forma_pago)
                    LEFT JOIN tb_terceros ON (tb_terceros.id_tercero_api = ctb_libaux.id_tercero_api)
                    LEFT JOIN
                        (SELECT 
                            doc.id_ctb_doc,
                            doc.id_manu,
                            doc.tipo_movimiento AS tipo,
                            CASE doc.tipo_movimiento
                                WHEN 1 THEN CONCAT(ff.prefijo, IFNULL(ff.num_efactura, ff.num_factura))
                                WHEN 2 THEN CONCAT(fo.prefijo, IFNULL(fo.num_efactura, fo.num_factura))
                                WHEN 3 THEN CONCAT(fv.prefijo, IFNULL(fv.num_efactura, fv.num_factura))
                                WHEN 4 THEN CONCAT(fc.prefijo, fc.num_factura)
                            END AS num_factura
                        FROM ctb_doc doc
                        LEFT JOIN fac_facturacion ff ON doc.tipo_movimiento = 1 AND doc.id_manu = ff.id_factura
                        LEFT JOIN fac_otros fo       ON doc.tipo_movimiento = 2 AND doc.id_manu = fo.id_factura
                        LEFT JOIN far_ventas fv      ON doc.tipo_movimiento = 3 AND doc.id_manu = fv.id_venta
                        LEFT JOIN fac_cartera fc     ON doc.tipo_movimiento = 4 AND doc.id_manu = fc.id_facturac
                        WHERE (doc.tipo_movimiento = 1 AND ff.id_factura IS NOT NULL)
                            OR (doc.tipo_movimiento = 2 AND fo.id_factura IS NOT NULL)
                            OR (doc.tipo_movimiento = 3 AND fv.id_venta IS NOT NULL)
                            OR (doc.tipo_movimiento = 4 AND fc.id_facturac IS NOT NULL)) AS facturas
                            ON (facturas.id_manu = ctb_doc.id_manu AND facturas.tipo = ctb_doc.tipo_movimiento AND facturas.id_ctb_doc = ctb_doc.id_ctb_doc)
                    WHERE ctb_doc.fecha BETWEEN $fec_ini AND $fec_fin AND ctb_doc.estado = 2 
                        AND ctb_libaux.id_cuenta IN ($inList)
                        $and_where
                    ORDER BY ctb_libaux.id_cuenta ASC, DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') ASC, ctb_libaux.debito DESC, ctb_libaux.credito DESC";

        $rs = $cmd->query($sqlAll);
        if ($rs === false) {
            $rs = null;
        }
        // recorrer en streaming
        $currentAccount = null;
        $saldo_inicial = 0;
        $total_deb = 0;
        $total_cre = 0;

        if ($rs) {
            while ($obj = $rs->fetch(PDO::FETCH_ASSOC)) {
            $acct = $obj['id_cuenta'];
            // cuando cambie de cuenta escribir cabeceras y saldo inicial
            if ($currentAccount === null || $currentAccount !== $acct) {
                // si ya estábamos en otra cuenta, escribir totales de la cuenta anterior
                if ($currentAccount !== null) {
                    $totalsRow = ["", "", "", "", "", "", "Totales", $total_deb, $total_cre, $saldo_inicial];
                    $writer->addRow(WriterEntityFactory::createRowFromArray($totalsRow));
                }

                // inicializar nueva cuenta
                $currentAccount = $acct;
                $total_deb = 0;
                $total_cre = 0;
                $saldo_inicial = isset($saldosMap[$acct]) ? $saldosMap[$acct] : 0;

                // obtener descripción desde el mapa (O(1))
                $cuentaDesc = isset($cuentaMap[$acct]) ? $cuentaMap[$acct] : '';

                $writer->addRow(WriterEntityFactory::createRowFromArray([])); // Línea en blanco
                $writer->addRow(WriterEntityFactory::createRowFromArray(["CUENTA", $cuentaDesc]));
                $writer->addRow(WriterEntityFactory::createRowFromArray([])); // línea en blanco
                $headers = ["Fecha", "Tipo Documento", "Documento", "Referencia", "Tercero", "CC/nit", "Detalle", "Debito", "Credito", "Saldo"];
                $writer->addRow(WriterEntityFactory::createRowFromArray($headers));
                $saldoInicialRow = ["", "", "", "", "", "", "Saldo inicial:", "", "", $saldo_inicial];
                $writer->addRow(WriterEntityFactory::createRowFromArray($saldoInicialRow));
            }

            // procesar fila
            $primer_caracter = substr($obj['cuenta'], 0, 1);
            $segundo_caracter = substr($obj['cuenta'], 0, 2);
            if ($primer_caracter == 1 || $primer_caracter == 5 || $primer_caracter == 6 || $primer_caracter == 7 || $segundo_caracter == 81 || $segundo_caracter == 83 || $segundo_caracter == 99) {
                $saldo_inicial = $saldo_inicial + $obj['debito'] - $obj['credito'];
            } else {
                $saldo_inicial = $saldo_inicial + $obj['credito'] - $obj['debito'];
            }

            $row = [
                $obj['fecha'],
                $obj['cod_tipo_doc'],
                $obj['id_manu'],
                mb_strtoupper($obj['forma_pago']),
                mb_strtoupper($obj['nom_tercero']),
                $obj['nit_tercero'],
                mb_strtoupper($obj['detalle']),
                $obj['debito'],
                $obj['credito'],
                $saldo_inicial
            ];
            $writer->addRow(WriterEntityFactory::createRowFromArray($row));

            $total_deb += $obj['debito'];
            $total_cre += $obj['credito'];
        }

            // escribir totales de la última cuenta
            if ($currentAccount !== null) {
                $totalsRow = ["", "", "", "", "", "", "Totales", $total_deb, $total_cre, $saldo_inicial];
                $writer->addRow(WriterEntityFactory::createRowFromArray($totalsRow));
            }

            $rs->closeCursor();
            unset($rs);
        }
    }

    $writer->close();
    exit;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
