<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = $_POST['id'];
$parametros = json_decode($_POST['parametros']);

$limite = ' LIMIT 100';
if ($_POST['limite']) {
    if ($_POST['limite'] < 100){
        $limite = " LIMIT " . $_POST['limite'];
    }    
}

try {
    
    $sql = 'SELECT consulta,nom_consulta FROM tb_consultas_sql WHERE id_consulta=' . $id . ' LIMIT 1';
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cnsql = $obj['consulta'];
    $nom_consulta = $obj['nom_consulta'];

    foreach ($parametros as $pr) {
        $cnsql = str_replace('[' . $pr->parametro . ']', $pr->valor, $cnsql);
    }
    
    $sqlcount = "SELECT COUNT(*) AS count FROM ($cnsql) AS c2";
    $rs = $cmd->query($sqlcount);
    $obj = $rs->fetch();
    $total = $obj['count'];

    $rs1 = $cmd->query($cnsql . $limite);
    $objs = $rs1->fetchAll();
    $n = $rs1->columnCount(); 

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

    <?php include('../common/reporte_header.php'); ?>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th><?php echo $nom_consulta ?></th>
        </tr>     
    </table> 

    <div class="table-responsive">
    <table style="width:100% !important">
        <thead style="font-size:60%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <?php
                for ($i = 0; $i < $n; $i++):
                    $col = $rs1->getColumnMeta($i);
                    ?>
                    <th><?php echo $col['name'] ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $j = 0;
            foreach ($objs as $obj) :?>
                <tr>
                    <?php for ($i = 0; $i < $n; $i++) : ?>
                        <td><?php echo $obj[$i] ?></td>
                    <?php endfor; ?>
                </tr>
            <?php $j++;
            endforeach;?>      
        </tbody> 
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="<?php echo $n ?>" style="text-align:left">
                    No. de Registros visualizados: <?php echo count($objs); ?>
                    De un total de: <?php echo $total; ?>
                </td>
            </tr>
        </tfoot>       
    </table>
    </div>
</div>