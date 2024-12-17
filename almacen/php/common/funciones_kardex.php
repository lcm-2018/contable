<?php

/*---------------------------------------------------
FUNCION DE RECALCULAR KARDEX
-----------------------------------------------------*/
function recalcular_kardex($cmd, $idlot, $tipo, $iding, $idegr, $idtra, $iddev, $fecini)
{
    //Consulta los lotes de medicamentos para recalcular Kardex
    $sql = "SELECT DISTINCT id_lote,id_med FROM far_kardex WHERE id_lote IN (" . $idlot . ") ORDER BY id_kardex";
    $rs = $cmd->query($sql);
    $objs_med = $rs->fetchAll();

    foreach ($objs_med as $med) {
        $existencia = 0;
        $promedio = 0;
        $existencia_lote = 0;
        $promedio_lote = 0;
        $id_karact = 0;

        //1. PARA UN LOTE ESPECIFICO CONSULTA EL ID. DE KARDEX A PARTIR DEL CUAL INICIA A RECALCULAR
        if ($tipo == 'I' || $tipo == 'E' || $tipo == 'T' || $tipo == 'D' || $tipo == 'O') {
            if ($tipo == 'I') {
                $sql = "SELECT id_kardex FROM far_kardex WHERE id_lote=" . $med['id_lote'] . " AND id_ingreso=" . $iding . " LIMIT 1";
            } elseif ($tipo == 'E') {
                $sql = "SELECT id_kardex FROM far_kardex WHERE id_lote=" . $med['id_lote'] . " AND id_egreso=" . $idegr . " LIMIT 1";
            } elseif ($tipo == 'T') {
                $sql = "SELECT id_kardex FROM far_kardex WHERE id_lote=" . $med['id_lote'] . " AND (id_ingreso_tra=" . $idtra . " OR id_egreso_tra=" . $idtra . ") LIMIT 1";
            } elseif ($tipo == 'D') {
                $sql = "SELECT far_kardex.id_kardex
                        FROM his_dev_pedido_detalle_cli
                        INNER JOIN his_pedido_detalle_cli ON (his_pedido_detalle_cli.id_ped_detalle = his_dev_pedido_detalle_cli.id_ped_detalle)
                        INNER JOIN adm_ingresos_detalle ON (adm_ingresos_detalle.id_ped_detalle = his_pedido_detalle_cli.id_ped_detalle)
                        INNER JOIN far_orden_egreso_detalle ON (far_orden_egreso_detalle.id_ing_detalle = adm_ingresos_detalle.id_ing_detalle)
                        INNER JOIN far_kardex ON (far_kardex.id_lote = far_orden_egreso_detalle.id_lote AND far_kardex.id_egreso = far_orden_egreso_detalle.id_egreso)
                        WHERE far_orden_egreso_detalle.id_lote=" . $med['id_lote'] . " AND his_dev_pedido_detalle_cli.id_dev_ped=" . $iddev . " LIMIT 1";
            } elseif ($tipo == 'O') {
                $sql = "SELECT id_kardex FROM far_kardex WHERE id_lote=" . $med['id_lote'] . " AND fec_movimiento >='" . $fecini . "' LIMIT 1";
            }

            $rs = $cmd->query($sql);
            $obj_karact = $rs->fetch();
            if ($obj_karact['id_kardex']) {
                $id_karact = $obj_karact['id_kardex'];
            }

            //Consulta la existencia Inicial de un LOTE
            $sql = "SELECT existencia_lote,val_promedio_lote  FROM far_kardex 
                    WHERE id_kardex = (SELECT MAX(id_kardex) FROM far_kardex WHERE id_lote=" . $med['id_lote'] . " AND id_kardex<" . $id_karact . " AND estado=1)";
            $rs = $cmd->query($sql);
            $obj_karlot = $rs->fetch();
            if ($obj_karlot) {
                $existencia_lote = $obj_karlot['existencia_lote'];
                $promedio_lote = $obj_karlot['val_promedio_lote'];
            }

            //Consulta la existencia y valor promedio inicial del MEDICAMENTO al que pertenece el lote
            $sql = "SELECT existencia, val_promedio FROM far_kardex 
                    WHERE id_kardex = (SELECT MAX(id_kardex) FROM far_kardex WHERE id_med=" . $med['id_med'] . " AND id_kardex<" . $id_karact . " AND estado=1)";
            $rs = $cmd->query($sql);
            $obj_karmed = $rs->fetch();
            if ($obj_karmed) {
                $existencia = $obj_karmed['existencia'];
                $promedio = $obj_karmed['val_promedio'];
            }
        }

        //2. RECORRE TODOS LOS MOVIMIENTOS DE UN MEDICAMENTO RECALCULANDO EL KARDEX
        $sql = "SELECT id_kardex,id_ingreso,id_egreso,id_ingreso_tra,id_egreso_tra,id_lote,id_egr_detalle
                FROM far_kardex 
                WHERE id_med=" . $med['id_med'] . " AND id_kardex>=" . $id_karact . " AND estado=1 
                ORDER BY id_kardex ASC";
        $rs = $cmd->query($sql);
        $objs_kar = $rs->fetchAll();

        foreach ($objs_kar as $kar) {

            if ($kar['id_ingreso']) {       //Si el movimiento es un Ingreso
                $sql = "SELECT far_orden_ingreso_detalle.cantidad*IFNULL(far_presentacion_comercial.cantidad,1) AS cantidad,
                            far_orden_ingreso_detalle.valor/IFNULL(far_presentacion_comercial.cantidad,1) AS valor 
                        FROM far_orden_ingreso_detalle 
                        INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_orden_ingreso_detalle.id_presentacion)
                        WHERE id_ingreso=" . $kar['id_ingreso'] . " AND id_lote=" . $kar['id_lote'];
                $rs = $cmd->query($sql);
                $obj_ing = $rs->fetch();

                //Calcula y Actualiza existencia y valor promedio del MEDICAMENTO y LOTE en el Kardex
                $total = $obj_ing['cantidad'] * $obj_ing['valor'] + $existencia * $promedio;
                $existencia = $existencia + $obj_ing['cantidad'];
                $promedio = $total / $existencia;

                $sql = "UPDATE far_kardex SET existencia=" . $existencia . ",val_promedio=" . $promedio . " WHERE id_kardex=" . $kar['id_kardex'];
                $rs = $cmd->query($sql);

                if ($kar['id_lote'] == $med['id_lote']) {
                    $total = $obj_ing['cantidad'] * $obj_ing['valor'] + $existencia_lote * $promedio_lote;
                    $existencia_lote = $existencia_lote + $obj_ing['cantidad'];
                    $promedio_lote = $total / $existencia_lote;

                    $sql = "UPDATE far_kardex SET can_ingreso=" . $obj_ing['cantidad'] . ",val_ingreso=" . $obj_ing['valor'] . ",
                                existencia_lote=" . $existencia_lote . ",val_promedio_lote=" . $promedio_lote . " WHERE id_kardex=" . $kar['id_kardex'];
                    $rs = $cmd->query($sql);
                }
            } elseif ($kar['id_egreso']) {      //Si el movimiento es un Egreso
                $sql = "SELECT cantidad FROM far_orden_egreso_detalle WHERE id_egr_detalle=" . $kar['id_egr_detalle'];
                $rs = $cmd->query($sql);
                $obj_egr = $rs->fetch();

                //Calcula y Actualiza existencia del MEDICAMENTO y LOTE en el Kardex
                $existencia = $existencia - $obj_egr['cantidad'];

                $sql = "UPDATE far_kardex SET existencia=" . $existencia . ",val_promedio=" . $promedio . " WHERE id_kardex=" . $kar['id_kardex'];
                $rs = $cmd->query($sql);

                if ($kar['id_lote'] == $med['id_lote']) {
                    $existencia_lote = $existencia_lote - $obj_egr['cantidad'];

                    $sql = "UPDATE far_kardex SET can_egreso=" . $obj_egr['cantidad'] . ",
                                existencia_lote=" . $existencia_lote . ",val_promedio_lote=" . $promedio_lote . " WHERE id_kardex=" . $kar['id_kardex'];
                    $rs = $cmd->query($sql);
                }

                // Actualiza Valores en la Orden de Egreso
                $sql = "UPDATE far_orden_egreso_detalle SET valor=" . $promedio . " WHERE id_egr_detalle=" . $kar['id_egr_detalle'];
                $rs = $cmd->query($sql);

                $sql = "SELECT SUM(cantidad*valor) AS total FROM far_orden_egreso_detalle WHERE id_egreso=" . $kar['id_egreso'];
                $rs = $cmd->query($sql);
                $obj_egrtot = $rs->fetch();

                $sql = "UPDATE far_orden_egreso SET val_total=" . $obj_egrtot['total'] . " WHERE id_egreso=" . $kar['id_egreso'];
                $rs = $cmd->query($sql);
            } elseif ($kar['id_egreso_tra']) {      //Si el movimiento es un traslado Egreso
                $sql = "SELECT cantidad FROM far_traslado_detalle WHERE id_traslado=" . $kar['id_egreso_tra'] . " AND id_lote_origen=" . $kar['id_lote'];
                $rs = $cmd->query($sql);
                $obj_egrtra = $rs->fetch();

                //Calcula y Actualiza existencia del MEDICAMENTO y LOTE en el Kardex
                $existencia = $existencia - $obj_egrtra['cantidad'];

                $sql = "UPDATE far_kardex SET existencia=" . $existencia . ",val_promedio=" . $promedio . " WHERE id_kardex=" . $kar['id_kardex'];
                $rs = $cmd->query($sql);

                if ($kar['id_lote'] == $med['id_lote']) {
                    $existencia_lote = $existencia_lote - $obj_egrtra['cantidad'];

                    $sql = "UPDATE far_kardex SET can_egreso=" . $obj_egrtra['cantidad'] . ",
                                existencia_lote=" . $existencia_lote . ",val_promedio_lote=" . $promedio_lote . " WHERE id_kardex=" . $kar['id_kardex'];
                    $rs = $cmd->query($sql);
                }

                // Actualiza Valores en el Traslado Egreso
                $sql = "UPDATE far_traslado_detalle SET valor=" . $promedio . " WHERE id_traslado=" . $kar['id_egreso_tra'] . " AND id_lote_origen=" . $kar['id_lote'];
                $rs = $cmd->query($sql);

                $sql = "SELECT SUM(cantidad*valor) AS total FROM far_traslado_detalle WHERE id_traslado=" . $kar['id_egreso_tra'];
                $rs = $cmd->query($sql);
                $obj_egrtratot = $rs->fetch();

                $sql = "UPDATE far_traslado SET val_total=" . $obj_egrtratot['total'] . " WHERE id_traslado=" . $kar['id_egreso_tra'];
                $rs = $cmd->query($sql);
            } elseif ($kar['id_ingreso_tra']) {     //Si el movimiento es un traslado Igreso
                $sql = "SELECT cantidad FROM far_traslado_detalle WHERE id_traslado=" . $kar['id_ingreso_tra'] . " AND id_lote_destino=" . $kar['id_lote'];
                $rs = $cmd->query($sql);
                $obj_ingtra = $rs->fetch();

                //Calcula y Actualiza existencia del MEDICAMENTO y LOTE en el Kardex
                $existencia = $existencia + $obj_ingtra['cantidad'];

                $sql = "UPDATE far_kardex SET existencia=" . $existencia . ",val_promedio=" . $promedio . " WHERE id_kardex=" . $kar['id_kardex'];
                $rs = $cmd->query($sql);

                if ($kar['id_lote'] == $med['id_lote']) {
                    $existencia_lote = $existencia_lote + $obj_ingtra['cantidad'];

                    $sql = "UPDATE far_kardex SET can_ingreso=" . $obj_ingtra['cantidad'] . ",val_ingreso=" . $promedio . ",
                                existencia_lote=" . $existencia_lote . ",val_promedio_lote=" . $promedio_lote . " WHERE id_kardex=" . $kar['id_kardex'];
                    $rs = $cmd->query($sql);
                }

                // Actualiza Valores en el Traslado Ingreso
                $sql = "UPDATE far_traslado_detalle SET valor=" . $promedio . " WHERE id_traslado=" . $kar['id_ingreso_tra'] . " AND id_lote_destino=" . $kar['id_lote'];
                $rs = $cmd->query($sql);

                $sql = "SELECT SUM(cantidad*valor) AS total FROM far_traslado_detalle WHERE id_traslado=" . $kar['id_ingreso_tra'];
                $rs = $cmd->query($sql);
                $obj_ingtratot = $rs->fetch();

                $sql = "UPDATE far_traslado SET val_total=" . $obj_ingtratot['total'] . " WHERE id_traslado=" . $kar['id_ingreso_tra'];
                $rs = $cmd->query($sql);
            }
        }

        //Actualiza Valor Promedio y Existencias Finales en el Medicamento y el LOTE
        $sql = "UPDATE far_medicamentos SET existencia=" . $existencia . ",val_promedio=" . $promedio . " WHERE id_med=" . $med['id_med'];
        $rs = $cmd->query($sql);
        $sql = "UPDATE far_medicamento_lote SET existencia=" . $existencia_lote . ",val_promedio=" . $promedio_lote . " WHERE id_lote=" . $med['id_lote'];
        $rs = $cmd->query($sql);
    }
}

