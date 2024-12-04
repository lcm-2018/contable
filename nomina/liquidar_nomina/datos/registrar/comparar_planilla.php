<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../simpleXLSX.php';
$id_nomina = isset($_POST['id_nomina']) ? $_POST['id_nomina'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_empleado`, `no_documento` FROM `nom_empleado`";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `aporte_salud_emp`
                , `aporte_pension_emp`
                , `aporte_solidaridad_pensional`
                , `aporte_salud_empresa`
                , `aporte_pension_empresa`
                , `aporte_rieslab`
            FROM
                `nom_liq_segsocial_empdo`
            WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $patronales = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`
                , `val_sena`
                , `val_icbf`
                , `val_comfam`
                , `id_nomina`
            FROM
                `nom_liq_parafiscales`
            WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $parafiscales = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$file_tmp = $_FILES['filePlanilla']['tmp_name'];

move_uploaded_file($file_tmp, "planilla.xlsx");
$t = 0;
$data = '';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if (!empty($empleados)) {
    if (file_exists('planilla.xlsx')) {
        $xlsx = new SimpleXLSX('planilla.xlsx');
        $data = '<table class="table table-bordered table-sm table-hover table-striped table-responsive">
                    <thead style="background-color: #E59866 !important; color: white;">
                        <tr>
                            <th scope="col">DOC</th>
                            <th scope="col">PLA_P_EMPL</th>
                            <th scope="col">SIS_P_EMPL</th>
                            <th scope="col">DIF_P_EMPL</th>
                            <th scope="col">PLA_P_PATR</th>
                            <th scope="col">SIS_P_PATR</th>
                            <th scope="col">DIF_P_PATR</th>
                            <th scope="col">PLA_P_SOLD</th>
                            <th scope="col">SIS_P_SOLD</th>
                            <th scope="col">DIF_P_SOLD</th>
                            <th scope="col">PLA_S_EMPL</th>
                            <th scope="col">SIS_S_EMPL</th>
                            <th scope="col">DIF_S_EMPL</th>
                            <th scope="col">PLA_S_PATR</th>
                            <th scope="col">SIS_S_PATR</th>
                            <th scope="col">DIF_S_PATR</th>
                            <th scope="col">PLA_CAJA</th>
                            <th scope="col">SIS_CAJA</th>
                            <th scope="col">DIF_CAJA</th>
                            <th scope="col">PLA_RIESGOS</th>
                            <th scope="col">SIS_RIESGOS</th>
                            <th scope="col">DIF_RIESGOS</th>
                            <th scope="col">PLA_SENA</th>
                            <th scope="col">SIS_SENA</th>
                            <th scope="col">DIF_SENA</th>
                            <th scope="col">PLA_ICBF</th>
                            <th scope="col">SIS_ICBF</th>
                            <th scope="col">DIF_ICBF</th>
                        </tr>
                    </thead>
                        <tbody>';
        foreach ($xlsx->rows() as $fila => $campo) {
            if ($fila < 1) {
                continue;
            }

            $cedula = $campo[0];
            $key = array_search($cedula, array_column($empleados, 'no_documento'));
            if (false !== $key) {
                $id_empleado = $empleados[$key]['id_empleado'];
                $p_empl = $campo[1];
                $p_patr = $campo[2];
                $p_sold = $campo[3];
                $s_empl = $campo[4];
                $s_patr = $campo[5];
                $caja = $campo[6];
                $riesgo = $campo[7];
                $sena = $campo[8];
                $icbf = $campo[9];
                $keypt = array_search($id_empleado, array_column($patronales, 'id_empleado'));
                $data .= '<tr>';
                $data .= '<td>' . $cedula . '</td>';
                $data .= '<td>' . ($p_empl) . '</td>';
                $data .= '<td>' . ($patronales[$keypt]['aporte_pension_emp']) . '</td>';
                $data .= '<td>' . ($p_empl - $patronales[$keypt]['aporte_pension_emp']) . '</td>';
                $data .= '<td>' . ($p_patr) . '</td>';
                $data .= '<td>' . ($patronales[$keypt]['aporte_pension_empresa']) . '</td>';
                $data .= '<td>' . ($p_patr - $patronales[$keypt]['aporte_pension_empresa']) . '</td>';
                $data .= '<td>' . ($p_sold) . '</td>';
                $data .= '<td>' . ($patronales[$keypt]['aporte_solidaridad_pensional']) . '</td>';
                $data .= '<td>' . ($p_sold - $patronales[$keypt]['aporte_solidaridad_pensional']) . '</td>';
                $data .= '<td>' . ($s_empl) . '</td>';
                $data .= '<td>' . ($patronales[$keypt]['aporte_salud_emp']) . '</td>';
                $data .= '<td>' . ($s_empl - $patronales[$keypt]['aporte_salud_emp']) . '</td>';
                $data .= '<td>' . ($s_patr) . '</td>';
                $data .= '<td>' . ($patronales[$keypt]['aporte_salud_empresa']) . '</td>';
                $data .= '<td>' . ($s_patr - $patronales[$keypt]['aporte_salud_empresa']) . '</td>';
                $data .= '<td>' . ($caja) . '</td>';
                $keypf = array_search($id_empleado, array_column($parafiscales, 'id_empleado'));
                $data .= '<td>' . ($parafiscales[$keypf]['val_comfam']) . '</td>';
                $data .= '<td>' . ($caja - $parafiscales[$keypf]['val_comfam']) . '</td>';
                $data .= '<td>' . ($riesgo) . '</td>';
                $data .= '<td>' . ($patronales[$keypt]['aporte_rieslab']) . '</td>';
                $data .= '<td>' . ($riesgo - $patronales[$keypt]['aporte_rieslab']) . '</td>';
                $data .= '<td>' . ($sena) . '</td>';
                $data .= '<td>' . ($parafiscales[$keypf]['val_sena']) . '</td>';
                $data .= '<td>' . ($sena - $parafiscales[$keypf]['val_sena']) . '</td>';
                $data .= '<td>' . ($icbf) . '</td>';
                $data .= '<td>' . ($parafiscales[$keypf]['val_icbf']) . '</td>';
                $data .= '<td>' . ($icbf - $parafiscales[$keypf]['val_icbf']) . '</td>';
                $data .= '</tr>';
                $t++;
            }
        }
        if ($t > 0) {
            echo $data;
        } else {
            unlink('planilla.xlsx');
            echo 'No se registró ningun cambio a la Planilla';
        }
    } else {
        echo "Archivo no encontrado";
    }
} else {
    echo 'No se econtró ningún empleado';
}
