<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : 0;
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `id_doc_fuente`, `cod`, `nombre`, `contab`, `tesor`
            FROM
                `ctb_fuente`
            WHERE (`id_doc_fuente` = $id)";
    $rs = $cmd->query($sql);
    $fuente = $rs->fetch();
    if (empty($fuente)) {
        $fuente = [
            'id_doc_fuente' => 0,
            'cod' => '',
            'nombre' => '',
            'contab' => 0,
            'tesor' => 0
        ];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <form id="formDocFuente">
        <input type="hidden" id="id_doc_fuente" name="id_doc_fuente" value="<?php echo $fuente['id_doc_fuente']; ?>">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;GESTIÓN DOCUMENTOS FUENTE</h5>
            </div>
            <div class="py-3 px-3">
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="txtCodigo" class="small">CÓDIGO: </label>
                    </div>
                    <div class="col-9">
                        <input type="text" id="txtCodigo" name="txtCodigo" class="form-control form-control-sm" value="<?php echo $fuente['cod']; ?>">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="txtNombre" class="small">NOMBRE: </label>
                    </div>
                    <div class="col-9">
                        <input type="text" id="txtNombre" name="txtNombre" class="form-control form-control-sm" value="<?php echo $fuente['nombre']; ?>">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="slcGrupoContab" class="small">Grupo contabilidad: </label>
                    </div>
                    <div class="col-9">
                        <select id="slcGrupoContab" name="slcGrupoContab" class="form-control form-control-sm">
                            <option value="0" <?php echo $fuente['contab'] == '0' ? 'selected' : ''; ?>>0</option>
                            <option value="1" <?php echo $fuente['contab'] == '1' ? 'selected' : ''; ?>>1</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="slcGrupoTesor" class="small">grupo tesorería: </label>
                    </div>
                    <div class="col-9">
                        <select id="slcGrupoTesor" name="slcGrupoTesor" class="form-control form-control-sm">
                            <option value="0" <?php echo $fuente['tesor'] == '0' ? 'selected' : ''; ?>>0</option>
                            <option value="1" <?php echo $fuente['tesor'] == '1' ? 'selected' : ''; ?>>1</option>
                            <option value="2" <?php echo $fuente['tesor'] == '2' ? 'selected' : ''; ?>>2</option>
                            <option value="3" <?php echo $fuente['tesor'] == '3' ? 'selected' : ''; ?>>3</option>
                            <option value="4" <?php echo $fuente['tesor'] == '4' ? 'selected' : ''; ?>>4</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-primary btn-sm" onclick="guardarDocFuente(this)">Guardar</button>
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>