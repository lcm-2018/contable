(function ($) {
    //Superponer modales
    $(".bttn-plus-dt span").html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
    $(document).on("show.bs.modal", ".modal", function () {
        var zIndex = 1040 + 10 * $(".modal:visible").length;
        $(this).css("z-index", zIndex);
        setTimeout(function () {
            $(".modal-backdrop")
                .not(".modal-stack")
                .css("z-index", zIndex - 1)
                .addClass("modal-stack");
        }, 0);
    });
    var showError = function (id) {
        $("#" + id).focus();
        $("#e" + id).show();
        setTimeout(function () {
            $("#e" + id).fadeOut(600);
        }, 800);
        return false;
    };
    var bordeError = function (p) {
        $("#" + p).css("border", "2px solid #F5B7B1");
        $("#" + p).css("box-shadow", "0 0 4px 3px pink");
        return false;
    };
    var reloadtable = function (nom) {
        $(document).ready(function () {
            var table = $("#" + nom).DataTable();
            table.ajax.reload();
        });
    };
    var confdel = function (i, t) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "../nomina/empleados/eliminar/confirdel.php",
            data: { id: i, tip: t },
        }).done(function (res) {
            $("#divModalConfDel").modal("show");
            $("#divMsgConfdel").html(res.msg);
            $("#divBtnsModalDel").html(res.btns);
        });
        return false;
    };
    //Separadores de mil
    var miles = function (i) {
        $("#" + i).on({
            focus: function (e) {
                $(e.target).select();
            },
            keyup: function (e) {
                $(e.target).val(function (index, value) {
                    return value
                        .replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                });
            },
        });
    };
    $('#areaReporte').on('click', '#btnExcelEntrada', function () {
        let tableHtml = $('#areaImprimir').html();
        let encodedTable = btoa(unescape(encodeURIComponent(tableHtml)));
        $('<form action="' + window.urlin + '/financiero/reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encodedTable + '" /></form>').appendTo('body').submit();
    });
    $('#areaReporte').on('click', '#btnPlanoEntrada', function () {
        let tableHtml = $('#areaImprimir').html();

        let tempDiv = $('<div>').html(tableHtml);

        let plainText = '';
        let rowCount = 0;
        tempDiv.find('tr').each(function () {
            rowCount++;
            // Si la fila actual es mayor que 5, entonces la procesamos
            if (rowCount > 5) {
                $(this).find('td, th').each(function () {
                    plainText += $(this).text() + '\t';
                });
                plainText = plainText.trim(); // Eliminar la última tabulación
                plainText += '\n';
            }
        });

        // Codificar el texto en Base64
        let encodedTable = btoa(unescape(encodeURIComponent(plainText)));

        // Enviar el formulario con el contenido codificado
        $('<form action="' + window.urlin + '/financiero/reporte_txt.php" method="post"><input type="hidden" name="txt" value="' + encodedTable + '" /></form>').appendTo('body').submit();
    });
    // Valido que el numerico con separador de miles
    $("#divModalForms").on("keyup", "#valorAprob", function () {
        let id = "valorAprob";
        miles(id);
    });
    // Valido que el valor del cdp sea numerico con separador de miles
    $("#divCuerpoPag").on("keyup", "#valorCdp", function () {
        let id = "valorCdp";
        miles(id);
    });
    // Si el campo es mayor desactiva valor aprobado
    $("#divModalForms").on("focus", "#valorAprob", function () {
        let valor = $("#tipoDato").val();
        let estado = $("#estadoPresupuesto").val();
        if (valor == "0" || valor == "A" || estado == "0") {
            $(this).prop("disabled", true);
            $(this).val('');
        } else {
            $(this).prop("disabled", false);
        }
    });
    $("#divModalForms").on("blur", "#tipoDato", function () {
        let valor = $("#tipoDato").val();
        let estado = $("#estadoPresupuesto").val();
        if (valor == "0" || valor == "A" || estado == "0") {
            $('#valorAprob').prop("disabled", true);
            $('#valorAprob').val('');
        } else {
            $('#valorAprob').prop("disabled", false);
        }
    });
    // Validar formulario nuevo rubros
    $("#divModalForms").on("blur", "#nomCod", function () {
        let id = "nomCod";
        let valor = $("#" + id).val();
        let pto = id_pto.value;
        //Enviar valor y consultar si ya existe en la base de datos
        $.ajax({
            type: "POST",
            url: "datos/consultar/buscar_rubro.php",
            data: { valor: valor, pto: pto },
            success: function (res) {
                if (res === "ok") {
                    $("#" + id).focus();
                    $("#divModalError").modal("show");
                    $("#divMsgError").html("¡El codigo presupuestal ya fue registrado!");
                } else {
                    //Dividir cadena con -
                    let cadena = res.split("-");
                    $("#tipoPresupuesto").val(cadena[1]);
                    $("#tipoRecurso").val(cadena[0]);
                }
            },
        });
    });
    var setIdioma = {
        decimal: "",
        emptyTable: "No hay información",
        info: "Mostrando _START_ - _END_ registros de _TOTAL_ ",
        infoEmpty: "Mostrando 0 to 0 of 0 Entradas",
        infoFiltered: "",
        infoPostFix: "",
        thousands: ",",
        lengthMenu: "Ver _MENU_ Filas",
        loadingRecords: "Cargando...",
        processing: "Procesando...",
        search: '<i class="fas fa-search fa-flip-horizontal" style="font-size:1.5rem; color:#2ECC71;"></i>',
        zeroRecords: "No se encontraron registros",
        paginate: {
            first: "&#10096&#10096",
            last: "&#10097&#10097",
            next: "&#10097",
            previous: "&#10096",
        },
    };
    var setdom;
    if ($("#peReg").val() === "1") {
        setdom = "<'row'<'col-md-5'l><'bttn-plus-dt col-md-2'B><'col-md-5'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    } else {
        setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    }
    $(document).ready(function () {
        let id_t = $("#id_ptp").val();
        //================================================================================ DATA TABLES ========================================
        //dataTable de presupuesto
        $("#tablePresupuesto").DataTable({
            dom: setdom,
            buttons: [
                {
                    text: ' <span class="fas fa-plus-circle fa-lg"></span>',
                    action: function (e, dt, node, config) {
                        $.post("datos/registrar/formadd_presupuesto.php", function (he) {
                            $("#divTamModalForms").removeClass("modal-xl");
                            $("#divTamModalForms").removeClass("modal-sm");
                            $("#divTamModalForms").addClass("modal-lg");
                            $("#divModalForms").modal("show");
                            $("#divForms").html(he);
                        });
                    },
                },
            ],
            language: setIdioma,
            ajax: {
                url: "datos/listar/datos_presupuestos.php",
                type: "POST",
                dataType: "json",
            },
            columns: [{ data: "id_pto" }, { data: "nombre" }, { data: "tipo" }, { data: "vigencia" }, { data: "botones" }],
            order: [[0, "asc"]],
        });
        $("#tablePresupuesto").wrap('<div class="overflow" />');
        //dataTable cargue de presupuesto
        let id_cpto = $("#id_pto_ppto").val();
        let id_ppto = $("#id_pto_ppto").val();

        $("#tableCargaPresupuesto").DataTable({
            dom: setdom,
            buttons: [
                {
                    text: ' <span class="fas fa-plus-circle fa-lg"></span>',
                    action: function (e, dt, node, config) {
                        $.post("datos/registrar/formadd_carga_presupuesto.php", { id_cpto: id_cpto, id_ppto: id_ppto }, function (he) {
                            $("#divTamModalForms").removeClass("modal-lg");
                            $("#divTamModalForms").removeClass("modal-sm");
                            $("#divTamModalForms").addClass("modal-xl");
                            $("#divModalForms").modal("show");
                            $("#divForms").html(he);
                        });
                    },
                },
            ],
            language: setIdioma,
            ajax: {
                url: "datos/listar/datos_carga_presupuesto.php",
                data: { id_cpto: id_cpto },
                type: "POST",
                dataType: "json",
            },
            columns: [{ data: "rubro" }, { data: "nombre" }, { data: "tipo_dato" }, { data: "valor" }, { data: "botones" }],
            order: [],
        });
        $("#tableCargaPresupuesto").wrap('<div class="overflow" />');
        //dataTable ejecucion de presupuesto
        let id_ejec = $("#id_pto_ppto").val();
        var tableEjecPresupuesto = $("#tableEjecPresupuesto").DataTable({
            dom: setdom,
            buttons: [
                {
                    text: ' <span class="fas fa-plus-circle fa-lg"></span>',
                    action: function (e, dt, node, config) {
                        $.post("datos/registrar/formadd_cdp.php", { id_pto: id_ejec }, function (he) {
                            $("#divTamModalForms").removeClass("modal-xl");
                            $("#divTamModalForms").removeClass("modal-sm");
                            $("#divTamModalForms").addClass("modal-lg");
                            $("#divModalForms").modal("show");
                            $("#divForms").html(he);
                        });
                    },
                },
            ],
            language: setIdioma,
            serverSide: true,
            processing: true,
            ajax: {
                url: "datos/listar/datos_ejecucion_presupuesto.php",
                data: function (d) {
                    // datos para enviar al servidor
                    d.id_ejec = id_ejec;
                    d.start = d.start || 0; // inicio de la página
                    d.length = d.length || 50; // tamaño de la página
                    d.search = $("#tableEjecPresupuesto_filter input").val();
                    return d;
                },
                type: "POST",
                dataType: "json",
            },
            columns: [{ data: "numero" }, { data: "fecha" }, { data: "objeto" }, { data: "valor" }, { data: "xregistrar" }, { data: "accion" }, { data: "botones" }],
            order: [[0, "desc"]],
            pageLength: 25,
        });
        // Control del campo de búsqueda
        $('#tableEjecPresupuesto_filter input').unbind(); // Desvinculamos el evento por defecto
        $('#tableEjecPresupuesto_filter input').bind('keypress', function (e) {
            if (e.keyCode == 13) { // Si se presiona Enter (código 13)
                tableEjecPresupuesto.search(this.value).draw(); // Realiza la búsqueda y actualiza la tabla
            }
        });
        $("#tableEjecPresupuesto").wrap('<div class="overflow" />');

        //dataTable detalle CDP
        let id_ejec2 = $("#id_pto_cdp").val();
        let id_cdp_eac = $("#id_cdp").val();
        let id_adq_eac = $("#id_adq").length ? $("#id_adq").val() : 0;
        $("#tableEjecCdp").DataTable({
            language: setIdioma,
            ajax: {
                url: "datos/listar/datos_detalle_cdp.php",
                data: { id_pto: id_ejec2, id_cdp: id_cdp_eac, id_adq: id_adq_eac },
                type: "POST",
                dataType: "json",
            },
            columns: [
                { data: "id" },
                { data: "rubro" },
                { data: "valor" },
                { data: "botones" }
            ],
            order: [[0, "asc"]],
            ordering: false,
            columnDefs: [
                {
                    targets: [0],
                    visible: false,
                }
            ],
        });
        $("#tableEjecCdp").wrap('<div class="overflow" />');

        //dataTable ejecucion de presupuesto listado de reistros presupuestales
        var tableEjecPresupuestoCrp = $("#tableEjecPresupuestoCrp").DataTable({
            dom: setdom,
            buttons: [
                {
                    text: ' <span class="fas fa-plus-circle fa-lg"></span>',
                    action: function (e, dt, node, config) {
                        $.post("datos/registrar/formadd_crp.php", { id_ejec: id_ejec }, function (he) {
                            $("#divTamModalForms").removeClass("modal-sm");
                            $("#divTamModalForms").removeClass("modal-xl");
                            $("#divTamModalForms").addClass("modal-lg");
                            $("#divModalForms").modal("show");
                            $("#divForms").html(he);
                        });
                    },
                },
            ],
            language: setIdioma,
            serverSide: true,
            processing: true,
            ajax: {
                url: "datos/listar/datos_ejecucion_presupuesto_crp.php",
                data: function (d) {
                    // datos para enviar al servidor
                    d.id_ejec = id_ejec;
                    d.start = d.start || 0; // inicio de la página
                    d.length = d.length || 50; // tamaño de la página
                    d.search = $("#tableEjecPresupuestoCrp_filter input").val();
                    return d;
                },
                type: "POST",
                dataType: "json",
            },
            columns: [
                { data: "numero" },
                { data: "cdp" },
                { data: "fecha" },
                { data: "contrato" },
                { data: "ccnit" },
                { data: "tercero" },
                { data: "valor" },
                { data: "botones" },
            ],
            order: [[0, "desc"]],
            pageLength: 25,
        });
        // Control del campo de búsqueda
        $('#tableEjecPresupuestoCrp_filter input').unbind(); // Desvinculamos el evento por defecto
        $('#tableEjecPresupuestoCrp_filter input').bind('keypress', function (e) {
            if (e.keyCode == 13) { // Si se presiona Enter (código 13)
                tableEjecPresupuestoCrp.search(this.value).draw(); // Realiza la búsqueda y actualiza la tabla
            }
        });
        $("#tableEjecPresupuestoCrp").wrap('<div class="overflow" />');

        //dataTable ejecucion de presupuesto listado de reistros presupuestales cuando es nuevo
        let id_cdp = $("#id_cdp").val();
        let crp = $("#id_crp").val();
        $("#tableEjecCrpNuevo").DataTable({
            language: setIdioma,
            ajax: {
                url: "datos/listar/datos_detalle_crp_nuevo.php",
                data: { id_cdp: id_cdp, id_crp: crp },
                type: "POST",
                dataType: "json",
            },
            columns: [{ data: "rubro" }, { data: "valor" }, { data: "botones" }],
            order: [[0, "desc"]],
        });
        $("#tableEjecCrpNuevo").wrap('<div class="overflow" />');
        //dataTable ejecucion de presupuesto listado de reistros presupuestales existente
        let id_crp = $("#id_pto_doc").val();
        $("#tableEjecCrp").DataTable({
            language: setIdioma,
            ajax: {
                url: "datos/listar/datos_detalle_crp.php",
                data: { id_crp: id_crp },
                type: "POST",
                dataType: "json",
            },
            columns: [{ data: "rubro" }, { data: "valor" }, { data: "botones" }],
            order: [[0, "asc"]],
        });
        $("#tableEjecCrp").wrap('<div class="overflow" />');
        //dataTable modificaciones presupuesto
        let id_pto_doc = $("#id_pto_doc").val();
        let id_pto_ppto = $("#id_pto_ppto").val();
        let id_mov = $("#id_mov").val();
        $("#tableModPresupuesto").DataTable({
            dom: setdom,
            buttons: [
                {
                    text: ' <span class="fas fa-plus-circle fa-lg"></span>',
                    action: function (e, dt, node, config) {
                        if ($('#id_pto_doc').val() == '0') {
                            $("#divModalError").modal("show");
                            $("#divMsgError").html("¡Debe seleccionar  un movimiento!");
                        } else {
                            $.post("datos/registrar/formadd_modifica_presupuesto_doc.php", { id_mov: id_mov, id_pto: id_pto_ppto }, function (he) {
                                $("#divTamModalForms").removeClass("modal-sm");
                                $("#divTamModalForms").removeClass("modal-xl");
                                $("#divTamModalForms").addClass("modal-lg");
                                $("#divModalForms").modal("show");
                                $("#divForms").html(he);
                            });
                        }
                    },
                },
            ],
            language: setIdioma,
            ajax: {
                url: "datos/listar/datos_modifica_doc.php",
                data: { id_pto_doc: id_pto_doc, id_pto_ppto: id_pto_ppto },
                type: "POST",
                dataType: "json",
            },
            columns: [{ data: "num" }, { data: "fecha" }, { data: "documento" }, { data: "numero" }, { data: "valor" }, { data: "botones" }],
            order: [[0, "asc"]],
        });
        $("#tableModPresupuesto").wrap('<div class="overflow" />');

        //dataTable modificación de presupuesto detalle de modificaciones
        let id_pto_mod = $("#id_pto_mod").val();
        let tipo_doc = $("#tipo_doc").val();
        let id_pto = $("#id_pto_movto").val();
        $("#tableModDetalle").DataTable({
            language: setIdioma,
            ajax: {
                url: "datos/listar/datos_modifica_det.php",
                data: { id_pto_mod: id_pto_mod, id_pto: id_pto },
                type: "POST",
                dataType: "json",
            },
            columns: [{ data: "id" }, { data: "rubro" }, { data: "valor" }, { data: "valor2" }, { data: "botones" }],
            ordering: false,
            columnDefs: [
                {
                    targets: [0],
                    visible: false,
                }
            ],
            scrollHeight: 10,
        });
        $("#tableModDetalle").wrap('<div class="overflow" />');

        //dataTable modificación de presupuesto detalle de modificaciones
        $("#tableAplDetalle").DataTable({
            language: setIdioma,
            ajax: {
                url: "datos/listar/datos_modifica_apl.php",
                data: { id_pto_mod: id_pto_mod, tipo_mod: tipo_doc },
                type: "POST",
                dataType: "json",
            },
            columns: [{ data: "rubro" }, { data: "valor" }, { data: "valor2" }, { data: "botones" }],
            order: [[0, "asc"]],
        });
        $("#tableAplDetalle").wrap('<div class="overflow" />');

        //Fin dataTable *****************************************************************************************
    });
    //===================================================================================== INSERT
    //Agregar nuevo Presupuesto
    $("#divForms").on("click", "#btnAddPresupuesto", function () {
        if ($("#nomPto").val() === "") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El nombre de presupuesto no puede estar vacio 1!");
        } else if ($("#tipoPto").val() === "") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡Tipo de presupuesto no puede ser Vacío 2!");
        } else if ($("#tipoPto").val() === "0") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡Tipo de presupuesto no puede ser Vacío 3!");
        } else {
            datos = $("#formAddPresupuesto").serialize();
            $.ajax({
                type: "POST",
                url: "datos/registrar/new_presupuesto.php",
                data: datos,
                success: function (r) {
                    if (r === "1") {
                        let id = "tablePresupuesto";
                        reloadtable(id);
                        $("#divModalForms").modal("hide");
                        $("#divModalDone").modal("show");
                        $("#divMsgDone").html("Adquisición Agregada Correctamente");
                    } else {
                        $("#divModalError").modal("show");
                        $("#divMsgError").html(r);
                    }
                },
            });
        }
        return false;
    });
    // Agregar nuevo cargue de rubros del presupuestos
    $("#divForms").on("click", "#btnCargaPresupuesto", function () {
        let value = $(this).attr('text');
        let id_tipoRubro = $("#tipoDato").val();
        let estado = $("#estadoPresupuesto").val();
        let codigo = $("#nomCod").val();
        var campos = codigo.length;
        if ($("#nomCod").val() === "") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El rubro no puede estar vacio!");
        } else if ($("#nomRubro").val() === "") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El nombre del rubro no puede estar vacio!");
        } else if ($("#tipoDato").val() === "A") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡Tipo de dato no puede ser vacio!");
        } else if ($("#valorAprob").val() === "" && id_tipoRubro === "1" && estado === "1") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El valor no puede estar vacio!");
        } else if ($("#tipoRecurso").val() === "" && campos > 1) {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El tipo de recurso no puede estar vacio!");
        } else if ($("#tipoPresupuesto").val() === "" && campos > 1) {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El tipo de presupuesto estar vacio!");
        } else {
            var url;
            var datos;
            if (value == '1') {
                datos = $("#formAddCargaPresupuesto").serialize();
                url = "datos/registrar/new_carga_presupuesto.php";
                msg = "agregado";
            } else {
                datos = $("#formUpCargaPresupuesto").serialize();
                url = "datos/actualizar/up_carga_presupuesto.php";
                msg = "modificado";
            }
            $.ajax({
                type: "POST",
                url: url,
                data: datos,
                success: function (r) {
                    if (r === "ok") {
                        let id = "tableCargaPresupuesto";
                        reloadtable(id);
                        $("#divModalForms").modal("hide");
                        $("#divModalDone").modal("show");
                        $("#divMsgDone").html("Rubro " + msg + " correctamente...");
                    } else {
                        $("#divModalError").modal("show");
                        $("#divMsgError").html(r);
                    }
                },
            });
        }
        return false;
    });
    // Agregar ejcución a presupuesto CDP
    $("#divForms").on("click", "#btnGestionCDP", function () {
        var op = $(this).attr('text');
        $('.is-invalid').removeClass('is-invalid');
        if ($("#dateFecha").val() === "") {
            $("#dateFecha").focus();
            $("#dateFecha").addClass('is-invalid');
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡La fecha no puede estar vacio!");
        } else if ($("#dateFecha").val() <= $('#fec_cierre').val()) {
            $("#dateFecha").focus();
            $("#dateFecha").addClass('is-invalid');
            $("#divModalError").modal("show");
            $("#divMsgError").html("Fecha debe ser mayor a la fecha de cierre del presupuesto:<br> <b>" + $('#fec_cierre').val()) + "</b>";
        } else if ($("#id_manu").val() === "") {
            $("#id_manu").focus();
            $("#id_manu").addClass('is-invalid');
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El numero de CDP no puede estar vacio!");
        } else if ($("#txtObjeto").val() === "") {
            $("#txtObjeto").focus();
            $("#txtObjeto").addClass('is-invalid');
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El objeto no puede ser vacio!");
        } else {
            var datos, url;
            if (op == 1) {
                datos = $("#formAddCDP").serialize()
                url = "datos/registrar/new_ejecucion_presupuesto.php";
            } else {
                datos = $("#formUpCDP").serialize()
                url = "datos/actualizar/up_ejecucion_presupuesto.php";
            }
            $.ajax({
                type: "POST",
                url: url,
                data: datos,
                dataType: "json",
                success: function (r) {
                    if (r.status === "ok") {
                        let id = "tableEjecPresupuesto";
                        reloadtable(id);
                        $("#divModalForms").modal("hide");
                        $("#divModalDone").modal("show");
                        $("#divMsgDone").html("Proceso realizado correctamente...");
                    } else {
                        $("#divModalError").modal("show");
                        $("#divMsgError").html(r.msg);
                    }
                },
            });
        }
        return false;
    });
    // Agregar cargue de rubros al CDP
    $("#divCuerpoPag").on("click", "#btnAddValorCdp", function () {
        if ($("#id_rubroCod").val() == "") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡Debe seleccionar un rubro...!");
        } else if ($("#valorCdp").val() == "") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El valor a registrar no debe estar vacio!");
        } else if ($("#tipoRubro").val() == 0) {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El rubro seleccionado es de tipo mayor!");
        } else {
            datos = $("#formAddValorCdp").serialize();
            $.ajax({
                type: "POST",
                url: "datos/registrar/new_ejecucion_presupuesto.php",
                data: datos,
                success: function (r) {
                    let cadena = r.split("-");
                    if (cadena[0] === "ok") {
                        let id = "tableEjecutaPresupuesto";
                        reloadtable(id);
                        $("#divModalForms").modal("hide");
                        $("#divModalDone").modal("show");
                        $("#divMsgDone").html("Rubro agregado correctamente...");
                        // Redireccionar a la pagina de presupuestos
                        $('<form action="lista_ejecucion_cdp.php" method="post"><input type="hidden" name="id_cdp" value="' + cadena[1] + '" /></form>')
                            .appendTo("body")
                            .submit();
                    } else {
                        $("#divModalError").modal("show");
                        $("#divMsgError").html(r);
                    }
                },
            });
        }
        return false;
    });

    $("#cerrarPresupuestos").on("click", function () {
        var idPto = $('#idPtoEstado').val();
        $.ajax({
            type: 'POST',
            url: 'datos/actualizar/update_estado_pto.php',
            data: { idPto: idPto },
            success: function (r) {
                if (r == 'ok') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Se ha cerrado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    //========================================================================================  FORM UPDATE */
    //1. Editar Presupuesto llama formulario
    $("#modificarPresupuesto").on("click", ".editar", function () {
        let idtbs = $(this).attr("value");
        $.post("datos/actualizar/edita_presupuesto.php", { idtbs: idtbs }, function (he) {
            $("#divTamModalForms").removeClass("modal-sm");
            $("#divTamModalForms").removeClass("modal-xl");
            $("#divTamModalForms").addClass("modal-lg");
            $("#divModalForms").modal("show");
            $("#divForms").html(he);
        });
    });
    //1.1. ejecuta editar presupuesto
    $("#divForms").on("click", "#btnUpdatePresupuesto", function () {
        if ($("#nomPto").val() === "") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡El nombre de presupuesto no puede estar vacio!");
        } else if ($("#tipoPto").val() === "") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡Tipo de presupuesto no puede ser Vacío!");
        } else if ($("#tipoPto").val() === "0") {
            $("#divModalError").modal("show");
            $("#divMsgError").html("¡Tipo de presupuesto no puede ser Vacío!");
        } else {
            datos = $("#formUpdatePresupuesto").serialize();
            $.ajax({
                type: "POST",
                url: "datos/actualizar/update_presupuesto.php",
                data: datos,
                success: function (r) {
                    if (r === "1") {
                        let id = "tablePresupuesto";
                        reloadtable(id);
                        $("#divModalForms").modal("hide");
                        $("#divModalDone").modal("show");
                        $("#divMsgDone").html("Adquisición Actualizada Correctamente");
                    } else {
                        $("#divModalError").modal("show");
                        $("#divMsgError").html(r);
                    }
                },
            });
        }
        return false;
    });
    //2. Editar detalles de CDP
    $("#modificarEjecPresupuesto").on("click", ".editar", function () {
        let id_cdp = $(this).attr("value");
        $.post("datos/actualizar/formup_cdp.php", { id_cdp: id_cdp }, function (he) {
            $("#divTamModalForms").removeClass("modal-xl");
            $("#divTamModalForms").removeClass("modal-sm");
            $("#divTamModalForms").addClass("modal-lg");
            $("#divModalForms").modal("show");
            $("#divForms").html(he);
        });
    });
    $("#modificarEjecPresupuesto").on("click", ".detalles", function () {
        let id_cdp = $(this).attr("value");
        let id_ppto = $("#id_pto_ppto").val();
        // Redireccionar a la pagina de presupuestos
        $(
            '<form action="lista_ejecucion_cdp.php" method="post"><input type="hidden" name="id_cdp" value="' +
            id_cdp +
            '" /><input type="hidden" name="id_ejec" value="' +
            id_ppto +
            '" /></form>'
        )
            .appendTo("body")
            .submit();
    });
    //===================================================================================== ELIMINAR
    // Eliminar presupuesto anexa campo a la etiqueta
    $("#modificarPresupuesto").on("click", ".borrar", function () {
        let id = $(this).attr("value");
        let tip = "ppto";
        confdel(id, tip);
    });
    //Eliminar presupuesto
    $("#divBtnsModalDel").on("click", "#btnConfirDelppto", function () {
        let id = $(this).attr('value');
        $("#divModalConfDel").modal("hide");
        $.ajax({
            type: "POST",
            url: "datos/eliminar/del_presupuestos.php",
            data: { id: id },
            success: function (r) {
                if (r === "ok") {
                    let id = "tablePresupuesto";
                    reloadtable(id);
                    $("#divModalDone").modal("show");
                    $("#divMsgDone").html("Presupuesto eliminado correctamente");
                } else {
                    $("#divModalError").modal("show");
                    $("#divMsgError").html(r);
                }
            },
        });
        return false;
    });
    // Eliminar cargue de presupuestos
    $("#modificarCargaPresupuesto").on("click", ".borrar", function () {
        let id = $(this).attr("value");
        let tip = "carga";
        confdel(id, tip);
    });
    $("#modificarCargaPresupuesto").on("click", ".editar", function () {
        let id = $(this).attr("value");
        $.post("datos/actualizar/formup_carga_presupuesto.php", { id: id }, function (he) {
            $("#divTamModalForms").removeClass("modal-xl");
            $("#divTamModalForms").removeClass("modal-sm");
            $("#divTamModalForms").addClass("modal-lg");
            $("#divModalForms").modal("show");
            $("#divForms").html(he);
        });
    });
    //Eliminar cargue de presupuestos
    $("#divBtnsModalDel").on("click", "#btnConfirDelcarga", function () {
        $("#divModalConfDel").modal("hide");
        let id_cargue = $(this).attr('value');
        $.ajax({
            type: "POST",
            url: "datos/eliminar/del_carga_presupuesto.php",
            data: { id_cargue: id_cargue },
            success: function (r) {
                if (r === "1") {
                    let id = "tableCargaPresupuesto";
                    reloadtable(id);
                    $("#divModalDone").modal("show");
                    $("#divMsgDone").html("Cargue de presupuesto eliminado correctamente");
                } else {
                    $("#divModalError").modal("show");
                    $("#divMsgError").html(r);
                }
            },
        });
        return false;
    });

    //==========================================================================  Menu Gestión cargue presupuesto */
    // 1. Agregar cargue presupuesto
    $("#modificarPresupuesto").on("click", ".carga", function () {
        let id_pto = $(this).attr("value");
        $('<form action="lista_cargue_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
            .appendTo("body")
            .submit();
    });
    // 2. Agregar ejecucion al presupuesto cuando es gastos
    $("#modificarPresupuesto").on("click", ".ejecuta", function () {
        let id_pto = $(this).attr("value");
        $('<form action="lista_ejecucion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
            .appendTo("body")
            .submit();
    });
    $("#modificarPresupuesto").on("click", ".homologa", function () {
        let id_pto = $(this).attr("value");
        $('<form action="lista_homologacion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
            .appendTo("body")
            .submit();
    });
    // 3. Agregar modificaciones al presupuestos
    $("#modificarPresupuesto").on("click", ".modifica", function () {
        let id_pto = $(this).attr("value");
        $('<form action="lista_modificacion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
            .appendTo("body")
            .submit();
    });
    // 4. Volver de edición de cdp a listado de documentos cdp
    $("#divCuerpoPag").on("click", "#volverListaCdps", function () {
        let id_pto = $("#id_pto_presupuestos").val();
        $('<form action="lista_ejecucion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
            .appendTo("body")
            .submit();
    });

    // Cargar lista_ejecucion_contratacion.php por ajax
    $("#divCuerpoPag").on("click", "#botonContrata", function () {
        $.post("lista_ejecucion_contratacion.php", {}, function (he) {
            $("#divTamModalForms").removeClass("modal-sm");
            $("#divTamModalForms").removeClass("modal-lg");
            $("#divTamModalForms").addClass("modal-xl");
            $("#divModalForms").modal("show");
            $("#divForms").html(he);
        });
    });
    // funcion imprimir arrow

    // Cargar lista_ejecucion_contratacion.php por ajax
    $("#divCuerpoPag").on("click", "#botonListaCdp", function () {
        $.post("lista_espacios_cdp.php", {}, function (he) {
            $("#divTamModalForms").removeClass("modal-sm");
            $("#divTamModalForms").removeClass("modal-lg");
            $("#divTamModalForms").addClass("modal-xl");
            $("#divModalForms").modal("show");
            $("#divForms").html(he);
        });
    });

    // Cargar lista de solicitudes para cdp de otro si
    $("#divCuerpoPag").on("click", "#botonOtrosi", function () {
        $.post("lista_modificacion_otrosi.php", {}, function (he) {
            $("#divTamModalForms").removeClass("modal-sm");
            $("#divTamModalForms").removeClass("modal-lg");
            $("#divTamModalForms").addClass("modal-xl");
            $("#divModalForms").modal("show");
            $("#divForms").html(he);
        });
    });
    $('#cargaExcelPto').on('click', function () {
        $.post("datos/registrar/form_cargar_pto.php", function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnAddPtoExcel', function () {
        if ($('#file').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe elegir un archivo!');
        } else {
            let archivo = $('#file').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (!(ext === '.xlsx' || ext === '.xls')) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Solo se permite documentos .xlsx!');
                return false;
            } else if ($('#file')[0].files[0].size > 2097152) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Documento debe tener un tamaño menor a 2Mb!');
                return false;
            }
            var btns = '<button class="btn btn-primary btn-sm" id="btnConfirCargaPto">Aceptar</button><button type="button" class="btn btn-secondary  btn-sm"  data-dismiss="modal">Cancelar</button>'
            $("#divModalConfDel").modal("show");
            $("#divMsgConfdel").html('Esta acción eliminará el cargue actual de presupuesto.<br> Confirmar.');
            $("#divBtnsModalDel").html(btns);
            $('#divModalConfDel').on('click', '#btnConfirCargaPto', function () {
                $("#divModalConfDel").modal("hide");
                let datos = new FormData();
                datos.append('file', $('#file')[0].files[0]);
                datos.append('idPto', $('#idPtoEstado').val());
                $('#btnAddPtoExcel').attr('disabled', true);
                $('#btnAddPtoExcel').html('<i class="fas fa-spinner fa-pulse"></i> Cargando...');
                $.ajax({
                    type: 'POST',
                    url: 'datos/registrar/cargar_pto_excel.php',
                    contentType: false,
                    data: datos,
                    processData: false,
                    cache: false,
                    success: function (r) {
                        $('#btnAddPtoExcel').attr('disabled', false);
                        $('#btnAddPtoExcel').html('Subir');
                        if (r == 'ok') {
                            reloadtable('tableCargaPresupuesto');
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Presupuesto Cargado Correctamente');
                        } else {
                            $('#divModalForms').modal('hide');
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            });
            return false;
        }
        return false;
    });
})(jQuery);

const imprimirFormatoCdp = (id) => {
    let url = "soportes/imprimir_formato_cdp.php";
    $.post(url, { id: id }, function (he) {
        $("#divTamModalForms").removeClass("modal-sm");
        $("#divTamModalForms").removeClass("modal-xl");
        $("#divTamModalForms").addClass("modal-lg");
        $("#divModalForms").modal("show");
        $("#divForms").html(he);
    });
};

const imprimirFormatoMod = (id) => {
    let url = "soportes/imprimir_formato_mod.php";
    $.post(url, { id: id }, function (he) {
        $("#divTamModalForms").removeClass("modal-sm");
        $("#divTamModalForms").removeClass("modal-xl");
        $("#divTamModalForms").addClass("modal-lg");
        $("#divModalForms").modal("show");
        $("#divForms").html(he);
    });
};

const imprimirFormatoCrp = (id) => {
    if (id == "") {
        id = id_pto_save.value;
    }
    if (id == "") {
    } else {
        let url = "soportes/imprimir_formato_crp.php";
        $.post(url, { id: id }, function (he) {
            $("#divTamModalForms").removeClass("modal-sm");
            $("#divTamModalForms").removeClass("modal-xl");
            $("#divTamModalForms").addClass("modal-lg");
            $("#divModalForms").modal("show");
            $("#divForms").html(he);
        });
    }
};
function imprSelecCdp(nombre, id) {
    if (Number(id) > 0) {
        cerrarCDP(id);
    }
    var ficha = document.getElementById(nombre);
    var ventimp = window.open(" ", "popimpr");
    ventimp.document.write(ficha.innerHTML);
    ventimp.document.close();
    ventimp.print();
    ventimp.close();
}
function imprSelecCrp(nombre, id) {
    if (Number(id) > 0) {
        cerrarCRP(id);
    }
    var ficha = document.getElementById(nombre);
    var ventimp = window.open(" ", "popimpr");
    ventimp.document.write(ficha.innerHTML);
    ventimp.document.close();
    ventimp.print();
    ventimp.close();
}

var reloadtable = function (nom) {
    $(document).ready(function () {
        var table = $("#" + nom).DataTable();
        table.ajax.reload();
    });
};
// Mensaje
function mje(titulo) {
    Swal.fire({
        title: titulo,
        icon: "success",
        showConfirmButton: true,
        timer: 1000,
    });
}
// funcion valorMiles
function milesp(i) {
    $("#" + i).on({
        focus: function (e) {
            $(e.target).select();
        },
        keyup: function (e) {
            $(e.target).val(function (index, value) {
                return value
                    .replace(/\D/g, "")
                    .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
            });
        },
    });
}
// Funcion para redireccionar la recarga de la pagina
function redireccionar(ruta) {
    console.log(ruta);
    setTimeout(() => {
        $(
            '<form action="' +
            ruta.url +
            '" method="post">\n\
    <input type="hidden" name="' +
            ruta.name +
            '" value="' +
            ruta.valor +
            '" />\n\
    </form>'
        )
            .appendTo("body")
            .submit();
    }, 100);
}

function redireccionar2(ruta) {
    setTimeout(() => {
        $(
            '<form action="' +
            ruta.url +
            '" method="post">\n\
    <input type="hidden" name="' +
            ruta.name1 +
            '" value="' +
            ruta.valor1 +
            '" />\n\
    <input type="hidden" name="' +
            ruta.name2 +
            '" value="' +
            ruta.valor2 +
            '" />\n\
    </form>'
        )
            .appendTo("body")
            .submit();
    }, 100);
}

function valorMiles(id) {
    console.log("valor" + id);
    milesp(id);
}
/*  ========================================================= Certificado de disponibilidad presupuestal ========================================================= */
// mostrar list_Ejecucion_cdp.php
function mostrarListaCdp(dato) {
    let ppto = id_pto_ppto.value;
    let ruta = {
        url: "lista_ejecucion_cdp.php",
        name1: "id_adq",
        valor1: dato,
        name2: "id_ejec",
        valor2: ppto,
    };
    redireccionar2(ruta);
}
$("#modificaHomologaPto").on('input', '.homologaPTO', function () {
    var elemento = $(this).parent();
    var tipo = $(this).attr("tipo");
    var inputHidden = elemento.find('input[type="hidden"]');
    var name = inputHidden.attr("name");
    var pto = $('#id_pto_tipo').val();
    $(this).autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "datos/listar/data_homologacion.php",
                dataType: "json",
                type: 'POST',
                data: { term: request.term, tipo: tipo, pto: pto },
                success: function (data) {
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            $('input[name="' + name + '"]').val(ui.item.id);
        }
    });
});
$('#tableHomologaPto').on('click', '#desmarcar', function () {
    var elemento = $(this);
    $('.dupLine').each(function () {
        if ($(this).is(':checked')) {
            $(this).prop("checked", false);
        }
    });

    elemento.prop("checked", false);
});
$('#modificaHomologaPto').on('click', '.dupLine', function () {
    var elemento = $(this);
    var id = $(this).val();
    var cgr = cpc = fte = tercero = politica = siho = sia = situacion = vig = secc = sect = csia = '0';
    var txtcgr = txtcpc = txtfte = txttercero = txtpolitica = txtsiho = txtsia = txtvig = txtsecc = txtsect = txtcsia = '';
    var ppto = $('#id_pto_tipo').val();
    if (elemento.is(':checked')) {
        $('#desmarcar').prop("checked", true);
        $('.dupLine').each(function () {
            var id_pto = $(this).val();
            if ($(this).is(':checked')) {
                cgr = $('input[name="codCgr[' + id_pto + ']"]').val();
                txtcgr = $('input[name="uno[' + id_pto + ']"]').val();
                cpc = $('input[name="cpc[' + id_pto + ']"]').val();
                txtcpc = $('input[name="cinco[' + id_pto + ']"]').val();
                fte = $('input[name="fuente[' + id_pto + ']"]').val();
                txtfte = $('input[name="seis[' + id_pto + ']"]').val();
                tercero = $('input[name="tercero[' + id_pto + ']"]').val();
                txttercero = $('input[name="siete[' + id_pto + ']"]').val();
                politica = $('input[name="polPub[' + id_pto + ']"]').val();
                txtpolitica = $('input[name="ocho[' + id_pto + ']"]').val();
                siho = $('input[name="siho[' + id_pto + ']"]').val();
                txtsiho = $('input[name="nueve[' + id_pto + ']"]').val();
                sia = $('input[name="sia[' + id_pto + ']"]').val();
                txtsia = $('input[name="diez[' + id_pto + ']"]').val();
                if (ppto == '2') {
                    vig = $('input[name="vigencia[' + id_pto + ']"]').val();
                    txtvig = $('input[name="dos[' + id_pto + ']"]').val();
                    secc = $('input[name="seccion[' + id_pto + ']"]').val();
                    txtsecc = $('input[name="tres[' + id_pto + ']"]').val();
                    sect = $('input[name="sector[' + id_pto + ']"]').val();
                    txtsect = $('input[name="cuatro[' + id_pto + ']"]').val();
                    csia = $('input[name="csia[' + id_pto + ']"]').val();
                    txtcsia = $('input[name="once[' + id_pto + ']"]').val();
                } else {
                    vig = $('select[name="vigencia[' + id_pto + ']"]').val();
                }
                situacion = $('select[name="situacion[' + id_pto + ']"]').val();
                return false;

            }
        });
        $('input[name="codCgr[' + id + ']"]').val(cgr);
        $('input[name="uno[' + id + ']"]').val(txtcgr);
        $('input[name="cpc[' + id + ']"]').val(cpc);
        $('input[name="cinco[' + id + ']"]').val(txtcpc);
        $('input[name="fuente[' + id + ']"]').val(fte);
        $('input[name="seis[' + id + ']"]').val(txtfte);
        $('input[name="tercero[' + id + ']"]').val(tercero);
        $('input[name="siete[' + id + ']"]').val(txttercero);
        $('input[name="polPub[' + id + ']"]').val(politica);
        $('input[name="ocho[' + id + ']"]').val(txtpolitica);
        $('input[name="siho[' + id + ']"]').val(siho);
        $('input[name="nueve[' + id + ']"]').val(txtsiho);
        $('input[name="sia[' + id + ']"]').val(sia);
        $('input[name="diez[' + id + ']"]').val(txtsia);
        $('select[name="situacion[' + id + ']"]').val(situacion);
        if (ppto == '2') {
            $('input[name="vigencia[' + id + ']"]').val(vig);
            $('input[name="dos[' + id + ']"]').val(txtvig);
            $('input[name="seccion[' + id + ']"]').val(secc);
            $('input[name="tres[' + id + ']"]').val(txtsecc);
            $('input[name="sector[' + id + ']"]').val(sect);
            $('input[name="cuatro[' + id + ']"]').val(txtsect);
            $('input[name="csia[' + id + ']"]').val(csia);
            $('input[name="once[' + id + ']"]').val(txtcsia);
        } else {
            $('select[name="vigencia[' + id + ']"]').val(vig)
        }
    }
});
$('#setHomologacionPto').on('click', '', function () {
    var valida = 1;
    $('.is-invalid').removeClass('is-invalid');
    $('.validaPto').each(function () {
        var celda = $(this).parent();
        if ($(this).val() == 0) {
            celda.find('.homologaPTO').focus();
            celda.find('.homologaPTO').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe diligenciar este campo');
            valida = 0;
            return false;
        }
    });
    if (valida == 1) {
        var data = $('#formDataHomolPto').serialize();
        $.ajax({
            type: 'POST',
            url: 'datos/actualizar/update_homologacion.php',
            data: data,
            success: function (r) {
                if (r.trim() === 'ok') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Homologación realizada correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    }
});
$('#divModalForms').on('click', '#registrarModificaPto', function () {
    $('.is-invalid').removeClass('is-invalid');
    if ($('#fecha').val() == '') {
        $('#fecha').addClass('is-invalid');
        $('#fecha').focus();
        $('#divModalError').modal('show');
        $('#divMsgError').html('La fecha no puede estar vacia');
    } else if ($('#tipo_acto').val() == '0') {
        $('#tipo_acto').addClass('is-invalid');
        $('#tipo_acto').focus();
        $('#divModalError').modal('show');
        $('#divMsgError').html('Debe seleccionar un tipo de acto');
    } else if ($('#numMod').val() == '') {
        $('#numMod').addClass('is-invalid');
        $('#numMod').focus();
        $('#divModalError').modal('show');
        $('#divMsgError').html('El número de acto no puede estar vacio');
    } else {
        var datos = $('#formAddModificaPresupuesto').serialize();
        $.ajax({
            type: 'POST',
            url: 'datos/registrar/registrar_modifica_pto_doc.php',
            data: datos,
            success: function (r) {
                if (r == 'ok') {
                    $('#tableModPresupuesto').DataTable().ajax.reload();
                    $('#divModalForms').modal('hide');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Registrado correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    }
});
// genera cdp y rp para nomina
//--!EDWIN
$("#divCuerpoPag").on("click", "#btnPtoNomina", function () {
    $.post("lista_ejecucion_nomina.php", {}, function (he) {
        $("#divTamModalForms").removeClass("modal-sm");
        $("#divTamModalForms").removeClass("modal-lg");
        $("#divTamModalForms").addClass("modal-xl");
        $("#divModalForms").modal("show");
        $("#divForms").html(he);
    });
});
function CofirmaCdpRp(boton) {
    var cant = document.getElementById("cantidad");
    var valor = Number(cant.value);
    var data = boton.value;
    var datos = data.split("|");
    var tipo = datos[1];
    var ruta = "";
    if (tipo == "PL") {
        ruta = "procesar/causacion_planilla.php";
    } else {
        ruta = "procesar/causacion_nomina.php";
    }
    Swal.fire({
        title: "¿Confirma asignacion de CPD y RP para Nómina?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#00994C",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si!",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {
            boton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            boton.disabled = true;
            fetch(ruta, {
                method: "POST",
                body: data,
            })
                .then((response) => response.text())
                .then((response) => {
                    if (response == "ok") {
                        boton.innerHTML = '<span class="fas fa-thumbs-up fa-lg"></span>';
                        cant.value = valor - 1;
                        document.getElementById("nCant").innerHTML = valor - 1;
                        let tabla = "tableEjecPresupuesto";
                        reloadtable(tabla);
                        $("#divModalForms").modal("hide");
                        mje("Registro exitoso");
                    } else {
                        mjeError("Error: " + response);
                    }
                });
        }
    });
}
//EDWIN!--
// Muestra formulario para cdp desde lsitado de otro si
function mostrarListaOtrosi(dato) {
    let ppto = id_pto_ppto.value;
    let ruta = {
        url: "lista_ejecucion_cdp.php",
        name1: "id_otro",
        valor1: dato,
        name2: "id_ejec",
        valor2: ppto,
    };
    redireccionar2(ruta);
}
/*  ========================================================= Certificado de registro pursupuestal ==========================================*/
//Carga el formulario del registro presupuestal con datos del cdp asociado

const CargarFormularioCrpp = (id) => {
    let pto = $("#id_pto_ppto").val();
    $('<form action="lista_ejecucion_crp_nuevo.php" method="POST">' +
        '<input type="hidden" name="id_pto" value="' + pto + '" />' +
        '<input type="hidden" name="id_cdp" value="' + id + '" />' +
        '</form>').appendTo("body").submit();
};
// Registrar en la tabla documentos la parte general del registro presupuestal
document.addEventListener("submit", (e) => {
    let id_cdp = $("#id_doc").val();
    e.preventDefault();
    if (e.target.id == "formAddCrpp") {
        fetch("datos/crp/registrar_doc_crp.php", {
            method: "POST",
            body: new FormData(formAddCrpp),
        })
            .then((response) => response.json())
            .then((response) => {
                if (response[0].value == "ok") {
                    //mje('Registrado todo ok');
                } else {
                    mje("Registro modificado");
                }
                formAddCrpp.reset();
                // Redirecciona documento para asignar valores por rubro
                setTimeout(() => {
                    $(
                        '<form action="lista_ejecucion_crp_nuevo.php" method="post">\n\
            <input type="hidden" name="id_crp" value="' +
                        response[0].id +
                        '" />\n\
            <input type="hidden" name="id_cdp" value="' +
                        id_cdp +
                        '" />\n\
            </form>'
                    )
                        .appendTo("body")
                        .submit();
                }, 500);
            });
    }
});
// Autocomplete para la selección del tercero que se asigna al registro presupuestal
document.addEventListener("keyup", (e) => {
    if (e.target.id == "tercerocrp") {
        $("#tercerocrp").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "datos/consultar/buscar_terceros.php",
                    type: "post",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#id_tercero').val(ui.item.id);
            }
        });
    }
});

// Redireccionar a la tabla de crp por acciones en el select
function cambiaListado(dato) {
    let id_pto = $("#id_pto_ppto").val();
    if (dato == "2") {
        $('<form action="lista_ejecucion_pto_crp.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
            .appendTo("body")
            .submit();
    }
    if (dato == "1") {
        $('<form action="lista_ejecucion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
            .appendTo("body")
            .submit();
    }
}
// Editar detalle de registro presupuestal al dar clic en listado
function CargarListadoCrpp(id_crp) {
    var pto = $("#id_pto_ppto").val();
    $('<form action="lista_ejecucion_crp_nuevo.php" method="POST">' +
        '<input type="hidden" name="id_pto" value="' + pto + '" />' +
        '<input type="hidden" name="id_cdp" value="0" />' +
        '<input type="hidden" name="id_crp" value="' + id_crp + '" />' +
        '</form>').appendTo("body").submit();
}
// Guradar detalle de rubros de registro presupuestal
document.addEventListener("click", (e) => {
    if (e.target.id == "registrarRubrosCrp") {
        let error = 0;
        let num = 0;
        var datos = {};
        let id_crp = id_pto_crp.value;
        let formulario = new FormData(formRegistrarRubrosCrp);
        formulario.delete("tableEjecCrpNuevo_length");
        // Validación de valores maximos permitidos
        for (var pair of formulario.entries()) {
            let div1 = document.getElementById(pair[0]);
            let max = div1.getAttribute("max");
            let valormax = parseFloat(max.replace(/\,/g, "", ""));
            let valor = parseFloat(pair[1].replace(/\,/g, "", ""));
            if (valor > valormax) {
                Swal.fire({
                    title: "Error",
                    text: "El valor ingresado: " + pair[1] + " supera el máximo permitido de: " + max,
                    icon: "error",
                    showConfirmButton: true,
                });
                error = 1;
                return false;
            }
            datos[pair[0]] = pair[1];
            num++;
        }
        // Creo los datos a Enviar
        var formEnvio = new FormData();
        formEnvio.append("crpp", id_crp);
        formEnvio.append("datos", JSON.stringify(datos));
        formEnvio.append("num", num);
        for (var pair of formEnvio.entries()) {
            console.log(pair[0] + ", " + pair[1]);
        }
        if (error == 0) {
            fetch("datos/crp/registrar_rubros_crp.php", {
                method: "POST",
                body: formEnvio,
            })
                .then((response) => response.json())
                .then((response) => {
                    if (response[0].value == "ok") {
                        mje("Registrado todo ok");
                    } else {
                        mje("Registro modificado");
                    }
                    formRegistrarRubrosCrp.reset();
                    // objeto Redireccionar
                    let ruta = {
                        url: "lista_ejecucion_crp.php",
                        name: "id_crp",
                        valor: id_crp,
                    };
                    redireccionar(ruta);
                    // Redirecciona documento para asignar valores por rubro
                });
        }
    }
});

// Eliminar registro presupuestal valida que el registro no tenga o facturas registradas o en proceso
function eliminarCrpp(id) {
    Swal.fire({
        title: "¿Está seguro de eliminar el registro?",
        text: "No podrá revertir esta acción",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.value) {
            fetch("datos/eliminar/del_eliminar_crp.php", {
                method: "POST",
                body: id,
            })
                .then((response) => response.text())
                .then((response) => {
                    if (response == "ok") {
                        // Reonlidar la tabla
                        $("#tableEjecPresupuestoCrp").DataTable().ajax.reload();
                        mje("Registro eliminado");
                    } else {
                        mjeError(response);
                    }
                });
        }
    });
}
//================================================== Modificaciones al presupuesto ==================================================
function cambiaListadoModifica() {
    let id_pto = $("#id_pto_ppto").val();
    let dato = $("#id_pto_doc").val();
    $('<form action="lista_modificacion_pto.php" method="POST"><input type="hidden" name="id_pto" value="' + id_pto + '"><input type="hidden" name="tipo_mod" value="' + dato + '" /></form>').appendTo("body").submit();
}
function cambiaListadoModificaEA(id_pto, dato) {
    $('<form action="lista_modificacion_pto.php" method="POST"><input type="hidden" name="id_pto" value="' + id_pto + '"><input type="hidden" name="tipo_mod" value="' + dato + '" /></form>').appendTo("body").submit();
}
// Registrar en la tabla documentos la parte general la modificacion presupuestal
document.addEventListener("submit", (e) => {
    let tipo_doc = $("#id_pto_doc").val();
    e.preventDefault();
    if (e.target.id == "formAddModificaPresupuesto") {
        let formEnvio = new FormData(formAddModificaPresupuesto);
        formEnvio.append("tipo_doc", tipo_doc);
        // Obtener atributos min y max del campo fecha
        let fecha_min = document.querySelector("#fecha").getAttribute("min");
        let fecha_max = document.querySelector("#fecha").getAttribute("max");
        // Validar que la fecha no sea mayor a la fecha maxima y menor a la fecha mínima
        if (formEnvio.get("fecha") > fecha_max || formEnvio.get("fecha") < fecha_min) {
            document.querySelector("#fecha").focus();
            mjeError("La fecha debe estar entre " + fecha_min + " y " + fecha_max, "");
            return false;
        }
        for (var pair of formEnvio.entries()) {
            console.log(pair[0] + ", " + pair[1]);
        }
        fetch("datos/registrar/registrar_modifica_pto_doc.php", {
            method: "POST",
            body: formEnvio,
        })
            .then((response) => response.json())
            .then((response) => {
                if (response[0].value == "ok") {
                    //mje('Registrado todo ok');
                } else {
                    mje("Registro modificado");
                }
                formAddModificaPresupuesto.reset();
                // Redirecciona documento para asignar valores de detalle
                let ruta = {
                    url: "lista_modificacion_det.php",
                    name: "id_mod",
                    valor: response[0].id,
                };
                redireccionar(ruta);
            });
    }
});
// Cargar lista detalle de moificaciones presupuestales
function cargarListaDetalleMod(id_doc) {
    var pto = $("#id_pto_ppto").val();
    $('<form action="lista_modificacion_det.php" method="post"><input type="hidden" name="id_pto" value="' + pto + '" /><input type="hidden" name="id_mod" value="' + id_doc + '" /></form>')
        .appendTo("body")
        .submit();
}
//Carga el formulario del detalle de modificación presupuestal
function CargarFormModiDetalle(busqueda) {
    fetch("datos/registrar/formadd_modifica_detalle.php", {
        method: "POST",
        body: busqueda,
    })
        .then((response) => response.text())
        .then((response) => {
            console.log(response);
            divformDetalle.innerHTML = response;
        })
        .catch((error) => {
            console.log("Error:");
        });
}
// Autocomplete rubro modificaciones presupuestales detalle
document.addEventListener("keyup", (e) => {
    let valor = 2;
    if (e.target.id == "rubroCod") {
        let tipo_doc = $("#tipo_doc").val();
        //salert(tipo_doc);
        console.log("llego");
        let id_pto = $("#id_pto_movto").val();
        $("#rubroCod").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/presupuesto/datos/consultar/consultaRubrosMod.php",
                    type: "post",
                    dataType: "json",
                    data: {
                        search: request.term,
                        id_pto: id_pto
                    },
                    success: function (data) {
                        response(data);
                    },
                });
            },
            select: function (event, ui) {
                $("#rubroCod").val(ui.item.label);
                $("#id_rubroCod").val(ui.item.value);
                $("#tipoRubro").val(ui.item.tipo);
                return false;
            },
            focus: function (event, ui) {
                $("#rubroCod").val(ui.item.label);
                $("#id_rubroCod").val(ui.item.value);
                $("#tipoRubro").val(ui.item.tipo);
                return false;
            },
        });
    }
});

// Registrar el detalle de las modificaciones

function RegDetalleMod(boton) {
    var fila = boton.closest('tr');
    var opcion = boton.getAttribute('text');
    var valorDeb = fila.querySelector('input[name="valorDeb"]').value;
    var valorCred = fila.querySelector('input[name="valorCred"]').value;
    var tipoRubro = fila.querySelector('input[name="tipoRubro"]').value;
    var id_rubroCod = fila.querySelector('input[name="id_rubroCod"]').value;
    var id_pto_mod = fila.querySelector('input[name="id_pto_mod"]').value;
    if (tipoRubro == '0') {
        mjeError("El rubro no es un detalle...", "Verifique la información registrada");
    } else if (Number(valorDeb) == 0 && Number(valorCred) == 0) {
        mjeError("Valor débito o crédito deben ser mayor a cero...", "Verifique la información registrada");
    } else if ((Number(valorDeb) > 0 && Number(valorCred) > 0)) {
        mjeError("Solo puede haber un valor débito o crédito...", "Verifique la información registrada");
    } else {
        datos = new FormData();
        datos.append('opcion', opcion);
        datos.append('valorDeb', valorDeb);
        datos.append('valorCred', valorCred);
        datos.append('tipoRubro', tipoRubro);
        datos.append('id_rubroCod', id_rubroCod);
        datos.append('id_pto_mod', id_pto_mod);
        fetch("datos/registrar/registrar_modifica_pto_det.php", {
            method: "POST",
            body: datos,
        })
            .then((response) => response.text())
            .then((response) => {
                if (response == "ok") {
                    $('#tableModDetalle').DataTable().ajax.reload();
                    mje("Proceso realizado correctamente");
                } else {
                    mjeError(response, "Verifique la información ingresada");
                }
            });
    }
    return false;
};
function RegDetalleCDPs(boton) {
    var fila = boton.closest('tr');
    var opcion = boton.getAttribute('text');
    var valorDeb = fila.querySelector('input[name="valorDeb"]').value;
    var tipoRubro = fila.querySelector('input[name="tipoRubro"]').value;
    var id_rubroCod = fila.querySelector('input[name="id_rubroCod"]').value;
    var id_pto_mod = fila.querySelector('input[name="id_pto_mod"]').value;
    var id_cdp = $("#id_cdp").val();
    var fecha = $("#fecha").val();
    if (tipoRubro == '0') {
        mjeError("El rubro no es un detalle...", "Verifique la información registrada");
    } else if (Number(valorDeb) == 0) {
        mjeError("Valor debe ser mayor a cero...", "Verifique la información registrada");
    } else {
        consultaSaldoRubro(valorDeb, id_rubroCod, fecha, id_cdp)
            .then(function (saldo) {
                if (saldo.status === 'error') {
                    mjeError("El valor es mayor al saldo del rubro: " + saldo.saldo, "Verifique la información registrada");
                } else {
                    var datos = new FormData();
                    datos.append('opcion', opcion);
                    datos.append('valorDeb', valorDeb);
                    datos.append('tipoRubro', tipoRubro);
                    datos.append('id_rubroCod', id_rubroCod);
                    datos.append('id_pto_mod', id_pto_mod);
                    if ($("#valida").length > 0) {
                        var data = new FormData();
                        data.append('id_pto', $("#id_pto_presupuestos").val());
                        data.append('dateFecha', $("#fecha").val());
                        data.append('numSolicitud', $("#solicitud").val());
                        data.append('txtObjeto', $("#objeto").val());
                        data.append('id_adq', $("#id_adq").val());
                        data.append('id_otro', $("#id_otro").val());

                        url = "datos/registrar/new_ejecucion_presupuesto.php";

                        fetch(url, {
                            method: "POST",
                            body: data,
                        })
                            .then((response) => response.json())
                            .then((response) => {
                                if (response.status == "ok") {
                                    var idCdp = response.msg;
                                    datos.append('id_cdp', idCdp);
                                    RegistraDetalle(datos, $('#id_pto_presupuestos').val() + '|' + idCdp);
                                } else {
                                    mjeError(response.msg, "Verifique la información ingresada");
                                }
                            });
                    } else {
                        datos.append('id_cdp', $("#id_cdp").val());
                        RegistraDetalle(datos, 0);
                    }
                }
            })
            .catch(function (error) {
                console.error("Error al consultar el saldo del rubro: ", error);
            });


    }
    function RegistraDetalle(campos, opcion) {
        fetch("datos/registrar/registrar_modifica_cdp_det.php", {
            method: "POST",
            body: campos,
        })
            .then((response) => response.json())
            .then((response) => {
                if (response.status == "ok") {
                    mje("Proceso realizado correctamente");
                    if (opcion == 0) {
                        $('#tableEjecCdp').DataTable().ajax.reload();
                    } else {
                        let id_pto_mod = opcion.split("|")[0];
                        let id_cdp = opcion.split("|")[1];
                        setTimeout(function () {
                            $('<form action="lista_ejecucion_cdp.php" method="POST">' +
                                '<input type="hidden" name="id_ejec" value="' + id_pto_mod + '" />' +
                                '<input type="hidden" name="id_cdp" value="' + id_cdp + '" />' +
                                '</form>').appendTo("body").submit();
                        }, 1000);
                    }
                } else {
                    mjeError(response.msg, "Verifique la información ingresada");
                }
            });
    }
    return false;
};
$('#modificarEjecCdp').on('click', '.editar', function () {
    var id = $(this).attr('value');
    var fila = $(this).parent().parent().parent();
    $.ajax({
        type: "POST",
        url: "datos/consultar/modifica_detalle_cdp.php",
        data: { id: id },
        dataType: "json",
        success: function (res) {
            if (res.status == "ok") {
                var celdas = fila.find('td');
                var pos = 1;
                celdas.each(function () {
                    $(this).html(res[pos]);
                    pos++;
                });
            } else {
                mjeError(res.msg, "Error en la consulta");
            }
        },
    });
});
$('#modificarEjecCdp').on('click', '.borrar', function () {
    var id = $(this).attr('value');
    Swal.fire({
        title: "¿Está seguro de eliminar el registro actual?",
        text: "No podrá revertir esta acción",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.value) {
            $.ajax({
                type: "POST",
                url: "datos/eliminar/del_eliminar_cdp_detalle.php",
                data: { id: id },
                success: function (res) {
                    if (res == 'ok') {
                        mje("Registro eliminado correctamente");
                        $('#tableEjecCdp').DataTable().ajax.reload();
                    } else {
                        mjeError(res, "Error");
                    }
                },
            });
        }
    });

});

function valorDif() {
    let dif = $("#dif").val();
    $("#valorDeb").val(dif);
}
// Terminar de registrar movimientos de detalle  verificando sumas sumas iguales en modificacion presupuestal
let terminarDetalleMod = function (dato) {
    let valida = $("#valida").val();
    let id_pto = $("#id_pto_movto").val();
    if (valida != '0') {
        mjeError("Las sumas deben ser iguales..", "Puede usar doble click en la casilla para verificar");
    } else {
        cambiaListadoModificaEA(id_pto, dato);
    }
};
// Cerrar documento presupuestal modificacion
let cerrarDocumentoMod = function (dato) {
    fetch("datos/consultar/consultaCerrar.php", {
        method: "POST",
        body: dato,
    })
        .then((response) => response.json())
        .then((response) => {
            if (response[0].value == "ok") {
                mje("Documento cerrado");
                let id = "tableModPresupuesto";
                reloadtable(id);
                document.getElementById("editar_" + dato).style.display = "none";
                document.getElementById("eliminar_" + dato).style.display = "none";
            } else {
                mjeError("Documento no aprobado", "Verifique sumas iguales");
            }
        });
};
// Cerrar documento presupuestal modificacion
var cerrarCDP = function (dato) {
    fetch("datos/actualizar/cerrar_cdp.php", {
        method: "POST",
        body: dato,
    })
        .then((response) => response.json())
        .then((response) => {
            if (response.status == "ok") {
                let id = "tableEjecPresupuesto";
                reloadtable(id);
                id = "tableEjecCdp";
                reloadtable(id);
            } else {
                mjeError("No se puede cerrar documento actual", "--");
            }
        });
};
var cerrarCRP = function (dato) {
    fetch("datos/actualizar/cerrar_crp.php", {
        method: "POST",
        body: dato,
    })
        .then((response) => response.json())
        .then((response) => {
            if (response.status == "ok") {
                let id = "tableEjecPresupuestoCrp";
                reloadtable(id);
                id = "tableEjecCrpNuevo";
                reloadtable(id);
            } else {
                mjeError("No se puede cerrar documento actual", "--");
            }
        });
};
// Abrir documento modificación presupuestal
function abrirCdp(id) {
    $.ajax({
        type: "POST",
        url: "datos/actualizar/abrir_cdp.php",
        data: { id: id },
        success: function (res) {
            if (res == 'ok') {
                mje("Documento abierto");
                $('#tableEjecPresupuesto').DataTable().ajax.reload();
            } else {
                mjeError("Documento no abierto", res);
            }
        },
    });
};
let abrirDocumentoMod = function (dato) {
    let doc = id_pto_doc.value;
    fetch("datos/consultar/consultaAbrir.php", {
        method: "POST",
        body: dato,
    })
        .then((response) => response.json())
        .then((response) => {
            if (response[0].value == "ok") {
                mje("Documento abierto");
                let id = "tableModPresupuesto";
                reloadtable(id);
            } else {
                mjeError("Documento no abierto", "Verifique sumas iguales");
            }
        });
};
// Editar rubros de modificacion presupuestal
$('#modificarModDetalle').on('click', '.editar', function () {
    var id = $(this).attr('value');
    var fila = $(this).parent().parent().parent();
    $.ajax({
        type: "POST",
        url: "datos/consultar/modifica_detalle_mod.php",
        data: { id: id },
        dataType: "json",
        success: function (res) {
            if (res.status == "ok") {
                var celdas = fila.find('td');
                var pos = 1;
                celdas.each(function () {
                    $(this).html(res[pos]);
                    pos++;
                });
            } else {
                mjeError(res.msg, "Error en la consulta");
            }
        },
    });
});

// Eliminar rubros de modificaciones presupuestales adición
$('#modificarModDetalle').on('click', '.borrar', function () {
    let id = $(this).attr('value');
    Swal.fire({
        title: "¿Está seguro de eliminar el registro actual?",
        text: "No podrá revertir esta acción",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.value) {
            fetch("datos/eliminar/del_eliminar_movimiento.php", {
                method: "POST",
                body: JSON.stringify({ id: id }),
            })
                .then((response) => response.json())
                .then((response) => {
                    console.log(response);
                    if (response[0].value == "ok") {
                        $('#tableModDetalle').DataTable().ajax.reload();
                        mje("Registro eliminado");
                    } else {
                        mjeError("No se puede eliminar el registro");
                    }
                });
        }
    });
});
// Establecer consecutivo para certificado de disponibilidad presupuestal
let buscarConsecutivo = function (doc, campo) {
    let fecha = $("#fecha").val();
    let id_doc = $("#id_pto_mvto").val();
    if (id_doc) {
        let id_pto_doc = $("#numCdp").val();
    } else {
        fetch("datos/consultar/consultaConsecutivo.php", {
            method: "POST",
            body: JSON.stringify({ fecha: fecha, documento: doc }),
        })
            .then((response) => response.json())
            .then((response) => {
                $("#numCdp").val(response[0].numero);
            });
    }
};
function eliminarCdp(id) {
    Swal.fire({
        title: "Esta seguro de eliminar el documento?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#00994C",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si!",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("datos/eliminar/del_eliminar_cdp.php", {
                method: "POST",
                body: id,
            })
                .then((response) => response.text())
                .then((response) => {
                    if (response == "ok") {
                        mje("Registro eliminado correctamente");
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        mjeError("No se puede eliminar el registro:" + response);
                    }
                });
        }
    });
}
// Buscar si numero de documento ya existe
let buscarCdp = function (doc, campo) {
    fetch("datos/consultar/consultaDocumento.php", {
        method: "POST",
        body: JSON.stringify({ doc: doc, tipo: campo }),
    })
        .then((response) => response.json())
        .then((response) => {
            console.log(response[0].numero);
            if (response[0].numero > 0) {
                let numini = $("#id_pto_docini").val();
                $("#numCdp").val(numini);
                mje("El documento ya existe");
            }
        });
};
// Redireccionar a lista_ejecucion_cdp
const redirecionarListacdp = (id, id_manu) => {
    let dato = id || 0;
    let ruta = {
        url: "lista_ejecucion_cdp.php",
        name: "id_cdp",
        valor: dato,
    };
    redireccionar(ruta);
};

// Funcion para mostrar formulario de fecha de sessión de usuario
const cambiarFechaSesion = (anno, user, url) => {
    // enviar anno y user a php para cargar informacion registrada
    let servidor = location.origin;
    fetch(servidor + url + "/financiero/fecha/form_fecha_sesion.php", {
        method: "POST",
        body: JSON.stringify({ vigencia: anno, usuario: user }),
    })
        .then((response) => response.text())
        .then((response) => {
            $("#divTamModalPermisos").removeClass("modal-xl");
            $("#divTamModalPermisos").removeClass("modal-lg");
            $("#divTamModalPermisos").addClass("modal-sm");
            $("#divModalPermisos").modal("show");
            divTablePermisos.innerHTML = response;
        })
        .catch((error) => {
            console.log("Error:");
        });
};
// funcion para cambiar sessión de usuario
const changeFecha = (url) => {
    let servidor = location.origin;
    let fromEnviar = new FormData(formFechaSesion);
    fetch(servidor + url + "/financiero/fecha/change_fecha_sesion.php", {
        method: "POST",
        body: fromEnviar,
    })
        .then((response) => response.json())
        .then((response) => {
            if (response[0].value == "ok") {
                formFechaSesion.reset();
                $("#divModalPermisos").modal("hide");
                mje("Fecha actualizada");
            } else {
                formFechaSesion.reset();
                $("#divModalPermisos").modal("hide");
                mje("Fecha actualizada");
            }
        });
};
// Funcion para generar formato de cdp
const generarFormatoCdp = (id) => {
    let formato = window.urlin + "/presupuesto/soportes/formato_cdp.php";
    let ruta = {
        url: formato,
        name: "datos",
        valor: id,
    };
    redireccionar(ruta);
};
// Funcion para generar formato de cdp

const generarFormatoCrp = (id) => {
    console.log(id);
    let formato = window.urlin + "/presupuesto/soportes/formato_rp.php";
    let ruta = {
        url: formato,
        name: "datos",
        valor: id,
    };
    redireccionar(ruta);
};

// Funcion para generar formato de Modificaciones
const generarFormatoMod = (id) => {
    let archivo = window.urlin + "/presupuesto/soportes/formato_modifica.php";
    let ruta = {
        url: archivo,
        name: "datos",
        valor: id,
    };
    redireccionar(ruta);
};
// Función eliminar modificación presupuestales
const eliminarModPresupuestal = (id) => {
    Swal.fire({
        title: "Esta seguro de eliminar el documento?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#00994C",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si!",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("datos/eliminar/del_eliminar_cdp.php", {
                method: "POST",
                body: id,
            })
                .then((response) => response.text())
                .then((response) => {
                    if (response == "ok") {
                        let tabla = "tableModPresupuesto";
                        reloadtable(tabla);
                        Swal.fire({
                            icon: "success",
                            title: "Eliminado",
                            showConfirmButton: true,
                            timer: 1500,
                        });
                    } else {
                        mjeError("No se puede eliminar el registro:" + response);
                    }
                });
        }
    });
};

