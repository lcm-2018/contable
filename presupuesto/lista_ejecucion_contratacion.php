<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
// Consulta tipo de presupuesto
include '../conexion.php';
include '../permisos.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_adquisiciones`.`id_adquisicion` 
                , `ctt_adquisiciones`.`fecha_adquisicion`
                , `ctt_adquisiciones`.`objeto`
                , `ctt_adquisiciones`.`val_contrato`
                , `ctt_adquisiciones`.`estado`
                , `tb_area_c`.`area`
            FROM
                `ctt_adquisiciones`
            INNER JOIN `tb_area_c` ON (`ctt_adquisiciones`.`id_area` = `tb_area_c`.`id_area`)   
            WHERE (`estado` = 6 AND (`id_cdp` = 1 OR `id_cdp` IS NULL) AND `vigencia` = $vigencia)";
    $rs = $cmd->query($sql);
    $solicitudes = $rs->fetchAll();

    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableContrtacionCdp').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableContrtacionCdp').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE CONTRATOS PARA CDP</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableContrtacionCdp" class="table table-striped table-bordered  table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th></th>
                        <th class="w-10">Numero ADQ</th>
                        <th class="w-15">Area</th>
                        <th class="w-50">Objeto</th>
                        <th class="w-15">Valor solicitado</th>
                        <th class="w-10">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($solicitudes as $ce) {
                        $id_doc = $ce['id_adquisicion'];
                        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="mostrarListaCdp(' . $id_doc . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                            $detalles = '<a value="' . $id_doc . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
                            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                                ...
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Cargar2 presupuesto</a>
                                <a value="' . $id_doc . '" class="dropdown-item sombra modifica" href="#">Modificaciones</a>
                                <a value="' . $id_doc . '" class="dropdown-item sombra ejecuta" href="#">Ejecución</a>
                                </div>';
                        } else {
                            $editar = null;
                            $detalles = null;
                        }
                    ?> <tr>
                            <td class="text-center"><input type="checkbox" value="" id="defaultCheck1"></td>
                            <td class="text-left"><?php echo $ce['id_adquisicion'] ?></td>
                            <td class="text-left"><?php echo $ce['area'] ?></td>
                            <td class="text-left"><?php echo $ce['objeto'] ?></td>
                            <td class="text-right">$ <?php echo number_format($ce['val_contrato'], 2, '.', ',') ?></td>
                            <td class="text-center"> <?php echo $editar ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Procesar lote</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>
<?php
