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

$id_hv = isset($_POST['id_hv']) ? $_POST['id_hv'] : -1;

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTRAR DOCUMENTOS ACTIVOS FIJOS</h5>
        </div>
        <div class="px-2">
            <input type="hidden" id="id_hv" name="id_hv" value="<?php echo $id_hv ?>">
            <!--Formulario de registro de Ordenes de Ingreso--> 
            <table id="tb_lista_documentos_acf" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Placa</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Archivo</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3 rigth">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Salir</a>
    </div>    
</div>

<script type="text/javascript" src="../../js/hojavida/hojavida_docs_reg.js?v=<?php echo date('YmdHis') ?>"></script>