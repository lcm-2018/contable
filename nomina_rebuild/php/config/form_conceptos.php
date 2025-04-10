<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id = $_POST['id'];
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

    $sql = "SELECT `id_concepto`,`valor` FROM `nom_valxvigencia` WHERE `id_valxvig` = $id";
    $rs = $cmd->query($sql);
    $concepto = $rs->fetch(PDO::FETCH_ASSOC);
    if (empty($concepto)) {
        $concepto = [
            'id_concepto' => 0,
            'valor' => 0
        ];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CONCEPTO DE LIQUIDACIÓN POR VIGENCIA</h5>
        </div>
        <div class="px-2">
            <form id="formConcepXvig">
                <input type="hidden" id="id_concepto" name="id_concepto" value="<?= $id; ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="concepto" class="small">CONCEPTO</label>
                        <select class="form-control form-control-sm" id="concepto" name="concepto">
                            <option value="0">--Seleccione--</option>
                            <?php
                            foreach ($conceptos as $cp) {
                                $slc = ($concepto['id_concepto'] == $cp['id_concp']) ? 'selected' : '';
                                echo '<option value="' . $cp['id_concp'] . '" ' . $slc . '>' . mb_strtoupper($cp['concepto']) . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="valor" class="small">VALOR</label>
                        <input type="number" class="form-control form-control-sm text-right" id="valor" name="valor" placeholder="Valor" value="<?= $concepto['valor']; ?>" required>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-right pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnGuardaConcxVig">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>