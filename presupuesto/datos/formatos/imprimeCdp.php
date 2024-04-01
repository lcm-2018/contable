<?php
session_start();
// Busca consecutivos del tipo de documento recibido para sugerir un numero 
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$_post = json_decode(file_get_contents('php://input'), true);
// Buscamos si hay registros posteriores a la fecha recibida
try {
    $sql = "SELECT codigo_doc, nombre, version_doc, fecha_doc FROM fin_maestro_doc WHERE tipo_doc ='CDP' AND estado=0";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT nombre FROM tb_datos_ips WHERE nit ='$_SESSION[nit_emp]'";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT objeto,fecha,id_manu FROM pto_documento WHERE id_pto_doc ='$_post[id]'";
    $res = $cmd->query($sql);
    $cdp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
    `pto_documento_detalles`.`rubro` as rubro 
    , `pto_cargue`.`nom_rubro` as nom_rubro
    , `pto_documento_detalles`.`valor` as valor
    , `pto_cargue`.`vigencia` as vigencia
    FROM
    `pto_documento_detalles`
    INNER JOIN `pto_cargue` 
        ON (`pto_documento_detalles`.`rubro` = `pto_cargue`.`cod_pptal`)
    WHERE `pto_documento_detalles`.`id_documento` =$_post[id];
    ";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$fecha = date('Y-m-d', strtotime($cdp['fecha']));
$fecha_doc = date('Y-m-d', strtotime($datos['fecha_doc']));
$cmd = null;
?>
<br>
<div id='imprimeFormatoCdp' style="width:85%; margin-left: auto;  margin-right: auto;">
    <table style="width:100%; border-collapse: collapse;">
        <tbody>
            <tr>
                <td style="border: 1px solid black; width:20%"><img src="../images/logos/logo.png" width="70%"></td>
                <td colspan="4" style="border: 1px solid black; width:63%"><strong><?php echo $datos['nombre']; ?></strong></td>
                <td style="border: 1px solid black; width:17%;text-align:center">
                    <small><?php echo $datos['codigo_doc'] . '<br>';
                            echo $fecha_doc . '<br>';
                            echo 'Versión ' . $datos['version_doc'];
                            ?>
                    </small>
                </td>
            </tr>
            <tr>
                <td colspan="6" class="text-left">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="6" class="text-left"><strong>NUMERO: </strong><?php echo $cdp['id_manu']; ?></td>
            </tr>
            <tr>
                <td colspan="6" class="text-left"><strong>SOLICITUD:</strong></td>
            </tr>
            <tr>
                <td colspan="6" class="text-left"><strong>LUGAR Y FECHA DE EXPEDICIÓN: </strong> YOPAL - <?php echo $fecha; ?></td>
            </tr>
            <tr>
                <td colspan="6" class="text-left"><strong>OBJETO: </strong><?php echo $cdp['objeto']; ?><br>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="6">El suscrito profesional del área financiera de la entidad <?php echo $empresa['nombre']; ?>, CERTIFICA que a la fecha existe saldo presupuestal libre de afectación para respaldar el siguiente compromiso: <br>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="6">
                    <table style="width:100%; border-collapse: collapse;" id="tablaRubros">
                        <tbody>
                            <tr>
                                <td style=" width:30%;border: 1px solid black"><strong>Código</strong> </td>
                                <td style="width:40%;border: 1px solid black"><strong>Nombre rubro</strong></td>
                                <td style="width:30%;border: 1px solid black"><strong>Valor</strong></td>
                            </tr>
                            <?php
                            if (!empty($rubros)) {
                                $total = 0;
                                foreach ($rubros as $rp) {
                                    $valor = number_format($rp['valor'], 2, ',', '.');
                            ?>
                                    <tr>
                                        <td style="border:1px solid black;text-align:left"><?php echo $rp['rubro']; ?></td>
                                        <td style="border:1px solid black;text-align:left"><?php echo $rp['nom_rubro']; ?></td>
                                        <td style=" border:1px solid black;text-align:right"><?php echo $valor; ?></td>
                                    </tr>
                            <?php
                                    $total = $total + $rp['valor'];
                                }
                                $fmt = new NumberFormatter("es", NumberFormatter::SPELLOUT);
                                $letras = $fmt->format($total, 2) . ' pesos';
                                $letras = str_replace("uno", "un", $letras);
                                $enletras = numeroLetras($total);
                            }
                            ?>
                            <tr>
                                <td colspan="2" class="text-left"><strong>Total</strong></td>
                                <td class="text-right"><strong><?php echo number_format($total, 2, ',', '.'); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="6" class="text-left"><strong>VALOR EN LETRAS:</strong> <?php echo $enletras; ?></td>
            </tr>
            <tr>
                <td colspan="6"><br><br><br><br><br></td>
            </tr>
        </tbody>
    </table>
    <div class="text-right">
        <button type="button" class="btn btn-primary btn-sm" onclick="imprimeDiv('imprimeFormatoCdp')">Imprimir</button>
        <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
    </div>
</div>