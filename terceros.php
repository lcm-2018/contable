<?php
function getTerceros($ids, $cmd)
{
    $ids = $ids == '' ? '0' : $ids;
    try {
        $sql = "SELECT
                    `nom_tercero`,`nit_tercero`, `id_tercero_api`, `tel_tercero` AS `telefono`, `dir_tercero` AS `direccion`, `id_municipio` AS `municipio`
                FROM `tb_terceros`
                WHERE `id_tercero_api` IN ($ids)";
        $rs = $cmd->query($sql);
        $terceros = $rs->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    return $terceros;
}
