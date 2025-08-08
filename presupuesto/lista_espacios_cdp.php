<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Consulta funcion fechaCierre del modulo 54
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 54, $cmd);
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
try {

    $sql = "SELECT MIN(id_manu) AS minimo, MAX(id_manu) AS mayor FROM pto_documento WHERE fecha > '$fecha_cierre' AND tipo_doc= 'CDP'";
    $rs = $cmd->query($sql);
    $rangos = $rs->fetch();
    $id_manu = $rangos['minimo'];
    $id_manu_max = $rangos['mayor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableListaCdp').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        autoWidth: false,
        pageLength: 50,
        "order": [
            [0, "asc"]
        ]
    });
    $('#tableListaCdp').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE CDP'S REGISTRADOS EN EL SISTEMA</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableListaCdp" class="table table-striped table-bordered  table-sm table-hover " style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 15%">Numero</th>
                        <th style="width: 15%">Fecha</th>
                        <th style="width: 50%">Objeto</th>
                        <th style="width: 10%">Estado</th>
                        <th style="width: 10%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $id = 1;
                    $estado = '';
                    do {
                        $sql = "SELECT * FROM pto_documento WHERE id_manu ='$id_manu' ORDER BY id_manu ASC";
                        $res = $cmd->query($sql);
                        $row = $res->fetch(PDO::FETCH_ASSOC);
                        $id = $row['id_pto_doc'];
                        if ($row) {
                            $estado = "";
                            $fecha = date('Y-m-d', strtotime($row['fecha']));
                            $buscar = '<a onclick=redirecionarListacdp("' . $id . '","' . $id_manu . '") class="btn btn-sm btn-outline-info btn-circle"><i class="fas fa-eye"></i></a>';
                        } else {
                            $estado = "Libre";
                            $fecha = "";
                            $buscar = null;
                        }
                        echo '<tr class="row-success">';
                        echo '<td>' . $id_manu . '</td>';
                        echo '<td>' . $fecha . '</td>';
                        echo '<td class="text-left">' . $row['objeto'] . '</td>';
                        echo '<td>' . $estado . '</td>';
                        echo '<td>' . $buscar . '</td>';
                        echo '</tr>';
                        $id_manu++;
                        $id++;
                    } while ($id_manu <= $id_manu_max);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal"> Cerrar</a>
    </div>
</div>
<?php