// Redireccionar a lista_modificacion_det.php
const redirecionarListaMod = (id) => {
    let ruta = {
        url: "lista_modificacion_des.php",
        name: "id_mod",
        valor: id,
    };
    redireccionar(ruta);
};
var modiapl = false;
$("#divCuerpoPag").ready(function () {
    $("#numApl").change(function () {
        modiapl = true;
    });
    $("#tipo_acto").change(function () {
        modiapl = true;
    });
    $("#fecha").change(function () {
        modiapl = true;
    });
    $("#objeto").change(function () {
        modiapl = true;
    });
});
// Registrar desaplazamiento presupuestal
document.addEventListener("submit", (e) => {
    e.preventDefault();
    if (e.target.id == "formAddDezaplazamiento") {
        let formEnvioApl = new FormData(formAddDezaplazamiento);
        if (modiapl) {
            formEnvioApl.append("estado", 0);
        }
        // Validación del formulario
        for (var pair of formEnvioApl.entries()) {
            console.log(pair[0] + ", " + pair[1]);
            // Validación del valor del desaplazamiento
            let valor_max = document.querySelector("#valorDeb").getAttribute("max");
            let valor_des = formEnvioApl.get("valorDeb");
            valor_des = parseFloat(valor_des.replace(/\,/g, "", ""));
            if (valor_des < 1 || valor_des > valor_max) {
                document.querySelector("#valorDeb").focus();
                mjeError("Debe digitar un valor valido", "");
                return false;
            }
        }

        fetch("datos/registrar/registrar_desaplazamiento_apl.php", {
            method: "POST",
            body: formEnvioApl,
        })
            .then((response) => response.json())
            .then((response) => {
                if (response[0].value == "ok") {
                    modiapl = false;
                    console.log(response);
                    id_pto_apl.value = response[0].id;
                    rubroCod.value = "";
                    id_rubroCod.value = "";
                    valorDeb.value = "";
                } else {
                    mje("Registro modificado");
                }
                let id = "tableAplDetalle";
                reloadtable(id);
            });
    }
});

