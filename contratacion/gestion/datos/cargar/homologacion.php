<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : exit('Acceso denegado');
$titulo = $tipo == '1' ? ' DE SERVICIOS' : ' ESCALA DE HONOARARIOS';
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CARGAR FORMATO / HOMOLOGACIÓN <?php echo $titulo; ?></h5>
        </div>
        <form id="formHomologacion" enctype="multipart/form-data">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="fileHomologacion" class="small">DOCUMENTO</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
                    <input type="file" class="form-control-file border" name="fileHomologacion" id="fileHomologacion">
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-sm" id="btnGuardaHomologacion" text="<?php echo $tipo; ?>">Guardar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
            <br>
        </form>
    </div>
</div>