<?php
session_start();
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$cx = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
// Buscamos si hay registros posteriores a la fecha recibida
$vigencia = $_SESSION['vigencia'];
$fecha = date('Y-m-d', strtotime($vigencia . '/12/31'));
$rubro = $_post['rubro'];

// consultar valor valor_aprobado en pto_cargue
$sql = "SELECT valor_aprobado FROM pto_cargue WHERE cod_pptal = '$rubro' AND vigencia = $_SESSION[vigencia]";
$rs = $cx->query($sql);
$valor_aprobado = $rs->fetch_assoc();
$adicion = saldoRubroGastos($vigencia, $rubro, $fecha, 'ADI', 0, $cx);
$reduccion = saldoRubroGastos($vigencia, $rubro, $fecha, 'RED', 0, $cx);
$credito = saldoRubroGastos($vigencia, $rubro, $fecha, 'TRA', 0, $cx);
$contracredito = saldoRubroGastos($vigencia, $rubro, $fecha, 'TRA', 1, $cx);
$aplazamiento = saldoRubroGastos($vigencia, $rubro, $fecha, 'APL', 0, $cx);
$desaplazamiento = saldoRubroGastos($vigencia, $rubro, $fecha, 'DES', 0, $cx);
$comprometido = saldoRubroGastos($vigencia, $rubro, $fecha, 'CDP', 0, $cx);
$liberado = saldoRubroGastos($vigencia, $rubro, $fecha, 'LCD', 0, $cx);
$definitivo =  $valor_aprobado['valor_aprobado'] + $adicion - $reduccion + $credito - $contracredito - $aplazamiento + $desaplazamiento;
$saldo = $definitivo - $comprometido - $liberado;
$response[] = array("total" => $saldo, "liberado" => $comprometido);
echo json_encode($response);
$cx->close();
exit;