/*-------------------------------------------------------------------------------------
FUNCIUON QUE VERIFICA SI AL ANULAR UN INGRESO O TRASLADO GENERA EXISTENCIAS NEGATIVAS
------------------------------------------------------------------------------------*/
function verificar_kardex($cmd, $id_ingreso, $tipo)
{
    $anular = 'ok';
    //Consulta movimientos de un tipo: I-Ingreso o T-Ingreso de Traslado
    if ($tipo == 'I') {
        $sql = "SELECT far_kardex.id_kardex,far_kardex.id_lote,far_kardex.can_ingreso,far_kardex.existencia_lote,
                    far_medicamento_lote.lote,far_medicamentos.nom_medicamento 
                FROM far_kardex 
                INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_kardex.id_lote)
                INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_kardex.id_med)
                WHERE far_kardex.id_ingreso=$id_ingreso ORDER BY far_kardex.id_kardex";
    } elseif ($tipo == 'T') {
        $sql = "SELECT far_kardex.id_kardex,far_kardex.id_lote,far_kardex.can_ingreso,far_kardex.existencia_lote,
                    far_medicamento_lote.lote,far_medicamentos.nom_medicamento 
                FROM far_kardex 
                INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_kardex.id_lote)
                INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_kardex.id_med)
                WHERE far_kardex.id_ingreso_tra= $id_ingreso ORDER BY far_kardex.id_kardex";
    }
    $rs = $cmd->query($sql);
    $objs_med = $rs->fetchAll();

    foreach ($objs_med as $med) {
        $existencia_lote = $med['existencia_lote'] - $med['can_ingreso'];

        //Recorre los movimientos de un Lote verificando Existencia        
        $sql = "SELECT id_ingreso,id_egreso,id_ingreso_tra,id_egreso_tra,can_ingreso,can_egreso 
                FROM far_kardex 
                WHERE id_lote=" . $med['id_lote'] . " AND id_kardex>" . $med['id_kardex'] . " AND estado=1 
                ORDER BY id_kardex ASC";
        $rs = $cmd->query($sql);
        $objs_kar = $rs->fetchAll();

        foreach ($objs_kar as $kar) {
            if ($kar['id_ingreso'] || $kar['id_ingreso_tra']) {
                $existencia_lote = $existencia_lote + $kar['can_ingreso'];
            } elseif ($kar['id_egreso'] || $kar['id_egreso_tra']) {
                $existencia_lote = $existencia_lote - $kar['can_egreso'];
            }

            //Evaluar si la existencia se vuelve negativa
            if ($existencia_lote < 0) {
                if ($anular == 'ok') {
                    $anular = 'Imposible anular el movimiento, los siguientes medicamentos generan existencias negativas.';
                    $anular .= '<br/><br/><label style="color:red">' . $med['lote'] . '</label> : ' . $med['nom_medicamento'];
                } else {
                    $anular .= '<br/><label style="color:red">' . $med['lote'] . '</label> : ' . $med['nom_medicamento'];
                }
                break;
            }
        }
    }
    return $anular;
}

