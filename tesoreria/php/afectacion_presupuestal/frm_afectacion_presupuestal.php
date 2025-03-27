<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';
//include 'cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_ctb_fuente = isset($_POST['id_ctb_fuente']) ? $_POST['id_ctb_fuente'] : 0;
$id_ctb_referencia = isset($_POST['id_ctb_referencia']) ? $_POST['id_ctb_referencia'] : 0;
$accion_pto = isset($_POST['accion_pto']) ? $_POST['accion_pto'] : 0;
$fecha = isset($_POST['fecha']) && strlen($_POST['fecha']) > 0 ? $_POST['fecha'] : '2000-01-01';
$id_tercero_api = isset($_POST['id_tercero_api']) ? $_POST['id_tercero_api'] : 0;
$tercero = isset($_POST['tercero']) && strlen($_POST['tercero']) > 0 ? $_POST['tercero'] : '';
$objeto = isset($_POST['objeto']) && strlen($_POST['objeto']) > 0 ? $_POST['objeto'] : '';
$id_ctb_doc = isset($_POST['id_ctb_doc']) ? $_POST['id_ctb_doc'] : 0;

// esta consulta es para generar el proximo id_manu
//------------------------------------
$sql = "SELECT COUNT(*) + 1 AS id_manu
        FROM pto_rad LIMIT 1";
$rs = $cmd->query($sql);
$obj_manu = $rs->fetch();
//---------------------------------------------------

// esta consulta es para consultar el id_pto_rad segun el id_ctb_doc y el manu para editar
//--------------------------------------------------
$sql = "SELECT 
            id_pto_rad
            , id_manu
        FROM pto_rad 
        WHERE pto_rad.id_ctb_doc = $id_ctb_doc LIMIT 1";
$rs = $cmd->query($sql);
$obj_id_pto_rad = $rs->fetch();

$id_pto_rad = 0;

if (!empty($obj_id_pto_rad['id_pto_rad'])) {
    $id_pto_rad = $obj_id_pto_rad['id_pto_rad'];
}
//---------------------------------------------------
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE AFECTACION PRESUPUESTAL DE INGRESOS</h5>
        </div>
        <div class="px-2">
            <form id="frm_afectacion_presupuestal">
                <input type="hidden" id="hd_id_ctb_fuente" name="hd_id_ctb_fuente" value="<?php echo $id_ctb_fuente ?>">
                <input type="hidden" id="hd_id_ctb_referencia" name="hd_id_ctb_referencia" value="<?php echo $id_ctb_referencia ?>">
                <input type="hidden" id="hd_accion_pto" name="hd_accion_pto" value="<?php echo $accion_pto ?>">

                <div class="form-row" style="text-align: left;">
                    <div class="form-group col-md-2">
                        <span class="small">Fecha</span>
                    </div>
                    <div class="form-group col-md-2">
                        <input type="text" class="form-control form-control-sm" id="txt_fecha" name="txt_fecha" readonly value="<?php echo $fecha ?>">
                    </div>
                    <div class="form-group col-md-1">
                        <span class="small"></span>
                    </div>
                    <div class="form-group col-md-2">
                        <span class="small">Id Manu</span>
                    </div>
                    <div class="form-group col-md-1">
                        <input type="text" class="form-control form-control-sm" id="txt_id_manu" name="txt_id_manu" value="<?php 
                            if($id_pto_rad==0){ 
                                echo $obj_manu['id_manu'];
                            }
                            else{
                                echo $obj_id_pto_rad['id_manu'];
                            }
                         ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <span class="small"></span>
                    </div>
                    <div class="form-group col-md-1">
                        <a type="button" id="btn_guardar_encabezado" class="btn btn-outline-success btn-sm" title="Guardar encabezado">
                            <span class="fas fa-save fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                    <input type="hidden" id="hd_id_pto_rad" name="hd_id_pto_rad" value=" <?php echo $id_pto_rad ?> ">
                    <input type="hidden" id="hd_id_ctb_doc" name="hd_id_ctb_doc" value=" <?php echo $id_ctb_doc ?> ">
                </div>

                <div class="form-row" style="text-align: left;">
                    <div class="form-group col-md-2">
                        <span class="small">Tercero</span>
                    </div>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control form-control-sm" id="txt_tercero" name="txt_tercero" readonly value="<?php echo $tercero ?>">
                        <input type="hidden" id="hd_id_tercero_api" name="hd_id_tercero_api" value="<?php echo $id_tercero_api ?>">
                    </div>
                </div>

                <div class="form-row" style="text-align: left;">
                    <div class="form-group col-md-2">
                        <span class="small">Objeto</span>
                    </div>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control form-control-sm" id="txt_objeto" name="txt_objeto" value="<?php echo $objeto ?>">
                    </div>
                </div>

                <!--<div class="separador" style="height: 30px;"></div>-->

                <div class="separador-decorativo"></div>

                <!--<div class="separador-con-texto">
                    <span class="texto-separador"> - o - </span>
                </div>-->

                <style>
                    .separador {
                        border-top: 1px solid #ccc;
                        /* Línea horizontal */
                        margin: 10px 0;
                        /* Espacio arriba y abajo */
                    }
                </style>

                <style>
                    .separador-decorativo {
                        height: 1.5px;
                        background: linear-gradient(to right, transparent, #16a085, transparent);
                        margin: 20px 0;
                    }
                </style>

                <style>
                    .separador-con-texto {
                        display: flex;
                        align-items: center;
                        text-align: center;
                        margin: 20px 0;
                    }

                    .separador-con-texto::before,
                    .separador-con-texto::after {
                        content: "";
                        flex: 1;
                        border-bottom: 1px solid #ccc;
                    }

                    .texto-separador {
                        padding: 0 10px;
                    }
                </style>

                <div class="form_row" style="text-align: center; width:100%; font-size:105%">
                    <span class="small">DETALLES RUBROS</span>
                </div>

                <div class="form_row" style="text-align: center;">
                    <span class="small">&nbsp;</span>
                </div>

                <div class=" form-row">
                    <div class="form-group col-md-8">
                        <input type="text" class="form-control form-control-sm" id="txt_rubro" name="txt_rubro" placeholder="Rubro">
                        <input type="hidden" id="hd_id_txt_rubro" name="hd_id_txt_rubro" class="form-control form-control-sm">
                        <input type="hidden" id="hd_id_tipo" name="hd_id_txt_rubro" class="form-control form-control-sm">
                        <input type="hidden" id="hd_anio" name="hd_id_txt_rubro" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-3">
                        <input type="text" class="form-control form-control-sm" id="txt_valor" name="txt_valor" placeholder="Valor">
                    </div>
                    <div class="form-group col-md-1">
                        <a type="button" id="btn_agregar_rubro" class="btn btn-outline-success btn-sm" title="Agregar">
                            <span class="fas fa-plus fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>

                <div class=" w-100 text-left">
                    <table id="tb_rubros" class="table table-striped table-bordered table-sm nowrap table-hover shadow w-100" style="width:100%; font-size:80%">
                        <thead>
                            <tr class="text-center centro-vertical">
                                <th>Id rad det</th>
                                <th style="min-width: 60%;">Rubro</th>
                                <th class="text-right">Valor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-left centro-vertical" id="body_tb_rubros"></tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../tesoreria/js/afectacion_presupuestal/afectacion_presupuestal.js?v=<?php echo date('YmdHis') ?>"></script>