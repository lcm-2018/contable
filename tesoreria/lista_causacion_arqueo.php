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
$fecha1 = $_POST['fecha1'] ?? date('Y-m-d');
$fecha2 = $_POST['fecha2'] ?? date('Y-m-d');
$id_facturador = $_POST['id_facturador'] ?? 0;
$valor = $_POST['valor'] ?? 0;
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Control de fechas
//$fecha_doc = date('Y-m-d');
$fecha_cierre = fechaCierre($vigencia, 56, $cmd);
$fecha = fechaSesion($vigencia, $_SESSION['id_user'], $cmd);
$fecha_max = date("Y-m-d", strtotime($vigencia . '-12-31'));

function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}

try {
    $sql = "SELECT
                `fac_arqueo`.`id_arqueo`
                , DATE_FORMAT(`fac_arqueo`.`fec_creacion`,'%Y-%m-%d') AS `fecha`
                , SUM(`fac_arqueo_detalles`.`valor`) AS `valor`
                , SUM(`fac_arqueo_detalles`.`valor`-IF(`fac_arqueo_detalles`.`valor_dif`<0,0,`fac_arqueo_detalles`.`valor_dif`)) AS `anulado`
                , 0 AS `descuento`
            FROM
                `fac_arqueo_detalles`
                INNER JOIN `fac_arqueo` 
                    ON (`fac_arqueo_detalles`.`id_arqueo` = `fac_arqueo`.`id_arqueo`)
                INNER JOIN `seg_usuarios_sistema` 
                    ON (`seg_usuarios_sistema`.`id_usuario` = `fac_arqueo`.`id_facturador`)
                INNER JOIN `tb_terceros` 
                    ON (`seg_usuarios_sistema`.`num_documento` = `tb_terceros`.`nit_tercero`)
            WHERE  `fac_arqueo`.`estado` = 2  AND `tb_terceros`.`id_tercero_api` = $id_facturador 
                    AND DATE_FORMAT(`fac_arqueo`.`fec_creacion`,'%Y-%m-%d') BETWEEN '$fecha1' AND '$fecha2'
            GROUP BY  `fac_arqueo`.`id_arqueo`";
    $rs = $cmd->query($sql);
    $tabla = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT 	tb_terceros.id_tercero_api
            FROM seg_usuarios_sistema 
            INNER JOIN tb_terceros ON (seg_usuarios_sistema.num_documento = tb_terceros.nit_tercero) 
            WHERE seg_usuarios_sistema.id_usuario IN 
                (SELECT DISTINCT id_usuario FROM(
            SELECT
                seg_usuarios_sistema.id_usuario
            FROM
                fac_arqueo_detalles
                INNER JOIN fac_arqueo ON (fac_arqueo_detalles.id_arqueo = fac_arqueo.id_arqueo)
                INNER JOIN fac_facturacion ON (fac_arqueo_detalles.id_factura = fac_facturacion.id_factura)
                INNER JOIN seg_usuarios_sistema ON (fac_facturacion.id_usr_cierre = seg_usuarios_sistema.id_usuario)
            WHERE  fac_arqueo.estado IN (1,2)
            UNION ALL 
            SELECT seg_usuarios_sistema.id_usuario
            FROM fac_arqueo_detalles
                INNER JOIN fac_arqueo ON (fac_arqueo_detalles.id_arqueo = fac_arqueo.id_arqueo)
                INNER JOIN far_ventas ON (fac_arqueo_detalles.id_venta = far_ventas.id_venta)
                INNER JOIN seg_usuarios_sistema ON (far_ventas.id_usr_crea = seg_usuarios_sistema.id_usuario)
            WHERE fac_arqueo.estado IN (1,2)) AS us)";
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
        'fecha_ini' => $fecha1,
        'fecha_fin' => $fecha2,
        'id_tercero' => $id_facturador,
        'valor_arq' => 0,
        'valor_fac' => $valor,
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
        language: setIdioma,
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
                            <div class="col input-group input-group-sm" id="divBanco">
                                <select name="id_facturador" id="id_facturador" class="custom-select" required onchange="calcularCopagos2(this)">
                                    <option value="0">--Seleccione--</option>
                                    <?php foreach ($terceros as $tc) {
                                        $slc = $tc['id_tercero_api'] == $detalle['id_tercero'] ? 'selected' : '';
                                        echo '<option value="' . $tc['id_tercero_api'] . '" ' . $slc . '>' . $tc['nom_tercero'] . ' -> ' . $tc['nit_tercero'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-success" type="button" title="Buscar Arqueos para Facturador" onclick="calcularCopagos2(this)"><i class="fas fa-arrow-right"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <?php
                        if (!empty($tabla)) {
                        ?>
                            <table class="table table-striped table-bordered table-sm table-hover shadow" id="tableArqueos" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No. Arqueo</th>
                                        <th>Fecha</th>
                                        <th>Valor Total</th>
                                        <th>Descuento</th>
                                        <th>Anulado</th>
                                        <th>Neto</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $vt = $des = $anul = $net = 0;
                                    foreach ($tabla as $row) {
                                        $vt += $row['valor'];
                                        $des += $row['descuento'];
                                        $anul += $row['anulado'];
                                        $tot = $row['valor'] - $row['descuento'] - $row['anulado'];
                                        $net += $tot;
                                        echo '<tr>';
                                        echo '<td>' . $row['id_arqueo'] . '</td>';
                                        echo '<td>' . $row['fecha'] . '</td>';
                                        echo '<td class="text-right">' . pesos($row['valor']) . '</td>';
                                        echo '<td class="text-right">' . pesos($row['descuento']) . '</td>';
                                        echo '<td class="text-right">' . pesos($row['anulado']) . '</td>';
                                        echo '<td class="text-right">' . pesos($tot) . '</td>';
                                        echo '<td><input onchange="SumarArqueos()" type="checkbox" name="arqueo[' . $row['id_arqueo'] . ']" value="' . $tot . '" checked></td>';
                                        echo '</tr>';
                                    }
                                    $detalle['valor_fac'] += $net;
                                    ?>
                                </tbody>
                            </table>
                        <?php
                        }
                        ?>
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