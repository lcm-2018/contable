<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_cat`, `descripcion` FROM `nom_categoria_tercero` ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $categorias = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR TERCERO NÓMINA</h5>
        </div>
        <div class="px-2">
            <form id="formRegTerceroNom">
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="slcCategoria" class="small">CATEGORIA</label>
                        <select name="slcCategoria" id="slcCategoria" class="form-control form-control-sm">
                            <option value="0">--Seleccione--</option>
                            <?php foreach ($categorias as $categoria) : ?>
                                <option value="<?= $categoria['id_cat'] ?>"><?= $categoria['descripcion'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label for="BuscaTerNom" class="small">TERCERO</label>
                        <input type="text" class="form-control form-control-sm" id="BuscaTerNom">
                        <input type="hidden" id="idTerceroNom" name="idTerceroNom" value="0">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnRegTerceroNom">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>