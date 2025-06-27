<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$id_pto = $_POST['id_pto'];
$id_mov = $_POST['id_mov'];
$id = $_POST['id'];
//Obtener fecha de cierre del módulo
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
// Obtener la fecha de sesión del usuario
$fecha = fechaSesion($_SESSION['vigencia'], $_SESSION['id_user'], $cmd);
try {
    // consulta select tipo de recursos
    $sql = "SELECT `id_acto`, `nombre` FROM `pto_actos_admin` ORDER BY `nombre`";
    $rs = $cmd->query($sql);
    $tipoActo = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // consulta select tipo de recursos
    $sql = "SELECT `id_tmvto`,`codigo`,`nombre` FROM `pto_tipo_mvto` WHERE `id_tmvto` = $id_mov";
    $rs = $cmd->query($sql);
    $movimiento = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // consulta select tipo de recursos
    $sql = "SELECT `id_tipo_acto`,`numero_acto`,DATE_FORMAT(`fecha`, '%Y-%m-%d') AS `fecha`,`objeto` FROM `pto_mod` WHERE `id_pto_mod` = $id";
    $rs = $cmd->query($sql);
    $registro = $rs->fetch();
    if (empty($registro)) {
        $registro = [
            'id_tipo_acto' => 0,
            'numero_acto' => '',
            'fecha' => date("Y-m-d"),
            'objeto' => ''
        ];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$cmd = null;
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CREAR NUEVA MODIFICACIÓN: <?php echo $movimiento['nombre']; ?></h5>
        </div>
        <form id="formAddModificaPresupuesto">
            <input type="hidden" name="id_pto" id="id_pto" value="<?php echo $id_pto; ?>">
            <input type="hidden" name="id_mov" id="id_mov" value="<?php echo $id_mov; ?>">
            <input type="hidden" name="id_registro" id="id_registro" value="<?php echo $id; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="fecha" class="small">FECHA MODIFICACION</label>
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo $registro['fecha']; ?>" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>">
                </div>
                <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-4">
                    <label for="tipo_acto" class="small">TIPO ACTO</label>
                    <select class="custom-select custom-select-sm" id="tipo_acto" name="tipo_acto" required>
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($tipoActo as $mov) {
                            $slc = ($mov['id_acto'] == $registro['id_tipo_acto']) ? 'selected' : '';
                            echo '<option value="' . $mov['id_acto'] . '" ' . $slc . '>' . $mov['nombre'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="numMod" class="small">NUMERO ACTO</label>
                    <input type="text" name="numMod" id="numMod" class="form-control form-control-sm" required value="<?php echo $registro['numero_acto']; ?>">
                </div>

            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-12">
                    <label for="objeto" class="small">OBJETO CDP</label>
                    <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="4"><?php echo $registro['objeto']; ?></textarea>
                </div>

            </div>
        </form>
        <div class="text-right pb-3 px-4">
            <button class="btn btn-primary btn-sm" id="guardaModificaPto">Guardar</button>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
        </div>
    </div>
</div>