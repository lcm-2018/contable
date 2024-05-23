<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';
// Div de acciones de la lista
$id_ctb_doc = $_POST['id_doc'];
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$dato = null;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`estado`
                , `nom_nominas`.`id_nomina`
                , `nom_nomina_pto_ctb_tes`.`tipo`
                , `ctb_doc`.`id_tipo_doc`
                , `ctb_doc`.`id_vigencia`
            FROM
                `ctb_doc`
                LEFT JOIN `nom_nomina_pto_ctb_tes` 
                    ON (`ctb_doc`.`id_ctb_doc` = `nom_nomina_pto_ctb_tes`.`ceva`)
                LEFT JOIN `nom_nominas` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar la fecha de cierre del periodo del módulo de presupuesto 
try {
    $sql = "SELECT `fecha_cierre` FROM `tb_fin_periodos` WHERE `id_modulo`= 6";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = $fecha_cierre['fecha_cierre'];
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    $ids = [];
    $id_cta = [];
    foreach ($listappto as $lp) {
        if ($lp['id_tercero'] !== null) {
            $ids[] = $lp['id_tercero'];
        }
        $id_cta[] = $lp['id_ctb_doc'];
    }
    $id_cta = implode(',', $id_cta);
    $payload = json_encode($ids);
    //API URL
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $terceros = json_decode($result, true);
    try {
        $sql = "SELECT 
                    `id_ctb_doc`
                    , SUM(`debito`) as `debito`
                    , SUM(`credito`) as `credito` 
                FROM `ctb_libaux` 
                WHERE `id_ctb_doc`IN ($id_cta) GROUP BY `id_ctb_doc`";
        $rs = $cmd->query($sql);
        $suma = $rs->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    foreach ($listappto as $lp) {
        $id_ctb = $lp['id_ctb_doc'];
        $estado = $lp['estado'];
        $enviar = NULL;
        $dato = null;
        // Buscar el nombre del tercero
        $key = array_search($lp['id_tercero'], array_column($terceros, 'id_tercero'));
        if ($key !== false) {
            $tercero = $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'];
            $ccnit = $terceros[$key]['cc_nit'];
        } else {
            $tercero = '';
        }
        if ($lp['tipo'] == 'N') {
            $enviar = '<button id ="enviar_' . $id_ctb . '" value="' . $lp['id_nomina'] . '" onclick="EnviarNomina(this)" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Procesar nómina (Soporte Electrónico)"><span class="fas fa-paper-plane fa-lg"></span></button>';
        }
        // fin api terceros
        $dif = 0;
        $key = array_search($id_ctb, array_column($suma, 'id_ctb_doc'));
        if ($key !== false) {
            $dif = $suma[$key]['debito'] - $suma[$key]['credito'];
        }
        if ($dif != 0) {
            $valor_total = 'Error';
        } else {
            $valor_total = number_format(!empty($suma) ? $suma[$key]['credito'] : 0, 2, ',', '.');
        }
        $fecha = date('Y-m-d', strtotime($lp['fecha']));


        // Sumar el valor del crp de la tabla id_pto_mtvo asociado al CDP
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        $editar = $detalles = $acciones = $borrar = null;
        if ($fecha <= $fecha_cierre) {
            $anular = null;
            $cerrar = null;
        } else {
            $anular = '<a value="' . $id_ctb . '" class="dropdown-item sombra " href="#" onclick="anularDocumentoTes(' . $id_ctb . ');">Anulación</a>';
        }
        if ((PermisosUsuario($permisos, 5601, 3) || $id_rol == 1)) {
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb modificar"  text="' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Detalles" onclick="cargarListaDetallePagoEdit(' . $id_ctb . ')"><span class="fas fa-eye fa-lg"></span></a>';
            $imprimir = '<a value="' . $id_ctb . '" onclick="imprimirFormatoTes(' . $lp['id_ctb_doc'] . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb " title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
            // Acciones teniendo en cuenta el tipo de rol
            //si es lider de proceso puede abrir o cerrar documentos
        }
        if ((PermisosUsuario($permisos, 5601, 4) || $id_rol == 1)) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarRegistroTec(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            if ($estado == 1) {
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoCtb(' . $id_ctb . ')" href="#">Cerrar documento</a>';
            } else {
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="abrirDocumentoTes(' . $id_ctb . ')" href="#">Abrir documento</a>';
            }
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
           ' . $cerrar . '
            ' . $anular . '
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Duplicar</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Parametrizar</a>
            </div>';
        }

        if ($estado >= 2) {
            $editar = null;
            $borrar = null;
        }
        if ($estado == 0) {
            $editar = null;
            $borrar = null;
            $imprimir = null;
            $acciones = null;
            $enviar = null;
            $dato = '<span class="badge badge-pill badge-danger">Anulado</span>';
        }
        /*
        if ($id_ctb == 3684) {
            $enviar = '<button id ="enviar_' . $id_ctb . '" value="14" onclick="EnviarNomina(this)" class="btn btn-outline-primary btn-sm bt-sm btn-circle shadow-gb"  title="Procesar nómina (Soporte Electrónico)"><span class="fas fa-paper-plane fa-lg"></span></button>';
        }
*/
        $data[] = [

            'numero' =>  $lp['id_manu'],
            'fecha' => $fecha,
            'ccnit' => $ccnit,
            'tercero' => $tercero,
            'valor' =>  '<div class="text-right">' . $valor_total . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $detalles . $borrar . $imprimir . $acciones . $enviar . $dato . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
