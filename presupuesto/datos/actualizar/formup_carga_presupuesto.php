<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_cargue = $_POST['id'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // consulta select tipo de recursos
    $sql = "SELECT 
                `id_pto_tipo`, `nombre_tipo` 
            FROM 
                `pto_tipo_recurso`
            ORDER BY `nombre_tipo` ASC";
    $rs = $cmd->query($sql);
    $tiporecurso = $rs->fetchAll();
    // consulta select tipo de recursos
    $sql = "SELECT
                `id_cargue`
                , `id_pto`
                , `id_tipo_recurso`
                , `cod_pptal`
                , `nom_rubro`
                , `tipo_dato`
                , `valor_aprobado`
                , `tipo_pto`
            FROM
                `pto_cargue`
            WHERE (`id_cargue`  = $id_cargue);";
    $rs = $cmd->query($sql);
    $detalle = $rs->fetch();
    $id_ppto = $detalle['id_pto'];
    //**** */
    $sql = "SELECT
                `id_pto`, `id_tipo`, `id_vigencia`, `nombre`, `descripcion`, `estado`
            FROM
                `pto_presupuestos`
            WHERE `id_pto` = $id_ppto";
    $rs = $cmd->query($sql);
    $presupuesto = $rs->fetch();
    $id_tipo = $presupuesto['id_tipo'];
    $sql = "SELECT 
                `id_trubro`, `nombre`, `id_pto` 
            FROM 
                `pto_tipo_rubro` 
            WHERE 
                `id_pto` = $id_tipo 
            ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $tipogasto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">MODIFICAR RUBRO</h5>
        </div>
        <form id="formUpCargaPresupuesto">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-3">
                    <label for="nomCod" class="small">CODIGO PRESUPUESTAL</label>
                    <input type="text" name="nomCod" id="nomCod" class="form-control form-control-sm" value="<?php echo $detalle['cod_pptal']; ?>">
                    <input type="hidden" name="id_cargue" id="id_cargue" value="<?php echo $id_cargue; ?>">
                </div>
                <div class="form-group col-md-7">
                    <label for="nomRubro" class="small">NOMBRE RUBRO</label>
                    <input type="text" name="nomRubro" id="nomRubro" class="form-control form-control-sm" value="<?php echo $detalle['nom_rubro']; ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="tipoDato" class="small">TIPO DATO</label>
                    <select id="tipoDato" name="tipoDato" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0" <?php echo $detalle['tipo_dato'] == '0' ? 'selected' : '' ?>>M - Mayor</option>
                        <option value="1" <?php echo $detalle['tipo_dato'] == '1' ? 'selected' : '' ?>>D - Detalle</option>
                    </select>
                </div>
            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-3">
                    <label for="valorAprob" class="small">VALOR APROBADO</label>
                    <input type="text" name="valorAprob" id="valorAprob" class="form-control form-control-sm" style='text-align: right;' value="<?php echo $detalle['valor_aprobado']; ?>">

                </div>
                <div class="form-group col-md-3">
                    <label for="tipoRecurso" class="small">TIPO RECURSOS</label>
                    <select id="tipoRecurso" name="tipoRecurso" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="">-- Seleccionar --</option>
                        <?php
                        foreach ($tiporecurso as $mo) {
                            $slc = $detalle['id_tipo_recurso'] == $mo['id_pto_tipo'] ? 'selected' : '';
                            echo '<option ' . $slc . ' value="' . $mo['id_pto_tipo'] . '">' . mb_strtoupper($mo['nombre_tipo']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="tipoPresupuesto" class="small">TIPO PRESUPUESTO</label>
                    <select id="tipoPresupuesto" name="tipoPresupuesto" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="">-- Seleccionar --</option>
                        <?php
                        foreach ($tipogasto as $mo) {
                            $slc = $detalle['tipo_pto'] == $mo['id_trubro'] ? 'selected' : '';
                            echo '<option ' . $slc . ' value="' . $mo['id_trubro'] . '">' . mb_strtoupper($mo['nombre']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="text-right pb-3 px-4">
                <button class="btn btn-success btn-sm" id="btnCargaPresupuesto" text="2">Actualizar</button>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </form>
    </div>
</div>