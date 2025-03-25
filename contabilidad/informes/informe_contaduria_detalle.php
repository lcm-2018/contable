<?php
session_start();
// set_time_limit(0);
// incrementar el tiempo de ejecucion del script
ini_set('max_execution_time', 5600);

include '../../conexion.php';
// Consexion a cronhis asistencial
$vigencia = $_SESSION['vigencia'];
// estraigo las variables que llegan por post en json
$fecha_inicial = $_POST['fecha_inicial'];
$fecha_corte = $_POST['fecha_final'];
$inicio = $_SESSION['vigencia'] . '-01-01';
$parametro = 2;
// contar los caracteres de $cuenta_ini
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "SELECT
                `taux`.`cuenta`
                , `taux`.`tipo`
                , SUM(`taux`.`debitoi`) AS `debitoi`
                , SUM(`taux`.`creditoi`) AS `creditoi`
                , SUM(`taux`.`debito`) AS `debito`
                , SUM(`taux`.`credito`) AS `credito`
            FROM
                (SELECT 
                    SUBSTRING(`ctb_pgcp`.`cuenta`, 1, 6) AS `cuenta`
                    , `ctb_pgcp`.`tipo_dato` AS `tipo`
                    , SUM(`t1`.`debitoi`) AS `debitoi`
                    , SUM(`t1`.`creditoi`) AS `creditoi`
                    , SUM(`t1`.`debito`) AS `debito`
                    , SUM(`t1`.`credito`) AS `credito`
                FROM
                    (SELECT
                        `ctb_libaux`.`id_cuenta`
                        , SUM(`ctb_libaux`.`debito`) AS `debitoi`
                        , SUM(`ctb_libaux`.`credito`) AS `creditoi`
                        , 0 AS `debito`
                        , 0 AS `credito`
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_doc`
                            ON `ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
                        INNER JOIN `ctb_pgcp`
                            ON `ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`
                    WHERE `ctb_doc`.`estado` = 2
                        AND ((SUBSTRING(`ctb_pgcp`.`cuenta`, 1, 1) IN ('1', '2', '3') AND `ctb_doc`.`fecha` < '$fecha_inicial')
                            OR
                        (SUBSTRING(`ctb_pgcp`.`cuenta`, 1, 1) IN ('4', '5', '6') AND `ctb_doc`.`fecha` < '$fecha_inicial' AND `ctb_doc`.`fecha` > '$inicio'))
                    GROUP BY `ctb_libaux`.`id_cuenta`
                    UNION ALL 
                    SELECT
                        `ctb_libaux`.`id_cuenta`
                        , 0 AS `debitoi`
                        , 0 AS `creditoi`
                        , SUM(`ctb_libaux`.`debito`) AS `debito`
                        , SUM(`ctb_libaux`.`credito`) AS `credito`
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_doc` 
                            ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `ctb_pgcp` 
                            ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    WHERE (`ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte' AND `ctb_doc`.`estado` = 2)
                    GROUP BY `ctb_libaux`.`id_cuenta`) AS `t1`
                    INNER JOIN `ctb_pgcp`
                        ON `t1`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`
                GROUP BY `t1`.`id_cuenta`) AS `taux`
            GROUP BY `taux`.`cuenta`
            ORDER BY `taux`.`cuenta`";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll();
} catch (Exception $e) {
    echo $e->getMessage();
}

$acum = [];

$nom_informe = "CONTADURÍA CGN";
include_once '../../financiero/encabezado_empresa.php';

?>
<table style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr>
            <td>FECHA INICIO</td>
            <td style='text-align: left;'><?php echo $fecha_inicial; ?></td>
            <td>FECHA FIN</td>
            <td style='text-align: left;'><?php echo $fecha_corte; ?></td>
        </tr>
    </thead>
</table>
<table style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr class="centrar">
            <td>Cuenta</td>
            <td>Inicial</td>
            <td>Debito</td>
            <td>Credito</td>
            <td>Saldo Final</td>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($datos)) {
            $separarCadenaResultados = [];
            foreach ($datos as $tp) {
                $cuenta = $tp['cuenta'];
                $nat = substr($cuenta, 0, 2);
                $naturaleza = ($nat[0] == '1' || $nat[0] == '5' || $nat[0] == '6' || $nat[0] == '7' || $nat == '81' || $nat == '83' || $nat == '99') ? "D" : "C";

                if (!isset($separarCadenaResultados[$cuenta])) {
                    $separarCadenaResultados[$cuenta] = SepararCadena($cuenta, $parametro);
                }

                $debitoi = $tp['debitoi'];
                $creditoi = $tp['creditoi'];
                $debito = $tp['debito'];
                $credito = $tp['credito'];

                $saldo_ini = ($naturaleza == "D") ? $debitoi - $creditoi : $creditoi - $debitoi;
                $saldo = $saldo_ini + (($naturaleza == "D") ? $debito - $credito : $credito - $debito);

                echo "<tr>
                        <td class='text'>" . $separarCadenaResultados[$cuenta] . "</td>
                        <td class='text-right'>" . Decimales($saldo_ini) . "</td>
                        <td class='text-right'>" . Decimales($debito) . "</td>
                        <td class='text-right'>" . Decimales($credito) . "</td>
                        <td class='text-right'>" . Decimales($saldo) . "</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No hay datos para mostrar</td></tr>";
        }
        ?>
    </tbody>
</table>
<?php
function SepararCadena($cadena, $tam)
{
    if (strlen($cadena) <= 1) {
        return $cadena;
    }
    $resultado = substr($cadena, 0, 1) . '.' . substr($cadena, 1, 1) . '.' . substr($cadena, 2, 2) . '.' . substr($cadena, 4, 2);
    $resto = substr($cadena, 6);
    for ($i = 0; $i < strlen($resto); $i += $tam) {
        $resultado .= '.' . substr($resto, $i, $tam);
    }
    return $resultado;
}

function Decimales($numero)
{
    return number_format($numero, 2, '.', '');
}
