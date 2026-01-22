<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../terceros.php';
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
            `ctt_adquisiciones`.`id_adquisicion`
            , `ctt_novedad_adicion_prorroga`.`id_nov_con`
            , `ctt_novedad_adicion_prorroga`.`fec_adcion`
            , `ctt_adquisiciones`.`id_tercero`
            , `ctt_novedad_adicion_prorroga`.`val_adicion`
            , `ctt_novedad_adicion_prorroga`.`id_cdp` AS `cdp`
            , `ctt_contratos`.`num_contrato`
        FROM
            `ctt_contratos`
            INNER JOIN `ctt_adquisiciones` 
                ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
            INNER JOIN `ctt_novedad_adicion_prorroga` 
                ON (`ctt_novedad_adicion_prorroga`.`id_adq` = `ctt_contratos`.`id_contrato_compra`)
        WHERE (`ctt_novedad_adicion_prorroga`.`id_cdp` IS NULL)";
    $rs = $cmd->query($sql);
    $solicitudes = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los id de terceros creado en la tabla ctb_doc
try {
    $sql = "SELECT
                `id_tercero_api`
                , `nom_tercero`
                , `nit_tercero`
            FROM
                `tb_terceros`";
    $res = $cmd->query($sql);
    $terceros = $res->fetchAll();
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
            <h5 style="color: white;">LISTA DE SOLICITUDES PARA CDP DE OTRO SI</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableContrtacionCdp" class="table table-striped table-bordered  table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-10">Numero ADQ</th>
                        <th class="w-10">Numero contrato</th>
                        <th class="w-10">Fecha adición</th>
                        <th class="w-10">CC / Nit</th>
                        <th class="w-40">Tercero</th>
                        <th class="w-15">Valor</th>
                        <th class="w-10">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($solicitudes as $ce) {
                        $id_tercero = $ce['id_tercero'];
                        $key = array_search($id_tercero, array_column($terceros, 'id_tercero_api'));
                        $tercero = $key !== false ? $terceros[$key]['nom_tercero'] : '---';
                        $ccnit = $key !== false ? $terceros[$key]['nit_tercero'] : '---';

                        $id_doc = $ce['id_nov_con'];
                        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="mostrarListaOtrosi(' . $id_doc . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                        } else {
                            $editar = null;
                            $detalles = null;
                        }
                    ?> <tr>
                            <!--td class="text-center"><input type="checkbox" value="" id="defaultCheck1"></td-->
                            <td class="text-left"><?php echo $ce['id_adquisicion'] ?></td>
                            <td class="text-left"><?php echo $ce['num_contrato'] ?></td>
                            <td class="text-left"><?php echo $ce['fec_adcion'] ?></td>
                            <td class="text-left"><?php echo  $ccnit  ?></td>
                            <td class="text-left"><?php echo $tercero  ?></td>
                            <td class="text-right">$ <?php echo number_format($ce['val_adicion'], 2, '.', ',') ?></td>
                            <td class="text-center"> <?php echo $editar ?></td>
                        </tr>

                    <?php
                        $tercero = null;
                        $ccnit = null;
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
