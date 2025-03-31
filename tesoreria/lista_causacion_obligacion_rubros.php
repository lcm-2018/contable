<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';

$id_cop = $_POST['id_cop'] ?? '';
$id_pag_doc = $_POST['id_doc'] ?? '';
// Consulta tipo de presupuesto
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                    pto_cop_detalle.id_tercero_api
                    , pto_cop_detalle.id_pto_crp_det
                    , pto_cop_detalle.id_pto_cop_det
                    , pto_cargue.nom_rubro                    
                    , pto_cargue.cod_pptal AS rubro
                    , pto_cargue.nom_rubro
                    , IFNULL(pto_crp_detalle.valor,0) - IFNULL(pto_crp_detalle.valor_liberado,0) AS valor
                    , IFNULL(pto_cop_detalle.valor,0) - IFNULL(pto_cop_detalle.valor_liberado,0) AS val_cop
                    , SUM(IFNULL(pto_pag_detalle.valor,0) - IFNULL(pto_pag_detalle.valor_liberado,0)) AS val_pag
                FROM
                    pto_cop_detalle
                    INNER JOIN pto_crp_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle.id_pto_crp_det)
                    INNER JOIN pto_cdp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
                    INNER JOIN pto_cargue ON (pto_cdp_detalle.id_rubro = pto_cargue.id_cargue)
                    LEFT JOIN pto_pag_detalle ON (pto_pag_detalle.id_pto_cop_det = pto_cop_detalle.id_pto_cop_det)
                WHERE pto_cop_detalle.id_ctb_doc = $id_cop
                GROUP BY   pto_cop_detalle.id_pto_cop_det";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
    $tercero = !empty($rubros) ? $rubros[0]['id_tercero_api'] : 0;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableContrtacionRp').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableContrtacionRpRubros').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE REGISTROS PRESUPUESTALES PARA PAGO</h5>
        </div>
        <div class="pb-3"></div>
        <input type="hidden" name="id_pto_rp" id="id_pto_rp" value="<?php echo $id_cop; ?>">
        <form id="rubrosPagar">
            <input type="hidden" name="id_pag_doc" value="<?php echo $id_pag_doc; ?>">
            <input type="hidden" name="id_tercero" value="<?php echo $tercero; ?>">
            <div class="px-3">
                <table id="tableContrtacionRpRubros" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Rubro</th>
                            <th style="width: 15%;">Valor Rp</th>
                            <th style="width: 15%;">Valor Causado</th>
                            <th style="width: 15%;">Valor Cxp</th>
                            <!--<th style="width: 15%;">Acciones</th>-->
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        foreach ($rubros as $ce) {
                            $editar = $detalles = null;
                            $id_doc = 0;
                            $valor = 0;
                            $id_det_cop = $ce['id_pto_cop_det'];
                            $pagado = $ce['val_pag'] > 0 ? $ce['val_pag'] : 0;
                            $obligado = $ce['val_cop'];
                            $valor =  $obligado - $pagado;
                            $valor_mil = number_format($valor, 2, '.', ',');
                            if (PermisosUsuario($permisos, 5601, 3) || $id_rol == 1) {
                                $editar = '<a value="' . $id_doc . '"  class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-print fa-lg"></span></a>';
                                $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            ...
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Historial</a>
                            </div>';
                            }
                            if (PermisosUsuario($permisos, 5601, 4) || $id_rol == 1) {
                                $borrar = '<a value="' . $id_doc . '" onclick="eliminarImputacionPag(' . $id_doc . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                            }
                            $valor_obl = number_format($obligado, 2, '.', ',');
                        ?>
                            <tr>
                                <td class="text-left"><?php echo $ce['rubro'] . ' - ' . $ce['nom_rubro']; ?></td>
                                <td class="text-right"><?php echo '$ ' . number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td class="text-right"><?php echo '$ ' . number_format($ce['val_cop'], 2, '.', ','); ?></td>
                                <td class="text-right">
                                    <input type="text" name="detalle[<?php echo $id_det_cop; ?>]" id="detalle_<?php echo $id_det_cop; ?>" class="form-control form-control-sm detalle-pag" value="<?php echo $valor_mil; ?>" style="text-align: right;" required onkeyup="valorMiles(id)" max="<?php echo $valor; ?>">
                                </td>
                                <!--<td class="text-center"> <?php //echo $editar  .  $acciones; 
                                                                ?></td>-->
                            </tr>
                        <?php
                        }
                        ?>

                    </tbody>
                </table>
                <div class="text-right py-3">
                    <button type="button" class="btn btn-success btn-sm" onclick="rubrosaPagar(this);"> Guardar</button>
                    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>
        </form>
    </div>


</div>

<?php
$cmd = null;
