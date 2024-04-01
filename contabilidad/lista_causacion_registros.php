<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
$id_vigencia = $_SESSION['id_vigencia'];
// Consulta tipo de presupuesto
function pesos($valor)
{
    return number_format($valor, 2, ',', '.');
}
$id_r = $_POST['dato'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto` FROM `pto_presupuestos` WHERE (`id_tipo` = 2 AND `id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_pto_crp`
                , `id_manu`
                , `id_tercero_api`
                , `fecha`
                , `objeto`
                , `id_cdp`
            FROM
                `pto_crp`
                WHERE (`estado` = 2 AND `causado` = 0 AND `id_pto` = " . $listappto['id_pto'] . ")";

    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `pto_crp`.`id_pto_crp`
                , (IFNULL(`pto_crp_detalle`.`valor`,0) - IFNULL(`pto_crp_detalle`.`valor_liberado`,0)) AS `valor`
            FROM
                `pto_crp_detalle`
                INNER JOIN `pto_crp` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
            WHERE (`pto_crp`.`estado` = 2 AND `pto_crp`.`id_pto` = " . $listappto['id_pto'] . "
            GROUP BY `pto_crp`.`id_pto_crp`";
    $rs = $cmd->query($sql);
    $valores = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultas totales obligados
try {
    $sql = "SELECT
    SUM(`valor`) AS causado
    , `id_pto_doc`
    , `tipo_mov`
FROM
    `pto_documento_detalles`
WHERE (`tipo_mov` ='COP' OR `tipo_mov` ='LCO')
GROUP BY `id_pto_doc`;";
    $rs = $cmd->query($sql);
    $causados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date('Y-m-d', strtotime($listado[0]['fecha']));
if ($id_r == 3) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                    `nom_nomina_pto_ctb_tes`.`id`
                    , `nom_nomina_pto_ctb_tes`.`id_nomina`
                    , `nom_nomina_pto_ctb_tes`.`tipo`
                    , `nom_nomina_pto_ctb_tes`.`cdp`
                    , `nom_nomina_pto_ctb_tes`.`crp`
                    , `nom_nominas`.`descripcion`
                    , `nom_nominas`.`mes`
                    , `nom_nominas`.`vigencia`
                    , `nom_nominas`.`estado`
                FROM
                    `nom_nomina_pto_ctb_tes`
                    INNER JOIN `nom_nominas` 
                        ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE (`nom_nominas`.`estado` = 3) AND`nom_nomina_pto_ctb_tes`.`tipo` <> 'PL'
                UNION 
                SELECT
                    `nom_nomina_pto_ctb_tes`.`id`
                    , `nom_nomina_pto_ctb_tes`.`id_nomina`
                    , `nom_nomina_pto_ctb_tes`.`tipo`
                    , `nom_nomina_pto_ctb_tes`.`cdp`
                    , `nom_nomina_pto_ctb_tes`.`crp`
                    , `nom_nominas`.`descripcion`
                    , `nom_nominas`.`mes`
                    , `nom_nominas`.`vigencia`
                    , `nom_nominas`.`planilla` AS `estado`
                FROM
                    `nom_nomina_pto_ctb_tes`
                    INNER JOIN `nom_nominas` 
                        ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE (`nom_nominas`.`planilla` = 3 AND `nom_nomina_pto_ctb_tes`.`tipo` = 'PL')";
        $rs = $cmd->query($sql);
        $nominas = $rs->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $rp = [];
    foreach ($nominas as $nm) {
        if ($nm['crp'] != '') {
            $rp[] = $nm['crp'];
        }
    }
    $rp = implode(',', $rp);
    if (!empty($nominas)) {
        try {
            $sql = "SELECT 
                    `t1`.`id_pto_doc`
                    , `t1`.`valor`
                    , `t1`.`tipo_mov`
                    , `pto_documento`.`id_manu`
                    , `pto_documento`.`fecha`
                    , `pto_documento`.`objeto`
                    
                FROM 
                    (SELECT
                        `id_pto_doc`
                        , SUM(`valor`) AS `valor`
                        , `tipo_mov`
                    FROM
                        `pto_documento_detalles`
                    WHERE (`id_pto_doc` IN ($rp) AND `tipo_mov` = 'CRP')
                    GROUP BY `id_pto_doc`) AS `t1`
                INNER JOIN
                    `pto_documento`
                    ON(`pto_documento`.`id_doc` = `t1`.`id_pto_doc`)";
            $rs = $cmd->query($sql);
            $valores = $rs->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
    }
}
?>
<script>
    $('#tableContrtacionRp').DataTable({
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
    $('#tableContrtacionCdp').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE REGISTROS PRESUPUESTALES PARA OBLIGACION </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableContrtacionRp" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Num</th>
                        <th>Rp</th>
                        <th>Contrato</th>
                        <th>Fecha</th>
                        <th>Terceros</th>
                        <th>Valor</th>
                        <th>Acciones</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($id_r == 1 || $id_r == 2) {
                        $id_t = [];
                        foreach ($listado as $rp) {
                            $id_t[] = $rp['id_tercero'];
                        }
                        $payload = json_encode($id_t);
                        //API URL
                        $url = $api . 'terceros/datos/res/lista/terceros';
                        $ch = curl_init($url);
                        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        $terceros = json_decode($result, true);
                        foreach ($listado as $ce) {
                            $key = array_search($ce['id_tercero'], array_column($terceros, 'id_tercero'));
                            $id_doc = $ce['id_pto_doc'];
                            $id_ter = $ce['id_tercero'];
                            // Consulta terceros en la api
                            $tercero = $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['razon_social'];
                            // fin api terceros
                            // Obtener el saldo del registro por obligar valor del registro - el valor obligado efectivamente
                            $liq = array_search($ce['id_pto_doc'], array_column($liquidados, 'id_auto_crp'));
                            if ($liq !== false) {
                                $valor_liquidado = $liquidados[$liq]['liquidado'];
                            } else {
                                $valor_liquidado = 0;
                            }
                            $key = array_search($ce['id_pto_doc'], array_column($registros, 'id_pto_doc'));
                            if ($key !== false) {
                                $valor_registro = $registros[$key]['registrado'] + $valor_liquidado;
                            } else {
                                $valor_registro = 0;
                            }
                            $key = array_search($ce['id_pto_doc'], array_column($causados, 'id_pto_doc'));
                            if ($key !== false) {
                                $valor_causado = $causados[$key]['causado'];
                            } else {
                                $valor_causado = 0;
                            }

                            $saldo_rp = $valor_registro - $valor_causado;

                            // Obtengo el numero del contrato
                            try {
                                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                                $sql = "SELECT
                            `ctt_contratos`.`id_compra`
                            , `pto_documento`.`id_auto`
                        FROM
                            `ctt_contratos`
                            INNER JOIN `ctt_adquisiciones` 
                                ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                            INNER JOIN `pto_documento` 
                                ON (`ctt_adquisiciones`.`id_cdp` = `pto_documento`.`id_auto`)
                        WHERE (`pto_documento`.`id_auto` =$ce[id_auto]);";
                                $rs = $cmd->query($sql);
                                $num_contrato = $rs->fetch();
                                $numeroc = $num_contrato['id_compra'];
                            } catch (PDOException $e) {
                                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                            }

                            if ((intval($permisos['editar'])) === 1) {
                                $editar = '<a value="' . $id_doc . '" onclick="cargarListaDetalleCont(' . $id_doc . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></a>';
                                $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            ...
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Historial</a>
                            </div>';
                            } else {
                                $editar = null;
                                $detalles = null;
                            }
                            if ($saldo_rp > 0) {
                    ?>
                                <tr>
                                    <td class="text-center"><input type="checkbox" value="" id="defaultCheck1"></td>
                                    <td class="text-left"><?php echo $ce['id_manu']; ?></td>
                                    <td class="text-left"><?php echo $numeroc  ?></td>
                                    <td class="text-left"><?php echo $fecha; ?></td>
                                    <td class="text-left"><?php echo $tercero; ?></td>
                                    <td class="text-right"> <?php echo  $saldo_rp; ?></td>
                                    <td class="text-center"> <?php echo $editar .  $acciones; ?></td>
                                </tr>
                                <?php
                            }
                        }
                    } else if ($id_r == 3) {
                        if (isset($valores)) {
                            foreach ($valores as $vl) {

                                $key = array_search($vl['id_pto_doc'], array_column($nominas, 'crp'));
                                if ($key !== false && $nominas[$key]['estado'] == 3) {
                                    $id_nomina = $nominas[$key]['id_nomina'] . '|' . $nominas[$key]['crp'] . '|' . $nominas[$key]['tipo'];
                                    $causar = '<button value="' . $id_nomina . '" onclick="CausaNomina(this)" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></button>';
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $nominas[$key]['id_nomina'] ?></td>
                                        <td class="text-left"><?php echo $vl['id_manu']; ?></td>
                                        <td class="text-left"><?php echo '-'  ?></td>
                                        <td class="text-left"><?php echo date('Y-m-d', strtotime($vl['fecha'])); ?></td>
                                        <td class="text-left"><?php echo $vl['objeto']; ?></td>
                                        <td class="text-right"> <?php echo  pesos($vl['valor']); ?></td>
                                        <td class="text-center"> <?php echo $causar ?></td>
                                    </tr>
                    <?php
                                } else {
                                    $id_nomina = 0;
                                }
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center">No hay registros</td></tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Procesar lote</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>
<?php
