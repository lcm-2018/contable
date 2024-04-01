<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_contr_liq = $_POST['id_lc'];
$vigencia = $_SESSION['vigencia'];
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../../conexion.php';
require('../../../fpdf/fpdf.php');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_contrato, nom_empleado.id_empleado, no_documento, descripcion_carg, CONCAT(nombre1, ' ', nombre2, ' ', apellido1, ' ', apellido2) AS nombre, fec_inicio, fec_fin, vigencia, tot_dias_lab, tot_dias_vac, sal_base, aux_transp, val_prima, val_cesantias, val_icesantias, val_vacaciones 
            FROM
                nom_liq_contrato_emp
            INNER JOIN nom_contratos_empleados 
                ON (nom_liq_contrato_emp.id_contrato = nom_contratos_empleados.id_contrato_emp)
            INNER JOIN nom_empleado 
                ON (nom_contratos_empleados.id_empleado = nom_empleado.id_empleado)
            INNER JOIN nom_cargo_empleado 
                ON (nom_empleado.cargo = nom_cargo_empleado.id_cargo)
            WHERE id_contrato = '$id_contr_liq'";
    $rs = $cmd->query($sql);
    $contrato_liq = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT nom_liq_vac.id_vac, id_contrato, dias_habiles
            FROM
                nom_liq_vac
            INNER JOIN nom_vacaciones 
                ON (nom_liq_vac.id_vac = nom_vacaciones.id_vac)
            WHERE id_contrato = '$id_contr_liq'";
    $rs = $cmd->query($sql);
    $vac_liq = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, SUM(val_liq_ps) AS tot_prima 
            FROM 
                (SELECT * FROM nom_liq_prima WHERE anio = '$vigencia') AS t
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $prima_liq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($contrato_liq)) {
    $id_empleado = $contrato_liq['id_empleado'];
    $key = array_search($id_empleado, array_column($prima_liq, 'id_empleado'));
    if (false !== $key) {
        $pri_liquidada = $prima_liq[$key]['tot_prima'];
    } else {
        $pri_liquidada = 0;
    }
    $nombre = mb_strtoupper($contrato_liq['nombre']);
    $id_contrato = $contrato_liq['id_contrato'];
    $no_doc = $contrato_liq['no_documento'];
    $cargo = mb_strtoupper($contrato_liq['descripcion_carg']);
    $causa = mb_strtoupper('terminación de contrato');
    $fec_inicio = $contrato_liq['fec_inicio'];
    $sal_bas = $contrato_liq['sal_base'];
    $fec_fin = $contrato_liq['fec_fin'];
    $aux_tra = $contrato_liq['aux_transp'];
    $tot_dias = $contrato_liq['tot_dias_lab'];
    $tot_sal = $sal_bas + $aux_tra;
    $val_prima = $contrato_liq['val_prima'];
    $val_cesantias = $contrato_liq['val_cesantias'];
    $val_icesantias = $contrato_liq['val_icesantias'];
    $tval_vac = $contrato_liq['val_vacaciones'];
    $t_diasvac =  $contrato_liq['tot_dias_vac'];
    $tdias_disf = isset($vac_liq['dias_habiles']) ? $vac_liq['dias_habiles'] : 0;
    $dias_pend = $t_diasvac - $tdias_disf;
    $v_dias_disf = ($tval_vac / $t_diasvac) * $tdias_disf;
    $v_dias_pend = ($tval_vac / $t_diasvac) * $dias_pend;
    $total_vac =  $v_dias_disf + $v_dias_pend;
    $tot_liquidacion = $val_cesantias + $val_icesantias + $val_prima + $tval_vac - $pri_liquidada - $v_dias_disf;
    $val_liq_letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
    $val_liq_letras = mb_strtoupper($val_liq_letras->format($tot_liquidacion, 2));
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Times', '', 14);
    $pdf->SetFillColor(224, 235, 255);
    $pdf->SetTextColor(0);
    $pdf->Cell(190, 5, '', 0, 0, 'C', false);
    $pdf->Ln();
    $pdf->Cell(55, 20, 'Logotipo', 1, 0, 'C', false);
    $pdf->Cell(135, 20, utf8_decode('LIQUIDACIÓN DE CONTRATO: CNE-') . $id_contrato, 1, 0, 'C', false);
    $pdf->Ln();
    $pdf->SetFont('', '', 11);
    $pdf->SetFillColor(254, 254, 254);
    $pdf->Cell(190, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->Cell(25, 4, utf8_decode('NOMBRE: '), 'L', 0, 'L', false);
    $pdf->Cell(165, 4, utf8_decode($nombre), 'R', 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell(25, 4, utf8_decode('C.C: '), 'L', 0, 'L', false);
    $pdf->Cell(165, 4, utf8_decode($no_doc), 'R', 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell(25, 4, utf8_decode('CARGO: '), 'L', 0, 'L', false);
    $pdf->Cell(165, 4, utf8_decode($cargo), 'R', 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell(25, 4, utf8_decode('CAUSA: '), 'L', 0, 'L', false);
    $pdf->Cell(165, 4, utf8_decode($causa), 'R', 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell(190, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->SetFillColor(72, 201, 176);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetFont('', 'B');
    //datos liquidacion
    $pdf->Cell(95, 7, utf8_decode('PERIODO DE LIQUIDACIÓN'), 1, 0, 'C', true);
    $pdf->Cell(95, 7, utf8_decode('SALARIO BASE DE LIQUIDACIÓN'), 1, 0, 'C', true);
    $pdf->Ln();
    $pdf->SetFillColor(254, 245, 231);
    $pdf->SetTextColor(0);
    $pdf->SetFont('', '', 10);
    $pdf->Cell(95, 2, ' ', 'L', 0, false);
    $pdf->Cell(95, 2, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->Cell(70, 4, 'FECHA INICIO CONTRATO', 'L', 0, false);
    $pdf->Cell(25, 4, $fec_inicio, 0, 0, 'R', false);
    $pdf->Cell(70, 4, utf8_decode('SALARIO BÁSICO'), 'L', 0, false);
    $pdf->Cell(25, 4, pesos($sal_bas), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(70, 4, utf8_decode('FECHA TERMINACIÓN CONTRATO'), 'L', 0, false);
    $pdf->Cell(25, 4, $fec_fin, 0, 0, 'R', false);
    $pdf->Cell(70, 4, utf8_decode('AUXILIO DE TRANSPORTE'), 'L', 0, false);
    $pdf->Cell(25, 4, pesos($aux_tra), 'RB', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(70, 4, utf8_decode('TOTAL DÍAS LABORADOS'), 'L', 0, false);
    $pdf->Cell(25, 4, $tot_dias, 0, 0, 'R', false);
    $pdf->Cell(70, 4, utf8_decode('TOTAL BASE LIQUIDACIÓN'), 'L', 0, false);
    $pdf->Cell(25, 4, pesos($tot_sal), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(95, 1, ' ', 'L', 0, false);
    $pdf->Cell(95, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->SetFillColor(72, 201, 176);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetFont('', 'B');
    //datos liquidacion
    $pdf->Cell(190, 7, utf8_decode('PRESTACIONES SOCIALES'), 1, 0, 'C', true);
    $pdf->Ln();
    $pdf->SetFillColor(254, 245, 231);
    $pdf->SetTextColor(0);
    $pdf->SetFont('', '', 10);
    $pdf->Cell(64, 5, utf8_decode('PRESTACIÓN'), 1, 0, 'C', false);
    $pdf->Cell(63, 5, 'DIAS', 1, 0, 'C', false);
    $pdf->Cell(63, 5, 'VALOR LIQUIDADO', 1, 0, 'C', false);
    $pdf->Ln();
    $pdf->Cell(190, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->Cell(64, 4, 'PRIMA DE SERVICIOS', 'L', 0, false);
    $pdf->Cell(63, 4, $tot_dias, 0, 0, 'C', false);
    $pdf->Cell(63, 4, pesos($val_prima), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(64, 4, 'CESANTIAS', 'L', 0, false);
    $pdf->Cell(63, 4, $tot_dias, 0, 0, 'C', false);
    $pdf->Cell(63, 4, pesos($val_cesantias), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(64, 4, 'INTERES DE CESANTIAS', 'L', 0, false);
    $pdf->Cell(63, 4, $tot_dias, 0, 0, 'C', false);
    $pdf->Cell(63, 4, pesos($val_icesantias), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(190, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->SetFillColor(72, 201, 176);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetFont('', 'B');
    //datos liquidacion
    $pdf->Cell(190, 7, utf8_decode('VACACIONES'), 1, 0, 'C', true);
    $pdf->Ln();
    $pdf->SetFont('', '', 10);
    $pdf->SetTextColor(0);
    $pdf->Cell(190, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'TOTAL DIAS VACACIONES', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, $t_diasvac, 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'DIAS DISFRUTADOS', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, $tdias_disf, 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'DIAS PENDIENDTES', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, $dias_pend, 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'VALOR DIAS DISFRUTADOS', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($v_dias_disf), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'VALOR DIAS PENDIENTE', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($v_dias_pend), 'RB', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'TOTAL', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($total_vac), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(190, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->SetFillColor(72, 201, 176);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetFont('', 'B');
    $pdf->Cell(190, 7, utf8_decode('RESUMEN LIQUIDACIÓN'), 1, 0, 'C', true);
    $pdf->Ln();
    $pdf->SetFont('', '', 10);
    $pdf->SetTextColor(0);
    $pdf->Cell(190, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'VALOR CESANTIAS', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($val_cesantias), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'VALOR INTERES DE DE CESANTIAS', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($val_icesantias), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'VALOR PRIMA DE SERVICIOS', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($val_prima), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'VALOR VACACIONES', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($tval_vac), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'VALOR DESCUENTO PRIMA DE SERVICIOS', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($pri_liquidada), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(165, 4, 'VALOR DESCUENTO VACACIONES', 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($v_dias_disf), 'RB', 0, 'R', false);
    $pdf->Ln();
    $pdf->SetFillColor(72, 201, 176);
    $pdf->SetFont('', 'B');
    $pdf->Cell(165, 4, utf8_decode('TOTAL LIQUIDACIÓN'), 'L', 0, 'L', false);
    $pdf->Cell(25, 4, pesos($tot_liquidacion), 'R', 0, 'R', false);
    $pdf->Ln();
    $pdf->Cell(190, 1, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->SetFillColor(213, 219, 219);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetFont('', 'B', '9');
    $pdf->Cell(190, 7, utf8_decode('LIQUIDACIÓN: ' . mb_strtoupper($val_liq_letras) . ' PESOS M/CTE'), 1, 0, 'C', true);
    $pdf->Ln();
    $pdf->Cell(190, 7, utf8_decode('SE HACE CONSTAR'), 1, 0, 'L', true);
    $pdf->Ln();
    $pdf->SetFillColor(242, 243, 244);
    $pdf->SetFont('', '', '9');
    $pdf->MultiCell(190, 4, utf8_decode("1. Que con el pago del dinero anotado en la presente liquidación, queda transada cualquier diferencia relativa al contrato de trabajo extinguido, o a cualquier diferencia anterior. Por lo tanto, esta transacción tiene como efecto la terminación de las obligaciones provenientes de la relación laboral que existió entre JOSE OCTAVIO CHAVES CHAMORRO identificado con cédula de ciudadanía No. 98.361.822 de Ipiales como Representante legal de IPS GASTROCENTER y el trabajador, quienes declaran estar a paz y salvo por todo concepto laboral."), 1, 'L', true);
    $pdf->Ln(0);
    $pdf->MultiCell(190, 4, utf8_decode("2. Con la entrega del presente documento al trabajador se hace constar que la liquidacion de las prestaciones sociales se realiza de acuerdo a la legislación laboral vigente."), 1, 'L', true);
    $pdf->Ln(0);
    $pdf->SetFont('', '', 10);
    $pdf->SetTextColor(0);
    $pdf->Cell(190, 15, ' ', 'LR', 0, false);
    $pdf->Ln();
    $pdf->Cell(5, 7, ' ', 'L', 0, false);
    $pdf->Cell(75, 7, 'EL EMPLEADO', 'T', 0, 'C', false);
    $pdf->Cell(30, 7, ' ', 0, 0, false);
    $pdf->Cell(75, 7, 'EL EMPLEADOR', 'T', 0, 'C', false);
    $pdf->Cell(5, 7, ' ', 'R', 0, false);
    $pdf->Ln();
    $pdf->Cell(15, 7, ' ', 'L', 0, false);
    $pdf->Cell(55, 7, 'C.C:', '', 0, 'L', false);
    $pdf->Cell(50, 7, ' ', 0, 0, false);
    $pdf->Cell(55, 7, 'C.C: ', '', 0, 'L', false);
    $pdf->Cell(15, 7, ' ', 'R', 0, false);
    $pdf->Ln();
    $pdf->Cell(190, 0, '', 'T');
    $pdf->Output('D', 'reporte_' . $no_doc . '.pdf');
} else {
    echo 'Contrato no encontrado';
}
