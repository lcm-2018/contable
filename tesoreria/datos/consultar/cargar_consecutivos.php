<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$tipo = $_POST['id'];
$id_vigencia = $_SESSION['id_vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_tipo_doc`,
                MIN(`id_manu`) AS `min`,
                MAX(`id_manu`) AS `max`
            FROM `ctb_doc`
                INNER JOIN `ctb_fuente` 
                    ON `ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`
            WHERE `ctb_fuente`.`tesor` > 0 AND `id_vigencia` = $id_vigencia AND `ctb_doc`.`id_tipo_doc` = $tipo";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch(PDO::FETCH_ASSOC);
    if (!empty($datos)) {
        $min = $datos['min'];
        $max = $datos['max'];

        $cmd->exec("CREATE TABLE IF NOT EXISTS `tmp_secuencia` (`consecutivo` INT)");
        $con = "INSERT INTO `tmp_secuencia` (`consecutivo`) VALUES (?)";
        $stmt = $cmd->prepare($con);
        $stmt->bindParam(1, $i, PDO::PARAM_INT);
        for ($i = $min; $i <= $max; $i++) {
            $stmt->execute();
        }
        $sql2 = "SELECT
                    `tmp_secuencia`.`consecutivo`
                FROM `tmp_secuencia`
                    LEFT JOIN 
                        (SELECT `id_manu` FROM `ctb_doc` WHERE `id_vigencia` = $id_vigencia AND `ctb_doc`.`id_tipo_doc` = $tipo) AS`ctb_doc` 
                        ON `ctb_doc`.`id_manu` = `tmp_secuencia`.`consecutivo`
                WHERE `ctb_doc`.`id_manu` IS NULL 
                ORDER BY `id_manu`";
        $rs2 = $cmd->query($sql2);
        $consecutivos = $rs2->fetchAll(PDO::FETCH_ASSOC);
        $consecutivos = !empty($consecutivos) ? $consecutivos : [0 => ['consecutivo' => $max + 1]];
    } else {
        $consecutivos = [0 => ['consecutivo' => 1]];
    }
    $cmd->exec("DROP TABLE IF EXISTS `tmp_secuencia`");
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}




?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;" class="mb-0">CONSECUTIVOS DISPONIBLES</h5>
        </div>
        <div class="p-3">
            <table class="w-100">
                <tr>
                    <?php
                    if (!empty($consecutivos)) {
                        $count = 0;
                        foreach ($consecutivos as $c) {
                            echo "<td class='border bg-success text-white rounded p-1'>{$c['consecutivo']}</td>";
                            $count++;
                            if ($count % 5 == 0) {
                                echo "</tr><tr>";
                            }
                        }
                        // Si la última fila tiene menos de 5 celdas, rellenar con celdas vacías
                        if ($count % 5 != 0) {
                            for ($i = $count % 5; $i < 5; $i++) {
                                echo "<td></td>";
                            }
                            echo "</tr>";
                        }
                    }
                    ?>
            </table>
        </div>
    </div>
    <div class="text-center">
        <a type="button" class="btn btn-secondary btn-sm mt-3" data-dismiss="modal"> Cerrar</a>
    </div>
</div>