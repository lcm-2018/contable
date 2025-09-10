<?php
session_start();
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '100M');
ini_set("memory_limit", "-1");
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
if (isset($_POST['datos'])) {

    $data = isset($_POST['datos']) ? json_decode($_POST['datos'], true) : exit('Acción no permitida');

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=reporte_excel.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo "\xEF\xBB\xBF";
    echo "<table>";

    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            $colspan = isset($cell['colspan']) ? " colspan='{$cell['colspan']}'" : "";
            $rowspan = isset($cell['rowspan']) ? " rowspan='{$cell['rowspan']}'" : "";
            $style   = isset($cell['style']) && $cell['style'] !== '' ? " style='{$cell['style']}'" : "";
            echo "<td{$colspan}{$rowspan}{$style}>" . htmlspecialchars($cell['text']) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    $data = isset($_POST['xls']) ? $_POST['xls'] : exit('Acción no permitida');
    $data = base64_decode($data);
    header('Content-type:application/xls');
    header('Content-Disposition: attachment; filename=reporte_excel.xls');
    header("Pragma: no-cache");
    header("Expires: 0");
    echo "\xEF\xBB\xBF";
    echo $data;
}
