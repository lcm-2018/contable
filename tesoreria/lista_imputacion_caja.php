<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';

$id_doc = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tes_caja_rubros`.`id_caja_rubros`
                , `tes_caja_rubros`.`valor`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , IFNULL(`gastado`.`valor`,0) AS `valor_pag`
            FROM
                `tes_caja_rubros`
                INNER JOIN `pto_cargue` 
                    ON (`tes_caja_rubros`.`id_rubro_gasto` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT 
                        `id_caja_rubros`, SUM(`valor`) AS `valor`
                    FROM `tes_caja_mvto`
                    GROUP BY `id_caja_rubros`) AS `gastado`
                    ON (`tes_caja_rubros`.`id_caja_rubros` = `gastado`.`id_caja_rubros`)
            WHERE (`tes_caja_rubros`.`id_caja_const` = $id_doc)";
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE IMPUTACIONES CAJA MENOR </h5>
        </div>
        <div class="p-3">
            <form id="formImputacion">
                <input type="hidden" name="id_doc" value="<?php echo $id_doc ?>">
                <?php
                $band = true;
                foreach ($listado as $l) {
                    $max = $l['valor'] - $l['valor_pag'];
                    $value = $max > 0 ? $max : 0;
                ?>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <?php if ($band) { ?>
                                <span class="small">Código presupuestal</span>
                            <?php } ?>
                            <div class="form-control form-control-sm text-left bg-light"><?php echo $l['cod_pptal'] ?></div>
                        </div>
                        <div class="form-group col-md-5">
                            <?php if ($band) { ?>
                                <span class="small">Rubro</span>
                            <?php } ?>
                            <div class="form-control form-control-sm text-left bg-light"><?php echo $l['nom_rubro'] ?></div>
                        </div>
                        <div class="form-group col-md-2">
                            <?php if ($band) { ?>
                                <span for="valor" class="small">Valor RP</span>
                            <?php } ?>
                            <div class="form-control form-control-sm text-left bg-light"><?php echo number_format($max, 2) ?></div>
                        </div>
                        <div class="form-group col-md-2">
                            <?php if ($band) { ?>
                                <span for="valor" class="small">Valor CxP</span>
                            <?php } ?>
                            <input type="text" name="valor[<?php echo $l['id_caja_rubros'] ?>]" id="valor" onkeyup="valorMiles(id)" class="form-control form-control-sm text-right ValImputacion" min="0" max="<?php echo $max ?>" value="<?php echo number_format($value, 2) ?>">
                        </div>
                    </div>
                <?php
                    $band = false;
                }
                ?>
            </form>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-primary btn-sm" onclick="DetalleImputacionCajaMenor()">Guardar</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>
<?php
