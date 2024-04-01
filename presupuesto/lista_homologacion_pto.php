<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
$vigencia = $_SESSION['vigencia'];
// consulto id_pto_tipo de la tabla pto_presupuestos cuando id_pto_presupuestos es igual a $id_pto_presupuestos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_cargue`, `id_pto`, `cod_pptal`, `nom_rubro`, `tipo_dato` 
            FROM `pto_cargue` 
            WHERE `id_pto` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_situacion`,
                `concepto`
            FROM `pto_situacion`";
    $rs = $cmd->query($sql);
    $situacion = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `nombre`, `id_tipo`
            FROM `pto_presupuestos` 
            WHERE `id_pto`= $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $nomPresupuestos = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($nomPresupuestos['id_tipo'] == 1) {
        $tabla = '`pto_homologa_ingresos`';
        $campos = '';
        $condicion = '';
    } else if ($nomPresupuestos['id_tipo'] == 2) {
        $tabla = '`pto_homologa_gastos`';
        $campos = ' , `pto_vigencias`.`id_vigencia` AS `codigo_vig`
                    , `pto_vigencias`.`vigencia` AS `nombre_vig`
                    , `pto_homologa_gastos`.`id_seccion`
                    , `pto_seccion`.`id_seccion` AS `codigo_secc`
                    , `pto_seccion`.`seccion` AS `nombre_secc`
                    , `pto_homologa_gastos`.`id_sector`
                    , `pto_sector`.`id_sector` AS `codigo_sect`
                    , `pto_sector`.`sector` AS `nombre_sect`
                    , `pto_homologa_gastos`.`id_csia`
                    , `pto_clase_sia`.`codigo` AS `codigo_csia`
                    , `pto_clase_sia`.`clase_sia` AS `nombre_csia`';
        $condicion = 'INNER JOIN `pto_vigencias` 
                        ON (`pto_homologa_gastos`.`id_vigencia` = `pto_vigencias`.`id_vigencia`)
                    INNER JOIN `pto_seccion` 
                        ON (`pto_homologa_gastos`.`id_seccion` = `pto_seccion`.`id_seccion`)
                    INNER JOIN `pto_sector` 
                        ON (`pto_homologa_gastos`.`id_sector` = `pto_sector`.`id_sector`)
                    INNER JOIN `pto_clase_sia` 
                        ON (`pto_homologa_gastos`.`id_csia` = `pto_clase_sia`.`id_csia`)';
    }
    $sql = "SELECT
                $tabla.`id_homologacion`
                , $tabla.`id_cargue`
                , $tabla.`id_cgr`
                , `pto_codigo_cgr`.`codigo` AS `codigo_cgr`
                , `pto_codigo_cgr`.`nombre` AS `nombre_cgr`
                , $tabla.`id_cpc`
                , `pto_cpc`.`codigo` AS `codigo_cpc`
                , `pto_cpc`.`division` AS `nombre_cpc`
                , $tabla.`id_fuente`
                , `pto_fuente`.`codigo` AS `codigo_fte`
                , `pto_fuente`.`fuente` AS `nombre_fte`
                , $tabla.`id_tercero`
                , `pto_terceros`.`codigo` AS `codigo_ter`
                , `pto_terceros`.`entidad` AS `nombre_ter`
                , $tabla.`id_politica`
                , `pto_politica`.`codigo` AS `codigo_pol`
                , `pto_politica`.`politica` AS `nombre_pol`
                , $tabla.`id_siho`
                , `pto_siho`.`codigo` AS `codigo_siho`
                , `pto_siho`.`nombre` AS `nombre_siho`
                , $tabla.`id_sia`
                , `pto_sia`.`codigo` AS `codigo_sia`
                , `pto_sia`.`nombre` AS `nombre_sia`
                , $tabla.`id_situacion`
                , `pto_situacion`.`concepto`
                , $tabla.`id_vigencia`
                $campos
            FROM
                $tabla
                INNER JOIN `pto_codigo_cgr` 
                    ON ($tabla.`id_cgr` = `pto_codigo_cgr`.`id_cod`)
                INNER JOIN `pto_cpc` 
                    ON ($tabla.`id_cpc` = `pto_cpc`.`id_cpc`)
                INNER JOIN `pto_fuente` 
                    ON ($tabla.`id_fuente` = `pto_fuente`.`id_fuente`)
                INNER JOIN `pto_politica` 
                    ON ($tabla.`id_politica` = `pto_politica`.`id_politica`)
                INNER JOIN `pto_terceros` 
                    ON ($tabla.`id_tercero` = `pto_terceros`.`id_tercero`)
                INNER JOIN `pto_siho` 
                    ON ($tabla.`id_siho` = `pto_siho`.`id_siho`)
                INNER JOIN `pto_sia` 
                    ON ($tabla.`id_sia` = `pto_sia`.`id_sia`)
                INNER JOIN `pto_situacion` 
                    ON ($tabla.`id_situacion` = `pto_situacion`.`id_situacion`)
                $condicion
                INNER JOIN `pto_cargue` 
                    ON ($tabla.`id_cargue` = `pto_cargue`.`id_cargue`)
            WHERE (`pto_cargue`.`id_pto` = $id_pto_presupuestos)";
    $rs = $cmd->query($sql);
    $homologacion = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$ingreso = empty($homologacion) ? 0 : 1;
$gasto = empty($homologacion) ? 0 : 1;;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php'; ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    HOMOLOGACIONES A <?php echo strtoupper($nomPresupuestos['nombre'])  ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="table-responsive">
                                <form id="formDataHomolPto">
                                    <input type="hidden" id="id_pto_tipo" name="id_pto_tipo" value="<?php echo $nomPresupuestos['id_tipo'] ?>">
                                    <?php
                                    if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
                                        echo  '<input type="hidden" id="peReg" value="1">';
                                    } else {
                                        echo  '<input type="hidden" id="peReg" value="0">';
                                    }
                                    ?>
                                    <table id="tableHomologaPto" class="table table-striped table-bordered table-sm nowrap shadow" style="width:100%">
                                        <thead style="position: sticky !important; top: 0 !important; z-index: 999 !important;">
                                            <tr class="text-center">
                                                <?php
                                                if ($nomPresupuestos['id_tipo'] == 1) {
                                                ?>
                                                    <th>Código</th>
                                                    <th>Nombre</th>
                                                    <th>
                                                        <div class="center-block px-4">
                                                            <input type="checkbox" id="desmarcar" title="Desmarcar Todos">
                                                            <input type="hidden" value="<?php echo $ingreso ?>" name="ingreso">
                                                        </div>
                                                    </th>
                                                    <th>Código CGR</th>
                                                    <th>Vigencia</th>
                                                    <th>CPC</th>
                                                    <th>Fuente</th>
                                                    <th>Terceros</th>
                                                    <th>Política<br>Pública</th>
                                                    <th>SIHO</th>
                                                    <th>SIA</th>
                                                    <th>Situación<br>Fondos</th>
                                                <?php
                                                } else if ($nomPresupuestos['id_tipo'] == 2) {
                                                ?>
                                                    <th>Código</th>
                                                    <th>Nombre</th>
                                                    <th>
                                                        <div class="center-block px-4">
                                                            <input type="checkbox" id="desmarcar" title="Desmarcar Todos">
                                                            <input type="hidden" value="<?php echo $gasto ?>" name="gasto">
                                                        </div>
                                                    </th>
                                                    <th>Codigo CGR</th>
                                                    <th>Vigencia</th>
                                                    <th>Sección<br>Presupuesto</th>
                                                    <th>Sector</th>
                                                    <th>CPC</th>
                                                    <th>Fuente</th>
                                                    <th>Terceros</th>
                                                    <th>Política<br>Pública</th>
                                                    <th>SIHO</th>
                                                    <th>SIA</th>
                                                    <th>Clase<br>SIA</th>
                                                    <th>Situación<br>Fondos</th>

                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody id="modificaHomologaPto">
                                            <?php
                                            foreach ($rubros as $rb) {
                                                $tp_cta = $rb['tipo_dato'] == 0 ? 'M' : 'D';
                                                echo "<tr>";
                                                echo "<td>" . $rb['cod_pptal'] . "</td>";
                                                if ($nomPresupuestos['id_tipo'] == 1) {
                                                    $colspan = $tp_cta == 'D' ? 1 : 11;
                                                    $centrar = $tp_cta == 'D' ? '' : '';
                                                    echo "<td colspan='" . $colspan . "' class='" . $centrar . "'>" . $rb['nom_rubro'] . "</td>";
                                                    if ($tp_cta == 'D') {
                                                        $key = array_search($rb['id_cargue'], array_column($homologacion, 'id_cargue'));
                                                        echo "<td class='text-center'>
                                                            <div class='center-block'>
                                                                <input type='checkbox' class='dupLine' value='" . $rb['id_cargue'] . "' title='Copiar datos de otra linea'>
                                                                <input type='hidden' value='" . ($key !== false ? $homologacion[$key]['id_homologacion'] : 0) . "' name='idHomol[" . $rb['id_cargue'] . "]'>
                                                            </div>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='1' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='uno[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_cgr'] . ' -> ' . $homologacion[$key]['nombre_cgr'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='codCgr[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_cgr'] : 0) . "'>
                                                            </td>";
                                                        $val_vig = $key !== false ? $homologacion[$key]['id_vigencia'] : 0;
                                                        echo "<td class='p-0'>
                                                            <select class='form-control form-control-sm py-0 px-1 validaPto homologaPTO'  name='vigencia[" . $rb['id_cargue'] . "]'>
                                                                <option value='0' " . ($val_vig == 0 ? 'selected' : '') . ">--Seleccionar--</option>
                                                                <option value='1' " . ($val_vig == 1 ? 'selected' : '') . ">ACTUAL</option>
                                                                <option value='2' " . ($val_vig == 2 ? 'selected' : '') . ">ANTERIOR</option>";
                                                        echo "</select>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='5' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='cinco[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_cpc'] . ' -> ' . $homologacion[$key]['nombre_cpc'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='cpc[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_cpc'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='6' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='seis[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_fte'] . ' -> ' . $homologacion[$key]['nombre_fte'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='fuente[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_fuente'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='7' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='siete[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_ter'] . ' -> ' . $homologacion[$key]['nombre_ter'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='tercero[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_tercero'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='8' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='ocho[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_pol'] . ' -> ' . $homologacion[$key]['nombre_pol'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='polPub[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_politica'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='9' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='nueve[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_siho'] . ' -> ' . $homologacion[$key]['nombre_siho'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='siho[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_siho'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='10' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='diez[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_sia'] . ' -> ' . $homologacion[$key]['nombre_sia'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='sia[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_sia'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <select class='form-control form-control-sm py-0 px-1 homologaPTO validaPto'  name='situacion[" . $rb['id_cargue'] . "]'>
                                                                    <option value='0'>--Seleccionar--</option>";

                                                        foreach ($situacion as $s) {
                                                            $val_sit = $key !== false ? $homologacion[$key]['id_situacion'] : 0;
                                                            $slc = $val_sit == $s['id_situacion'] ? 'selected' : '';
                                                            echo '<option value="' . $s['id_situacion'] . '" ' . $slc . '>' . $s['concepto'] . '</option>';
                                                        }
                                                        echo        "</select>
                                                            </td>";
                                                    }
                                                } else if ($nomPresupuestos['id_tipo'] == 2) {
                                                    $colspan = $tp_cta == 'D' ? 1 : 14;
                                                    $centrar = $tp_cta == 'D' ? '' : '';
                                                    echo "<td colspan='" . $colspan . "' class='" . $centrar . "'>" . $rb['nom_rubro'] . "</td>";
                                                    if ($tp_cta == 'D') {
                                                        $key = array_search($rb['id_cargue'], array_column($homologacion, 'id_cargue'));
                                                        echo "<td class='text-center'>
                                                            <div class='center-block'>
                                                                <input type='checkbox' class='dupLine' value='" . $rb['id_cargue'] . "' title='Copiar datos de otra linea'>
                                                                <input type='hidden' value='" . ($key !== false ? $homologacion[$key]['id_homologacion'] : 0) . "' name='idHomol[" . $rb['id_cargue'] . "]'>
                                                            </div>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='1' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='uno[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_cgr'] . ' -> ' . $homologacion[$key]['nombre_cgr'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='codCgr[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_cgr'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='2' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='dos[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_vig'] . ' -> ' . $homologacion[$key]['nombre_vig'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='vigencia[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_vigencia'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='3' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='tres[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_secc'] . ' -> ' . $homologacion[$key]['nombre_secc'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='seccion[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_seccion'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='4' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='cuatro[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_sect'] . ' -> ' . $homologacion[$key]['nombre_sect'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='sector[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_sector'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='5' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='cinco[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_cpc'] . ' -> ' . $homologacion[$key]['nombre_cpc'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='cpc[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_cpc'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='6' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='seis[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_fte'] . ' -> ' . $homologacion[$key]['nombre_fte'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='fuente[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_fuente'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='7' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='siete[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_ter'] . ' -> ' . $homologacion[$key]['nombre_ter'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='tercero[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_tercero'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='8' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='ocho[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_pol'] . ' -> ' . $homologacion[$key]['nombre_pol'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='polPub[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_politica'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='9' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='nueve[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_siho'] . ' -> ' . $homologacion[$key]['nombre_siho'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='siho[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_siho'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='10' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='diez[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_sia'] . ' -> ' . $homologacion[$key]['nombre_sia'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='sia[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_sia'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='11' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='once[" . $rb['id_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_csia'] . ' -> ' . $homologacion[$key]['nombre_csia'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='csia[" . $rb['id_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_csia'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <select class='form-control form-control-sm py-0 px-1 homologaPTO validaPto'  name='situacion[" . $rb['id_cargue'] . "]'>
                                                                    <option value='0'>--Seleccionar--</option>";

                                                        foreach ($situacion as $s) {
                                                            $val_sit = $key !== false ? $homologacion[$key]['id_situacion'] : 0;
                                                            $slc = $val_sit == $s['id_situacion'] ? 'selected' : '';
                                                            echo '<option value="' . $s['id_situacion'] . '" ' . $slc . '>' . $s['concepto'] . '</option>';
                                                        }
                                                        echo        "</select>
                                                            </td>";
                                                    } else {
                                                        echo "<td colspan='13'></td>";
                                                    }
                                                }
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-secondary" style="width: 7rem;" href="lista_presupuestos.php">Regresar</a>
                                <button type="button" class="btn btn-success" style="width: 7rem;" id="setHomologacionPto">Modificar</button>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <?php include '../modales.php' ?>
    </div>
    <?php include '../scripts.php' ?>
</body>

</html>