<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//include_once $_SESSION['urlin'].'/conexion.php';
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
?>
<style>
    .resaltar:nth-child(even) {
        background-color: #F8F9F9;
    }

    .resaltar:nth-child(odd) {
        background-color: #ffffff;
    }

    .centrar {
        text-align: center;
    }
</style>
<table style="border-collapse: collapse;" width="100%">
    <thead>
        <tr>
            <td rowspan="4" colspan="2" style="text-align:center; max-width:20px;"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="99" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="99" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="99" style="text-align:center"><?php echo $nom_informe ?></td>
        </tr>
        <tr>
            <td colspan="99" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
    </thead>
</table>