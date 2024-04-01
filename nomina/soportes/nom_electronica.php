<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include_once '../../conexion.php';
include_once '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_soporte, nom_meses.id_mes, nom_meses.codigo, nom_meses.nom_mes, nom_empleado.id_empleado, nom_empleado.no_documento, nom_empleado.apellido1, nom_empleado.apellido2, nom_empleado.nombre2, nom_empleado.nombre1, nom_soporte_ne.shash, nom_soporte_ne.referencia
            FROM
                nom_meses, 
                nom_soporte_ne
                INNER JOIN nom_empleado 
                    ON (nom_soporte_ne.id_empleado = nom_empleado.id_empleado)
            WHERE  nom_meses.codigo = nom_soporte_ne.mes AND nom_soporte_ne.anio = '$vigencia'
            ORDER BY nom_meses.codigo ASC";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
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
                                    <i class="fas fa-ticket-alt fa-lg" style="color:#FF1B1B"></i>
                                    SOPORTES DE PAGO DE NÓMINA ELECTRÓNICA.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="table-responsive">
                                <div id="accordion">
                                    <?php
                                    for ($i = 1; $i <= 12; $i++) {
                                        $key = array_search($i, array_column($obj, 'id_mes'));
                                        if (false !== $key) {
                                    ?>
                                            <!-- parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="heading<?php echo $obj[$key]['codigo'] ?>">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapse<?php echo $obj[$key]['codigo'] ?>" aria-expanded="true" aria-controls="collapse<?php echo $obj[$key]['codigo'] ?>">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-clipboard-list fa-lg" style="color: #3498DB;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php echo $obj[$key]['nom_mes'] ?>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapse<?php echo $obj[$key]['codigo'] ?>" class="collapse" aria-labelledby="heading<?php echo $obj[$key]['codigo'] ?>">
                                                    <div class="card-body">
                                                        <table class="table-bordered table-sm  order-column nowrap dataTableMes" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-center" style="background-color: rgb(22, 160, 133);">Nombre Completo</th>
                                                                    <th class="text-center">No. Documento</th>
                                                                    <th class="text-center">Soporte</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                foreach ($obj as $o) {
                                                                    if ($o['id_mes'] == $i) {
                                                                ?>
                                                                        <tr>
                                                                            <td><?php echo mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2'] . ' ' . $o['nombre1'] . ' ' . $o['nombre2']) ?></td>
                                                                            <td><?php echo $o['no_documento'] ?></td>
                                                                            <td>
                                                                                <div class="text-center">
                                                                                    <?php
                                                                                    if (PermisosUsuario($permisos, 5112, 6) || $id_rol == 1) {
                                                                                    ?>
                                                                                        <a value="<?php echo $o['id_soporte'] ?>" class="btn btn-outline-danger btn-sm btn-circle shadow-gb soporteNE" title="Reporte"><span class="fas fa-file-pdf fa-lg"></span></a>
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                <?php

                                                                    }
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>