<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$key = array_search('53', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$id_adq = $_POST['id_adq'];
$tipo_servicio = $_POST['tipo_servicio'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_b_s`, `tipo_compra`,`tb_tipo_contratacion`.`id_tipo`, `tipo_contrato`, `tipo_bn_sv`, `bien_servicio`
            FROM
                `tb_tipo_contratacion`
            INNER JOIN `tb_tipo_compra` 
                ON (`tb_tipo_contratacion`.`id_tipo_compra` = `tb_tipo_compra`.`id_tipo`)
            INNER JOIN `tb_tipo_bien_servicio` 
                ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
            INNER JOIN `ctt_bien_servicio` 
                ON (`ctt_bien_servicio`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE `id_tipo_b_s` = $tipo_servicio
            ORDER BY `tipo_compra`,`tipo_contrato`, `tipo_bn_sv`, `bien_servicio`";
    $rs = $cmd->query($sql);
    $bnsv = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<script>
    //dataTable Adquisicion de bienes o servicios
    $('#tableAdqBnSv').DataTable({
        /*dom: "<'row'<'reg-orden col-md-6'B><'col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [{
            text: 'CREAR ORDEN',
            action: function() {
                let b = 1;
                $('input[type=checkbox]:checked').each(function() {
                    let idcheck = $(this).val();
                    let idCant = 'bnsv_' + idcheck;
                    let idVAl = 'val_bnsv_' + idcheck;
                    if ($('#' + idCant).val() === '' || parseInt($('#' + idCant).val()) <= 0) {
                        showError(idCant);
                        bordeError(idCant);
                        b = 0
                        return false;;
                    }
                    if ($('#' + idVAl).val() === '' || parseInt($('#' + idVAl).val()) <= 0) {
                        showError(idVAl);
                        bordeError(idVAl);
                        b = 0
                        return false;;
                    }

                });
                if (b === 1) {
                    let datos = $('#formDetallesAdq').serialize();
                    $.ajax({
                        type: 'POST',
                        url: 'registrar/new_adquisicion_bn_sv.php',
                        data: datos,
                        success: function(r) {
                            if (r === 0) {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html("No se agregó ningún bien o servicio");
                            } else if (r > 0) {
                                let id = 'tableAdquisiciones';
                                reloadtable(id);
                                $('#divModalForms').modal('hide');
                                $('#divModalDone').modal('show');
                                $('#divEstadoBnSv').html('<div class="p-3 mb-2 bg-success text-white">ORDEN AGREGADA CORRECTAMENTE</div>');
                                $('#divMsgDone').html('Se agregaron' + r + 'bien(es) o servicio(s) a la compra actual');
                            } else {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html(r);
                            }
                        }
                    });
                    return false;
                }
            }
        }],*/
        //language: setIdioma,
        paginate: false,
    });
    $('#tableAdqBnSv').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIÓN DE SERVICIOS DE ORDEN DE COMPRA</h5>
        </div>
        <form id="formDetallesAdq">
            <input type="hidden" name="idAdq" value="<?php echo $id_adq ?>">
            <div class="px-3 py-2">
                <table id="tableAdqBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Pago</th>
                            <th>Bien o Servicio</th>
                            <th>Cantidad</th>
                            <th>Valor Unitario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($bnsv as $bs) {
                        ?>
                            <tr>
                                <td>
                                    <div class="text-center listado">
                                        <input type="checkbox" name="check[]" value="<?php echo $bs['id_b_s'] ?>">
                                    </div>
                                </td>
                                <?php if (true) { ?>
                                    <td>
                                        <select class="form-control form-control-sm altura py-0" id="tipo_<?php echo $bs['id_b_s'] ?>">
                                            <option value="H">Horas</option>
                                            <option value="M">Mensual</option>
                                        </select>
                                    </td>
                                <?php } ?>
                                <td class="text-left"><i><?php echo $bs['bien_servicio'] ?></i></td>
                                <td><input type="number" name="bnsv_<?php echo $bs['id_b_s'] ?>" id="bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura cantidad"></td>
                                <td><input type="number" name="val_bnsv_<?php echo $bs['id_b_s'] ?>" id="val_bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura" value="0"></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="text-right pb-3 px-3">
                <button class="btn btn-sm btn-success">Guardar</button>
                <button class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </form>
    </div>
</div>