// Funcióm para editar el valor del aplazamiento
function editarAplazamiento(id) {
    fetch("datos/consultar/editarRubrosApl.php", {
        method: "POST",
        body: id,
    })
        .then((response) => response.json())
        .then((response) => {
            console.log(response);
            rubroCod.value = response.rubro + " - " + response.nom_rubro;
            id_rubroCod.value = response.rubro;
            valorDeb.value = response.valor;
            valorDeb.max = response.valor;
        });
}

// Ver historial de ejecución del rubro
const verHistorial = (boton) => {
    var fila = boton.closest('tr');
    var inputRubroCod = fila.querySelector('input[name="id_rubroCod"]');
    var rubro = inputRubroCod.value;
    var fecha = $("#fecha").val();
    var id_cdp = $("#id_cdp").val();
    $.ajax({
        type: "POST",
        url: "datos/reportes/form_resumen_rubro.php",
        data: { rubro: rubro, fecha: fecha, id_cdp: id_cdp },
        success: function (res) {
            $("#divTamModalPermisos").removeClass("modal-xl");
            $("#divTamModalPermisos").removeClass("modal-lg");
            $("#divTamModalPermisos").addClass("");
            $("#divModalPermisos").modal("show");
            divTablePermisos.innerHTML = res;
        },
    });
};

