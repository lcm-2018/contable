<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include('../../../../conexion.php');
$idT = $_POST['idt'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_soporte`, `descripcion`
            FROM
                `ctt_soportes_contrato`
            ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $tipo_docs = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_banco`, `nom_banco`
            FROM
                `tb_bancos`
            ORDER BY `nom_banco` ASC";
    $rs = $cmd->query($sql);
    $bancos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_perfil`,`descripcion`
            FROM `ctt_perfil_tercero`
            ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $perfiles = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CARGAR DOCUMENTOS</h5>
        </div>
        <form id="formAddDocsTercero" enctype="multipart/form-data">
            <input type="hidden" id="idTercero" name="idTercero" value="<?php echo $idT ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="slcTipoDocs" class="small">Tipo</label>
                    <select id="slcTipoDocs" name="slcTipoDocs" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($tipo_docs as $td) {
                            echo '<option value="' . $td['id_soporte'] . '">' . $td['descripcion'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="datFecInicio" class="small">FECHA INICIO</label>
                    <input type="date" class="form-control form-control-sm" id="datFecInicio" name="datFecInicio">
                </div>
                <div class="form-group col-md-4">
                    <label for="datFecVigencia" class="small">FECHA VIGENTE</label>
                    <input type="date" class="form-control form-control-sm" id="datFecVigencia" name="datFecVigencia">
                </div>
            </div>
            <div id="rowCertfBanc" style="display: none;">
                <div class="form-row px-4">
                    <div class="form-group col-md-4">
                        <label for="slcBanco" class="small">BANCO</label>
                        <select class="form-control form-control-sm" id="slcBanco" name="slcBanco">
                            <option value="0">-- Seleccionar --</option>
                            <?php
                            foreach ($bancos as $b) {
                                echo '<option value="' . $b['id_banco'] . '">' . $b['nom_banco'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="slcTipoCta" class="small">TIPO CUENTA</label>
                        <select class="form-control form-control-sm" id="slcTipoCta" name="slcTipoCta">
                            <option value="0">-- Seleccionar --</option>
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numCuenta" class="small">Número de cuenta</label>
                        <input type="text" class="form-control form-control-sm" id="numCuenta" name="numCuenta">
                    </div>
                </div>
            </div>
            <div id="rowCcontrato" style="display: none;">
                <div class="form-row px-4">
                    <div class="form-group col-md-6">
                        <label for="slcPerfil" class="small">Perfil</label>
                        <select class="form-control form-control-sm" id="slcPerfil" name="slcPerfil">
                            <option value="0">-- Seleccionar --</option>
                            <?php
                            foreach ($perfiles as $p) {
                                echo '<option value="' . $p['id_perfil'] . '">' . $p['descripcion'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="txtCargo" class="small">Cargo</label>
                        <input type="text" class="form-control form-control-sm" id="txtCargo" name="txtCargo">
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="fileDoc" class="small">DOCUMENTO</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
                    <input type="file" class="form-control-file border" name="fileDoc" id="fileDoc">
                </div>
            </div>
        </form>
        <div class="text-right px-4 pb-3">
            <button class="btn btn-primary btn-sm" id="btnGuardaDocTercero">Guardar</button>
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</button>
        </div>
    </div>
</div>