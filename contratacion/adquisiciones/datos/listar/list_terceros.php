<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id_cot = isset($_POST['id']) ? $_POST['id'] : exit('No permitido');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`id_tercero`, `seg_terceros`.`no_doc`, `seg_terceros`.`id_tercero_api`
            FROM
                `tb_rel_tercero`
                INNER JOIN `seg_terceros` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
            WHERE `seg_terceros`.`estado` = 1";
    $rs = $cmd->query($sql);
    $terceros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($terceros)) {
    $id_t = [];
    foreach ($terceros as $l) {
        if ($l['id_tercero_api'] != '') {
            $id_t[] = $l['id_tercero_api'];
        }
    }
    $payload = json_encode($id_t);
    //API URL
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $terceros_api = json_decode($result, true);
    if ($terceros_api != '0') { ?>
        <script>
            $('#tableLisTerCot').DataTable({
                dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
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
                    },
                },
                "order": [
                    [2, "desc"]
                ]
            });
            $('#tableLisTerCot').wrap('<div class="overflow" />');
        </script>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">SELECIONAR TERCEROS A ENVIAR COTIZACIÓN</h5>
                </div>
                <form id="formListTerc">
                    <input type="hidden" name="id_cotizacion" value="<?php echo $id_cot ?>">
                    <div class="px-4 pt-4">
                        <table id="tableLisTerCot" class="table table-striped table-bordered table-sm nowrap shadow text-left" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Elegir</th>
                                    <th>Identificación</th>
                                    <th>Nombre / Razón Social</th>
                                </tr>
                            </thead>
                            <?php
                            foreach ($terceros_api as $tc) {
                            ?>
                                <tr>
                                    <td>
                                        <div class="text-center list_ter_cot"><input type="checkbox" name="check[]" value="<?php echo $tc['id_tercero'] ?>"></div>
                                    </td>
                                    <td><?php echo $tc['cc_nit'] ?></td>
                                    <td><?php
                                        $razsoc = $tc['razon_social'] != '' ? ' - ' . $tc['razon_social'] : '';
                                        echo mb_strtoupper($tc['apellido1'] . ' ' . $tc['apellido2'] . ' ' . $tc['nombre1'] . ' ' . $tc['nombre2'] . $razsoc);
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-row px-4 pt-2">
                        <div class="text-center pb-3">
                            <button class="btn btn-primary btn-sm" id="btnEnviarCotizacion">Enviar Cotización</button>
                            <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
<?php
    } else {
        echo 'Error al intentar recuperar terceros';
    }
} else {
    echo "No se ha registrado ningun tercero";
}