// Ver historial de ejecución del rubro desde CDP
const verHistorialCdp = (anno) => {
    let rubro = id_rubroCdp.value;
    let fecha = ""; //fecha.value;
    fetch("datos/reportes/form_resumen_rubro.php", {
        method: "POST",
        body: JSON.stringify({ vigencia: anno, rubro: rubro, fecha: fecha }),
    })
        .then((response) => response.text())
        .then((response) => {
            $("#divTamModalPermisos").removeClass("modal-xl");
            $("#divTamModalPermisos").removeClass("modal-lg");
            $("#divTamModalPermisos").addClass("");
            $("#divModalPermisos").modal("show");
            divTablePermisos.innerHTML = response;
        })
        .catch((error) => {
            console.log("Error:");
        });
};

// Consultar saldo del cdp
const consultaSaldoCdp = (anno) => {
    let rubro = id_rubroCdp.value;
    let valor = valorCdp.value;
    valor = parseFloat(valor.replace(/\,/g, "", ""));
    fetch("datos/consultar/consultaSaldoCdp.php", {
        method: "POST",
        body: JSON.stringify({ vigencia: anno, rubro: rubro }),
    })
        .then((response) => response.json())
        .then((response) => {
            let saldo = response[0].total;
            valorCdp.max = response[0].total;
            if (saldo < valor) {
                mjeError("El saldo del rubro es insuficiente .....", "");
                valorDeb.focus();
                // Inhabilitar el boton de guardar
            }
        })
        .catch((error) => {
            console.log("Error:");
        });
};

