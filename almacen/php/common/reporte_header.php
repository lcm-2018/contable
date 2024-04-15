<?php

include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$sql = 'SELECT razon_social_ips,nit_ips,codigo_sgsss_ips,telefono_ips,direccion_ips FROM tb_datos_ips LIMIT 1';
$rs = $cmd->query($sql);
$obj = $rs->fetch();
$razhd = $obj['razon_social_ips'];
$nithd = $obj['nit_ips'];
$codhd = $obj['codigo_sgsss_ips'];
$dirhd = $obj['direccion_ips'];
$telhd = $obj['telefono_ips'];

?>
<table style="width:100% !important;">
    <tr>
        <td colspan="3" style="text-align:right; font-size:50%">
            Generado por: <strong>CRONHIS</strong>. Fecha Impresión:<?php echo date('Y-m-d h:i:s A') ?>
        </td>
    </tr>    
    <tr>
        <th style="width:15%">
            <label><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label>
        </th>        
        <th style="text-align:center">
            <div><?php echo $razhd; ?></div>
            <div>NIT: <?php echo $nithd; ?></div>
            <div><?php echo $dirhd; ?> TELÉFONO <?php echo $telhd; ?></div>
        </th>
        <th style="width:15%"></th>
    </tr>
</table>