<?php

include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$sql = 'SELECT razon_social_ips,nit_ips,codigo_sgsss_ips,telefono_ips,direccion_ips FROM tb_datos_ips LIMIT 1';
$rs = $cmd->query($sql);
$obj_ent = $rs->fetch();
$razhd = $obj_ent['razon_social_ips'];
$nithd = $obj_ent['nit_ips'];
$codhd = $obj_ent['codigo_sgsss_ips'];
$dirhd = $obj_ent['direccion_ips'];
$telhd = $obj_ent['telefono_ips'];

?>
<table style="width:100% !important; border:#A9A9A9 1px solid">
    <tr>
        <th rowspan="2" style="width:15%">
            <img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100">
        </th>
        <th colspan="2" style="text-align:right; font-size:50%">
            Generado por: <strong>CRONHIS</strong>. Fecha Impresión:<?php echo date('Y-m-d h:i:s A') ?>. Usuario:<?php echo mb_strtoupper($_SESSION['user']); ?>
        </th>
    </tr>    
    <tr>
        <th style="text-align:center; font-size:80%">
            <div><?php echo $razhd; ?></div>
            <div>NIT: <?php echo $nithd; ?></div>
            <div><?php echo $dirhd; ?> TELÉFONO <?php echo $telhd; ?></div>
        </th>
        <th style="width:15%"></td>
    </tr>
</table>