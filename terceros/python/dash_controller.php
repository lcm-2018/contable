<?php
// dash_controller.php
header('Content-Type: application/json'); // Para respuestas JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); // Leer JSON del cuerpo

    if (isset($data['action'])) {
        $action = $data['action'];

        if ($action == 'start') {
            //$command = 'start /B "Dash" python "C:\wamp64\www\contable\terceros\python\dashboard.py"'; podria ser asi para linux
            $command = 'start /B cmd /C "C:\wamp64\www\contable\terceros\python\start_dash.bat"';
            shell_exec($command);
            echo json_encode(["status" => "Dashboard iniciado"]);
        } elseif ($action == 'stop') {
            shell_exec('taskkill /F /IM python.exe');
            echo json_encode(["status" => "Dashboard detenido"]);
        } else {
            echo json_encode(["error" => "Acción no válida"]);
        }
    } else {
        echo json_encode(["error" => "Campo 'action' no proporcionado"]);
    }
} else {
    echo json_encode(["error" => "Método no permitido. Use POST"]);
}
