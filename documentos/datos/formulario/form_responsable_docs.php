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
$data = isset($_POST['data']) ? $_POST['data'] : exit('Acceso no autorizado');
$data = explode('|', base64_decode($_POST['data']));
$id_maestro = $data[0];
$id_detalle = $data[1];
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
    if ($id_detalle > 0) {
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
                WHERE (`fin_respon_doc`.`id_respon_doc` = $id_detalle)";
        $rs = $cmd->query($sql);
        $responsable = $rs->fetch(PDO::FETCH_ASSOC);
    } else {
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
    $sql = "SELECT
                `fin_respon_doc`.`id_respon_doc`
                , `fin_respon_doc`.`id_tercero`
                , `fin_respon_doc`.`fecha_ini`
                , `fin_respon_doc`.`fecha_fin`
                , `fin_respon_doc`.`cargo`
                , `fin_respon_doc`.`estado`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `fin_tipo_control`.`descripcion` AS `tipo_control`
            FROM
                `fin_respon_doc`
                INNER JOIN `tb_terceros` 
                    ON (`fin_respon_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN `fin_tipo_control` 
                    ON (`fin_respon_doc`.`tipo_control` = `fin_tipo_control`.`id_tipo`)
            WHERE (`fin_respon_doc`.`id_maestro_doc` = $id_maestro)";
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
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
            <h5 style="color: white;" class="mb-0"><i class="fas fa-cog fa-lg mr-3" style="color:#2FDA49"></i>GESTIÓN DETALLE DOCUMENTOS</p>
            </h5>
        </div>

        <div class="p-3">
            <form id="formGestDetDocs">
                <input type="hidden" id="id_maestro" name="id_maestro" value="<?= $id_maestro ?>">
                <input type="hidden" id="id_respon" name="id_respon" value="<?= $responsable['id_respon_doc'] ?>">
                <input type="hidden" id="control" name="control" value="<?= $maestro['control_doc'] ?>">
                <div class="form-row">
                    <div class="form-group col-md-3">
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
                    <div class="form-group col-md-6">
                        <label class="small" for="SeaTercer">Responsable</label>
                        <input type="text" class="form-control form-control-sm" id="SeaTercer" value="<?= $responsable['nom_tercero'] != '' ? $responsable['nom_tercero'] . ' -> ' . $responsable['nit_tercero'] : '' ?>">
                        <input type="hidden" id="id_tercero" name="id_tercero" value="<?= $responsable['id_tercero'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="cargo_resp">Cargo</label>
                        <input type="text" class="form-control form-control-sm" id="cargo_resp" name="cargo_resp" value="<?= $responsable['cargo'] ?>">
                    </div>
                </div>
                <div class="form-row">
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
            <table class="table table-sm table-bordered table-striped table-hover">
                <thead>
                    <tr class="text-center">
                        <th>ID</th>
                        <th>Responsable</th>
                        <th>Cargo</th>
                        <th>Control</th>
                        <th>Inicia</th>
                        <th>Termina</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="modificarDetDocs">
                    <?php
                    foreach ($datos as $dt) {
                        $id = base64_encode($id_maestro . '|' . $dt['id_respon_doc']);
                        $editar = '<a text="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                        $borrar = '<a text="' . base64_encode($dt['id_respon_doc']) . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                        $estado = $dt['estado'];
                        $st = base64_encode($dt['id_respon_doc'] . '|' . $estado);
                        if ($estado == 1) {
                            $title = 'Activo';
                            $icono = 'on';
                            $color = '#37E146';
                        } else {
                            $title = 'Inactivo';
                            $icono = 'off';
                            $color = 'gray';
                        }
                        $boton = '<a text="' . $st . '" class="btn btn-sm btn-circle estado" title="' . $title . '"><span class="fas fa-toggle-' . $icono . ' fa-2x" style="color:' . $color . ';"></span></a>';
                        echo '<tr class="text-left">
                                <td>' . $dt['id_respon_doc'] . '</td>
                                <td>' . mb_strtoupper($dt['nom_tercero']) . '</td>
                                <td>' . mb_strtoupper($dt['cargo']) . '</td>
                                <td>' . mb_strtoupper($dt['tipo_control']) . '</td>
                                <td>' . date('Y-m-d', strtotime($dt['fecha_ini'])) . '</td>
                                <td>' . date('Y-m-d', strtotime($dt['fecha_fin'])) . '</td>
                                <td class="text-center">' . $boton . '</td>
                                <td>' . $editar . $borrar . '</td>
                            </tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right">
        <button type="button" class="btn btn-success btn-sm" id="btnGuardarDetDocs">Guardar</button>
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
    </div>
</div>