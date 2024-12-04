<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$id_cargo = isset($_POST['id_cargo']) ? $_POST['id_cargo'] : exit("Acción no permitida");
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_cod`, `denominacion`, `nivel`
            FROM
                `nom_cargo_codigo` ORDER BY `nivel`,`denominacion` ASC";
    $rs = $cmd->query($sql);
    $codigo = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id`, `tipo`
            FROM
                `nom_cargo_nombramiento`; ORDER BY `tipo` ASC";
    $rs = $cmd->query($sql);
    $nombramiento = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_cargo`, `codigo`, `descripcion_carg`, `grado`, `perfil_siho`, `id_nombramiento`
            FROM
                `nom_cargo_empleado`
            WHERE (`id_cargo` = $id_cargo)";
    $rs = $cmd->query($sql);
    $cargo = $rs->fetch(PDO::FETCH_ASSOC);
    if (empty($cargo)) {
        $cargo = [
            'id_cargo' => 0,
            'codigo' => 0,
            'descripcion_carg' => '',
            'grado' => '',
            'perfil_siho' => '',
            'id_nombramiento' => 0
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
            <h5 style="color: white;">GESTIONAR CARGO DE NÓMINA</h5>
        </div>
        <div class="px-2">
            <form id="formGestCargoNom">
                <input type="hidden" id="id_cargo" name="id_cargo" value="<?php echo $cargo['id_cargo']; ?>">
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="slcCodigo" class="small">CÓDIGO</label>
                        <select name="slcCodigo" id="slcCodigo" class="form-control form-control-sm">
                            <option value="0" <?php $cargo['codigo'] == '0' ? 'selected' : ''; ?>>--Seleccione--</option>
                            <?php foreach ($codigo as $cd) {
                                $slc = $cargo['codigo'] == $cd['id_cod'] ? 'selected' : '';
                                echo '<option value="' . $cd['id_cod'] . '" ' . $slc . '>' . mb_strtoupper($cd['nivel'] . ' -> ' . $cd['denominacion']) . '</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txtNomCargo" class="small">NOMBRE</label>
                        <input type="text" id="txtNomCargo" name="txtNomCargo" class="form-control form-control-sm" value="<?php echo $cargo['descripcion_carg']; ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numGrado" class="small">GRADO</label>
                        <input type="number" id="numGrado" name="numGrado" class="form-control form-control-sm" value="<?php echo $cargo['grado']; ?>">
                    </div>
                </div>
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="slcNombramiento" class="small">NOMBRAMIENTO</label>
                        <select name="slcNombramiento" id="slcNombramiento" class="form-control form-control-sm">
                            <option value="0" <?php $cargo['id_nombramiento'] == '0' ? 'selected' : ''; ?>>--Seleccione--</option>
                            <?php foreach ($nombramiento as $nom) {
                                $slc = $cargo['id_nombramiento'] == $nom['id'] ? 'selected' : '';
                                echo '<option value="' . $nom['id'] . '" ' . $slc . '>' . mb_strtoupper($nom['tipo']) . '</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="txtPerfilSiho" class="small">PERFÍL SIHO</label>
                        <input type="text" id="txtPerfilSiho" name="txtPerfilSiho" class="form-control form-control-sm" value="<?php echo $cargo['perfil_siho']; ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnGuardaCargo">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>