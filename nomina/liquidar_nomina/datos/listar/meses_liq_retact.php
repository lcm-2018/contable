<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id_retroactivo = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_retroactivo`, `fec_inicio`, `fec_final`, `meses`, `porcentaje`, `observaciones`, `vigencia`, `estado`
            FROM
                `nom_retroactivos`
            WHERE `id_retroactivo` = '$id_retroactivo'";
    $rs = $cmd->query($sql);
    $retroactivo = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$fec_inicio = explode('-', $retroactivo['fec_inicio']);
$fec_final = explode('-', $retroactivo['fec_final']);
$mes_ini = $fec_inicio[1];
$mes_fin = $fec_final[1];
$vigencia = $retroactivo['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `mes`, `nom_mes`, `anio`, `tipo_liq`
            FROM
                (SELECT 
                    `mes`, `nom_mes`, `anio`, `tipo_liq`
                FROM
                    `nom_liq_salario`, `nom_meses`
                WHERE `nom_liq_salario`.`mes` = `nom_meses`.`codigo` AND `tipo_liq` = 'R' AND `nom_liq_salario`.`mes` BETWEEN '$mes_ini' AND '$mes_fin') AS t
            WHERE `anio` = '$vigencia'
            GROUP BY `mes` ";
    $rs = $cmd->query($sql);
    $mesliqdo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">MESES LIQUIDADOS CON RETROACTIVO</h5>
        </div>
        <div class="px-2">
            <input type="hidden" id="id_retro_all" value="<?php echo $id_retroactivo; ?>">
            <div class="mesliquidadoreact pb-3">
                <?php
                if (!empty($mesliqdo)) {
                    $c = 1;
                    foreach ($mesliqdo as $ml) {
                        if ($c === 1 || $c === 5 || $c === 9) { ?>
                            <div class="center-block">
                                <div class="container-fluid">
                                    <div class="input-group">
                                    <?php } ?>
                                    <div id="grupo<?php echo $ml['mes'] ?>" class="col-mb-4 py-2 px-3">
                                        <div class="card shadow-g" style="width: 6rem; border-radius: 0.7rem !important;">
                                            <a data-toggle="collapse" href="#" role="button" aria-expanded="false" value="<?php echo $ml['mes'] ?>">
                                                <img class="card-img-top " src="../../../images/meses/<?php echo $ml['mes'] ?>.png" title=" <?php echo ucfirst(strtolower($ml['nom_mes'])) ?>" alt="mes">
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                    if ($c === 4 || $c === 8 || $c === 12) { ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                <?php $c++;
                    }
                    if ($c <= 4 || ($c > 5  && $c <= 8) || ($c > 9  && $c < 12)) {
                        echo '</div>
                            </div>
                        </div>';
                    }
                }
                ?>
                <div class="pt-3">
                    <a class="btn btn-info btn-sm w-75" value="00">
                        <i class="fab fa-rebel fa-lg" style="color: #EC7063;"></i>
                        &nbsp;&nbsp;VER TOTAL RETROACTIVO
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>