<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
$id_consulta = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$parametros = $_POST['p'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_consulta`,`nombre`, `parametros`, `consulta`, `fec_reg`
            FROM
                `seg_consultas_sql`
            WHERE `id_consulta` = $id_consulta";
    $rs = $cmd->query($sql);
    $consulta = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$sql = $consulta['consulta'];
$x = 1;
foreach ($parametros as $p) {
    $sql = str_replace('p' . $x, $p, $sql);
    $x++;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$head = '';
if (!empty($datos)) {
    foreach ($datos[0] as $key => $value) {
        $head .= '<th>' . $key . '</th>';
    }
} else {
    exit('No hay datos para mostrar');
}

?>
<script>
    var setIdioma = {
        "decimal": "",
        "emptyTable": "No hay información",
        "info": "Mostrando _START_ - _END_ registros de _TOTAL_ ",
        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
        "infoFiltered": "(Filtrado de _MAX_ entradas en total )",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "Ver _MENU_ Filas",
        "loadingRecords": "Cargando...",
        "processing": "Procesando...",
        "search": '<i class="fas fa-search fa-flip-horizontal" style="font-size:1.5rem; color:#2ECC71;"></i>',
        "zeroRecords": "No se encontraron registros",
        "paginate": {
            "first": "&#10096&#10096",
            "last": "&#10097&#10097",
            "next": "&#10097",
            "previous": "&#10096"
        }
    };
    var setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    $('#tableConsultaExec').DataTable({
        language: setIdioma,
        "pageLength": 10,
        "order": [
            [0, "asc"]
        ]
    });
    $('#tableConsultaExec').wrap('<div class="overflow" />');
    $("#tableConsultaExec_length").addClass("text-left");
</script>
<div class="px-0">
    <table id="tableConsultaExec" class="table-striped table-bordered table-sm nowrap" style="width:100%">
        <thead>
            <tr>
                <?php echo $head; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($datos as $d) {
                echo '<tr>';
                foreach ($d as $key => $value) {
                    echo '<td>' . $value . '</td>';
                }
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>