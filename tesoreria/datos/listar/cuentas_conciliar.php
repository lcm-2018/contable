<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$mes = $_POST['mes'];
$vigencia = $_SESSION['vigencia'];
$data = [];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT `fin_mes` FROM `nom_meses` WHERE (`codigo` = '$mes')";
    $rs = $cmd->query($sql);
    $dia = $rs->fetch(PDO::FETCH_ASSOC);
    $fin_mes = !(empty($dia)) ? $vigencia . '-' . $mes . '-' . $dia['fin_mes'] : 0;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($fin_mes != 0) {
    try {
        $sql = "SELECT
                    `tb_bancos`.`id_banco`
                    , `tes_cuentas`.`id_cuenta`
                    , `tes_cuentas`.`id_tes_cuenta`
                    , `tb_bancos`.`nom_banco`
                    , `tes_tipo_cuenta`.`tipo_cuenta`
                    , `tes_cuentas`.`numero`
                    , `tes_cuentas`.`nombre` AS `descripcion`
                    , `t1`. `debito`
                    , `t1`.`credito`
                    , `ctb_pgcp`.`cuenta` AS `cta_contable`
                FROM
                    `tes_cuentas`
                    INNER JOIN `ctb_pgcp` 
                        ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    INNER JOIN `tb_bancos` 
                        ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                    INNER JOIN `tes_tipo_cuenta` 
                        ON (`tes_cuentas`.`id_tipo_cuenta` = `tes_tipo_cuenta`.`id_tipo_cuenta`)
                    INNER JOIN 
                        (SELECT
                            `ctb_libaux`.`id_cuenta`
                            , SUM(`ctb_libaux`.`debito`) AS `debito` 
                            , SUM(`ctb_libaux`.`credito`) AS `credito`
                            , `ctb_doc`.`fecha`
                        FROM
                            `ctb_libaux`
                            INNER JOIN `ctb_doc` 
                                ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        WHERE (`ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` <= '$fin_mes')
                        GROUP BY `ctb_libaux`.`id_cuenta`)AS `t1`  
                        ON (`t1`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)";
        $rs = $cmd->query($sql);
        $lista = $rs->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // consutlar fin de cada mes 
    if (!empty($lista)) {
        foreach ($lista as $lp) {
            $editar = $borrar = $acciones = $cerrar = null;
            $id_ctb = $lp['id_tes_cuenta'];
            $estado = '<a href="javascript:void(0)" class="conciliar" text="' . $id_ctb . '"><span class="badge badge-success">Activa</span></a>';
            if (PermisosUsuario($permisos, 5606, 5) || $id_rol == 1) {
                $imprimir = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="ImpConcBanc(' . $id_ctb . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-print fa-lg"></span></a>';
                //si es lider de proceso puede abrir o cerrar documentos
            }
            $valor = $lp['debito'] - $lp['credito'];
            if ($valor < 0) {
                $valor = $valor * -1;
                $signo = '-$ ';
                $color = 'text-danger';
            } else {
                $signo = '$ ';
                $color = 'text-success';
            }
            $data[] = [
                'banco' => $lp['nom_banco'],
                'tipo' => $lp['tipo_cuenta'],
                'nombre' => $lp['descripcion'],
                'numero' => $lp['cta_contable'],
                'saldo' => '<div class="text-right ' . $color . '">' . $signo . number_format($valor, 2, ',', '.') . '</div>',
                'estado' => '<div class="text-center">' . $estado . '</div>',
                'botones' => '<div class="text-center" style="position:relative">' . $imprimir . '</div>',
            ];
        }
    }
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
