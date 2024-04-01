<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
function calcularDV($nit)
{
    if (!is_numeric($nit)) {
        return false;
    }

    $arr = array(
        1 => 3, 4 => 17, 7 => 29, 10 => 43, 13 => 59, 2 => 7, 5 => 19,
        8 => 37, 11 => 47, 14 => 67, 3 => 13, 6 => 23, 9 => 41, 12 => 53, 15 => 71
    );
    $x = 0;
    $y = 0;
    $z = strlen($nit);
    $dv = '';

    for ($i = 0; $i < $z; $i++) {
        $y = substr($nit, $i, 1);
        $x += ($y * $arr[$z - $i]);
    }

    $y = $x % 11;

    if ($y > 1) {
        $dv = 11 - $y;
        return $dv;
    } else {
        $dv = $y;
        return $dv;
    }
}
include '../../../conexion.php';
$mes = 02;
$serial="SERIAL NUEVO";
$vigencia = $_SESSION['vigencia'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT id_dependencia,nom_dependencia FROM tb_dependencias";
    $res = $cmd->query($sql);
    $objs = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="text-right py-3">
    <a type="button" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
    <a type="button" class="btn btn-primary btn-sm" id="btnImprimir">Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="content bg-light" id="areaImprimir">
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
            }
        }

        .resaltar:nth-child(even) {
            background-color: #F8F9F9;
        }

        .resaltar:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
    <table style="width:100% !important; border-collapse: collapse;">
        <thead style="background-color: white !important;font-size:80%">
            <tr style="padding: bottom 3px; color:black">
                <td colspan="10">
                    <table style="width:100% !important;">
                        <tr>
                            <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                            <td colspan="9" style="text-align:center">
                                <header><strong><?php echo "EMPRESA RED SALUD"; ?> </strong></header>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="9" style="text-align:center">
                                NIT <?php echo "EMPRESA"  . '-' .  "EMPRESA";  ?>
                            </td>
                        </tr>
                        <tr>                            
                            <td colspan="2" style="text-align:right">
                                Fec. Imp.: <?php echo date('Y/m/d'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" style="text-align:center">
                                DEPENDENCIAS
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background-color: #CED3D3; text-align:center">
                <th>ID</th>
                <th>Nombre</th>                
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php           
          $row_placa = '';                                      
                            foreach ($objs as $obj) {                              
                           $row_placa .=  '<tr class="resaltar" style="text-align:left">. 
                                <td>' .$obj['id_dependencia'] .'</td>
                                <td>' . mb_strtoupper($obj['nom_dependencia']). '</td>                               
                            </tr>';
                            }
                
            $tabla = '<tr style="font-size: 12px;" class="resaltar">              
                <th style="text-align: right;" colspan="1"></th>
            </tr>'.$row_placa ;
            echo $tabla;
            ?>
            <tr>
                <td colspan="10" style="height: 30px;"></td>
            </tr>
            <tr>
                <td colspan="10">
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="1">
                                Elaboró:
                            </td>
                            <td colspan="2">
                                _____________________________________________
                            </td>  
                        </tr>
                        <tr>
                            <td colspan="1">
                                Nombre:
                            </td>
                            <td colspan="2">
                                <?php echo "NOMBRE"; ?>
                            </td> 
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="n">
                    <div class="footer">
                        <div class="page-number"></div>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>