/*-------------------------------------------------------------------------------------
FUNCIUON QUE VERIFICA SI AL CERRAR UN EGRESO O TRASLADO HAY EXISTENCIAS SUFICIENTES
------------------------------------------------------------------------------------*/
function verificar_existencias($cmd, $id, $tipo)
{
    $cerrar = 'ok';
    //Consulta movimientos de un tipo: E-Egreso o Un T-Traslado
    if ($tipo == 'E') {
        $sql = "SELECT far_medicamento_lote.id_lote,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,far_medicamento_lote.lote,
                    far_medicamento_lote.existencia,egr.cantidad
                FROM (SELECT id_lote,SUM(cantidad) AS cantidad FROM far_orden_egreso_detalle WHERE id_egreso=$id GROUP BY id_lote) AS egr
                INNER JOIN far_medicamento_lote ON(far_medicamento_lote.id_lote = egr.id_lote)
                INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_medicamento_lote.id_med)
                WHERE egr.cantidad>far_medicamento_lote.existencia";
    } elseif ($tipo == 'T') {
        $sql = "SELECT far_medicamento_lote.id_lote,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,far_medicamento_lote.lote,
                    far_medicamento_lote.existencia,tra.cantidad
                FROM (SELECT id_lote_origen,SUM(cantidad) AS cantidad FROM far_traslado_detalle WHERE id_traslado=$id GROUP BY id_lote_origen) AS tra
                INNER JOIN far_medicamento_lote ON(far_medicamento_lote.id_lote = tra.id_lote_origen)
                INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_medicamento_lote.id_med)
                WHERE tra.cantidad>far_medicamento_lote.existencia";
    }
    $rs = $cmd->query($sql);
    $objs_med = $rs->fetchAll();

    foreach ($objs_med as $med) {
        if ($cerrar == 'ok') {
            $cerrar = 'Imposible cerrar el movimiento, los siguientes medicamentos tiene existencias insuficientes.';
            $cerrar .= '<br/><br/><label style="color:red">' . $med['lote'] . '</label> : ' . $med['nom_medicamento'];
        } else {
            $cerrar .= '<br/><label style="color:red">' . $med['lote'] . '</label> : ' . $med['nom_medicamento'];
        }
    }
    return $cerrar;
}
