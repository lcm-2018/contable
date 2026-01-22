<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
if ($id_rol != 1) {
    exit('Usuario no autorizado');
}
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `seg_modulos`.`nom_modulo`,
                GROUP_CONCAT(`tb_fin_periodos`.`mes` SEPARATOR ',') AS `meses`,
                `seg_modulos`.`id_modulo`
            FROM
                `seg_modulos`
                LEFT JOIN `tb_fin_periodos` 
                    ON (`seg_modulos`.`id_modulo` = `tb_fin_periodos`.`id_modulo` AND `tb_fin_periodos`.`vigencia` = $vigencia)
            WHERE `seg_modulos`.`id_modulo` >= 50
            GROUP BY `seg_modulos`.`id_modulo`
            ORDER BY `seg_modulos`.`id_modulo`, `tb_fin_periodos`.`mes` ASC";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow mb-3">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;" class="mb-0"><i class="fas fa-user-lock fa-lg mr-3" style="color:#2FDA49"></i>CIERRE DE MÓDULOS</p>
            </h5>
        </div>

        <div class="p-3">
            <table id="tableModulos" class="table-striped table-bordered table-sm nowrap" style="width:100%">
                <thead>
                    <tr class="text-center">
                        <th>Módulo</th>
                        <th>Ene.</th>
                        <th>Feb.</th>
                        <th>Mar.</th>
                        <th>Abr.</th>
                        <th>May.</th>
                        <th>Jun.</th>
                        <th>Jul.</th>
                        <th>Ago.</th>
                        <th>Sep.</th>
                        <th>Oct.</th>
                        <th>Nov.</th>
                        <th>Dic.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($obj as $o) {
                        echo '<tr>';
                        echo '<td class="text-left">' . mb_strtoupper($o['nom_modulo']) . '</td>';
                        $meses = explode(',', $o['meses']);
                        for ($i = 1; $i <= 12; $i++) {
                            $ids = base64_encode($o['id_modulo'] . '|' . $i);
                            echo '<td class="text-center">';
                            if (in_array($i, $meses)) {
                                echo '<span class="badge badge-pill badge-secondary" title="Cerrado"><i class="far fa-folder"></i></span>';
                            } else {
                                echo '<a href="javascript:void(0);" text="' . $ids . '" class="cerrar"><span class="badge badge-pill badge-success" title="Abierto"><i class="far fa-folder-open"></i></span></a>';
                            }
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
    </div>
</div>