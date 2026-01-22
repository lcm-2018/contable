<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

include '../../../conexion.php';

$tipo = (int)$_POST['id'];
$id_vigencia = (int)$_SESSION['id_vigencia'];

try {
    $cmd = new PDO(
        "$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset",
        $bd_usuario,
        $bd_clave,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $sql = "SELECT d1.id_manu + 1 AS consecutivo
            FROM ctb_doc d1
            LEFT JOIN ctb_doc d2
            ON d2.id_manu = d1.id_manu + 1
            AND d2.id_vigencia = d1.id_vigencia
            AND d2.id_tipo_doc = d1.id_tipo_doc
            WHERE d1.id_vigencia = :vigencia
            AND d1.id_tipo_doc = :tipo
            AND d1.id_manu < (
                SELECT MAX(id_manu)
                FROM ctb_doc
                WHERE id_vigencia = :vigencia
                    AND id_tipo_doc = :tipo
            )
            AND d2.id_manu IS NULL
            ORDER BY consecutivo
            LIMIT 100";

    $stmt = $cmd->prepare($sql);
    $stmt->execute([
        ':vigencia' => $id_vigencia,
        ':tipo'     => $tipo
    ]);

    $consecutivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Si no existen documentos aún */
    if (empty($consecutivos)) {
        $consecutivos = [];
    }

    $cmd = null;
} catch (PDOException $e) {
    die('Error de base de datos: ' . $e->getMessage());
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

                        /* Rellenar última fila */
                        if ($count % 5 != 0) {
                            for ($i = $count % 5; $i < 5; $i++) {
                                echo "<td></td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<td class='text-muted'>No hay consecutivos disponibles</td></tr>";
                    }
                    ?>
            </table>
        </div>
    </div>

    <div class="text-center">
        <a type="button" class="btn btn-secondary btn-sm mt-3" data-dismiss="modal">Cerrar</a>
    </div>
</div>