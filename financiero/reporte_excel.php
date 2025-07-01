<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
if (isset($_POST['datos'])) {

    $data = isset($_POST['datos']) ? json_decode($_POST['datos'], true) : exit('Acción no permitida');

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=reporte_excel.xls");
    echo "<table border='1'>";

    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    $data = isset($_POST['xls']) ? $_POST['xls'] : exit('Acción no permitida');
    $data = base64_decode($data);
    header('Content-type:application/xls');
    header('Content-Disposition: attachment; filename=reporte_excel.xls');
    echo $data;
}
