<?php

function sedes($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT tb_sedes.id_sede,tb_sedes.nom_sede FROM tb_sedes";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_sede']  == $id) {
                echo '<option value="' . $obj['id_sede'] . '" selected="selected">' . $obj['nom_sede'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_sede'] . '">' . $obj['nom_sede'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function sedes_usuario($cmd, $titulo = '', $id = 0)
{
    try {
        $idusr = $_SESSION['id_user'];
        $idrol = $_SESSION['rol'];
        echo '<option value="">' . $titulo . '</option>';
        if ($idrol == 1) {
            $sql = "SELECT tb_sedes.id_sede,tb_sedes.nom_sede FROM tb_sedes";
        } else {
            $sql = "SELECT tb_sedes.id_sede,tb_sedes.nom_sede FROM tb_sedes 
                    INNER JOIN seg_sedes_usuario ON (seg_sedes_usuario.id_sede=tb_sedes.id_sede AND seg_sedes_usuario.id_usuario=$idusr)";
        }
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_sede']  == $id) {
                echo '<option value="' . $obj['id_sede'] . '" selected="selected">' . $obj['nom_sede'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_sede'] . '">' . $obj['nom_sede'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function centros_costo($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id_centro,nom_centro FROM tb_centrocostos WHERE id_centro<>0";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_centro']  == $id) {
                echo '<option value="' . $obj['id_centro'] . '" selected="selected">' . $obj['nom_centro'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_centro'] . '">' . $obj['nom_centro'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function terceros($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id_tercero,nom_tercero FROM tb_terceros WHERE id_tercero<>0";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_tercero']  == $id) {
                echo '<option value="' . $obj['id_tercero'] . '" selected="selected">' . $obj['nom_tercero'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_tercero'] . '">' . $obj['nom_tercero'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function tipo_ingreso($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id_tipo_ingreso,nom_tipo_ingreso,es_int_ext FROM far_orden_ingreso_tipo";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            $dtad = 'data-intext="' . $obj['es_int_ext'] . '"';
            if ($obj['id_tipo_ingreso']  == $id) {
                echo '<option value="' . $obj['id_tipo_ingreso'] . '"' . $dtad . ' selected="selected">' . $obj['nom_tipo_ingreso'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_tipo_ingreso'] . '"' . $dtad . '>' . $obj['nom_tipo_ingreso'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function marcas($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id, descripcion FROM acf_marca WHERE id<>0";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id']  == $id) {
                echo '<option value="' . $obj['id'] . '" selected="selected">' . $obj['descripcion'] . '</option>';
            } else {
                echo '<option value="' . $obj['id'] . '">' . $obj['descripcion'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function estados_movimientos($titulo = '', $estado = 3)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($estado == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>PENDIENTE</option>';
    $selected = ($estado == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>CERRADO</option>';
    $selected = ($estado == 0) ? 'selected="selected"' : '';
    echo '<option value="0"' . $selected . '>ANULADO</option>';
}

function estados_pedidos($titulo = '', $estado = -1)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($estado == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>PENDIENTE</option>';
    $selected = ($estado == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>CONFIRMADO</option>';
    $selected = ($estado == 3) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>ACEPTADO</option>';
    $selected = ($estado == 4) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>CERRADO</option>';
    $selected = ($estado == 0) ? 'selected="selected"' : '';
    echo '<option value="0"' . $selected . '>ANULADO</option>';
}

function estado_general_activo($titulo = '', $estado = 3)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($estado == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>BUENO</option>';
    $selected = ($estado == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>REGULAR</option>';
    $selected = ($estado == 3) ? 'selected="selected"' : '';
    echo '<option value="3"' . $selected . '>MALO</option>';
    $selected = ($estado == 4) ? 'selected="selected"' : '';
    echo '<option value="4"' . $selected . '>FUERA DE SERVICO</option>';
}

function tipo_documento_activo($titulo = '', $estado = 3)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($estado == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>FICHA TECNICA</option>';
    $selected = ($estado == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>MANUAL</option>';
    $selected = ($estado == 3) ? 'selected="selected"' : '';
    echo '<option value="3"' . $selected . '>OTRO</option>';

}

function estado_activo($titulo = '', $estado = 0)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($estado == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>ACTIVO</option>';
    $selected = ($estado == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>EN MANTENIMIENTO</option>';
    $selected = ($estado == 3) ? 'selected="selected"' : '';
    echo '<option value="3"' . $selected . '>INACTIVO</option>';
    $selected = ($estado == 4) ? 'selected="selected"' : '';
    echo '<option value="4"' . $selected . '>DADO DE BAJA</option>';

}

function riesgos($titulo = '', $riesgo = 0)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($riesgo == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>ALTO</option>';
    $selected = ($riesgo == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>MEDIO</option>';
    $selected = ($riesgo == 3) ? 'selected="selected"' : '';
    echo '<option value="3"' . $selected . '>BAJO</option>';

}

function usos($titulo = '', $uso = 0)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($uso == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>MEDICO</option>';
    $selected = ($uso == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>BASICO</option>';
    $selected = ($uso == 3) ? 'selected="selected"' : '';
    echo '<option value="3"' . $selected . '>APOYO</option>';

}

function calif4725($titulo = '', $calif = 0)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($calif == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>I</option>';
    $selected = ($calif == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>IIA</option>';
    $selected = ($calif == 3) ? 'selected="selected"' : '';
    echo '<option value="3"' . $selected . '>IIB</option>';

}

function iva($valor = 0)
{
    $selected = ($valor == 0) ? 'selected="selected"' : '';
    echo '<option value="0"' . $selected . '>0</option>';
    $selected = ($valor == 5) ? 'selected="selected"' : '';
    echo '<option value="5"' . $selected . '>5</option>';
    $selected = ($valor == 19) ? 'selected="selected"' : '';
    echo '<option value="19"' . $selected . '>19</option>';
}

function tipos_activo($titulo = '', $valor = 0)
{
    echo '<option value="">' . $titulo . '</option>';
    $selected = ($valor == 1) ? 'selected="selected"' : '';
    echo '<option value="1"' . $selected . '>PROPIDAD, PLANTA Y EQUIPO</option>';
    $selected = ($valor == 2) ? 'selected="selected"' : '';
    echo '<option value="2"' . $selected . '>PROPIEDAD PARA LA VENTA</option>';
    $selected = ($valor == 3) ? 'selected="selected"' : '';
    echo '<option value="3"' . $selected . '>PROPIEDAD DE INVERSION</option>';
}


function articulosActivosFijos($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT FM.id_med,
                    FM.cod_medicamento,
                    FM.nom_medicamento
                FROM far_medicamentos FM 
                INNER JOIN far_subgrupos SG ON SG.id_subgrupo = FM.id_subgrupo 
                INNER JOIN far_grupos G ON G.id_grupo = SG.id_grupo
                WHERE FM.estado=1 AND G.id_grupo IN (3 , 4, 5)";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_med']  == $id) {
                echo '<option value="' . $obj['id_med'] . '" selected="selected">' . $obj['nom_medicamento'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_med'] . '">' . $obj['nom_medicamento'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function subgrupo_articulo($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT id_subgrupo,nom_subgrupo FROM far_subgrupos WHERE id_grupo IN (3,4,5)";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_subgrupo']  == $id) {
                echo '<option value="' . $obj['id_subgrupo'] . '" selected="selected">' . $obj['nom_subgrupo'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_subgrupo'] . '">' . $obj['nom_subgrupo'] . '</option>';
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