// Consultar saldo del rubro en modificacion
function consultaSaldoRubro(valor, rubro, fecha, id_cdp) {
    return new Promise((resolve, reject) => {
        $.ajax({
            type: "POST",
            url: "datos/consultar/consultaSaldoRubro.php",
            data: { valor: valor, rubro: rubro, fecha: fecha, id_cdp: id_cdp },
            dataType: "json",
            success: function (res) {
                resolve(res);
            },
            error: function (err) {
                reject(err);
            }
        });
    });
}

// Funcion para realizar el registro presupuestal a un crp
$("#divForms").on("click", "#btnGestionCRP", function () {
    var op = $(this).attr('text');
    $('.is-invalid').removeClass('is-invalid');
    if ($("#dateFecha").val() === "") {
        $("#dateFecha").focus();
        $("#dateFecha").addClass('is-invalid');
        $("#divModalError").modal("show");
        $("#divMsgError").html("¡La fecha no puede estar vacio!");
    } else if ($("#id_manu").val() === "") {
        $("#id_manu").focus();
        $("#id_manu").addClass('is-invalid');
        $("#divModalError").modal("show");
        $("#divMsgError").html("¡El numero de CRP no puede estar vacio!");
    } else if ($("#txtContrato").val() === "") {
        $("#txtContrato").focus();
        $("#txtContrato").addClass('is-invalid');
        $("#divModalError").modal("show");
        $("#divMsgError").html("¡El numero de contrato no puede estar vacio!");
    } else if ($("#id_tercero").val() === "0") {
        $("#id_tercero").focus();
        $("#id_tercero").addClass('is-invalid');
        $("#divModalError").modal("show");
        $("#divMsgError").html("¡Debe elegir un tercero!");
    } else if ($("#txtObjeto").val() === "") {
        $("#txtObjeto").focus();
        $("#txtObjeto").addClass('is-invalid');
        $("#divModalError").modal("show");
        $("#divMsgError").html("¡El objeto no puede ser vacio!");
    } else {
        var datos, url;
        if (op == 1) {
            datos = $("#formAddCRP").serialize()
            url = "datos/registrar/registrar_crp.php";
        } else {
            datos = $("#formUpCRP").serialize()
            url = "datos/actualizar/up_ejecucion_presupuesto.php";
        }
        $.ajax({
            type: "POST",
            url: url,
            data: datos,
            success: function (r) {
                if (r === "ok") {
                    let id = "tableEjecPresupuestoCrp";
                    reloadtable(id);
                    $("#divModalForms").modal("hide");
                    $("#divModalDone").modal("show");
                    $("#divMsgDone").html("Proceso realizado correctamente...");
                } else {
                    $("#divModalError").modal("show");
                    $("#divMsgError").html(r);
                }
            },
        });
    }
    return false;
});

