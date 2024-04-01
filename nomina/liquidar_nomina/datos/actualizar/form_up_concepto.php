<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id  = isset($_POST['id']) ? $_POST['id'] : exit('Acceso denegado');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_concp`
                , `concepto`
            FROM
                `nom_conceptosxvigencia`
            WHERE (`id_concp` <> 4 AND `id_concp` <> 5)";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_valxvig`,`valor`,`id_concepto`
            FROM `nom_valxvigencia`
            WHERE (`id_valxvig` = '$id') LIMIT 1";
    $rs = $cmd->query($sql);
    $ccpto = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR CONCEPTO DE LIQUIDACIÓN POR VIGENCIA</h5>
        </div>
        <div class="px-2">
            <form id="formUpConcepXvig">
                <input type="hidden" name="id" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="concepto" class="small">CONCEPTO</label>
                        <select class="form-control form-control-sm" id="concepto" name="concepto" disabled>
                            <?php
                            foreach ($conceptos as $cp) {
                                if ($cp['id_concp'] == $ccpto['id_concepto']) {
                                    echo "<option value='$cp[id_concp]'>" . mb_strtoupper($cp['concepto']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="valor" class="small">VALOR</label>
                        <input type="number" class="form-control form-control-sm" id="valor" name="valor" value="<?php echo $ccpto['valor'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnUpConceptoXvig">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>