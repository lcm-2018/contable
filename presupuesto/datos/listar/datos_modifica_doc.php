<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);

// Div de acciones de la lista
$tipo_doc = $_POST['id_pto_doc'];
$id_pto_presupuestos = $_POST['id_pto_ppto'];
try {
    $sql = "SELECT
                `pto_mod`.`id_pto_mod`
                , `pto_mod`.`id_pto`
                , `pto_mod`.`id_tipo_acto`
                , `pto_mod`.`id_tipo_mod`
                , `pto_tipo_mvto`.`nombre`
                , `pto_mod`.`fecha`
                , `pto_mod`.`id_manu`
                , `pto_mod`.`objeto`
                , `pto_mod`.`estado`
                , `pto_actos_admin`.`nombre` AS `acto`
                , `pto_mod`.`numero_acto`
            FROM
                `pto_mod`
                INNER JOIN `pto_tipo_mvto` 
                    ON (`pto_mod`.`id_tipo_mod` = `pto_tipo_mvto`.`id_tmvto`)
                INNER JOIN `pto_actos_admin` 
                    ON (`pto_mod`.`id_tipo_acto` = `pto_actos_admin`.`id_acto`)
            WHERE `pto_mod`.`id_tipo_mod` = $tipo_doc AND `pto_mod`.`id_pto` = $id_pto_presupuestos
            ORDER BY `pto_mod`.`id_manu` ASC";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `pto_mod_detalle`.`id_pto_mod`
                , SUM(`pto_mod_detalle`.`valor_deb`) AS `debito`
                , SUM(`pto_mod_detalle`.`valor_cred`) AS `credito`
            FROM
                `pto_mod_detalle`
                INNER JOIN `pto_mod` 
                    ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
            WHERE (`pto_mod`.`id_tipo_mod` = $tipo_doc AND `pto_mod`.`estado` >= 1)
            GROUP BY `pto_mod_detalle`.`id_pto_mod`";
    $rs = $cmd->query($sql);
    $valores = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$num = 0;
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $dato = null;
        $id_pto = $lp['id_pto_mod'];
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        $key = array_search($id_pto, array_column($valores, 'id_pto_mod'));
        if ($key !== false) {
            $valor1 = $valores[$key]['debito'];
            $valor2 = $valores[$key]['credito'];
        } else {
            $valor1 = 0;
            $valor2 = 0;
        }
        $diferencia = $valor1 - $valor2;
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if ($fecha <= $fecha_cierre) {
            $anular = null;
        } else {
            if (PermisosUsuario($permisos, 5401, 5) || $id_rol == 1) {
                $anular = '<a value="' . $id_pto . '" class="dropdown-item sombra " href="#" onclick="anulacionCrp(' . $id_pto . ');">Anulación</a>';
            }
        }
        // Para el caso de los documentos aplazados
        if ($tipo_doc == '4' || $tipo_doc == '5') {
            $diferencia = 0;
        }
        if ($diferencia == 0) {
            $valor2 = number_format($valor2, 2, '.', ',');
            $estado = '<div class="text-right">' . $valor2 . '</div>';
        } else {
            $estado = '<div class="text-center"><span class="label text-danger">Incorrecto</span></div>';
        }

        if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
            if ($lp['estado'] == 0) {
                $cerrar = '<a value="' . $id_pto . '" class="dropdown-item sombra carga" onclick="abrirDocumentoMod(' . $id_pto . ')" href="#">Abrir documento</a>';
            } else {
                $cerrar = '<a value="' . $id_pto . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoMod(' . $id_pto . ')" href="#">Cerrar documento</a>';
            }
            /*
            if ($fecha < $fecha_cierre) {
                $cerrar = null;
            }*/
        } else {
            $cerrar = null;
        }
        if ($tipo_doc == 4) {
            $desaplazar = '<a value="' . $id_pto . '" class="dropdown-item sombra carga" onclick="redirecionarListaMod(' . $id_pto . ')" href="#">Desaplazar</a>';;
        } else {
            $desaplazar = null;
        }
        if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
            $detalles = '<a value="' . $id_pto . '" onclick="cargarListaDetalleMod(' . $id_pto . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Detalles"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            ' . $cerrar . '
            ' . $desaplazar . '
            ' . $anular . '
            </div>';
            /*
            if ($fecha < $fecha_cierre) {
                $detalles = null;
            }*/
        } else {
            $detalles = null;
        }
        if (PermisosUsuario($permisos, 5401, 6) || $id_rol == 1) {
            $imprimir = '<a value="' . $id_pto . '" onclick="imprimirFormatoMod(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
        }

        if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
            $borrar = '<a id ="eliminar_' . $id_pto . '" value="' . $id_pto . '" onclick="eliminarModPresupuestal(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            /*if ($fecha < $fecha_cierre) {
                $borrar = null;
            }*/
        } else {
            $borrar = null;
        }
        // verifico estado del documento
        if ($lp['estado'] == '0') {
            $borrar = null;
        }
        if ($lp['estado'] == 5) {
            $borrar = null;
            $detalles = null;
            $acciones = null;
            $imprimir = null;
            $dato = 'Anulado';
        }
        $num = $lp['id_manu'];
        $data[] = [
            'num' => $num,
            'fecha' => $fecha,
            'documento' => $lp['acto'],
            'numero' => $lp['numero_acto'],
            'valor' => $estado,
            'botones' => '<div class="text-center" style="position:relative">' . $borrar . $detalles . $imprimir . $acciones . $dato . '</div>',

        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];


echo json_encode($datos);