$('#registrarMovDetalle').on('click', function () {
    var pto = $("#id_pto_ppto").val();
    var id_cdp = $("#id_cdp").val();
    $('.is-invalid').removeClass('is-invalid');
    if ($('#fecha').val() == '') {
        $('#fecha').focus();
        $('#fecha').addClass('is-invalid');
        mjeError('La fecha no puede estar vacia', '');
    } else if ($('#id_tercero').val() == '0') {
        $('#tercero').focus();
        $('#tercero').addClass('is-invalid');
        mjeError('Debe elegir un tercero', '');
    } else if ($('#objeto').val() == '') {
        $('#objeto').focus();
        $('#objeto').addClass('is-invalid');
        mjeError('El objeto no puede estar vacio', '');
    } else if ($('#contrato').val() == '') {
        $('#contrato').focus();
        $('#contrato').addClass('is-invalid');
        mjeError('El contrato no puede estar vacio', '');
    } else {
        var validar = true;
        $('.valor-detalle').each(function () {
            var valor = parseFloat($(this).val().replace(/\,/g, "", ""));
            if (valor <= 0 || $(this).val() == '') {
                validar = false;
                $(this).focus();
                $(this).addClass('is-invalid');
                mjeError('El valor no puede ser cero o menor', '');
                return false;
            } else {
                let min = $(this).attr('min');
                let max = $(this).attr('max');
                if (valor < min || valor > max) {
                    validar = false;
                    $(this).focus();
                    $(this).addClass('is-invalid');
                    mjeError('El valor no puede ser menor a ' + min + ' o mayor a ' + max, '');
                    return false;
                }
            }
        });
        if (validar) {
            var datos = $("#formGestionaCrp").serialize();
            $.ajax({
                type: "POST",
                url: "datos/registrar/registrar_crp.php",
                data: datos,
                dataType: "json",
                success: function (res) {
                    if (res.status === "ok") {
                        mje('Proceso realizado correctamente');
                        setTimeout(function () {
                            $('<form action="lista_ejecucion_crp_nuevo.php" method="POST">' +
                                '<input type="hidden" name="id_pto" value="' + pto + '" />' +
                                '<input type="hidden" name="id_cdp" value="' + id_cdp + '" />' +
                                '<input type="hidden" name="id_crp" value="' + res.msg + '" />' +
                                '</form>').appendTo("body").submit();
                        }, 1000);

                    } else {
                        mjeError(res.msg, '');
                    }
                },
            });
        }
    }

});
// Ver historial de CDP para liquidación de saldos sin ejecutar
const verLiquidarCdp = (id) => {
    fetch("lista_historial_cdp.php", {
        method: "POST",
        body: JSON.stringify({ id: id }),
    })
        .then((response) => response.text())
        .then((response) => {
            $("#divTamModalForms").removeClass("modal-sm");
            $("#divTamModalForms").removeClass("modal-lg");
            $("#divTamModalForms").addClass("modal-xl");
            $("#divModalForms").modal("show");
            divForms.innerHTML = response;
        })
        .catch((error) => {
            console.log("Error:");
        });
};
// Ver historial de CDP para liquidación de saldos sin ejecutar
const CargarFormularioLiquidar = (id) => {
    fetch("datos/registrar/form_liquidar_saldo_cdp.php", {
        method: "POST",
        body: JSON.stringify({ id: id }),
    })
        .then((response) => response.text())
        .then((response) => {
            $("#divTamModalForms3").removeClass("modal-lg");
            $("#divTamModalForms3").removeClass("modal-sm");
            $("#divTamModalForms3").addClass("modal-xl");
            $("#divModalForms3").modal("show");
            divForms3.innerHTML = response;
        })
        .catch((error) => {
            console.log("Error:");
        });
};
// Ver historial de CDP para liquidación de saldos sin ejecutar
const CargarFormularioLiquidarCrp = (id) => {
    fetch("datos/registrar/form_liquidar_saldo_crp.php", {
        method: "POST",
        body: JSON.stringify({ id: id }),
    })
        .then((response) => response.text())
        .then((response) => {
            $("#divTamModalForms3").removeClass("modal-lg");
            $("#divTamModalForms3").removeClass("modal-sm");
            $("#divTamModalForms3").addClass("modal-xl");
            $("#divModalForms3").modal("show");
            divForms3.innerHTML = response;
        })
        .catch((error) => {
            console.log("Error:");
        });
};

