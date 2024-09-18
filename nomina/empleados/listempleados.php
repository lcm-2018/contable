<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *  FROM nom_empleado";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `nom_salarios_basico`.`id_empleado`
                , `nom_salarios_basico`.`id_salario`
                , `nom_salarios_basico`.`vigencia`
                , `nom_salarios_basico`.`salario_basico`
            FROM (SELECT
                MAX(`id_salario`) AS `id_salario`, `id_empleado`
                FROM
                    `nom_salarios_basico`
                WHERE `vigencia` <= '$vigencia'
                GROUP BY `id_empleado`) AS `t`
            INNER JOIN `nom_salarios_basico`
                ON (`nom_salarios_basico`.`id_salario` = `t`.`id_salario`)";
    $rs = $cmd->query($sql);
    $salarios = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTA DE EMPLEADOS
                                </div>
                                <?php
                                if ((PermisosUsuario($permisos, 5101, 2) || $id_rol == 1)) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                } ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <?php
                                if ((PermisosUsuario($permisos, 5101, 1) || $id_rol == 1)) {
                                ?>
                                    <table id="tableListEmpleados" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No. Doc.</th>
                                                <th>Apellidos</th>
                                                <th>Nombres</th>
                                                <th>Correo</th>
                                                <th>Teléfono</th>
                                                <th>Salario</th>
                                                <th>Estado</th>
                                                <th>Acción</th>

                                            </tr>
                                        </thead>
                                        <tbody id="modificarEmpleados">
                                            <?php
                                            $sal_bas = 0;
                                            foreach ($obj as $o) {
                                                $ide = $o['no_documento'];
                                            ?>
                                                <tr id="filaempl">
                                                    <td><?php echo $o['no_documento'] ?></td>
                                                    <td><?php echo mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2']) ?></td>
                                                    <td><?php echo mb_strtoupper($o['nombre1'] . ' ' . $o['nombre2']) ?></td>
                                                    <td><?php echo $o['correo'] ?></td>
                                                    <td><?php echo $o['telefono'] ?></td>
                                                    <td>
                                                        <?php
                                                        $emplkey = array_search($ide, array_column($salarios, 'id_empleado'));
                                                        if ($emplkey !== "") {
                                                            foreach ($salarios as $sa) {
                                                                if ($o['id_empleado'] === $sa['id_empleado']) {
                                                                    echo pesos($sa['salario_basico']);
                                                                    $sal_bas = $sa['salario_basico'];
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-center" id="tdEstado">
                                                        <?php
                                                        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
                                                            if ($o['estado'] === '1') {
                                                        ?>
                                                                <button class="btn-estado" value="<?php echo $o['id_empleado'] ?>">
                                                                    <div id="divIconoshow<?php echo $o['id_empleado'] ?>">
                                                                        <i class="fas fa-toggle-on fa-lg" style="color:#37E146;"></i>
                                                                    </div>
                                                                    <div id="divIcono<?php echo $o['id_empleado'] ?>">

                                                                    </div>
                                                                </button>
                                                            <?php } else {
                                                            ?>
                                                                <button class="btn-estado" value="<?php echo $o['id_empleado'] ?>">
                                                                    <div id="divIconoshow<?php echo $o['id_empleado'] ?>">
                                                                        <i class="fas fa-toggle-off fa-lg" style="color:gray;"></i>
                                                                    </div>
                                                                    <div id="divIcono<?php echo $o['id_empleado'] ?>">

                                                                    </div>
                                                                </button>
                                                        <?php
                                                            }
                                                        } else {
                                                            $es = $o['estado'] == '1' ? 'ACTIVO' : 'INACTIVO';
                                                            echo $es;
                                                        }
                                                        ?>

                                                    </td>
                                                    <td>
                                                        <div class="text-center">
                                                            <div>
                                                                <?php
                                                                if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
                                                                ?>
                                                                    <button value="<?php echo $o['id_empleado'] ?>" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar">
                                                                        <span class="fas fa-pencil-alt fa-lg"></span>
                                                                    </button>
                                                                    <?php
                                                                }
                                                                if (intval($sal_bas) > 0) {
                                                                    if ((PermisosUsuario($permisos, 5101, 2) || $id_rol == 1)) {
                                                                    ?>
                                                                        <button value="<?php echo $o['id_empleado'] ?>" class="btn btn-outline-success btn-sm btn-circle horas" title="+ Horas extras">
                                                                            <span class="fas fa-clock fa-lg"></span>
                                                                        </button>
                                                                        <?php if (false) { ?>
                                                                            <button value="<?php echo $o['id_empleado'] ?>" class="btn btn-outline-info btn-sm btn-circle viaticos" title="+ Viáticos">
                                                                                <span class="fas fa-suitcase fa-lg"></span>
                                                                            </button>
                                                                    <?php
                                                                        }
                                                                    }
                                                                }
                                                                if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
                                                                    ?>
                                                                    <button class="btn btn-outline-danger btn-sm btn-circle eliminar" value="<?php echo $o['id_empleado'] ?>" title="Eliminar">
                                                                        <span class="fas fa-trash-alt fa-lg"></span>
                                                                    </button>
                                                                <?php }
                                                                if (intval($sal_bas) > 0 && (PermisosUsuario($permisos, 5101, 1) || $id_rol == 1)) {
                                                                ?>
                                                                    <button value="<?php echo $o['id_empleado'] ?>" class="btn btn-outline-warning btn-sm btn-circle detalles" title="Detalles">
                                                                        <span class="far fa-eye fa-lg"></span>
                                                                    </button>
                                                                <?php
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>No. Doc.</th>
                                                <th>Nombres</th>
                                                <th>Apellidos</th>
                                                <th>Correo</th>
                                                <th>Teléfono</th>
                                                <th>Salario</th>
                                                <th>Estado</th>
                                                <th>Opciones</th>

                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>