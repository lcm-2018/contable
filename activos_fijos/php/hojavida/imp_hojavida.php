<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'todo';

try {
    $sql = "SELECT HV.*,
                SED.nom_sede,ARE.nom_area,
                CONCAT_WS(' ',USR.apellido1,USR.apellido2,USR.nombre1,USR.nombre2) AS nom_responsable,
                ART.nom_medicamento AS nom_articulo,MAR.descripcion AS nom_marca,
                CASE HV.tipo_activo WHEN 1 THEN 'PROPIDAD, PLANTA Y EQUIPO' WHEN 2 THEN 'PROPIEDAD PARA LA VENTA' 
                                    WHEN 3 THEN 'PROPIEDAD DE INVERSION' END AS tipo_activo,
                TER.nom_tercero,TIG.nom_tipo_ingreso,
                CASE HV.calif_4725 WHEN 1 THEN 'I' WHEN 2 THEN 'IIA' WHEN 3 THEN 'IIB' END AS calificacion_4725,
                CASE HV.riesgo WHEN 1 THEN 'ALTO' WHEN 2 THEN 'MEDIO' WHEN 3 THEN 'BAJO' END AS riesgo,
                CASE HV.uso WHEN 1 THEN 'MEDICO' WHEN 2 THEN 'BASICO' WHEN 3 THEN 'APOYO' END AS uso,
                CASE HV.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' 
                                        WHEN 4 THEN 'SIN SERVICIO' END AS estado_general,
                CASE HV.estado WHEN 1 THEN 'ACTIVO' WHEN 2 THEN 'PARA MANTENIMIENTO' 
                                WHEN 3 THEN 'EN MANTENIMIENTO' WHEN 4 THEN 'INACTIVO' WHEN 5 THEN 'DADO DE BAJA' END AS estado
            FROM acf_hojavida AS HV
            INNER JOIN far_medicamentos AS ART  ON (ART.id_med=HV.id_articulo)
            LEFT JOIN acf_marca AS MAR ON (MAR.id=HV.id_marca)
            LEFT JOIN tb_terceros AS TER ON (TER.id_tercero=HV.id_proveedor)
            LEFT JOIN far_orden_ingreso_tipo AS TIG ON (TIG.id_tipo_ingreso=HV.id_tipo_ingreso)
            LEFT JOIN tb_sedes AS SED ON (SED.id_sede=HV.id_sede)
            LEFT JOIN far_centrocosto_area AS ARE ON (ARE.id_area=HV.id_area)
            LEFT JOIN seg_usuarios_sistema AS USR ON (USR.id_usuario=HV.id_responsable)
            WHERE HV.id_activo_fijo=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT HVC.*,
                ART.nom_medicamento AS nom_articulo,
                MAR.descripcion AS nom_marca
            FROM acf_hojavida_componentes AS HVC
            INNER JOIN far_medicamentos AS ART ON (ART.id_med=HVC.id_articulo)
            INNER JOIN acf_marca AS MAR ON (MAR.id=HVC.id_marca)
            WHERE HVC.id_activo_fijo=" . $id . " ORDER BY HVC.id_componente";
    $rs = $cmd->query($sql);
    $obj_com = $rs->fetchAll();

    $sql = "SELECT acf_hojavida_documentos.*,	
                CASE tipo WHEN 1 THEN 'FICHA TECNICA' WHEN 2 THEN 'MANUAL' WHEN 3 THEN 'OTRO' END AS nom_tipo
            FROM acf_hojavida_documentos
            WHERE id_activo_fijo=" . $id . " ORDER BY id_documento";
    $rs = $cmd->query($sql);
    $obj_doc = $rs->fetchAll();

} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="text-right py-3">
    <a type="button" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
    <a type="button" class="btn btn-primary btn-sm" id="btnImprimir">Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="content bg-light" id="areaImprimir">
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
            }
        }
        .resaltar:nth-child(even) {
            background-color: #F8F9F9;
        }
        .resaltar:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>

    <?php include('../common/reporte_header.php'); ?>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>REGISTRO DE ACTIVO FIJO</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">        
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Sede</td>
            <td>Área</td>
            <td>Responsable</td>
            <td>Placa</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['nom_sede']; ?></td>
            <td><?php echo $obj_e['nom_area']; ?></td>
            <td><?php echo $obj_e['nom_responsable']; ?></td>
            <td><?php echo $obj_e['placa']; ?></td>
        </tr> 
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td colspan="2">Artículo</td>
            <td>No. Serial</td>
            <td>Marca</td>
        </tr>
        <tr>
            <td colspan="2"><?php echo $obj_e['nom_articulo']; ?></td>
            <td><?php echo $obj_e['num_serial']; ?></td>
            <td><?php echo $obj_e['nom_marca']; ?></td>
        </tr> 
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td colspan="4" style="text-align:center">Nombre del Activo Fijo</td>
        </tr>
        <tr>
            <td colspan="4"><?php echo $obj_e['des_activo']; ?></td>
        </tr>
        <?php if($tipo=='todo'): ?>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Tipo Activo</td>
                <td>Proveedor</td>
                <td>Valor</td>
                <td>Modelo</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['tipo_activo']; ?></td>
                <td><?php echo $obj_e['nom_tercero']; ?></td>
                <td><?php echo $obj_e['valor']; ?></td>
                <td><?php echo $obj_e['modelo']; ?></td>
            </tr>         
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Lote</td>
                <td>Fecha de fabricación</td>
                <td>Registro Invima</td>
                <td>Fabricante</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['lote']; ?></td>
                <td><?php echo $obj_e['fecha_fabricacion']; ?></td>
                <td><?php echo $obj_e['reg_invima']; ?></td>
                <td><?php echo $obj_e['fabricante']; ?></td>
            </tr> 
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Lugar de Origen</td>
                <td>Representante</td>
                <td>Dirección del Representante</td>
                <td>Teléfono del Representante</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['lugar_origen']; ?></td>
                <td><?php echo $obj_e['representante']; ?></td>
                <td><?php echo $obj_e['dir_representante']; ?></td>
                <td><?php echo $obj_e['tel_representante']; ?></td>
            </tr>   
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td colspan="4" style="text-align:center">Recomendaciones del Fabricante</td>
            </tr>    
            <tr>
                <td colspan="4"><?php echo $obj_e['recom_fabricante']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Tipo de Adquisición</td>
                <td>Fecha de Adquisición</td>
                <td>Fecha de Instalación</td>
                <td>Periodo de Garantía</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['nom_tipo_ingreso']; ?></td>
                <td><?php echo $obj_e['fecha_adquisicion']; ?></td>
                <td><?php echo $obj_e['fecha_instalacion']; ?></td>
                <td><?php echo $obj_e['periodo_garantia']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Vida Útil</td>
                <td>Calificación 4725</td>
                <td>Calibración</td>
                <td>Voltaje Mínimo</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['vida_util']; ?></td>
                <td><?php echo $obj_e['calificacion_4725']; ?></td>
                <td><?php echo $obj_e['calibracion']; ?></td>
                <td><?php echo $obj_e['vol_min']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Voltaje Máximo</td>
                <td>Frecuencia Mínima</td>
                <td>Frecuencia Máxima</td>
                <td>Potencia Mínima</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['vol_max']; ?></td>
                <td><?php echo $obj_e['frec_min']; ?></td>
                <td><?php echo $obj_e['frec_max']; ?></td>
                <td><?php echo $obj_e['pot_min']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Potencia Máxima</td>
                <td>Corriente Mínima</td>
                <td>Corriente Máxima</td>
                <td>Temperatura Mínima</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['pot_max']; ?></td>
                <td><?php echo $obj_e['cor_min']; ?></td>
                <td><?php echo $obj_e['cor_max']; ?></td>
                <td><?php echo $obj_e['temp_min']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Temperatura Máxima</td>
                <td>Riesgo</td>
                <td>Uso</td>
                <td>CB Diagnóstico</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['temp_max']; ?></td>
                <td><?php echo $obj_e['riesgo']; ?></td>
                <td><?php echo $obj_e['uso']; ?></td>
                <td><?php echo $obj_e['cb_diagnostico']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>CB Prevención</td>
                <td>CB Rehabilitación</td>
                <td>CB Análisis de Laboratorio</td>
                <td>CB Tratamiento y Mantenimiento</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['cb_prevencion']; ?></td>
                <td><?php echo $obj_e['cb_rehabilitacion']; ?></td>
                <td><?php echo $obj_e['cb_analisis_lab']; ?></td>
                <td><?php echo $obj_e['cb_trat_mant']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td>Estado de Funcionamiento</td>
                <td>Fecha Fuera de Servicio</td>
                <td>Estado</td>
            </tr>
            <tr>
                <td><?php echo $obj_e['estado_general']; ?></td>
                <td><?php echo $obj_e['fecha_fuera_servicio']; ?></td>
                <td><?php echo $obj_e['estado']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td colspan="4" style="text-align:center">Causa del Estado de Funcionamiento</td>
            </tr>    
            <tr>
                <td colspan="4"><?php echo $obj_e['causa_est_general']; ?></td>
            </tr>
            <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
                <td colspan="4" style="text-align:center">Observaciones Generales</td>
            </tr>    
            <tr>
                <td colspan="4"><?php echo $obj_e['observaciones']; ?></td>
            </tr>
        <?php endif ?>    
    </table>
    
    <?php if(count($obj_com)>0 && ($tipo=='com' || $tipo=='todo')): ?>
        <table style="width:100%; border:#A9A9A9 1px solid;">
            <thead style="font-size:60%">
                <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                    <th colspan="4" style="text-align:center">COMPONENTES</th>
                </tr>    
                <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                    <th>Componente</th>
                    <th>No. Serial</th>
                    <th>Modelo</th>
                    <th>Marca</th>
                </tr>
            </thead>
            <tbody style="font-size: 60%;">
                <?php
                $tabla = '';
                foreach ($obj_com as $obj) {
                    $tabla .=  '<tr class="resaltar"> 
                            <td style="text-align:left">' . mb_strtoupper($obj['nom_articulo']) . '</td>   
                            <td>' . $obj['num_serial'] . '</td>
                            <td>' . $obj['modelo'] . '</td>
                            <td>' . $obj['nom_marca'] . '</td></tr>';
                }
                echo $tabla;
                ?>
            </tbody>
        </table>
    <?php endif ?>

    <?php if(count($obj_doc)>0 && ($tipo=='doc' || $tipo=='todo')): ?>
        <table style="width:100%; border:#A9A9A9 1px solid;">
            <thead style="font-size:60%">
                <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                    <th colspan="4" style="text-align:center">DOCUMENTOS</th>
                </tr>    
                <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Archivo</th>
                </tr>
            </thead>
            <tbody style="font-size: 60%;">
                <?php
                $tabla = '';
                foreach ($obj_doc as $obj) {
                    $tabla .=  '<tr class="resaltar"> 
                            <td>' . $obj['nom_tipo'] . '</td>
                            <td>' . $obj['descripcion'] . '</td>
                            <td>' . $obj['archivo'] . '</td></tr>';
                }
                echo $tabla;
                ?>
            </tbody>            
        </table>
    <?php endif ?>    
</div>