// Autocomplete para seleccionar terceros
document.addEventListener("keyup", (e) => {
    if (e.target.id == "tercero") {
        $("#tercero").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "datos/consultar/buscar_terceros.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        term: request.term,
                    },
                    success: function (data) {
                        response(data);
                    },
                });
            },
            select: function (event, ui) {
                $("#tercero").val(ui.item.label);
                $("#id_tercero").val(ui.item.id);
                return false;
            },
            focus: function (event, ui) {
                $("#tercero").val(ui.item.label);
                return false;
            },
        });
    }
});

//========================================================= LIQUIDAR SALDO DE CDP =====================================
// Funcion para liquidar saldo de CDP
const EnviarLiquidarCdp = async (id) => {
    let formEnvio = new FormData(modLiberaCdp2);
    for (var pair of formEnvio.entries()) {
        console.log(pair[0] + ", " + pair[1]);
    }
    // validar que concepto este lleno
    if (formEnvio.get("objeto") == "") {
        document.querySelector("#objeto").focus();
        mjeError("Debe digitar un concepto", "");
        return false;
    }
    try {
        const response = await fetch("datos/registrar/registrar_liquidacion_cdp.php", {
            method: "POST",
            body: formEnvio,
        });
        const data = await response.json();
        id_doc_neo.value = data[0].id;
        if (data[0].value == "ok") {
            mje("Registro guardado exitosamente");
        }
        console.log(data);
    } catch (error) {
        console.error(error);
    }
};

// Registra el movimiento de detalle de la liberación de saldo del cdp
const registrarLiquidacionDetalle = async (id) => {
    if (id_doc_neo.value != "") {
        let campo_form = id.split("_");
        let input = document.getElementById("valor" + campo_form[1]);
        let formEnvio = new FormData(modLiberaCdp2);
        formEnvio.append("dato", id);
        for (var pair of formEnvio.entries()) {
            console.log(pair[0] + ", " + pair[1]);
        }
        if (input.value == 0) {
            mjeError("El valor no puede ser cero", "");
            return false;
        }
        if (input.value > input.max) {
            mjeError("El valor no puede ser mayor al saldo", "");
            return false;
        }
        try {
            const response = await fetch("datos/registrar/registrar_liquidacion_cdp_det.php", {
                method: "POST",
                body: formEnvio,
            });
            const data = await response.json();
            console.log(data);
            if (data[0].value == "ok") {
                input.value = data[0].valor;
                input.max = data[0].valor;
                mje("Registro guardado exitosamente");
            }
        } catch (error) {
            console.error(error);
        }
    } else {
        mjeError("Debe registrar el documento con el botón guardar", "");
    }
};

// Eliminar registro de detalle de la liberación de saldo del cdp
const eliminarLiberacion = (id) => {
    Swal.fire({
        title: "¿Está seguro de eliminar el registro?",
        text: "No podrá revertir esta acción",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "Cancelar",
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch("datos/eliminar/del_eliminar_liberacion_cdp.php", {
                    method: "POST",
                    body: JSON.stringify({ id: id }),
                });
                const data = await response.json();
                console.log(data);
                if (data[0].value == "ok") {
                    $("#" + id).remove();
                    mje("Registro eliminado");
                }
            } catch (error) {
                console.error(error);
            }
        }
    });
};

// ============================================================================================= FIN

// ================================================== REGISTRAR LIQUIDACION DE SALDO DE CRP =====================================
// Funcion para liquidar saldo de CDP
const EnviarLiquidarCrp = async (id) => {
    let formEnvio = new FormData(modLiberaCrp);
    for (var pair of formEnvio.entries()) {
        console.log(pair[0] + ", " + pair[1]);
    }
    // validar que concepto este lleno
    if (formEnvio.get("objeto") == "") {
        document.querySelector("#objeto").focus();
        mjeError("Debe digitar un concepto", "");
        return false;
    }
    try {
        const response = await fetch("datos/registrar/registrar_liquidacion_crp.php", {
            method: "POST",
            body: formEnvio,
        });
        const data = await response.json();
        id_doc_neo.value = data[0].id;
        if (data[0].value == "ok") {
            mje("Registro guardado exitosamente");
        }
        console.log(data);
    } catch (error) {
        console.error(error);
    }
};

