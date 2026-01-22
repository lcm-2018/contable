<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

$id_cot = isset($_POST['id']) ? $_POST['id'] : exit('No permitido');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_terceros`.`id_tercero_api`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`nom_tercero`
            FROM
                `tb_terceros`
            WHERE `tb_terceros`.`estado` = 1";
    $rs = $cmd->query($sql);
    $terceros_api = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($terceros_api)) { ?>
    <script>
        $('#tableLisTerCot').DataTable({
            dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: setIdioma,
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
                        <tbody>
                            <?php
                            foreach ($terceros_api as $tc) {
                            ?>
                                <tr>
                                    <td>
                                        <div class="text-center list_ter_cot"><input type="checkbox" name="check[]" value="<?php echo $tc['id_tercero_api'] ?>"></div>
                                    </td>
                                    <td><?php echo $tc['nit_tercero'] ?></td>
                                    <td><?php
                                        echo mb_strtoupper($tc['nom_tercero']);
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
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
    echo 'No hay terceros registrados';
}
