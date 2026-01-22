<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
include '../../../terceros.php';
$_post = json_decode(file_get_contents('php://input'), true);
$valores = explode('|', $_post['valores']);
$base = $valores[0];
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_municipios`.`id_municipio`
                , `tb_municipios`.`nom_municipio` 
                , SUM(`ctb_causa_costos`.`valor`) AS `base`
                
            FROM
                `ctb_causa_costos`
                INNER JOIN `far_centrocosto_area` 
                    ON (`ctb_causa_costos`.`id_area_cc` = `far_centrocosto_area`.`id_area`)
                INNER JOIN `tb_sedes` 
                    ON (`far_centrocosto_area`.`id_sede` = `tb_sedes`.`id_sede`)
                INNER JOIN `tb_municipios` 
                    ON (`tb_sedes`.`id_municipio` = `tb_municipios`.`id_municipio`)
            WHERE (`ctb_causa_costos`.`id_ctb_doc` = {$_post['id_doc']})
            GROUP BY `tb_municipios`.`id_municipio`";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT id_retencion, nombre_retencion FROM ctb_retenciones WHERE id_retencion_tipo= 3 ORDER BY nombre_retencion ASC";
    $rs = $cmd->query($sql);
    $retica = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT `id_retencion`, `nombre_retencion` FROM `ctb_retenciones` WHERE `id_retencion_tipo` = 4";
    $rs = $cmd->query($sql);
    $sobretasa = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_retencion, nombre_retencion FROM ctb_retenciones WHERE id_retencion_tipo= 3 ORDER BY nombre_retencion ASC";
    $rs = $cmd->query($sql);
    $datas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$lbl_mun = '<label class="small">Municipio</label>';
$lbl_dat = '<label class="small">Retención</label>';
$lbl_sob = '<label class="small">Sobretasa</label>';
$lbl_ret = '<label class="small">Valor retención</label>';
$lbl_vsb = '<label class="small">Valor sobretasa</label>';
$val_ret = '<input type="text" name="valor_rte[]" class="form-control form-control-sm text-right" onkeyup="valorMiles(id)" value="0" disabled>';
$val_sob = '<input type="text" name="valor_sob[]" class="form-control form-control-sm text-right" onkeyup="valorMiles(id)" value="0" disabled>';
$primero = true;
$response = '<div class="border px-2 rounded mb-1 bg-light" style="max-height: 200px; overflow-y: auto;">';
foreach ($retenciones as $ret) {
    $response .= '<div class="form-row">';
    $response .= '<div class="form-group col-md-4">';
    if ($primero) {
        $response .= $lbl_mun;
    }
    $response .= '<input type="hidden" name="base[]" value="' . $base . '">';
    $response .= '<input type="hidden" name="id_rete_sede[]" value="' . $ret['id_municipio'] . '">';
    $response .= '<div class="form-control form-control-sm text-left">' . $ret['nom_municipio'] . '->' . pesos($base) . '</div>';
    $response .= '</div>';
    $response .= '<div class="form-group col-md-2">';
    if ($primero) {
        $response .= $lbl_dat;
    }
    $response .= '<select class="form-control form-control-sm py-0 sm" name="id_rete[]" onchange="aplicaDctoRetIca(this,value,1)" required>';
    $response .= '<option value="0">-- Seleccionar--</option>';
    foreach ($datas as $dat) {
        $response .= '<option value="' . $dat['id_retencion'] . '">' . $dat['nombre_retencion'] .  '</option>';
    }
    $response .= "</select>";
    $response .= '</div>';
    $response .= '<div class="form-group col-md-2">';
    if ($primero) {
        $response .= $lbl_sob;
    }
    $response .= '<select class="form-control form-control-sm py-0 sm" name="id_rete_sobre[]" onchange="aplicaDctoRetIca(this,value,2)" required>';
    $response .= '<option value="0">-- Seleccionar--</option>';
    foreach ($sobretasa as $sb) {
        $response .= '<option value="' . $sb['id_retencion'] . '">' . $sb['nombre_retencion'] .  '</option>';
    }
    $response .= "</select>";
    $response .= '</div>';
    $response .= '<div class="form-group col-md-2">';
    if ($primero) {
        $response .= $lbl_ret;
    }
    $response .= $val_ret;
    $response .= '</div>';
    $response .= '<div class="form-group col-md-2">';
    if ($primero) {
        $response .= $lbl_vsb;
    }
    $response .= $val_sob;
    $response .= '</div>';
    $response .= '</div>';
    $primero = false;
}
$response .= '</div>';
echo $response;
exit;
