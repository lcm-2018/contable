<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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
    if (!empty($dia)) {
        $last = $mes == '02' ? cal_days_in_month(CAL_GREGORIAN, 2, $vigencia) : $dia['fin_mes'];
    } else {
        $last = 0;
    }
    $fin_mes = !(empty($dia)) ? $vigencia . '-' . $mes . '-' . $last : 0;
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
                    , IFNULL(`t3`.`debito`,0) AS `debito_conciliado`
                    , IFNULL(`t3`.`credito`,0) AS `credito_conciliado`

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
                        ON (`t1`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    LEFT JOIN 
                        (SELECT
                            `tes_conciliacion`.`id_conciliacion`
                            , `tes_cuentas`.`id_cuenta`
                        FROM
                            `tes_conciliacion`
                            INNER JOIN `tes_cuentas` 
                                ON (`tes_conciliacion`.`id_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                        WHERE (`tes_conciliacion`.`mes` = '$mes' AND `tes_conciliacion`.`vigencia` = '$vigencia')) AS `t2`
                        ON (`t2`.`id_cuenta` = `t1`.`id_cuenta`)
                    LEFT JOIN 
                        (SELECT
                            `tes_conciliacion_detalle`.`id_concilia`
                            , SUM(`ctb_libaux`.`debito`) AS `debito`
                            , SUM(`ctb_libaux`.`credito`) AS `credito`
                        FROM
                            `tes_conciliacion_detalle`
                            INNER JOIN `ctb_libaux` 
                                ON (`tes_conciliacion_detalle`.`id_ctb_libaux` = `ctb_libaux`.`id_ctb_libaux`)
                        GROUP BY `tes_conciliacion_detalle`.`id_concilia`) AS `t3`
                        ON (`t3`.`id_concilia` = `t2`.`id_conciliacion`)";
        //        echo $sql;
        $rs = $cmd->query($sql);
        $lista = $rs->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $sql = "SELECT `id_cuenta`, `estado` FROM `tes_conciliacion` 
                WHERE `mes` = '$mes' AND `vigencia` = '$vigencia'";
        $rs = $cmd->query($sql);
        $estados = $rs->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // consutlar fin de cada mes 
    if (!empty($lista)) {
        foreach ($lista as $lp) {
            $estado = $editar = $borrar = $acciones = $cerrar = null;
            $id_ctb = $lp['id_tes_cuenta'];
            $key = array_search($id_ctb, array_column($estados, 'id_cuenta'));
            $estado = '<a href="javascript:void(0)" onclick="ConciliacionBancaria(' . $id_ctb . ')"><span class="badge badge-warning">Conciliar</span></a>';
            if ($key !== false) {
                if ($estados[$key]['estado'] == 1) {
                    $cerrar = '<a value="' . $id_ctb . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb" onclick="CerrarConciliacion(' . $id_ctb . ')" title="Cerrar"><span class="fas fa-unlock fa-lg"></span></a>';
                } else  if ($id_rol == 1) {
                    $cerrar = '<a value="' . $id_ctb . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" onclick="AbrirConciliacion(' . $id_ctb . ')" title="Abrir"><span class="fas fa-lock fa-lg"></span></a>';
                    $estado = '';
                }
                if ($estados[$key]['estado'] == 2) {
                    $estado = '';
                }
            }
            if (PermisosUsuario($permisos, 5606, 6) || $id_rol == 1) {
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
            // la raiz de $lp['cta_contable'] debe ser diferrente a 1105
            if (substr($lp['cta_contable'], 0, 4) != '1105') {
                $data[] = [
                    'banco' => $lp['nom_banco'],
                    'tipo' => $lp['tipo_cuenta'],
                    'nombre' => $lp['descripcion'],
                    'numero' => $lp['cta_contable'],
                    'saldo' => '<div class="text-right ' . $color . '">' . $signo . number_format($valor, 2, ',', '.') . '</div>',
                    'estado' => '<div class="text-center">' . $estado . '</div>',
                    'botones' => '<div class="text-center" style="position:relative">' . $imprimir . $cerrar . '</div>',
                ];
            }
        }
    }
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
