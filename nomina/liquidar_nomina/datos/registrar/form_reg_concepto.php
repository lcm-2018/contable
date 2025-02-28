<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_concp`
                , `concepto`
            FROM
                `nom_conceptosxvigencia`
            WHERE (`id_concp` <> 4 AND `id_concp` <> 5 AND `habilitado` = 1)";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR CONCEPTO DE LIQUIDACIÓN POR VIGENCIA</h5>
        </div>
        <div class="px-2">
            <form id="formRegConcepXvig">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="concepto" class="small">CONCEPTO</label>
                        <select class="form-control form-control-sm" id="concepto" name="concepto">
                            <option value="0">--Seleccione--</option>
                            <?php foreach ($conceptos as $cp) { ?>
                                <option value="<?php echo $cp['id_concp']; ?>"><?php echo mb_strtoupper($cp['concepto']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="valor" class="small">VALOR</label>
                        <input type="number" class="form-control form-control-sm" id="valor" name="valor" placeholder="Valor" required>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnRegConceptoXvig">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>