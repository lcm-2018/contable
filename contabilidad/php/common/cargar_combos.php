<?php
function regimenes($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id_regimen,descripcion_reg FROM tb_regimenes WHERE id_regimen<>0";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_regimen']  == $id) {
                echo '<option value="' . $obj['id_regimen'] . '" selected="selected">' . $obj['descripcion_reg'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_regimen'] . '">' . $obj['descripcion_reg'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function cobertura($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id_cobertura,nom_cobertura FROM tb_cobertura WHERE id_cobertura<>0";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_cobertura']  == $id) {
                echo '<option value="' . $obj['id_cobertura'] . '" selected="selected">' . $obj['nom_cobertura'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_cobertura'] . '">' . $obj['nom_cobertura'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function modalidad($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id_modalidad,nom_modalidad FROM tb_modalidad WHERE id_modalidad<>0";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_modalidad']  == $id) {
                echo '<option value="' . $obj['id_modalidad'] . '" selected="selected">' . $obj['nom_modalidad'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_modalidad'] . '">' . $obj['nom_modalidad'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function estados_registros($titulo = '',$estado = 2)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($estado == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>ACTIVO</option>';
    $selected = ($estado == 0) ? 'selected="selected"' : '';
    echo '<option value="0"' . $selected . '>INACTIVO</option>';
}

function estados_sino($titulo = '',$estado = 2)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($estado == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>SI</option>';
    $selected = ($estado == 0) ? 'selected="selected"' : '';
    echo '<option value="0"' . $selected . '>NO</option>';
}

function grupo_articulo($cmd, $titulo = '', $id = 0)
{
    /*Tipo  =0  Cuando se utiliza en formularios como filtro de búsqueda
            !=0 Cuando se utiliza en formularios como dato solicitado */
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id_grupo,nom_grupo FROM far_grupos WHERE id_grupo<>0";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_grupo']  == $id) {
                echo '<option value="' . $obj['id_grupo'] . '" selected="selected">' . $obj['nom_grupo'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_grupo'] . '">' . $obj['nom_grupo'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