// Registra el movimiento de detalle de la liberación de saldo del crp
const registrarLiquidacionDetalleCrp = async (id) => {
    console.log(id);

    if (id_doc_neo.value != "") {
        let campo_form = id.split("_");
        let input = document.getElementById("valor" + campo_form[1]);
        let formEnvio = new FormData(modLiberaCrp);
        formEnvio.append("dato", id);
        for (var pair of formEnvio.entries()) {
            console.log(pair[0] + ", " + pair[1]);
        }
        if (input.value == 0) {
            mjeError("El valor no puede ser cero", "");
            return false;
        }
        let valor_libera = parseFloat(input.value.replace(/\,/g, "", ""));
        let valor_max = parseFloat(input.max.replace(/\,/g, "", ""));
        if (valor_libera > valor_max) {
            mjeError("El valor no puede ser mayor al saldo del RP", "");
            return false;
        }
        try {
            const response = await fetch("datos/registrar/registrar_liquidacion_crp_det.php", {
                method: "POST",
                body: formEnvio,
            });
            const data = await response.json();
            console.log(data);
            if (data[0].value == "ok") {
                input.value = data[0].valor;
                input.max = data[0].valor;
                mje("Registro guardado exitosamente");
                let tabla = "tableEjecPresupuesto";
                reloadtable(tabla);
            }
        } catch (error) {
            console.error(error);
        }
    } else {
        mjeError("Debe registrar el documento con el botón guardar", "");
    }
};
// ============================================================================================= FIN

//================================================ ANULACION DE DOCUMENTO =============================================
// Funcion para anular documento
const anulacionPto = (button) => {
    var data = button.getAttribute("text");
    $.post("form_anula.php", { data: data }, function (he) {
        $("#divTamModalForms").removeClass("modal-sm");
        $("#divTamModalForms").removeClass("modal-xl");
        $("#divTamModalForms").addClass("modal-lg");
        $("#divModalForms").modal("show");
        $("#divForms").html(he);
    });
};

const anulacionCdp = (id) => {
    let url = "form_fecha_anulacion_cdp.php";
    $.post(url, { id: id }, function (he) {
        $("#divTamModalForms").removeClass("modal-sm");
        $("#divTamModalForms").removeClass("modal-xl");
        $("#divTamModalForms").addClass("modal-lg");
        $("#divModalForms").modal("show");
        $("#divForms").html(he);
    });
};

const generarInformeConsulta = (id) => {
    let url = "informes/informe_ejecucion_gas_xls_consulta.php";
    $.post(url, { id: id }, function (he) {
        $("#divTamModalForms").removeClass("modal-sm");
        $("#divTamModalForms").removeClass("modal-lg");
        $("#divTamModalForms").addClass("modal-xl");
        $("#divModalForms").modal("show");
        $("#divForms").html(he);
    });
};

// Enviar datos para anulacion
function changeEstadoAnulacion() {
    $('.is-invalid').removeClass('is-invalid');
    var tipo = $('#tipo').val();
    if ('fecha' == '') {
        $('#fecha').focus();
        $('#fecha').addClass('is-invalid');
        mjeError('La fecha no puede estar vacia', '');
    } else if ($('#objeto').val() == '') {
        $('#objeto').focus();
        $('#objeto').addClass('is-invalid');
        mjeError('El Motivo de anulación no puede estar vacio', '');
    } else {
        var datos = $("#formAnulaDoc").serialize();
        Swal.fire({
            title: "¿Confirma anulación de documento?, Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "datos/registrar/registrar_anulacion_doc.php",
                    data: datos,
                    success: function (r) {
                        if (r === "ok") {
                            var tabla = "tableEjecPresupuesto";
                            if (tipo == 'crp') {
                                tabla = "tableEjecPresupuestoCrp";
                            }
                            $('#divModalForms').modal('hide');
                            $('#' + tabla).DataTable().ajax.reload();
                            mje('Proceso realizado correctamente');
                        } else {
                            mjeError('Error:', r);
                        }
                    },
                });
            }
        });
    }
};
/*
const changeEstadoAnulacion = async () => {
    let formEnvio = new FormData(formAnulacionCrpp);
    for (var pair of formEnvio.entries()) {
        console.log(pair[0] + ", " + pair[1]);
        // obtener el valor de la etiqueta min del imput fecha
        let fecha_min = document.querySelector("#fecha").getAttribute("min");
        // validar que el value del campo  fecha no sea menor a fecha_min
        if (formEnvio.get("fecha") < fecha_min) {
            mjeError("La fecha no puede ser menor al cierre de periodo", "Fecha permitida: " + fecha_min);
            return false;
        }
    }
    try {
        const response = await fetch("datos/registrar/registrar_anulacion_doc.php", {
            method: "POST",
            body: formEnvio,
        });
        const data = await response.json();
        console.log(data);
        if (data[0].value == "ok") {
            // realizar un case para opciones 1.2.3
            if (data[0].tipo == 1) {
                let tabla = "tableEjecPresupuesto";
                reloadtable(tabla);
            }
            if (data[0].tipo == 2) {
                let tabla = "tableEjecPresupuestoCrp";
                reloadtable(tabla);
            }
            if (data[0].tipo == 3) {
                let tabla = "tableModPresupuesto";
                reloadtable(tabla);
            }
            mje("Anulación guardada con  éxito...");
            // cerrar modal
            $("#divModalForms").modal("hide");
        }
    } catch (error) {
        console.error(error);
    }
};
*/
// ================================================   FIN LIQUIDAR SALDO DE CDP =====================================

const cargarReportePresupuesto = (id) => {
    let url = "";
    if (id == 1) {
        url = "informes/informe_ejecucion_ing_list.php";
    }
    if (id == 2) {
        url = "informes/informe_ejecucion_gas_list.php";
    }
    if (id == 3) {
        url = "informes/informe_ejecucion_gas_libros.php";
    }
    if (id == 4) {
        url = "informes/informe_ejecucion_gas_xls_mes_form.php";
    }
    if (id == 5) {
        url = "informes/informe_ejecucion_ing_xls_mes_form.php";
    }
    if (id == 6) {
        url = "informes/informe_ejecucion_form.php";
    }
    if (id == 7) {
        url = "informes/informe_ejecucion_gas_libros_anula.php";
    }
    if (id == 8) {
        url = "informes/informe_ejecucion2_form.php";
    }
    fetch(url, {
        method: "POST",
        body: JSON.stringify({ id: id }),
    })
        .then((response) => response.text())
        .then((response) => {
            areaReporte.innerHTML = response;
        })
        .catch((error) => {
            console.log("Error:");
        });
};

// Funcion para generar formato de Modificaciones
const generarInforme = (boton) => {
    var data;
    let id = boton.value;
    let fecha_corte = $('#fecha').length ? $('#fecha').val() : '';
    let archivo = '';
    const areaImprimir = document.getElementById("areaImprimir");
    if (id == 1) {
        archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_gas_xls.php";
        let mes = $("#mes").length ? $("#mes").is(":checked") : false;
        mes = mes ? 1 : 0;
        data = { fecha_corte: fecha_corte, mes: mes };
    }
    if (id == 2) {
        archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_ing_xls.php";
        let mes = $("#mes").length ? $("#mes").is(":checked") : false;
        mes = mes ? 1 : 0;
        data = { fecha_corte: fecha_corte, mes: mes };
    }
    if (id == 3) {
        archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_gas_xls_mes.php";
    }
    if (id == 4) {
        archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_trimestral.php";
        let tipo_ppto = $('#tipo_pto').val();
        let informe = $('#informe').val();
        data = { fecha_corte: fecha_corte, tipo_ppto: tipo_ppto, informe: informe };
    }
    if (id == 5) {
        archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_gas_xls_consulta.php";
    }
    if (id == 6) {
        archivo = window.urlin + "/presupuesto/informes/informe_ejecucion2_trimestral.php";
    }
    boton.disabled = true;
    var span = boton.querySelector("span")
    span.classList.add("spinner-border", "spinner-border-sm");
    $.ajax({
        url: archivo,
        type: "POST",
        data: data,
        success: function (response) {
            boton.disabled = false;
            span.classList.remove("spinner-border", "spinner-border-sm")
            areaImprimir.innerHTML = response;
        }, error: function (error) {
            console.log("Error:" + error);
        }
    });
};
// Funcion para generar libros presupuestales
const generarInformeLibros = (boton) => {
    let id = boton.value;
    let tipo = $('#tipo_libro').val();
    let fecha_ini = $('#fecha_ini').val();
    let fecha_corte = $('#fecha').val();
    let ruta = window.urlin + "/presupuesto/informes/";
    var data = { fecha_corte: fecha_corte, fecha_ini: fecha_ini };
    if (tipo == 1) {
        ruta = ruta + "informe_libro_cdp_xls.php";
    }
    if (tipo == 2) {
        ruta = ruta + "informe_libro_crp_xls.php";
    }
    if (tipo == 3) {
        ruta = ruta + "informe_libro_cop_xls.php";
    }
    if (tipo == 4) {
        ruta = ruta + "informe_libro_pag_xls.php";
    }
    if (tipo == 5) {
        ruta = ruta + "informe_libro_cxp.php";
    }
    if (tipo == 6) {
        ruta = ruta + "informe_libro_ft04.php";
    }
    if (tipo == 7) {
        ruta = ruta + "informe_libro_cdp_anula_xls.php";
    }
    if (tipo == 8) {
        ruta = ruta + "informe_libro_crp_anula_xls.php";
    }
    if (tipo == 9) {
        ruta = ruta + "informe_libro_rad_xls.php";
    }
    if (tipo == 10) {
        ruta = ruta + "informe_libro_rec_xls.php";
    }
    if (tipo == 11) {
        ruta = ruta + "informe_libro_mod_anula_xls.php";
    }
    if (tipo == 13) {
        ruta = ruta + "informe_libro_pag_anula.php";
    }
    if (id == 20) {
        ruta = ruta + "informe_ejecucion_ing_xls.php ";
    }
    boton.disabled = true;
    var span = boton.querySelector("span")
    span.classList.add("spinner-border", "spinner-border-sm");
    areaImprimir.innerHTML = "";
    $.ajax({
        url: ruta,
        type: "POST",
        data: data,
        success: function (response) {
            boton.disabled = false;
            span.classList.remove("spinner-border", "spinner-border-sm")
            areaImprimir.innerHTML = response;
        }, error: function (error) {
            console.log("Error:" + error);
        }
    });
};

// Funcion para redireccionar la recarga de la pagina
function redireccionar3(ruta) {
    console.log(ruta);
    setTimeout(() => {
        $(
            '<form action="' +
            ruta.url +
            '" method="post">\n\
    <input type="hidden" name="' +
            ruta.name +
            '" value="' +
            ruta.valor +
            '" />\n\
    </form>'
        )
            .appendTo("body")
            .submit();
    }, 100);
}

const abrirLink = (link) => {
    if (link == 1) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_sia/index.php");
    if (link == 2) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_chip/cgr_ingresos.php");
    if (link == 3) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_chip/cgr_gastos.php");
    if (link == 4) window.open("http://localhost:3080/2022/USUARIOS_REG/mod_informes/ejec_pptal_ing.php");
    if (link == 5) window.open("http://localhost:3080/2022/USUARIOS_REG/mod_informes/ejec_pptal_gastos.php");
    if (link == 6) window.open("http://localhost:3080/2022/USUARIOS_REG/mvto_ppto_gas/relacion_compromisos_corte.php");
    if (link == 7) window.open("http://localhost:3080/2022/USUARIOS_REG/mod_informes/modificaciones_mensual.php");
    if (link == 8) window.open("http://localhost:3080/2022/USUARIOS_REG/mod_informes/modificaciones_mensual_ing.php");
    if (link == 9) window.open("http://localhost:3080/2022/USUARIOS_REG/2193/2193_hom_ing.php");
    if (link == 10) window.open("http://localhost:3080/2022/USUARIOS_REG/2193_gas/2193_hom_ing.php");
    if (link == 11) window.open("http://localhost:3080/2022/USUARIOS_REG/2193/a.php");
    if (link == 12) window.open("http://localhost:3080/2022/USUARIOS_REG/2193_gas/a.php");
    if (link == 13) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_sia/busca_contrato.php");
    if (link == 14) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contabilidad/libro_auxiliar.php");
    if (link == 15) window.open("http://localhost:3080/2022/USUARIOS_REG/balance_prueba/balance_prueba.php");
    if (link == 16) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contabilidad/mayor_balance_corte_f.php");
    if (link == 17) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contabilidad/balance_general_corte.php");
    if (link == 18) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contabilidad/estado_resultados_corte.php");
    if (link == 19) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contaduria_gral/a.php");
    if (link == 20) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contaduria_gral/cuenta_puntos.php");
    if (link == 21) window.open("");

    // generar funcion numeros para
};
