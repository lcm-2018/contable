<?php

function cargar_opcion_csql($cmd, $titulo ='', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT seg_opciones.id_opcion,seg_modulos.nom_modulo FROM seg_opciones
                INNER JOIN seg_modulos ON (seg_modulos.id_modulo=seg_opciones.id_modulo)
                WHERE id_opcion LIKE '%99'";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_opcion']  == $id) {
                echo '<option value="' . $obj['id_opcion'] . '" selected="selected">' . $obj['nom_modulo'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_opcion'] . '">' . $obj['nom_modulo'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}