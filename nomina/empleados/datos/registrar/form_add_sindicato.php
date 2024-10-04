<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}

include '../../../../conexion.php';
$idemp  = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * 
            FROM nom_sindicatos
            ORDER BY nom_sindicato ASC";
    $rs = $cmd->query($sql);
    $listsind = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR CUOTA SINDICAL</h5>
        </div>
        <form id="formAddSindicato">
            <input type="number" id="idEmpSindicato" name="idEmpSindicato" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 py-2">
                <div class="form-group col-md-8">
                    <label class="small" for="slcSindicato">Sindicato</label>
                    <select id="slcSindicato" name="slcSindicato" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar Sindicato--</option>
                        <?php
                        foreach ($listsind as $ls) {
                            echo '<option value="' . $ls['id_sindicato'] . '">' . mb_strtoupper($ls['nom_sindicato']) . '</option>';
                        }
                        ?>
                    </select>
                    <div id="eslcSindicato" class="invalid-tooltip">
                        <?php echo 'Diligenciar este campo' ?>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label class="small" for="txtPorcentajeSind">Porcentaje %</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="txtPorcentajeSind" name="txtPorcentajeSind" placeholder="Ej: 10.5">
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label class="small" for="datFecInicioSind">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioSind" name="datFecInicioSind" value="<?php echo date('Y-m-d') ?>">
                        <div id="edatFecInicioSind" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label class="small" for="datFecFinSind">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinSind" name="datFecFinSind">
                        <div id="edatFecFinSind" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label class="small" for="datFecFinSind">Valor sindicalización</label>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm" id="numValSindicalizar" name="numValSindicalizar">
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddSindicato">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>