<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
include '../terceros.php';

$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no disponible');
$id_detalle = $_POST['id_detalle'] ?? 0;
$fecha_doc = $_POST['fecha'] ?? '';
$vigencia = $_SESSION['vigencia'];
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Control de fechas
//$fecha_doc = date('Y-m-d');
$fecha_cierre = fechaCierre($vigencia, 5, $cmd);
$fecha = fechaSesion($vigencia, $_SESSION['id_user'], $cmd);
$fecha_max = date("Y-m-d", strtotime($vigencia . '-12-31'));

try {
    $sql = "SELECT 
                `tb_terceros`.`id_tercero_api` 
            FROM
                `seg_usuarios_sistema` 
                INNER JOIN `tb_terceros` 
                    ON (`seg_usuarios_sistema`.`num_documento` = `tb_terceros`.`nit_tercero`) 
            WHERE `seg_usuarios_sistema`.`id_usuario` IN 
                (SELECT DISTINCT 
                    `id_usr_crea` 
                FROM
                    (SELECT  `id_usr_crea`  FROM `fac_facturacion`  
                    UNION 
                    SELECT  `id_usr_crea`  FROM `fac_otros` 
                    UNION SELECT  `id_usr_crea`  FROM `far_ventas`) AS `t`)";
    $rs = $cmd->query($sql);
    $facturador = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                `id_ctb_doc`
                , `estado`
            FROM
                `ctb_doc`
            WHERE (`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $estado = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$id_t = [];
$terceros = [];
if (!empty($facturador)) {
    foreach ($facturador as $fact) {
        $id_t[] = $fact['id_tercero_api'];
    }
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);
    //ordenar terceros por nom_tercero
    usort($terceros, function ($a, $b) {
        return $a['nom_tercero'] <=> $b['nom_tercero'];
    });
}
// consultar los conceptos asociados al recuado del arqueo
try {
    $sql = "SELECT `id_concepto_arq`,`concepto` FROM `tes_concepto_arqueo` WHERE `estado` = 1";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar los arqueos registrados en seg_tes_arqueo_caja
try {
    $sql = "SELECT
                `tes_causa_arqueo`.`id_causa_arqueo`
                , `tes_causa_arqueo`.`fecha_ini`
                , `tes_causa_arqueo`.`fecha_fin`
                , `tes_causa_arqueo`.`id_tercero`
                , `tes_causa_arqueo`.`valor_arq`
                , `tes_causa_arqueo`.`valor_fac`
                , `tes_causa_arqueo`.`observaciones`
                , `tb_terceros`.`nom_tercero` AS `facturador`
                , `tb_terceros`.`nit_tercero` AS `documento`
            FROM
                `tes_causa_arqueo`
                INNER JOIN `tb_terceros` 
                    ON (`tes_causa_arqueo`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`tes_causa_arqueo`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $arqueos = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($id_detalle > 0) {
    $detalle = array_values(array_filter($arqueos, function ($ar) use ($id_detalle) {
        return $ar['id_causa_arqueo'] == $id_detalle;
    }))[0];
} else {
    $detalle = [
        'id_causa_arqueo' => 0,
        'fecha_ini' => $fecha_doc,
        'fecha_fin' => $fecha_doc,
        'id_tercero' => 0,
        'valor_arq' => 0,
        'valor_fac' => 0,
        'observaciones' => '',
        'facturador' => ''
    ];
}

$valor_pagar = 0;

?>
<script>
    $('#tableCausacionArqueo').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ - _END_ registros de _TOTAL_ ",
            "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
            "infoFiltered": "(Filtrado de _MAX_ entradas en total )",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Ver _MENU_ Filas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": '<i class="fas fa-search fa-flip-horizontal" style="font-size:1.5rem; color:#2ECC71;"></i>',
            "zeroRecords": "No se encontraron registros",
            "paginate": {
                "first": "&#10096&#10096",
                "last": "&#10097&#10097",
                "next": "&#10097",
                "previous": "&#10096"
            },
        },
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableCausacionArqueo').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE ARQUEO DE CAJA POR FECHA</h5>
        </div>
        <div class="px-3 pt-2">
            <?php
            if ($estado['estado'] == 1) {
            ?>
                <form id="formAddFacturador">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="fecha_arqueo_ini" class="small">FECHA INICIAL</label>
                            <input type="date" name="fecha_arqueo_ini" id="fecha_arqueo_ini" class="form-control form-control-sm" max="<?php echo $fecha_max; ?>" value="<?php echo $detalle['fecha_ini']; ?>">
                            <input type="hidden" name="id_doc" id="id_doc" value="<?php echo $id_doc; ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="fecha_arqueo_fin" class="small">FECHA FINAL</label>
                            <input type="date" name="fecha_arqueo_fin" id="fecha_arqueo_fin" class="form-control form-control-sm" max="<?php echo $fecha_max; ?>" value="<?php echo $detalle['fecha_fin']; ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="id_facturador" class="small">FACTURADOR:</label>
                            <div class="col" id="divBanco">
                                <select name="id_facturador" id="id_facturador" class="form-control form-control-sm" required onchange="calcularCopagos2(this)">
                                    <option value="0">--Seleccione--</option>
                                    <?php foreach ($terceros as $tc) {
                                        $slc = $tc['id_tercero_api'] == $detalle['id_tercero'] ? 'selected' : '';
                                        echo '<option value="' . $tc['id_tercero_api'] . '" ' . $slc . '>' . $tc['nom_tercero'] . ' -> ' . $tc['nit_tercero'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label for="valor_fact" class="small">VALOR FACTURADO:</label>
                            <div id="divForma">
                                <input type="text" name="valor_fact" id="valor_fact" class="form-control form-control-sm" value="<?php echo $detalle['valor_fac']; ?>" required style="text-align: right;" onkeyup="valorMiles(id)" readonly>
                            </div>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="valor_arq" class="small">VALOR:</label>
                            <div class="btn-group">
                                <input type="text" name="valor_arq" id="valor_arq" class="form-control form-control-sm" value="<?php echo $detalle['valor_arq']; ?>" required style="text-align: right;" onkeyup="valorMiles(id)" ondblclick="copiarValor()" onchange="validarDiferencia()">
                                <button type="submit" class="btn btn-primary btn-sm" id="registrarMvtoDetalle">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" rows="3" placeholder="OBSERVACIONES:"><?php echo $detalle['observaciones']; ?></textarea>
                        </div>
                    </div>
                </form>
            <?php
            }
            ?>
            <table id="tableCausacionArqueo" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-10">Fecha Inicio</th>
                        <th class="w-10">Fecha Fin</th>
                        <th class="w-55">Facturador</th>
                        <th class="w-10">Documento</th>
                        <th class="w-10">Valor cobrado</th>
                        <th class="w-10">Valor entregado</th>
                        <th class="w-5">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        foreach ($arqueos as $ar) {
                            $editar = $borrar = $detalles = NULL;
                            $id = $ar['id_causa_arqueo'];
                            if (PermisosUsuario($permisos, 5602, 1) || $id_rol == 1) {
                                $detalles = '<button onclick="DetalleArqueoCaja(' . $id . ',this)" class="btn btn-outline-info btn-sm btn-circle shadow-gb" title="Detalles"><span class="fas fa-list fa-lg"></span></buttn>';
                            }
                            if (PermisosUsuario($permisos, 5602, 3) || $id_rol == 1) {
                                $editar = '<a onclick="CargaArqueoCajaTes(' . $id_doc . ',' . $id . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                            }
                            if (PermisosUsuario($permisos, 5602, 4) || $id_rol == 1) {
                                $borrar = '<a onclick="eliminarRecaduoArqeuo(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                            }
                            if ($estado['estado'] != 1) {
                                $editar = $borrar = NULL;
                            }
                            echo '<tr class="text-left">
                                    <td>' . $ar['fecha_ini'] . '</td>
                                    <td>' . $ar['fecha_fin'] . '</td>
                                    <td >' . $ar['facturador'] . '</td>
                                    <td>' . $ar['documento'] . '</td>
                                    <td class="text-right"> ' . number_format($ar['valor_fac'], 2, '.', ',') . '</td>
                                    <td class="text-right"> ' . number_format($ar['valor_arq'], 2, '.', ',') . '</td>
                                    <td class="text-center"> ' . $editar . $borrar . $detalles . '</td>
                                </tr>';
                        }
                        ?>
                    </div>
                </tbody>
            </table>
            <div class="text-right py-3">
                <?php
                if ($estado['estado'] == 1) {
                ?>
                    <button type="button" class="btn btn-success btn-sm" onclick="GuardaMvtoDetalle(<?php echo $id_doc . ',' . $id_detalle ?>,this)">Guardar</button>
                <?php
                }
                ?>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
            </div>
        </div>
    </div>
</div>