<?php
session_start();
/*
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$idT = $_POST['idt'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM tb_tipo_docs_tercero";
    $rs = $cmd->query($sql);
    $tipo_docs = $rs->fetchAll();
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
                        foreach($tipo_docs as $td){
                            echo '<option value="'.$td['id_doc'].'">'.$td['descripcion'].'</option>';
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
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="fileDoc" class="small">DOCUMENTO</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
                    <input type="file" class="form-control-file border" name="fileDoc" id="fileDoc">
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-sm" id="btnAddDocsTercero">Agregar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
            <br>
        </form>
    </div>
</div> */