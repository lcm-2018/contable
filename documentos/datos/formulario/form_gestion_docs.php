<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
if ($id_rol != 1) {
    exit('Usuario no autorizado');
}
$ids = isset($_POST['ids']) ? $_POST['ids'] : exit('Acceso no autorizado');
if ($ids != '0') {
    $ids = explode('|', base64_decode($_POST['ids']));
    $id_maestro = $ids[0];
    $id_respon = $ids[1];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                    `id_maestro`, `id_modulo`, `id_doc_fte`, `control_doc`, `fecha_doc`, `version_doc`
                FROM
                    `fin_maestro_doc`
                WHERE (`id_maestro` = $id_maestro)";
        $rs = $cmd->query($sql);
        $maestro = $rs->fetch(PDO::FETCH_ASSOC);
        $sql = "SELECT
                    `fin_respon_doc`.`id_respon_doc`
                    , `fin_respon_doc`.`id_tercero`
                    , `fin_respon_doc`.`tipo_control`
                    , `fin_respon_doc`.`fecha_ini`
                    , `fin_respon_doc`.`fecha_fin`
                    , `fin_respon_doc`.`cargo`
                    , `tb_terceros`.`nom_tercero`
                    , `tb_terceros`.`nit_tercero`
                    , `tb_terceros`.`genero`
                FROM
                    `fin_respon_doc`
                    INNER JOIN `tb_terceros` 
                        ON (`fin_respon_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                WHERE (`fin_respon_doc`.`id_respon_doc` = $id_respon)";
        $rs = $cmd->query($sql);
        $responsable = $rs->fetch(PDO::FETCH_ASSOC);
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    $maestro = [
        'id_maestro' => 0,
        'id_modulo' => 0,
        'id_doc_fte' => 0,
        'control_doc' => 0,
        'fecha_doc' => date('Y-m-d'),
        'version_doc' => '',
    ];
    $responsable = [
        'id_respon_doc' => 0,
        'id_tercero' => 0,
        'tipo_control' => 0,
        'fecha_ini' => date('Y-m-d'),
        'fecha_fin' => date('Y-m-d'),
        'nom_tercero' => '',
        'nit_tercero' => '',
        'genero' => 0,
        'cargo' => '',
    ];
}

$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_modulo`,`nom_modulo`
            FROM `seg_modulos`
            WHERE `id_modulo` >= 50
            ORDER BY `nom_modulo` ASC";
    $rs = $cmd->query($sql);
    $modulos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_doc_fuente`,`nombre`
            FROM `ctb_fuente`
            ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $fuente = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_tipo`,`descripcion`
            FROM `fin_tipo_control`
            ORDER BY `descripcion`";
    $rs = $cmd->query($sql);
    $control = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

?>
<div class="px-0">
    <div class="shadow mb-3">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;" class="mb-0"><i class="fas fa-cog fa-lg mr-3" style="color:#2FDA49"></i>GESTIÓN DOCUMENTOS</p>
            </h5>
        </div>

        <div class="p-3">
            <form id="formGestDocs">
                <input type="hidden" id="id_maestro" name="id_maestro" value="<?= $maestro['id_maestro'] ?>">
                <input type="hidden" id="id_respon" name="id_respon" value="<?= $responsable['id_respon_doc'] ?>">
                <div class="form-row">
                    <div class="form-group col-md-9">
                        <label class="small" for="id_doc_fte">Fuente</label>
                        <select class="form-control form-control-sm" id="id_doc_fte" name="id_doc_fte">
                            <option value="0">--Seleccionar--</option>
                            <?php
                            foreach ($fuente as $f) {
                                $slc = $f['id_doc_fuente'] == $maestro['id_doc_fte'] ? 'selected' : '';
                                echo '<option value="' . $f['id_doc_fuente'] . '" ' . $slc . '>' . mb_strtoupper($f['nombre']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="id_modulo">Módulo</label>
                        <select class="form-control form-control-sm" id="id_modulo" name="id_modulo">
                            <option value="0">--Seleccionar--</option>
                            <?php
                            foreach ($modulos as $mod) {
                                $slc = $mod['id_modulo'] == $maestro['id_modulo'] ? 'selected' : '';
                                echo '<option value="' . $mod['id_modulo'] . '" ' . $slc . '>' . mb_strtoupper($mod['nom_modulo']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="small" for="tipo_control">Versión</label>
                        <input type="text" class="form-control form-control-sm" id="version_doc" name="version_doc" value="<?= $maestro['version_doc'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small" for="control">Control</label>
                        <select class="form-control form-control-sm" id="control" name="control">
                            <option value="0" <?= $maestro['control_doc'] == 0 ? 'selected' : '' ?>>Sin Control</option>
                            <option value="1" <?= $maestro['control_doc'] == 1 ? 'selected' : '' ?>>Con Control</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small" for="tipo_control">Tipo Control</label>
                        <select class="form-control form-control-sm" id="tipo_control" name="tipo_control">
                            <option value="0">--Seleccionar--</option>
                            <?php
                            foreach ($control as $c) {
                                $slc = $c['id_tipo'] == $responsable['tipo_control'] ? 'selected' : '';
                                echo '<option value="' . $c['id_tipo'] . '" ' . $slc . '>' . mb_strtoupper($c['descripcion']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="small" for="SeaTercer">Responsable</label>
                        <input type="text" class="form-control form-control-sm" id="SeaTercer" value="<?= $responsable['nom_tercero'] != '' ? $responsable['nom_tercero'] . ' -> ' . $responsable['nit_tercero'] : '' ?>">
                        <input type="hidden" id="id_tercero" name="id_tercero" value="<?= $responsable['id_tercero'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="small" for="cargo_resp">Cargo</label>
                        <input type="text" class="form-control form-control-sm" id="cargo_resp" name="cargo_resp" value="<?= $responsable['cargo'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small" for="fecha_doc">Fecha Documento</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_doc" name="fecha_doc" value="<?= date('Y-m-d', strtotime($maestro['fecha_doc'])) ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="fecha_ini">Fecha Inicio</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_ini" name="fecha_ini" value="<?= date('Y-m-d', strtotime($responsable['fecha_ini'])) ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="fecha_fin">Fecha Fin</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_fin" name="fecha_fin" value="<?= date('Y-m-d', strtotime($responsable['fecha_fin'])) ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-right">
        <button type="button" class="btn btn-success btn-sm" id="btnGuardarDocs">Guardar</button>
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
    </div>
</div>