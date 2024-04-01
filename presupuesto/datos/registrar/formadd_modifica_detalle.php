<?php
include '../../../conexion.php';
?>
<div class="px-0">
    <div class="shadow">
        <form id="formAddValorCdp">
            <tr>
                <th>
                    <input type="hidden" id="id_pto_mvto" name="id_pto_mvto" value="">
                    <input type="text" name="rubroCod" id="rubroCod" class="form-control form-control-sm" value="">
                    <input type="hidden" name="id_rubroCod" id="id_rubroCod" class="form-control form-control-sm" value="">
                    <input type="hidden" name="id_pto_cdp" id="id_pto_cdp" value="">
                </th>
                <th>
                    <input type="text" name="valorCdp" id="valorCdp" class="form-control form-control-sm" size="6" value="" style="text-align: right;">
                </th>
                <th>
                    <input type="text" name="valorCdp" id="valorCdp" class="form-control form-control-sm" size="6" value="" style="text-align: right;">
                </th>
                <th class="text-center"><a id="registrar" class="btn btn-primary btn-sm">Registrar</a></th>
            </tr>
        </form>
    </div>
</div>