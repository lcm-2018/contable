<?php

session_start();
include '../../../conexion.php';
$id_rol = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_opciones`.`id_opcion`
                , IFNULL(`nom_opcion`, 0) AS `nom_opcion`
                , IFNULL(`per_consultar`, 0) AS `per_consultar`
                , IFNULL(`per_adicionar`, 0) AS `per_adicionar`
                , IFNULL(`per_modificar`, 0) AS `per_modificar`
                , IFNULL(`per_eliminar`, 0) AS `per_eliminar`
                , IFNULL(`per_anular`, 0) AS `per_anular`
                , IFNULL(`per_imprimir`, 0) AS `per_imprimir`
            FROM
                `seg_opciones`
            LEFT JOIN 
                (SELECT
                    `id_rol`
                    , `id_opcion`
                    , `per_consultar`
                    , `per_adicionar`
                    , `per_modificar`
                    , `per_eliminar`
                    , `per_anular`
                    , `per_imprimir`
                FROM
                    `seg_rol_permisos`
                WHERE (`id_rol` = $id_rol)) AS `t1`
                ON (`t1`.`id_opcion` = `seg_opciones`.`id_opcion`)
            ORDER BY `seg_opciones`.`id_opcion`";
    $rs = $cmd->query($sql);
    $opciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow mb-3">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;" class="mb-0"><i class="fas fa-user-lock fa-lg mr-3" style="color:#2FDA49"></i>ACTUALIZAR PERMISOS DE OPCIONES DE MÓDULO</p>
            </h5>
        </div>

        <div class="p-3">
            <input type="hidden" id="id_rol" value="<?php echo $id_rol ?>">
            <table id="tableOpcionesModuloRol" class="table-striped table-bordered table-sm nowrap" style="width:100%">
                <thead class="fixed-header">
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Opción</th>
                        <th class="text-center">Consultar</th>
                        <th class="text-center">Adicionar</th>
                        <th class="text-center">Modificar</th>
                        <th class="text-center">Eliminar</th>
                        <th class="text-center">Anular</th>
                        <th class="text-center">Imprimir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tipos = [
                        1 => 'per_consultar',
                        2 => 'per_adicionar',
                        3 => 'per_modificar',
                        4 => 'per_eliminar',
                        5 => 'per_anular',
                        6 => 'per_imprimir'
                    ];
                    foreach ($opciones as $op) {
                        $id_opc = $op['id_opcion'];
                        echo '<tr>';
                        echo '<td class="text-center">' . $id_opc . '</td>';
                        echo '<td class="text-left">' . mb_strtoupper($op['nom_opcion']) . '</td>';
                        for ($i = 1; $i <= 6; $i++) {
                            $tipo = $i;
                            $estado =  $op[$tipos[$i]];
                            if ($estado == 1) {
                                $title = 'Activo';
                                $icono = 'on';
                                $color = '#37E146';
                            } else {
                                $title = 'Inactivo';
                                $icono = 'off';
                                $color = 'gray';
                            }
                            $boton = '<a value="' . $id_opc . '|' . $tipo . '|' . $estado . '" class="btn btn-sm btn-circle estado" title="' . $title . '"><span class="fas fa-toggle-' . $icono . ' fa-2x" style="color:' . $color . ';"></span></a>';
                            echo '<td class="text-center">' . $boton . '</td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
    </div>
</div>