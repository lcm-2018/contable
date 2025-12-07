<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id_hv']) ? $_POST['id_hv'] : -1;
$sql = "SELECT HV.*,ART.nom_medicamento AS nom_articulo            
        FROM acf_hojavida HV
        INNER JOIN far_medicamentos AS ART  ON (ART.id_med=HV.id_articulo)
        WHERE HV.id_activo_fijo=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if ($obj === false) {
    $obj = array(); // Inicializa $obj como un array vacío
}

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
    //Inicializa variable por defecto
    $obj['id_tipo_ingreso'] = 0;
    $obj['estado_general'] = 1;
    $obj['estado'] = 1;

    $bodega = sede_principal($cmd);
    $obj['id_sede'] = $bodega['id_sede'];    
    $area = area_principal($cmd);
    $obj['id_area'] = $area['id_area'];    
    $obj['id_responsable'] = $area['id_responsable'];    
} 

$edit = edit_estados_activo_fijo($cmd,$id);
$edit_ubi = $edit['edit_ubi'] == 1 ? '' : 'disabled="disabled"';
$edit_art = $edit['edit_art'] == 1 ? '' : 'disabled="disabled"';
$edit_est = $edit['edit_est'] == 1 ? '' : 'disabled="disabled"';
$imprimir = $id != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR HOJA DE VIDA ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="acf_reg_hojavida">
                <input type="hidden" id="id_hv" name="id_hv" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="sl_sede" class="small">Sede</label>
                        <select class="form-control form-control-sm" id="sl_sede" name="sl_sede" <?php echo $edit_ubi ?>>
                            <?php sedes($cmd, '', $obj['id_sede']) ?>   
                        </select>
                        <input type="hidden" id="id_sede" name="id_sede" value="<?php echo $obj['id_sede'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_area" class="small">Area</label>
                        <select class="form-control form-control-sm" id="sl_area" name="sl_area" <?php echo $edit_ubi ?>>
                            <?php areas_sede($cmd, '', $obj['id_sede'], $obj['id_area']) ?>   
                        </select>
                        <input type="hidden" id="id_area" name="id_area" value="<?php echo $obj['id_area'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_responsable" class="small">Reponsable</label>
                        <select class="form-control form-control-sm" id="sl_responsable" name="sl_responsable" <?php echo $edit_ubi ?>>
                            <?php usuarios($cmd, '', $obj['id_responsable']) ?>
                        </select>
                        <input type="hidden" id="id_responsable" name="id_responsable" value="<?php echo $obj['id_responsable'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="placa" class="small">Placa</label>
                        <input type="text" class="form-control form-control-sm" id="placa" name="placa" value="<?php echo $obj['placa'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="nom_articulo" class="small">Artículo</label>
                        <input type="text" class="form-control form-control-sm" id="nom_articulo" name="nom_articulo" class="small" value="<?php echo $obj['nom_articulo'] ?>" readonly="readonly" title="Doble Click para Seleccionar el Articulo" <?php echo $edit_art ?>>
                        <input type="hidden" id="id_articulo" name="id_articulo" value="<?php echo $obj['id_articulo'] ?>">
                    </div>                    
                    <div class="form-group col-md-3">
                        <label for="num_serial" class="small">No. Serial</label>
                        <input type="text" class="form-control form-control-sm" id="num_serial" name="num_serial" value="<?php echo $obj['num_serial'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_marca" class="small">Marca</label>
                        <select class="form-control form-control-sm" id="sl_marca" name="sl_marca">
                            <?php marcas($cmd, '', $obj['id_marca']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="des_activo" class="small">Nombre del Activo Fijo</label>
                        <textarea class="form-control form-control-sm" id="des_activo" name="des_activo" rows="2"><?php echo $obj['des_activo'] ?></textarea>
                    </div> 
                    <div class="form-group col-md-3">
                        <label for="sl_tipo_activo" class="small">Tipo Activo</label>
                        <select class="form-control form-control-sm" id="sl_tipo_activo" name="sl_tipo_activo">
                            <?php tipos_activo('', $obj['tipo_activo']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_proveedor" class="small">Proveedor</label>
                        <select class="form-control form-control-sm" id="sl_proveedor" name="sl_proveedor">
                            <?php terceros($cmd, '', $obj['id_proveedor']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="valor" class="small">Valor</label>
                        <input type="number" step="0.0001" class="form-control form-control-sm" id="valor" name="valor" value="<?php echo $obj['valor'] ?>">
                    </div>                    
                    <div class="form-group col-md-3">
                        <label for="modelo" class="small">Modelo</label>
                        <input type="text" class="form-control form-control-sm" id="modelo" name="modelo" value="<?php echo $obj['modelo'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="lote" class="small">Lote</label>
                        <input type="text" class="form-control form-control-sm" id="lote" name="lote" value="<?php echo $obj['lote'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_fabricacion" class="small">Fecha de Fabricación</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_fabricacion" name="fecha_fabricacion" class="small" value="<?php echo $obj['fecha_fabricacion'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="reg_invima" class="small">Registro INVIMA</label>
                        <input type="text" class="form-control form-control-sm" id="reg_invima" name="reg_invima" value="<?php echo $obj['reg_invima'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fabricante" class="small">Fabricante</label>
                        <input type="text" class="form-control form-control-sm" id="fabricante" name="fabricante" value="<?php echo $obj['fabricante'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="lugar_origen" class="small">Lugar de Origen</label>
                        <input type="text" class="form-control form-control-sm" id="lugar_origen" name="lugar_origen" value="<?php echo $obj['lugar_origen'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="representante" class="small">Representante</label>
                        <input type="text" class="form-control form-control-sm" id="representante" name="representante" value="<?php echo $obj['representante'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="dir_representante" class="small">Dirección del Representante</label>
                        <input type="text" class="form-control form-control-sm" id="dir_representante" name="dir_representante" value="<?php echo $obj['dir_representante'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="tel_representante" class="small">Teléfono del Representante</label>
                        <input type="text" class="form-control form-control-sm" id="tel_representante" name="tel_representante" value="<?php echo $obj['tel_representante'] ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="recom_fabricante" class="small">Recomendaciones del Fabricante</label>
                        <textarea class="form-control form-control-sm" id="recom_fabricante" name="recom_fabricante" rows="3"><?php echo $obj['recom_fabricante'] ?></textarea>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_tipo_ingreso" class="small">Tipo de Adquisición</label>
                        <select class="form-control form-control-sm" id="sl_tipo_ingreso" name="sl_tipo_ingreso">
                            <?php tipo_ingreso($cmd, '', $obj['id_tipo_ingreso']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_adquisicion" class="small">Fecha de Adquisición</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_adquisicion" name="fecha_adquisicion" value="<?php echo $obj['fecha_adquisicion'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_instalacion" class="small">Fecha de Instalación</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_instalacion" name="fecha_instalacion" value="<?php echo $obj['fecha_instalacion'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="periodo_garantia" class="small">Periodo de Garantía</label>
                        <input type="text" class="form-control form-control-sm" id="periodo_garantia" name="periodo_garantia" value="<?php echo $obj['periodo_garantia'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="vida_util" class="small">Vida Útil</label>
                        <input type="text" class="form-control form-control-sm" id="vida_util" name="vida_util" value="<?php echo $obj['vida_util'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_calif_4725" class="small">Calificación 4725</label>
                        <select class="form-control form-control-sm" id="sl_calif_4725" name="sl_calif_4725">
                            <?php calif4725('', $obj['calif_4725']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="calibracion" class="small">Calibración</label>
                        <input type="text" class="form-control form-control-sm" id="calibracion" name="calibracion" value="<?php echo $obj['calibracion'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="vol_min" class="small">Voltaje Mínimo</label>
                        <input type="number" class="form-control form-control-sm" id="vol_min" name="vol_min" value="<?php echo $obj['vol_min'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="vol_max" class="small">Voltaje Máximo</label>
                        <input type="number" class="form-control form-control-sm" id="vol_max" name="vol_max" value="<?php echo $obj['vol_max'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="frec_min" class="small">Frecuencia Mínima</label>
                        <input type="number" class="form-control form-control-sm" id="frec_min" name="frec_min" value="<?php echo $obj['frec_min'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="frec_max" class="small">Frecuencia Máxima</label>
                        <input type="number" class="form-control form-control-sm" id="frec_max" name="frec_max" value="<?php echo $obj['frec_max'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="pot_min" class="small">Potencia Mínima</label>
                        <input type="number" class="form-control form-control-sm" id="pot_min" name="pot_min" value="<?php echo $obj['pot_min'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="pot_max" class="small">Potencia Máxima</label>
                        <input type="number" class="form-control form-control-sm" id="pot_max" name="pot_max" value="<?php echo $obj['pot_max'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cor_min" class="small">Corriente Mínima</label>
                        <input type="number" class="form-control form-control-sm" id="cor_min" name="cor_min" value="<?php echo $obj['cor_min'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cor_max" class="small">Corriente Máxima</label>
                        <input type="number" class="form-control form-control-sm" id="cor_max" name="cor_max" value="<?php echo $obj['cor_max'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="temp_min" class="small">Temperatura Mínima</label>
                        <input type="number" class="form-control form-control-sm" id="temp_min" name="temp_min" value="<?php echo $obj['temp_min'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="temp_max" class="small">Temperatura Máxima</label>
                        <input type="number" class="form-control form-control-sm" id="temp_max" name="temp_max" value="<?php echo $obj['temp_max'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_riesgo" class="small">Riesgo</label>
                        <select class="form-control form-control-sm" id="sl_riesgo" name="sl_riesgo">
                            <?php riesgos('', $obj['riesgo']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_uso" class="small">Uso</label>
                        <select class="form-control form-control-sm" id="sl_uso" name="sl_uso">
                            <?php usos('', $obj['uso']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cb_diagnostico" class="small">CB Diagnóstico</label>
                        <input type="text" class="form-control form-control-sm" id="cb_diagnostico" name="cb_diagnostico" value="<?php echo $obj['cb_diagnostico'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cb_prevencion" class="small">CB Prevención</label>
                        <input type="text" class="form-control form-control-sm" id="cb_prevencion" name="cb_prevencion" value="<?php echo $obj['cb_prevencion'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cb_rehabilitacion" class="small">CB Rehabilitación</label>
                        <input type="text" class="form-control form-control-sm" id="cb_rehabilitacion" name="cb_rehabilitacion" value="<?php echo $obj['cb_rehabilitacion'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cb_analisis_lab" class="small">CB Análisis de Laboratorio</label>
                        <input type="text" class="form-control form-control-sm" id="cb_analisis_lab" name="cb_analisis_lab" value="<?php echo $obj['cb_analisis_lab'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cb_trat_mant" class="small">CB Tratamiento y Mantenimiento</label>
                        <input type="text" class="form-control form-control-sm" id="cb_trat_mant" name="cb_trat_mant" value="<?php echo $obj['cb_trat_mant'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado_general" class="small">Estado de Funcionamiento</label>
                        <select class="form-control form-control-sm" id="sl_estado_general" name="sl_estado_general" <?php echo $edit_est ?>>
                            <?php estado_general_activo('', $obj['estado_general']) ?>
                        </select>
                        <input type="hidden" id="id_estado_general" name="id_estado_general" value="<?php echo $obj['estado_general'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_fuera_servicio" class="small">Fecha Fuera de Servicio</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_fuera_servicio" name="fecha_fuera_servicio" value="<?php echo $obj['fecha_fuera_servicio'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado" class="small">Estado</label>
                        <select class="form-control form-control-sm" id="sl_estado" name="sl_estado" <?php echo $edit_est ?>>
                            <?php estado_activo('', $obj['estado']) ?>
                        </select>
                        <input type="hidden" id="id_estado" name="id_estado" value="<?php echo $obj['estado'] ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="causa_est_general" class="small">Causa del Estado de Funcionamiento</label>
                        <textarea class="form-control form-control-sm" id="causa_est_general" name="causa_est_general" rows="3"><?php echo $obj['causa_est_general'] ?></textarea>
                    </div> 
                    <div class="form-group col-md-12">
                        <label for="observaciones" class="small">Observaciones Generales</label>
                        <textarea class="form-control form-control-sm" id="observaciones" name="observaciones" rows="3"><?php echo $obj['observaciones'] ?></textarea>
                    </div>                    
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">    
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar">Guardar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir" <?php echo $imprimir ?>>Imprimir</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>  
</div>



