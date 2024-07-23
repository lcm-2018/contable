<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

try {
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
    
    $sql = 'SELECT consulta FROM tb_consultas_sql WHERE id_consulta=' . $id . ' LIMIT 1';
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cnsql = $obj['consulta'];

    foreach ($parametros as $pr) {
        $cnsql = str_replace('[' . $pr->parametro . ']', $pr->valor, $cnsql);
    }
    
    $sqlcount = "SELECT COUNT(*) AS count FROM ($cnsql) AS c2";
    $rs = $cmd->query($sqlcount);
    $obj = $rs->fetch();
    $total = $obj['count'];

    $rs = $cmd->query($cnsql . $limite);
    $objs = $rs->fetchAll();
    $n = $rs->columnCount();   
    ?>    

    <div class="table-responsive">
    <table id="tabla" class="table table-striped table-bordered table-sm" style="width:100%; font-size:80%">
        <thead>
            <tr id="encabezado">
                <?php
                for ($i = 0; $i < $n; $i++):
                    $col = $rs->getColumnMeta($i);
                    ?>
                    <th><?php echo $col['name'] ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
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
    </table>
    </div>
    <label><strong>No. Registros Visualizados:<?php echo $j ?></strong></label>
    <label><strong>De un Total de:<?php echo $total ?></strong></label>
    <?php
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getMessage();
}