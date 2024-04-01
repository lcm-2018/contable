<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
$id_consulta = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_consulta`,`nombre`, `parametros`, `consulta`, `fec_reg`
            FROM
                `seg_consultas_sql`
            WHERE `id_consulta` = $id_consulta";
    $rs = $cmd->query($sql);
    $consultas = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$parametros = json_decode($consultas['parametros'], true);
$head = '';
foreach ($consultas as $key => $value) {
    $head .= '<th>' . $key . '</th>';
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
    <div class="shadow mb-3">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;"><i class="fas fa-user-lock fa-lg" style="color:#2FDA49"></i>CONSULTAS SQL</h5>
        </div>

        <div class="p-3">
            <?php
            echo ' <form id="formParams"><div class="form-row"><input type="hidden" name="id" value="' . $id_consulta . '">';
            foreach ($parametros as $key => $value) {
                echo '<div class="form-group col-md-3">
                    <label class="small">' . $key . '</label>
                    <input type="' . $value . '" class="form-control form-control-sm" name="p[]">
                </div>';
            }
            echo '</div></form>';
            ?>
            <div class="text-left mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnEjecutarConsulta">Ejecutar</button>
            </div>
            <div id="resultado">

            </div>
        </div>
    </div>
    <div class="text-right">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
    </div>
</div>