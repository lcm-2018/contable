<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
if ($id_rol != 1) {
    exit('Usuario no autorizado');
}
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acceso denegado');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_usuario`, `nombre1`, `nombre2`, `apellido1`, `apellido2`, `login`
            FROM
                `seg_usuarios_sistema`
            WHERE `id_usuario` = $id";
    $rs = $cmd->query($sql);
    $objs = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                IFNULL(`t1`.`id_per_mod`,0) AS `id_per_mod`
                ,`seg_modulos`.`id_modulo`
                , `seg_modulos`.`nom_modulo`
                , CASE WHEN `t1`.`id_per_mod` IS NULL THEN 0 ELSE 1 END AS `estado`
            FROM
                `seg_modulos`
                LEFT JOIN (SELECT `id_modulo`,`id_per_mod` FROM `seg_permisos_modulos` WHERE `id_usuario` = $id) AS `t1`
                ON (`t1`.`id_modulo` = `seg_modulos`.`id_modulo`)
            ORDER BY `seg_modulos`.`nom_modulo` ASC";
    $rs = $cmd->query($sql);
    $modulos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow mb-3">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;" class="mb-0"><i class="fas fa-user-lock fa-lg mr-3" style="color:#2FDA49"></i>ACTUALIZAR PERMISOS DE <p class="text-secondary mb-0"><?php echo mb_strtoupper($objs['nombre1'] . ' ' . $objs['apellido1']) ?></p>
            </h5>
        </div>

        <div class="p-3">
            <input type="hidden" id="id_usuario" value="<?php echo $id ?>">
            <table id="tableModulos" class="table-striped table-bordered table-sm nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Módulo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($modulos as $md) {
                        $id_pm = $md['id_per_mod'];
                        if ($md['estado'] == '1') {
                            $estado = '<a value="' . $id_pm . '|1' . '" class="btn btn-sm btn-circle estado" title="Activo"><span class="fas fa-toggle-on fa-2x" style="color:#37E146;"></span></a>';
                            $set = '<a value="' . $md['id_modulo'] . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb listPermisos" title="Configurar permisos"><span class="fas fa-cogs fa-lg"></span></a>';
                        } else {
                            $estado = '<a value="' . $md['id_modulo'] . '|0' . '" class="btn btn-sm btn-circle estado" title="Inactivo"> <span class="fas fa-toggle-off fa-2x" style="color:gray;"></span></a>';
                            $set = NULL;
                        }
                        echo '<tr>';
                        echo '<td class="text-center">' . $md['id_modulo'] . '</td>';
                        echo '<td class="text-left">' . mb_strtoupper($md['nom_modulo']) . '</td>';
                        echo '<td class="text-center">' . $estado . '</td>';
                        echo '<td class="text-center">' . $set . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
    </div>
</div>