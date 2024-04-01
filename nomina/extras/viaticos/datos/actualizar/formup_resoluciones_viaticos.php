<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
$id_resolucion = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_resol_viat`, `fec_inicia`, `fec_final`, `tot_dias`, `dias_pernocta`, `objetivo`, `destino`
            FROM
                `nom_resolucion_viaticos`
            WHERE `id_resol_viat` = '$id_resolucion'";
    $rs = $cmd->query($sql);
    $resolucion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR RESOLUCIÓN DE VIÁTICOS</h5>
        </div>
        <div class="px-4 pt-3">
            <form id="formUpResolucionViaticos">
                <input type="hidden" name="id_resolucion" id="id_resolucion" value="<?php echo $id_resolucion ?>">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fec_inicia" class="small">Fecha Inicia</label>
                        <input type="date" name="fec_inicia" id="fec_inicia" class="form-control form-control-sm" value="<?php echo $resolucion['fec_inicia'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fec_final" class="small">Fecha Termina</label>
                        <input type="date" name="fec_final" id="fec_final" class="form-control form-control-sm" value="<?php echo $resolucion['fec_final'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="tot_dias" class="small">Total días</label>
                        <input type="number" name="tot_dias" id="tot_dias" class="form-control form-control-sm" value="<?php echo $resolucion['tot_dias'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="dias_pernocta" class="small">Días pernoncta</label>
                        <input type="number" name="dias_pernocta" id="dias_pernocta" class="form-control form-control-sm" value="<?php echo $resolucion['dias_pernocta'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="objetivo" class="small">Objetivo</label>
                        <input type="text" name="objetivo" id="objetivo" class="form-control form-control-sm" value="<?php echo $resolucion['objetivo'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="destino" class="small">Destino</label>
                        <input type="text" name="destino" id="destino" class="form-control form-control-sm" value="<?php echo $resolucion['destino'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button class="btn btn-primary btn-sm" id="btnActualizaResolucion">
            Actualizar
        </button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
    </div>
</div>