<?php

try {
    $sql = "SELECT
                 `razon_social_ips`AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver`
            FROM
                `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `fin_maestro_doc`.`control_doc`
                , `fin_maestro_doc`.`acumula`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`genero`
                , `fin_respon_doc`.`cargo`
                , `fin_respon_doc`.`tipo_control`
                , `fin_tipo_control`.`descripcion` AS `nom_control`
                , `fin_respon_doc`.`fecha_ini`
                , `fin_respon_doc`.`fecha_fin`
            FROM
                `fin_respon_doc`
                INNER JOIN `fin_maestro_doc` 
                    ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
                INNER JOIN `tb_terceros` 
                    ON (`fin_respon_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `fin_tipo_control` 
                    ON (`fin_respon_doc`.`tipo_control` = `fin_tipo_control`.`id_tipo`)
            WHERE (`fin_maestro_doc`.`id_modulo` = $id_modulo AND `fin_maestro_doc`.`id_doc_fte` = $doc_fte 
                AND `fin_respon_doc`.`fecha_fin` >= '$fecha' 
                AND `fin_respon_doc`.`fecha_ini` <= '$fecha'
                AND `fin_respon_doc`.`estado` = 1
                AND `fin_maestro_doc`.`estado` = 1)";
    $res = $cmd->query($sql);
    $responsables = $res->fetchAll();
    $key = array_search('4', array_column($responsables, 'tipo_control'));
    $nom_respon = $key !== false ? $responsables[$key]['nom_tercero'] : '';
    $cargo_respon = $key !== false ? $responsables[$key]['cargo'] : '';
    $gen_respon = $key !== false ? $responsables[$key]['genero'] : '';
    $control = $key !== false ? $responsables[$key]['control_doc'] : '';
    $control = $control == '' || $control == '0' ? false : true;
    if (!isset($responsables[0]['acumula'])) {
        $responsables[0]['acumula'] = 0;
    }
    $ver_acumula = $responsables[0]['acumula'] == 1 ?  true : false;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$where = '';
if ($ver_acumula) {
    $where = "AND `pto_cargue`.`tipo_dato` = 1";
}
$html = <<<HTML
<style>
    /* Estilos para la pantalla */
    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        /* Se añade rotación */
        font-size: 100px;
        color: rgba(255, 0, 0, 0.2);
        /* Cambia la opacidad para que sea tenue */
        z-index: 1000;
        pointer-events: none;
        /* Para que no interfiera con el contenido */
        white-space: nowrap;
        /* Evita que el texto se divida en varias líneas */
    }

    /*
    estilos para todas las tablas a imprimir, collapse y borde de color gris que tengan la clase bordes
    */

    .bordeado {
        border-collapse: collapse;
    }

    .bordeado th,
    .bordeado td {
        border: 1px solid #ccc;
        padding-left: 5px;
        padding-right: 5px;
    }

    /* Estilos específicos para la impresión */
    @media print {

        body {
            position: relative;
            margin: 0;
            padding: 0;
        }

        .watermark {
            position: fixed;
            /* Cambiar a 'fixed' para impresión */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(255, 0, 0, 0.2);
            /* Asegura que el color y opacidad se mantengan */
            z-index: -1;
            /* Colocar detrás del contenido impreso */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        tbody {
            display: table-row-group;
        }

        tfoot tr {
            page-break-inside: avoid;
            padding-bottom: 50px;
            width: 100%;
            text-align: center;
        }

        tr {
            page-break-inside: avoid;
        }
    }
</style>
<table style="width:100% !important;" border="0">
    <tr>
        <td style="width:25%">
            <label>
                <img src="../images/logos/logo.png" width="150">
            </label>
        </td>
        <td style="text-align:center">
            <strong>{$empresa['nombre']}</strong>
            <div>NIT {$empresa['nit']} - {$empresa['dig_ver']}</div>
        </td>
    </tr>
</table>
HTML;
