<?php
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include_once '../../conexion.php';
include_once '../../permisos.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_estudios_previos`.`id_est_prev`
                , `ctt_estudios_previos`.`id_compra`
                , `ctt_estudios_previos`.`fec_ini_ejec`
                , `ctt_estudios_previos`.`fec_fin_ejec`
                , `ctt_estudios_previos`.`val_contrata`
                , `tb_forma_pago_compras`.`descripcion`
                , `ctt_estudios_previos`.`id_supervisor`
            FROM
                `ctt_estudios_previos`
            INNER JOIN `tb_forma_pago_compras` 
                ON (`ctt_estudios_previos`.`id_forma_pago` = `tb_forma_pago_compras`.`id_form_pago`)
            WHERE `id_compra` = '$id_adq'";
    $rs = $cmd->query($sql);
    $estudio_prev = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_ter_sup = $estudio_prev['id_supervisor'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `no_doc` FROM `seg_terceros` WHERE `id_tercero_api` = '$id_ter_sup'";
    $rs = $cmd->query($sql);
    $terceros_sup = $rs->fetch();
    //API URL
    $url = $api . 'terceros/datos/res/lista/' . $terceros_sup['no_doc'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $supervisor = json_decode($result, true);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$est_prev = isset($estudio_prev) ? $estudio_prev['id_est_prev'] : 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_garantias_compra`.`id_est_prev`
                ,`seg_garantias_compra`.`id_poliza`
                , `tb_polizas`.`descripcion`
                , `tb_polizas`.`porcentaje`
            FROM
                `seg_garantias_compra`
            INNER JOIN `tb_polizas` 
                ON (`seg_garantias_compra`.`id_poliza` = `tb_polizas`.`id_poliza`)
            WHERE `seg_garantias_compra`.`id_est_prev` = '$est_prev'";
    $rs = $cmd->query($sql);
    $garantias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

?>
<div class="overflow">
    <table class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
        <thead>
            <tr>
                <th colspan="2" class="text-center centro-vertical">Plazo de ejecución</th>
                <th rowspan="2" class="text-center centro-vertical">Valor contrato</th>
                <th rowspan="2" class="text-center centro-vertical">Forma de Pago</th>
                <th rowspan="2" class="text-center centro-vertical">Garantías / Pólizas</th>
                <th colspan="2" class="text-center centro-vertical">Supervisor</th>
                <th rowspan="2" class="text-center centro-vertical">Acciones</th>
            </tr>
            <tr>
                <th class="text-center centro-vertical">Fecha Inicial</th>
                <th class="text-center centro-vertical">Fecha Final</th>
                <th class="text-center centro-vertical">No. Documento</th>
                <th class="text-center centro-vertical">Nombre</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="centro-vertical"><?php echo $estudio_prev['fec_ini_ejec'] ?></td>
                <td class="centro-vertical"><?php echo $estudio_prev['fec_fin_ejec'] ?></td>
                <td class="centro-vertical text-right"><?php echo pesos($estudio_prev['val_contrata']) ?></td>
                <td class="centro-vertical"><?php echo $estudio_prev['descripcion'] ?></td>
                <td class="centro-vertical">
                    <?php
                    foreach ($garantias as $g) {
                        echo '<li>' . $g['descripcion'] . ' ' . $g['porcentaje'] . '%</li>';
                    }
                    ?>
                </td>
                <td class="centro-vertical"><?php echo $id_ter_sup == '' ? 'PENDIENTE' : $terceros_sup['no_doc'] ?></td>
                <td class="centro-vertical"><?php echo mb_strtoupper($supervisor[0]['apellido1'] . ' ' . $supervisor[0]['apellido2'] . ' ' . $supervisor[0]['nombre1'] . ' ' . $supervisor[0]['nombre2'] . ' ' . $supervisor[0]['razon_social']) ?></td>
                <td class="centro-vertical" id="modificarEstPrev">
                    <?php
                    $editar = $borrar = null;
                    if ($adquisicion['estado'] <= 6) {
                        if (PermisosUsuario($permisos, 5302, 3) || $id_rol == 1) {
                            $editar = '<a value="' . $est_prev . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                        }
                        if (PermisosUsuario($permisos, 5302, 4) || $id_rol == 1) {
                            $borrar = '<a value="' . $est_prev . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                        }
                    }
                    ?>
                    <div class="text-center">
                        <?php echo $editar . $borrar ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>