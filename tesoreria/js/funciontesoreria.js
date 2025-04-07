var tabla;
(function ($) {
	//Superponer modales
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


	GetFormDocCtb = function (id_doc, id_var, id_detalle) {
		$.post("form_doc_pago.php", { id_tipo: id_doc, id_var: id_var, id_detalle: id_detalle }, function (he) {
			$("#divTamModalForms").removeClass("modal-xl");
			$("#divTamModalForms").removeClass("modal-sm");
			$("#divTamModalForms").addClass("modal-lg");
			$("#divModalForms").modal("show");
			$("#divForms").html(he);
		});
	}
	GetFormDocCaja = function (id_doc, id_var, id_detalle) {
		$.post("form_doc_caja.php", { id_tipo: id_doc, id_var: id_var, id_detalle: id_detalle }, function (he) {
			$("#divTamModalForms").removeClass("modal-xl");
			$("#divTamModalForms").removeClass("modal-sm");
			$("#divTamModalForms").addClass("modal-lg");
			$("#divModalForms").modal("show");
			$("#divForms").html(he);
		});
	}
	FormCuentasBanco = function (id_tes_cuenta) {
		$.post("form_cuenta_nueva.php", { id_tes_cuenta: id_tes_cuenta }, function (he) {
			$("#divTamModalForms").removeClass("modal-xl");
			$("#divTamModalForms").removeClass("modal-sm");
			$("#divTamModalForms").addClass("modal-lg");
			$("#divModalForms").modal("show");
			$("#divForms").html(he);
		});
	}
	//================================================================================ DATA TABLES ========================================
	$(document).ready(function () {
		//dataTable de movimientos contables
		let id_ejec = 0;
		let id_doc = $("#id_ctb_tipo").val();
		let id_var = $("#var_tip").val();
		// obtener el value de id_ctb_tipo
		var tbMvtoTes;
		if (id_doc == 13) {
			tbMvtoTes = $("#tableMvtoTesoreriaPagos").DataTable({
				dom: setdom,
				buttons: [
					{
						text: ' <span class="fas fa-plus-circle fa-lg"></span>',
						action: function (e, dt, node, config) {
							GetFormDocCaja(id_doc, id_var, 0);
						},
					},
				],
				language: setIdioma,
				serverSide: true,
				processing: true,
				ajax: {
					url: "datos/listar/datos_mvto_caja.php",
					data: function (d) {
						d.id_doc = id_doc;
						d.anulados = $('#verAnulados').is(':checked') ? 1 : 0;
						return d;
					},
					type: "POST",
					dataType: "json",
				},
				columns: [
					{ data: "acto" },
					{ data: "num_acto" },
					{ data: "nombre_caja" },
					{ data: "fecha_ini" },
					{ data: "fecha_acto" },
					{ data: "valor_total" },
					{ data: "valor_minimo" },
					{ data: "num_poliza" },
					{ data: "porcentaje" },
					{ data: "estado" },
					{ data: "botones" },

				],
				columnDefs: [
					{ class: 'text-wrap', targets: [3] },
					{ orderable: false, targets: 10 },
				],
				order: [[0, "desc"]],
			});
		} else {
			tbMvtoTes = $("#tableMvtoTesoreriaPagos").DataTable({
				dom: setdom,
				buttons: [
					{
						text: ' <span class="fas fa-plus-circle fa-lg"></span>',
						action: function (e, dt, node, config) {
							GetFormDocCtb(id_doc, id_var, 0);
						},
					},
				],
				language: setIdioma,
				serverSide: true,
				processing: true,
				ajax: {
					url: "datos/listar/datos_mvto_tesoreria.php",
					data: function (d) {
						d.id_doc = id_doc;
						d.anulados = $('#verAnulados').is(':checked') ? 1 : 0;
						return d;
					},
					type: "POST",
					dataType: "json",
				},
				columns: [
					{ data: "numero" },
					{ data: "fecha" },
					{ data: "ccnit" },
					{ data: "tercero" },
					{ data: "valor" },
					{ data: "botones" }
				],
				columnDefs: [
					{ class: 'text-wrap', targets: [3] },
					{ orderable: false, targets: 5 },
				],
				order: [[0, "desc"]],
			});
		}
		$('#tableMvtoTesoreriaPagos_filter input').unbind(); // Desvinculamos el evento por defecto
		$('#tableMvtoTesoreriaPagos_filter input').bind('keypress', function (e) {
			if (e.keyCode == 13) { // Si se presiona Enter (código 13)
				tbMvtoTes.search(this.value).draw(); // Realiza la búsqueda y actualiza la tabla
			}
		});
		$("#tableMvtoTesoreriaPagos").wrap('<div class="overflow" />');
		// dataTable de movimientos contables

		$("#tableMvtoContableDetallePag").DataTable({
			search: "false",
			language: setIdioma,
			processing: true,
			ajax: {
				url: "datos/listar/datos_mvto_contabilidad_detalle.php",
				data: function (d) {
					d.id_doc = $("#id_ctb_doc").val();
				},
				type: "POST",
				dataType: "json",
			},
			columns: [
				{ data: "cuenta" },
				{ data: "tercero" },
				{ data: "debito" },
				{ data: "credito" },
				{ data: "botones" }
			],
			order: [[0, "desc"]],
			initComplete: function () {
				var api = this.api();
				// Obtener los datos del tfoot de la DataTable
				var tfootData = api.ajax.json().tfoot;
				// Construir el tfoot de la DataTable
				var tfootHtml = '<tfoot><tr>';
				$.each(tfootData, function (index, value) {
					tfootHtml += '<th>' + value + '</th>';
				});
				tfootHtml += '</tr></tfoot>';
				// Agregar el tfoot a la tabla
				$(this).append(tfootHtml);
			}
		});
		$("#tableMvtoContableDetallePag").wrap('<div class="overflow" />');
		// Lista de chequeras creadas en el sistema
		$("#tableFinChequeras").DataTable({
			dom: setdom,
			buttons: [
				{
					text: ' <span class="fas fa-plus-circle fa-lg"></span>',
					action: function (e, dt, node, config) {
						$.post("form_chequera_nueva.php", function (he) {
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
				url: "datos/listar/datos_chequeras_list.php",
				data: function (d) {
					d.id_doc = id_doc;
				},
				type: "POST",
				dataType: "json",
			},
			columns: [{ data: "fecha" }, { data: "banco" }, { data: "cuenta" }, { data: "numero" }, { data: "inicial" }, { data: "en_uso" }, { data: "botones" }],
			order: [[0, "desc"]],
		});
		$("#tableFinChequeras").wrap('<div class="overflow" />');

		// Lista de cuentas de tesorería
		$("#tableCuentasBanco").DataTable({
			dom: setdom,
			buttons: [
				{
					text: ' <span class="fas fa-plus-circle fa-lg"></span>',
					action: function (e, dt, node, config) {
						FormCuentasBanco(0);
					},
				},
			],
			language: setIdioma,
			ajax: {
				url: "datos/listar/datos_cuentas_list.php",
				data: function (d) {
					d.id_doc = id_doc;
				},
				type: "POST",
				dataType: "json",
			},
			columns: [{ data: "banco" }, { data: "tipo" }, { data: "nombre" }, { data: "numero" }, { data: "cuenta" }, { data: "estado" }, { data: "botones" }],
			order: [[0, "desc"]],
			"pageLength": 10,
			columnDefs: [{
				class: 'text-wrap',
				targets: [2]
			}],
		});
		$("#tableCuentasBanco").wrap('<div class="overflow" />');

		$("#tableConcBancaria").DataTable({
			dom: "<'row'<'col-md-5'l><'col-md-2'B><'col-md-5'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
			buttons: [
				{
					text: '<div class="input-group input-group-sm border border-warning">' +
						'<div class="input-group-prepend">' +
						'<label class="input-group-text bg-warning text-light" for="slcMesConcBanc">MES</label>' +
						'</div>' +
						'<select class="custom-select" id="slcMesConcBanc" onchange="recargarConciliacion()">' +
						'<option value="00">--Seleccionar--</option>' +
						'<option value="01">ENERO</option>' +
						'<option value="02">FEBRERO</option>' +
						'<option value="03">MARZO</option>' +
						'<option value="04">ABRIL</option>' +
						'<option value="05">MAYO</option>' +
						'<option value="06">JUNIO</option>' +
						'<option value="07">JULIO</option>' +
						'<option value="08">AGOSTO</option>' +
						'<option value="09">SEPTIEMBRE</option>' +
						'<option value="10">OCTUBRE</option>' +
						'<option value="11">NOVIEMBRE</option>' +
						'<option value="12">DICIEMBRE</option>' +
						'</select>' +
						'</div>',
					action: function (e, dt, node, config) {
					},
				},
			],
			language: setIdioma,
			ajax: {
				url: "datos/listar/cuentas_conciliar.php",
				data: function (d) {
					d.mes = $("#slcMesConcBanc").length ? $("#slcMesConcBanc").val() : '00';
				},
				type: "POST",
				dataType: "json",
			},
			columns: [
				{ data: "banco" },
				{ data: "tipo" },
				{ data: "nombre" },
				{ data: "numero" },
				{ data: "saldo" },
				{ data: "estado" },
				{ data: "botones" }
			],
			order: [[0, "desc"]],
			"pageLength": 10,
			columnDefs: [{
				class: 'text-wrap',
				targets: [2]
			}],
			processing: true,
		});
		$("#tableConcBancaria").wrap('<div class="overflow" />');
		if ($("#slcMesConcBanc").length) {
			$(".dt-button").addClass("p-0 border-0");
			$(".dt-button").attr('disabled', true)
		}
		$("#tableDetConciliacion").DataTable({
			dom: setdom,
			language: setIdioma,
			ajax: {
				url: "datos/listar/datos_detalles_conciliacion.php",
				data: function (d) {
					d.id_cuenta = $("#id_cuenta").val();
					d.mes = $('#cod_mes').val();
				},
				type: "POST",
				dataType: "json",
			},
			columns: [
				{ data: "fecha" },
				{ data: "no_comprobante" },
				{ data: "tercero" },
				{ data: "documento" },
				{ data: "debito" },
				{ data: "credito" },
				{ data: "estado" },
				{ data: "accion" }
			],
			order: [[0, "desc"]],
			"pageLength": 10,
			columnDefs: [{
				class: 'text-wrap',
				targets: [2]
			}],
		});
		$("#tableDetConciliacion").wrap('<div class="overflow" />');
		//Fin dataTable
	});

	//--------------------------------
	//--------------informes internos
	$('#sl_libros_aux_tesoreria').on("click", function () {
		//let idt = $(this).attr('value');
		//$.post("../php/informes/frm_informes_internos.php", { idt: idt }, function (he) {
		$.post("php/informes/frm_libros_aux_tesoreria.php", {}, function (he) {
			$('#divTamModalForms').removeClass('modal-lg');
			$('#divTamModalForms').removeClass('modal-sm');
			$('#divTamModalForms').addClass('modal-lg');
			//(modal-sm, modal-lg, modal-xl) - pequeño,mediano,grande
			$('#divModalForms').modal('show');
			$("#divForms").html(he);
		});
	});

	//--------------informes bancos
	$('#sl_libros_aux_bancos').on("click", function () {
		$.post("php/informes_bancos/frm_libros_aux_bancos.php", {}, function (he) {
			$('#divTamModalForms').removeClass('modal-lg');
			$('#divTamModalForms').removeClass('modal-sm');
			$('#divTamModalForms').addClass('modal-lg');
			//(modal-sm, modal-lg, modal-xl) - pequeño,mediano,grande
			$('#divModalForms').modal('show');
			$("#divForms").html(he);
		});
	});

	//--------------- boton cargar presupuesto
	$('#btn_cargar_presupuesto').click(function () {
		var id_ctb_fuente = $('#tipodato').val();  //ej 7- nota bancaria
		var id_ctb_referencia = $('#ref_mov').val(); // ej 1 - rendimientos financieros propios
		var accion_pto = $('#hd_accion_pto').val(); // 1 o 2
		var fecha = $('#fecha').val();
		var id_tercero_api = $('#id_tercero').val();
		var tercero = $('#tercero').val();
		var objeto = $('#objeto').val();
		var id_ctb_doc = $('#id_ctb_doc').val();

		if (accion_pto > 0) {
			$.post(window.urlin + "/tesoreria/php/afectacion_presupuestal/frm_afectacion_presupuestal.php", { id_ctb_fuente: id_ctb_fuente, id_ctb_referencia: id_ctb_referencia, accion_pto: accion_pto, fecha: fecha, id_tercero_api: id_tercero_api, tercero: tercero, objeto: objeto, id_ctb_doc: id_ctb_doc }, function (he) {
				$('#divTamModalReg').removeClass('modal-xl');
				$('#divTamModalReg').removeClass('modal-sm');
				$('#divTamModalReg').addClass('modal-lg');
				$('#divModalReg').modal('show');
				$("#divFormsReg").html(he);
			});
		}
		else {
			mjeError("La acción no esta habilitada para presupuesto");
		}
	});

	//-------------- historico pagos pendientes a terceros
	$('#sl_historico_pagos_pendientes').on("click", function () {
		$.post("php/historico_pagos_pendientes/frm_historico_pagos_pendientes.php", {}, function (he) {
			$('#divTamModalForms').removeClass('modal-lg');
			$('#divTamModalForms').removeClass('modal-sm');
			$('#divTamModalForms').addClass('modal-xl');
			//(modal-sm, modal-lg, modal-xl) - pequeño,mediano,grande
			$('#divModalForms').modal('show');
			$("#divForms").html(he);
		});
	});
})(jQuery);
/*========================================================================== Utilitarios ========================================*/
//Recargar consiliación bancaria
function recargarConciliacion() {
	$('#tableConcBancaria').DataTable().ajax.reload(null, false);
};
// Cargar lista de registros para obligar en contabilidad de
let CargaObligaPago = function (boton) {
	InactivaBoton(boton);
	$.post("lista_causacion_obligaciones.php", {}, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
	ActivaBoton(boton);
};
//--- EGRESO Tesoreria nómina
function CegresoNomina(boton) {
	boton.disabled = true;
	$.post("lista_causacion_registros.php", {}, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
	setTimeout(function () {
		boton.disabled = false;
	}, 1500);
}
function CausaCENomina(boton) {
	var fila = boton.parentNode.parentNode;
	var fecha = fila.querySelector("input[name='fec_doc[]']").value;
	if (fecha == "") {
		mjeError("La fecha no puede estar vacia");
		return false;
	}
	var cant = document.getElementById("total");
	var valor = Number(cant.value);
	var data = atob(boton.getAttribute("text")) + "|" + fecha;
	data = data.split("|");
	var tipo = data[1];
	var ruta = "";
	if (tipo == "PL") {
		ruta = "procesar/causacion_planilla.php";
	} else {
		ruta = "procesar/causacion_nomina.php";
	}
	Swal.fire({
		title: "¿Confirma Causación de Nómina?",
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
						cant.value = valor - 1;
						document.getElementById("totalCausa").innerHTML = valor - 1;
						boton.innerHTML = '<span class="fas fa-thumbs-up fa-lg"></span>';
						$('#tableMvtoTesoreriaPagos').DataTable().ajax.reload(null, false);
						$("#divModalForms").modal("hide");
						mje("Registro exitoso");
					} else {
						mjeError("Error: " + response);
					}
				});
		}
	});
}
// Carga el listado de arqeuo de caja para realizar consignación
let CargaArqueoCaja = function (dato) {
	$.post("lista_consignacion_arqueo_caja.php", {}, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
let CargaArqueoCajaTes = function (id, detalle) {
	var fecha = $("#fecha").val();
	$.post("lista_causacion_arqueo.php", { id_doc: id, id_detalle: detalle, fecha: fecha }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
// Carga el listado de imputación presupuestal para ingresos
let cargaPresupuestoIng = function (dato) {

	/*
	let id_pto_do = id_ctb_doc.value;
	$.post("lista_causacion_presupuesto.php", { id_doc: id_pto_do }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});*/
};
$('#modificartableMvtoTesoreriaPagos').on('click', '.modificar', function () {
	var id_detalle = $(this).attr('text');
	let id_doc = $("#id_ctb_tipo").val();
	let id_var = $("#var_tip").val();
	GetFormDocCtb(id_doc, id_var, id_detalle);
});
$('#modificartableMvtoTesoreriaPagos').on('click', '.editarCaja', function () {
	var id_detalle = $(this).attr('text');
	let id_doc = $("#id_ctb_tipo").val();
	let id_var = $("#var_tip").val();
	GetFormDocCaja(id_doc, id_var, id_detalle);
});
// Carga el listado de imputación presupuestal para ingresos
let cargaLegalizacionCajaMenor = function (dato) {
	let id_pto_do = id_ctb_doc.value;
	$.post("lista_caja_menor_legalizacion.php", { id_doc: id_pto_do }, function (he) {
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};

// Cargar lista de causaciones para adicionar al pago del tercero
let cargaListaCausaciones = function (boton) {
	boton.disabled = true;
	let id_cop = $('#id_cop_pag').val();
	let id_tercero = $("#id_tercero").val();
	$.post("lista_causacion_listas_ter.php", { id_cop: id_cop, id_tercero: id_tercero }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
	setTimeout(function () {
		boton.disabled = false;
	}, 1500);
};

// Cargar lista de causaciones para adicionar al pago del tercero
let cargaListaInputaciones = function (dato) {
	// obtener el id del id_ctb_cop
	let id_ctb_d = id_ctb_doc.value;
	$.post("lista_causacion_rubros_consultar.php", { id_doc: id_ctb_d }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};

// Cargar lista de obligaciones para pagar
function cargarListaDetallePago(id_cop, id_doc) {
	let tipo_dato = $("#id_ctb_tipo").val();
	let tipo_movi = $("#var_tip").val();
	$('<form action="lista_documentos_pag.php" method="post">' +
		'<input type="hidden" name="id_cop" value="' + id_cop + '" />' +
		'<input type="hidden" name="id_doc" value="' + id_doc + '" />' +
		'<input type="hidden" name="tipo_dato" value="' + tipo_dato + '" />' +
		'<input type="hidden" name="tipo_var" value="' + tipo_movi + '" />' +
		'</form>').appendTo("body").submit();
}

function ListarDetallePago2(id_cop, id_doc, tipo_dato, tipo_movi) {
	$('<form action="lista_documentos_pag.php" method="post">' +
		'<input type="hidden" name="id_cop" value="' + id_cop + '" />' +
		'<input type="hidden" name="id_doc" value="' + id_doc + '" />' +
		'<input type="hidden" name="tipo_dato" value="' + tipo_dato + '" />' +
		'<input type="hidden" name="tipo_var" value="' + tipo_movi + '" />' +
		'</form>').appendTo("body").submit();
}
// Cargar lista de obligaciones para pagar
function cargarListaArqueoConsignacion(id_doc) {
	let tipo_dato = $("#id_ctb_tipo").val();
	let tipo_movi = $("#var_tip").val();
	console.log(id_doc);
	$(
		'<form action="lista_documentos_pag.php" method="post"><input type="hidden" name="id_arq" value="' +
		id_doc +
		'" /><input type="hidden" name="tipo_dato" value="' +
		tipo_dato +
		'" /><input type="hidden" name="tipo_var" value="' +
		tipo_movi +
		'" />/n</form>'
	)
		.appendTo("body")
		.submit();
}
// Cargar lista de obligaciones para pagar
function cargarListaDetallePagoEdit(id_doc) {
	let tipo_dato = $("#id_ctb_tipo").val(); // tiene el id_doc_fuente ej  7 - nota bancaria
	let tipo_movi = $("#var_tip").val();
	let url;
	if (tipo_dato == '14') {
		url = 'lista_documentos_caja.php';
	} else {
		url = 'lista_documentos_pag.php';

	}

	$('<form action="' + url + '" method="post"><input type="hidden" name="id_doc" value="' +
		id_doc +
		'" /><input type="hidden" name="tipo_dato" value="' +
		tipo_dato +
		'" /><input type="hidden" name="tipo_var" value="' +
		tipo_movi +
		'" />/n</form>'
	)
		.appendTo("body")
		.submit();
}
// Terminar de registrar movimientos de detalle  verificando sumas sumas iguales
let terminarDetalleTes = function (dato, tipo) {
	let dif = $("#total").val();
	if (dif != 0) {
		mjeError("Las sumas deben ser iguales..", "Puede usar doble click en la casilla para verificar");
	} else {
		cambiaListadoTesoreria(dato, tipo);
	}
};
// Recargar a la tabla de documento contable  por acciones en el select
function cambiaListadoTesoreria(dato, tipo) {
	$('<form action="lista_documentos_com.php" method="post">' +
		'<input type="hidden" name="id_tipo_doc" value="' + dato + '" />' +
		'<input type="hidden" name="var" value="' + tipo + '" />' +
		'</form>').appendTo("body").submit();
}
let buscarConsecutivoTeso = function (doc) {
	let fecha = $("#fecha").val();
	// verificar si ya exite numero de id_ctb_doc.value
	if (id_ctb_doc.value < 1) {
		fetch("datos/consultar/consulta_consecutivo_conta.php", {
			method: "POST",
			body: JSON.stringify({ fecha: fecha, documento: doc }),
		})
			.then((response) => response.json())
			.then((response) => {
				console.log(response);
				console.log("respuesta");
				$("#numDoc").val(response[0].numero);
			})
			.catch((error) => {
				console.log("Error:");
			});
	}
};
// Cargar lista de rubros para realizar el pago del valor
let cargaRubrosPago = function (dato, boton) {
	let id_doc = id_ctb_doc.value;
	if (id_doc == 0) {
		mjeError("Seleccione un documento contable", "Verifique");
		return false;
	} else {
		$.post("lista_causacion_obligacion_rubros.php", { id_cop: dato, id_doc: id_doc }, function (he) {
			$("#divTamModalForms").removeClass("modal-sm");
			$("#divTamModalForms").removeClass("modal-3x");
			$("#divTamModalForms").removeClass("modal-lg");
			$("#divTamModalForms").addClass("modal-xl");
			$("#divModalForms").modal("show");
			$("#divForms").html(he);
		});
	}
};

// Guardar los rubros y el valor de la afectación presupuestal asociada a la cuenta por pagar
let rubrosaPagar = function (boton) {
	InactivaBoton(boton);
	var max = 0;
	var bandera = true;
	var valor;
	$('.is-invalid').removeClass('is-invalid');
	//recorrer las input con clases detalle-pag 
	$.each($('.detalle-pag'), function () {
		max = $(this).attr('max');
		//quitar las comas y convertir a numero
		valor = $(this).val().replace(/\,/g, "", "");
		if (Number(valor) < 0 || Number(valor) > max) {
			$(this).addClass('is-invalid');
			$(this).focus();
			mjeError('El valor no puede ser menor a cero o mayor al saldo', '');
			bandera = false;
		}
	});
	if (bandera) {
		var data = $('#rubrosPagar').serialize();
		$.ajax({
			type: 'POST',
			url: 'datos/registrar/registrar_mvto_pago.php',
			data: data,
			dataType: 'json',
			success: function (r) {
				if (r.status == 'ok') {
					valor = r.valor;
					$('#valor').val(valor.toLocaleString('es-MX'));
					$('#divModalForms').modal('hide');
					mje('Proceso realizado correctamente', 'Exito');
				} else {
					mjeError('Error: ' + r.msg);
				}
			}
		});
	}
	ActivaBoton(boton);
	return false;
};
function GuardaDocPag(id) {
	$('.is-invalid').removeClass('is-invalid');
	if ($('#fecha').val() == '') {
		$('#fecha').addClass('is-invalid');
		$('#fecha').focus();
		mjeError('La fecha no puede estar vacia');
	} else if (Number($('#numDoc').val()) <= 0) {
		$('#numDoc').addClass('is-invalid');
		$('#numDoc').focus();
		mjeError('El número de documento debe ser mayor a cero');
	} else if ($('#id_tercero').val() == '0') {
		$('#terceromov').addClass('is-invalid');
		$('#terceromov').focus();
		mjeError('El tercero no puede estar vacio');
	} else if ($('#objeto').val() == '') {
		$('#objeto').addClass('is-invalid');
		$('#objeto').focus();
		mjeError('El objeto no puede estar vacio');
	} else {
		var band = true;
		if ($('#id_caja').length) {
			if ($('#id_caja').val() == '0') {
				$('#id_caja').addClass('is-invalid');
				$('#id_caja').focus();
				mjeError('La caja no puede estar vacia');
				band = false;
			}
		}
		if (band) {
			var datos = $('#formGetMvtoTes').serialize() + '&id=' + id;
			url = "datos/registrar/registrar_mvto_contable_doc_pag.php";
			$.ajax({
				type: 'POST',
				url: url,
				data: datos,
				dataType: 'json',
				success: function (r) {
					if (r.status == 'ok') {
						$('#divModalForms').modal('hide');
						mje('Proceso realizado correctamente');
						$('#tableMvtoTesoreriaPagos').DataTable().ajax.reload(null, false);
						if ($('#tableMvtoContableDetallePag').length) {
							setTimeout(function () {
								ListarDetallePago2(0, r.id, $('#tipodato').val(), 1);
							}, 400);
						}
					} else {
						mjeError('Error:', r.msg);
					}

				}
			});
		}
	}
}
// Procesar causación de cuentas por pagar con boton guardar
$('#divModalForms').on('click', '#gestionarMvtoCtbPag', function () {
	var id = $(this).attr('text');
	var btn = $(this).get(0);
	InactivaBoton(btn);
	GuardaDocPag(id);
	ActivaBoton(btn);
});
$('#GuardaDocMvtoPag').on('click', function () {
	var btn = $(this).get(0);
	InactivaBoton(btn);
	var id = $(this).attr('text');
	GuardaDocPag(id);
	ActivaBoton(btn);
});
$('#divModalForms').on('click', '#gestionarMvtoCtbCaja', function () {
	var id = $(this).attr('text');
	$('.is-invalid').removeClass('is-invalid');
	if ($('#slcTipActo').val() == '0') {
		$('#slcTipActo').addClass('is-invalid');
		$('#slcTipActo').focus();
		mjeError('El tipo de acto no puede estar vacio');
	} else if ($('#numActo').val() == '') {
		$('#numActo').addClass('is-invalid');
		$('#numActo').focus();
		mjeError('El número de acto no puede estar vacio');
	} else if ($('#txtNomCaja').val() == '') {
		$('#txtNomCaja').addClass('is-invalid');
		$('#txtNomCaja').focus();
		mjeError('El nombre de la caja no puede estar vacio');
	} else if ($('#fecIniciaCaja').val() == '') {
		$('#fecIniciaCaja').addClass('is-invalid');
		$('#fecIniciaCaja').focus();
		mjeError('La fecha de inicio no puede estar vacia');
	} else if ($('#fecActoDc').val() == '') {
		$('#fecActoDc').addClass('is-invalid');
		$('#fecActoDc').focus();
		mjeError('La fecha del acto no puede estar vacia');
	} else if ($('#txtPoliza').val() == '') {
		$('#txtPoliza').addClass('is-invalid');
		$('#txtPoliza').focus();
		mjeError('La poliza no puede estar vacia');
	} else if (Number($('#valTotal').val()) <= 0) {
		$('#valTotal').addClass('is-invalid');
		$('#valTotal').focus();
		mjeError('El valor total debe ser mayor a cero');
	} else if (Number($('#valMinimo').val()) <= 0) {
		$('#valMinimo').addClass('is-invalid');
		$('#valMinimo').focus();
		mjeError('El valor minimo debe ser mayor a cero');
	} else if (Number($('#porcentajeCs').val()) <= 0) {
		$('#porcentajeCs').addClass('is-invalid');
		$('#porcentajeCs').focus();
		mjeError('El porcentaje de caja menor debe ser mayor a cero');
	} else {
		var datos = $('#formGetMvtoCaja').serialize() + '&id=' + id;
		url = "datos/registrar/registrar_mvto_contable_caja.php";
		$.ajax({
			type: 'POST',
			url: url,
			data: datos,
			success: function (r) {
				if (r == 'ok') {
					$('#divModalForms').modal('hide');
					mje('Proceso realizado correctamente');
					$('#tableMvtoTesoreriaPagos').DataTable().ajax.reload(null, false);
				} else {
					mjeError('Error:', r);
				}

			}
		});
	}
	return false;

});
/*
const procesaCausacionPago = (id) => {
	let formEnvio = new FormData(formAddDetallePag);
	for (var pair of formEnvio.entries()) {
		console.log(pair[0] + ", " + pair[1]);
		// Espacio para validaciones
		if (formEnvio.get("fechaDoc") == "") {
			document.querySelector("#fechaDoc").focus();
			mjeError("Debe digitar un valor valido para el documento ", "");
			return false;
		}
		// verificar que el valor de la fecha no sea menor a min
		let fecha = formEnvio.get("fecha");
		// consulto el valor de min del input fecha
		let min = document.querySelector("#fecha").min;
		if (fecha < min) {
			document.querySelector("#fecha").focus();
			mjeError("La fecha no puede ser menor a la fecha de cierre ", "Fecha abierta " + min);
			return false;
		}
		if (formEnvio.get("valor_pagar") == "") {
			document.querySelector("#valor").focus();
			mjeError("Debe digitar un valor valido para el documento ", "");
			return false;
		}
		// Validar campo tercero id_tercero
		if (formEnvio.get("id_tercero") == "") {
			document.querySelector("#id_tercero").focus();
			mjeError("Debe seleccionar un tercero ", "");
			return false;
		}
	}
	fetch("datos/registrar/registrar_mvto_contable_doc_pag.php", {
		method: "POST",
		body: formEnvio,
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response[0].value == "ok" || response[0].value == "mod") {
				id_ctb_doc.value = response[0].id;
				mje("Registro guardado");
			} else {
				mjeError("Error al guardar");
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};*/
//Enviar nómina (Soporte electrónico)
const EnviarNomina = (boton) => {
	boton.disabled = true;
	let id = boton.value;
	boton.value = "";
	boton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
	let url = window.urlin + "/nomina/enviar/soportenomelec.php";
	fetch(url, {
		method: "POST",
		body: JSON.stringify({ id: id }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response[0].value == "ok") {
				boton.innerHTML = '<span class="fas fa-thumbs-up fa-lg"></span>';
				id = "tableMvtoTesoreriaPagos";
				reloadtable(id);
				mje(response[0].msg);
			} else {
				boton.disabled = false;
				boton.value = id;
				boton.innerHTML = '<span class="fas fa-paper-plane fa-lg"></span>';
				mjeError(response[0].msg);
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};
// Funcion para agregar o editar registros contables en el libro auxiliar
function GestMvtoDetallePag(elemento) {
	InactivaBoton(elemento);
	$('.is-invalid').removeClass('is-invalid');
	var opc = elemento.getAttribute('text');
	var fila = elemento.closest('tr');
	var tipoDato = fila.querySelector('input[name="tipoDato"]');
	var codigoCta = fila.querySelector('input[name="codigoCta"]');
	var idTercero = fila.querySelector('input[name="idTercero"]');
	var bTercero = fila.querySelector('input[name="bTercero"]');
	var valorDebito = fila.querySelector('input[name="valorDebito"]');
	var valorCredito = fila.querySelector('input[name="valorCredito"]');
	var id_codigoCta = fila.querySelector('input[name="id_codigoCta"]');
	if (tipoDato.value == 'M' || tipoDato.value == '0') {
		codigoCta.focus();
		mjeError('La cuenta seleccionada no es de tipo detalle', '');
		codigoCta.classList.add('is-invalid');
	} else if (idTercero.value == '0') {
		bTercero.focus();
		mjeError('El tercero no puede estar vacio', '');
		bTercero.classList.add('is-invalid');
	} else if (Number(valorDebito.value) == 0 && Number(valorCredito.value) == 0 || (Number(valorDebito.value) > 0 && Number(valorCredito.value) > 0)) {
		valorDebito.focus();
		mjeError('El valor del debito o credito debe ser mayor a cero', '');
		valorDebito.classList.add('is-invalid');
		valorCredito.classList.add('is-invalid');
	} else {
		var datos = new FormData();
		datos.append('id_ctb_doc', $('#id_ctb_doc').val());
		datos.append('idTercero', idTercero.value);
		datos.append('id_crpp', $('#id_crpp').val());
		datos.append('id_codigoCta', id_codigoCta.value);
		datos.append('valorDebito', valorDebito.value);
		datos.append('valorCredito', valorCredito.value);
		datos.append('opcion', opc);
		var url = 'datos/registrar/registrar_mvto_contable_det.php';
		fetch(url, {
			method: "POST",
			body: datos,
		})
			.then((response) => response.text())
			.then((response) => {
				if (response == "ok") {
					if (opc == '0') {
						$('#codigoCta').val('');
						$('#id_codigoCta').val('0');
						$('#tipoDato').val('0');
						$('#bTercero').val('');
						$('#idTercero').val('');
						$('#valorDebito').val('0');
						$('#valorCredito').val('0');
						$('#tipoDato').val('');
					}
					$('#tableMvtoContableDetallePag').DataTable().ajax.reload(function (json) {
						// Obtener los datos del tfoot de la DataTable
						var tfootData = json.tfoot;
						// Construir el tfoot de la DataTable
						var tfootHtml = '<tfoot><tr>';
						$.each(tfootData, function (index, value) {
							tfootHtml += '<th>' + value + '</th>';
						});
						tfootHtml += '</tr></tfoot>';
						// Reemplazar el tfoot existente en la tabla
						$('#tableMvtoContableDetallePag').find('tfoot').remove();
						$('#tableMvtoContableDetallePag').append(tfootHtml);
					});
					mje('Registro exitoso');
				} else {
					mjeError('Error:', response);
				}
			});
	}
	ActivaBoton(elemento);
	return false;
};
// Eliminar un registro de detalles
const eliminarRegistroDetalletesPag = (id) => {
	// mensaje de confirmación
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
		if (result.isConfirmed) {
			fetch("datos/eliminar/eliminar_mvto_libaux.php", {
				method: "POST",
				body: JSON.stringify({ id: id }),
			})
				.then((response) => response.json())
				.then((response) => {
					if (response.status == "ok") {
						$('#tableMvtoContableDetallePag').DataTable().ajax.reload(function (data) {
							// Obtener los datos del tfoot de la DataTable
							var tfootData = data.tfoot;
							// Construir el tfoot de la DataTable
							var tfootHtml = '<tfoot><tr>';
							$.each(tfootData, function (index, value) {
								tfootHtml += '<th>' + value + '</th>';
							});
							tfootHtml += '</tr></tfoot>';
							// Reemplazar el tfoot existente en la tabla
							$('#tableMvtoContableDetallePag').find('tfoot').remove();
							$('#tableMvtoContableDetallePag').append(tfootHtml);
						});
						mje("Registro eliminado");
					} else {
						mjeError("Error: " + response.msg);
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};
$("#modificartableMvtoContableDetallePag").on('click', '.modificar', function () {
	var id = $(this).attr("text");
	var fila = $(this).parent().parent().parent();
	$.ajax({
		type: "POST",
		url: "datos/consultar/modifica_detalle_libaux.php",
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
$("#tableMvtoContableDetallePag").on("input", ".bTercero", function () {
	var fila = $(this).closest("tr");
	var idTercero = fila.find("input[name='idTercero']");
	$(this).autocomplete({
		source: function (request, response) {
			$.ajax({
				url: window.urlin + "/presupuesto/datos/consultar/buscar_terceros.php",
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
			idTercero.val(ui.item.id);
		}
	});
});
// Eliminar documento contable ctb_doc
const eliminarRegistroTec = (id) => {
	// mensaje de confirmación
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
		if (result.isConfirmed) {
			fetch("datos/eliminar/eliminar_mvto_doc.php", {
				method: "POST",
				body: JSON.stringify({ id: id }),
			})
				.then((response) => response.json())
				.then((response) => {
					if (response.status == "ok") {
						mje("Registro eliminado");
						$('#tableMvtoTesoreriaPagos').DataTable().ajax.reload(null, false);
					} else {
						mjeError("Error al eliminar: " + response.msg);
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};
const eliminarRegistroCaja = (id) => {
	// mensaje de confirmación
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
		if (result.isConfirmed) {
			fetch("datos/eliminar/eliminar_mvto_caja.php", {
				method: "POST",
				body: JSON.stringify({ id: id }),
			})
				.then((response) => response.json())
				.then((response) => {
					if (response.status == "ok") {
						mje("Registro eliminado");
						$('#tableMvtoTesoreriaPagos').DataTable().ajax.reload(null, false);
					} else {
						mjeError("Error al eliminar: " + response.msg);
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};

function cargarResponsableCaja(id_caja, id_detalle) {
	$.post("lista_caja_menor_responsable.php", { id_caja: id_caja, id_detalle: id_detalle }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
}
function cargarListaDetallePag(id_doc) {
	/* $('<form action="lista_documentos_det.php" method="post"><input type="hidden" name="id_doc" value="' + id_doc + '" /></form>')
	  .appendTo("body")
	  .submit();
	  */
	let ruta = {
		url: "lista_documentos_pag.php",
		name1: "id_doc",
		valor1: id_doc,
		name2: "tipo_dato",
		valor2: "NCXP",
	};
	redireccionar2(ruta);
}
function GuardaRespCaja() {
	var id = $('#id_caja').val();
	$('.is-invalid').removeClass('is-invalid');
	if ($('#id_tercero').val() == '0') {
		$('#tercerocrp').addClass('is-invalid');
		$('#tercerocrp').focus();
		mjeError('El responsable no puede estar vacio');
	} else if ($('#fecha_ini').val() == '') {
		$('#fecha_ini').addClass('is-invalid');
		$('#fecha_ini').focus();
		mjeError('La fecha de inicio no puede estar vacia');
	} else if ($('#fecha_fin').val() == '') {
		$('#fecha_fin').addClass('is-invalid');
		$('#fecha_fin').focus();
		mjeError('La fecha de fin no puede estar vacia');
	} else if ($('#fecha_ini').val() > $('#fecha_fin').val()) {
		$('#fecha_ini').addClass('is-invalid');
		$('#fecha_ini').focus();
		mjeError('La fecha de inicio no puede ser mayor a la fecha de fin');
	} else if (Number($('#reg').val()) == 1 && Number($('#id_detalle').val()) == 0) {
		mjeError('Esta caja ya tiene un usuario activo responsable', 'Desactive los usuarios anteriores');
	} else {
		var data = $('#formAddResponsableCaja').serialize();
		$.ajax({
			type: 'POST',
			url: 'datos/registrar/guarda_resposable.php',
			data: data,
			dataType: 'json',
			success: function (r) {
				if (r.status == 'ok') {
					cargarResponsableCaja(id, 0);
					mje('Proceso realizado correctamente', 'Exito');
				} else {
					mjeError('Error: ' + r.msg);
				}
			}
		});
	}
}
function GuardarRubrosCaja() {
	var id = $('#id_caja').val();
	$('.is-invalid').removeClass('is-invalid');
	if ($('#slcConcepto').val() == '0') {
		$('#slcConcepto').addClass('is-invalid');
		$('#slcConcepto').focus();
		mjeError('Debe seleccionar un tipo de gasto');
	} else if (Number($('#numValor').val()) <= 0) {
		$('#numValor').addClass('is-invalid');
		$('#numValor').focus();
		mjeError('El valor no puede ser menor o igual a cero');
	} else if (Number($('#numValor').val()) > Number($('#numValor').attr('max'))) {
		$('#numValor').addClass('is-invalid');
		$('#numValor').focus();
		mjeError('El valor no puede ser mayor al saldo ' + $('#numValor').attr('max'));
	} else if ($('#id_rubroCod').val() == '0') {
		$('#rubroCod').addClass('is-invalid');
		$('#rubroCod').focus();
		mjeError('Debe seleccionar un tipo de rubro');
	} else if ($('#tipoRubro').val() == '0') {
		$('#rubroCod').addClass('is-invalid');
		$('#rubroCod').focus();
		mjeError('El rubro no es un detalle', 'Seleccione un rubro de detalle');
	} else if ($('#id_codigoCta').val() == '0') {
		$('#codigoCta').addClass('is-invalid');
		$('#codigoCta').focus();
		mjeError('Debe seleccionar una cuenta contable');
	} else if ($('#tipoDato').val() != 'D') {
		$('#codigoCta').addClass('is-invalid');
		$('#codigoCta').focus();
		mjeError('La cuenta seleccionada no es de tipo detalle', 'Seleccione una cuenta de detalle');
	} else {
		var data = $('#formAddRubrosCaja').serialize();
		$.ajax({
			type: 'POST',
			url: 'datos/registrar/guarda_rubros.php',
			data: data,
			dataType: 'json',
			success: function (r) {
				if (r.status == 'ok') {
					cargarRubrosCaja(id, 0);
					mje('Proceso realizado correctamente', 'Exito');
				} else {
					mjeError('Error: ' + r.msg);
				}
			}
		});
	}
}
var DetalleImputacionCajaMenor = function () {
	var band = true;
	var valor = 0;
	var min, max;
	$('.is-invalid').removeClass('is-invalid');
	$('.ValImputacion').each(function () {
		valor = $(this).val();
		min = Number($(this).attr('min'));
		max = Number($(this).attr('max'));
		valor = Number(valor.replace(/\,/g, "", ""));
		if (valor < min || valor > max) {
			$(this).addClass('is-invalid');
			$(this).focus();
			mjeError('El valor debe estar entre ' + min.toLocaleString("es-MX") + ' y ' + max.toLocaleString("es-MX"));
			band = false;
			return false;
		}
	});
	if (band) {
		var id_ctb_doc = $('#id_ctb_doc').val();
		var data = $('#formImputacion').serialize() + '&id_ctb_doc=' + id_ctb_doc;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'datos/registrar/registrar_mvto_caja.php',
			data: data,
			success: function (r) {
				if (r.status == 'ok') {
					$('#valor').val(r.acumulado);
					mje('Proceso realizado correctamente');
				} else {
					mjeError('Error:', r.msg);
				}
			}
		});
	}
};
function EditResponsableCaja(detalle) {
	let id = $('#id_caja').val();
	cargarResponsableCaja(id, detalle);
	$('#tercerocrp').focus();
}
function EditRubroCaja(detalle) {
	let id = $('#id_caja').val();
	cargarRubrosCaja(id, detalle);
	$('#slcConcepto').focus();
}
function ModEstadoResposableCaja(id, estado) {
	let id_caja = $('#id_caja').val();
	$.ajax({
		type: 'POST',
		url: 'datos/registrar/mod_estado_responsable_caja.php',
		data: { id: id, estado: estado },
		success: function (r) {
			cargarResponsableCaja(id_caja, 0);
		}
	});
	return false;
}

function cargarRubrosCaja(id_caja, id_detalle) {
	$.post("lista_caja_menor_rubros.php", { id_caja: id_caja, id_detalle: id_detalle }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
}
//============================================  FORMA DE PAGO ============================================*/

// Cargar lista de centros de costo para realizar la causación del valor
let cargaFormaPago = (cop, detalle, boton) => {
	boton.disabled = true;
	let valor_pago = 0;
	let id_docu = id_ctb_doc.value;
	let id_cop = id_cop_pag.value;
	if (id_cop == 0) {
		valor_pago = 1;
	} else {
		valor_pago = parseFloat(valor.value.replace(/\,/g, "", ""));
	}

	if (id_docu > 0) {
		if (valor_pago != "") {
			$.post("lista_causacion_formapago.php", { id_doc: id_docu, id_cop: id_cop, valor: valor_pago, id_fp: detalle }, function (he) {
				$("#divTamModalForms").removeClass("modal-sm");
				$("#divTamModalForms").removeClass("modal-lg");
				$("#divTamModalForms").removeClass("modal-3x");
				$("#divTamModalForms").addClass("modal-xl");
				$("#divModalForms").modal("show");
				$("#divForms").html(he);
			});
		} else {
			//document.querySelector("#valor").focus();
			mjeError("No ha seleccionado un valor de la obligación");
		}
	} else {
		mjeError("No puede causar centros de costo", "Primero guarde el documento");
	}
	setTimeout(() => {
		boton.disabled = false;
	}, 1500);
};

// ==========================================================  ARQUEO DE CAJA ============================================*/

// Calcular copagos por cajero
const calcularCopagos2 = (postData) => {
	$.ajax({
		type: "POST",
		url: "datos/consultar/consulta_copagos.php",
		data: { tercero: postData.value, fecha_ini: fecha_arqueo_ini.value, fecha_fin: fecha_arqueo_fin.value },
		dataType: "json",
		success: function (data) {
			if (data.status == "ok") {
				valor_fact.value = data.facturado;
			} else {
				mjeError("Sin valor facturado", "No se encontraron registros");
				valor_fact.value = 0;
			}
		},
	});
};
// validar diferencia de arqueo a consignación
let validarDiferencia = () => {
	let valor_facturado = parseFloat(valor_fact.value.replace(/\,/g, "", ""));
	let valor_arqueo = parseFloat(valor_arq.value.replace(/\,/g, "", ""));
	let diferencia = valor_arqueo - valor_facturado;
	// validar si observacion es diferente de cero
	if (diferencia > 0) {
		observaciones.value = "MAYOR VALOR RECAUDADO " + diferencia;
	}
	if (diferencia < 0) {
		observaciones.value = "MENOR VALOR RECAUDADO " + diferencia;
	}
};

// copiar el valor de valor_fact a valor_arq
let copiarValor = function () {
	valor_arq.value = valor_fact.value;
};

//Guarda el arqueo de caja
function GuardaMvtoDetalle(id, op, boton) {
	InactivaBoton(boton);
	$('.is-invalid').removeClass('is-invalid');
	if ($('#id_facturador').val() == '0') {
		$('#id_facturador').addClass('is-invalid');
		$('#id_facturador').focus();
		mjeError('Debe seleccionar un facturador');
	} else if ($('#valor_fact').val() == '0') {
		$('#valor_fact').addClass('is-invalid');
		$('#valor_fact').focus();
		mjeError('El valor facturado no puede ser cero');
	} else if (Number($('#valor_arq').val()) <= 0) {
		$('#valor_arq').addClass('is-invalid');
		$('#valor_arq').focus();
		mjeError('El valor del arqueo debe ser mayor a cero');
	} else {
		var data = $('#formAddFacturador').serialize() + '&id=' + id + '&op=' + op;
		$.ajax({
			type: 'POST',
			url: 'datos/registrar/registrar_mvto_arqueo_caja.php',
			data: data,
			dataType: 'json',
			success: function (r) {
				if (r.status == 'ok') {
					$('#arqueo_caja').val(r.valor);
					CargaArqueoCajaTes($('#id_ctb_doc').val(), 0);
					mje('Proceso realizado correctamente');
				} else {
					mjeError('Error:', r.msg);
				}
			}
		});
	}
	ActivaBoton(boton);
}
document.addEventListener("submit", async (e) => {
	e.preventDefault();
	if (e.target.id == "formAddFacturador") {
		// Valida que descuento sea mayor a cero
		let valor = parseFloat(valor_arq.value.replace(/\,/g, "", ""));
		if (valor > 0) {
			let formEnvio = new FormData(formAddFacturador);
			for (var pair of formEnvio.entries()) {
				console.log(pair[0] + ", " + pair[1]);
				// validar que el campo id_facturador sea mayor a cero
				if (pair[0] == "id_facturador") {
					if (pair[1] == 0) {
						mjeError("No ha seleccionado un facturador");
						return false;
					}
				}
			}
			try {
				const response = await fetch("datos/registrar/registrar_mvto_arqueo_caja.php", {
					method: "POST",
					body: formEnvio,
				});

				// verificar que data existe
				if (response.ok) {
					const data = await response.text();
					valor_fact.value = 0;
					valor_arq.value = 0;
					// consultar el valor total recaudado
					const recaudado = await fetch("datos/consultar/consulta_total_recaudado.php", {
						method: "POST",
						body: JSON.stringify({ doc: id_ctb_doc.value }),
					});
					const data2 = await recaudado.json();
					arqueo_caja.value = parseFloat(data2[0].total.replace(/\,/g, "", ""));
					$("#id_facturador").val("0");
					observaciones.value = "";
					$("#tableCausacionArqueo>tbody").prepend(data);
				}
			} catch (error) {
				console.error(error);
			}
		} else {
			mjeError("El valor debe ser mayor a cero");
		}
	}
});
//Eliminar arqueo de caja en la tabla tes_causa_arqueo
let eliminarRecaduoArqeuo = async (id) => {
	try {
		const response = await fetch("datos/eliminar/eliminar_causa_arqueo.php", {
			method: "POST",
			body: JSON.stringify({ id: id }),
		});
		const data = await response.json();
		if (data[0].value == "ok") {
			mje("Registro eliminado");
			// recargar tabla tableCausacionArqueo
			$("#" + data[0].id).remove();
			valor_fact.value = 0;
		} else {
			mjeError("No se pudo eliminar el registro");
		}
	} catch (error) {
		console.error(error);
	}
};

//=====================================================================================================================*/
// Eliminar imputacion presupuestal de ingresos
let eliminaRubroIng = async (id) => {
	try {
		const response = await fetch("datos/eliminar/eliminar_imputacion_ing.php", {
			method: "POST",
			body: JSON.stringify({ id: id }),
		});
		const data = await response.json();
		if (data[0].value == "ok") {
			mje("Registro eliminado");
			// recargar tabla tableCausacionArqueo
			$("#" + data[0].id).remove();
			valor_fact.value = 0;
		} else {
			mjeError("No se pudo eliminar el registro");
		}
	} catch (error) {
		console.error(error);
	}
};
//

// Mostrar sedes por municipio
let mostrarCuentas = function (dato) {
	let id_banco = banco.value;
	fetch("datos/consultar/consulta_tes_cuentas.php", {
		method: "POST",
		body: JSON.stringify({ id: id_banco }),
	})
		.then((response) => response.text())
		.then((response) => {
			divBanco.innerHTML = response;
			documento.value = "";
			forma_pago.selectedIndex = 0;
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Mostrar cuentas pendientes de relacionar en tesoreria
let mostrarCuentasPendiente = function (dato) {
	let id_banco = banco.value;
	fetch("datos/consultar/consulta_tes_cuentas_pendiente.php", {
		method: "POST",
		body: JSON.stringify({ id: id_banco }),
	})
		.then((response) => response.text())
		.then((response) => {
			divBanco.innerHTML = response;
			documento.value = "";
			forma_pago.selectedIndex = 0;
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Guarda cuenta bancaria, forma de pago y valor
var GuardaFormaPago = function (boton) {
	InactivaBoton(boton);
	$('.is-invalid').removeClass('is-invalid');
	if ($('#banco').val() == '0') {
		$('#banco').addClass('is-invalid');
		$('#banco').focus();
		mjeError('Debe seleccionar un banco');
	} else if ($('#cuentas').val() == '0') {
		$('#cuentas').addClass('is-invalid');
		$('#cuentas').focus();
		mjeError('Debe seleccionar una cuenta');
	} else if ($('#forma_pago_det').val() == '0') {
		$('#forma_pago_det').addClass('is-invalid');
		$('#forma_pago_det').focus();
		mjeError('Debe seleccionar una forma de pago');
	} else if ($('#documento').val() == '') {
		$('#documento').addClass('is-invalid');
		$('#documento').focus();
		mjeError('Debe digitar un número de documento');
	} else if (Number($('#valor_pag').val()) <= 0) {
		$('#valor_pag').addClass('is-invalid');
		$('#valor_pag').focus();
		mjeError('El valor debe ser mayor a cero');
	} else {
		var datos = $('#formAddFormaPago').serialize();
		var url = "datos/registrar/registrar_mvto_registrar_forma_pago.php";
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: url,
			data: datos,
			success: function (r) {
				if (r.status == 'ok') {
					let value = r.valor;
					$('#forma_pago').val(value.toLocaleString('es-MX'));
					mje('Proceso realizado correctamente');
					cargaFormaPago(0, 0, boton);
				} else {
					mjeError('Error:', r.msg);
				}

			}
		});

	}
	ActivaBoton(boton);
};

function DetalleArqueoCaja(id, boton) {
	boton.disabled = true;
	$.post("datos/listar/datos_detalle_facturador.php", { id: id }, function (he) {
		$("#divTamModalReg").removeClass("modal-sm");
		$("#divTamModalReg").removeClass("modal-lg");
		$("#divTamModalReg").addClass("modal-xl");
		$("#divModalReg").modal("show");
		$("#divFormsReg").html(he);
	});
	setTimeout(() => {
		boton.disabled = false;
	}, 1500);
};
document.addEventListener("submit", (e) => {
	e.preventDefault();
	if (e.target.id == "formAddFormaPago") {
		// Valida que descuento sea mayor a cero
		let id_ctb_doc = id_doc.value;
		let data = [id_ctb_doc, 0];
		let valor = parseFloat(valor_pag.value.replace(/\,/g, "", ""));
		if (valor > 0) {
			let formEnvio = new FormData(formAddFormaPago);
			for (var pair of formEnvio.entries()) {
				console.log(pair[0] + ", " + pair[1]);
			}
			fetch("datos/registrar/registrar_mvto_registrar_forma_pago.php", {
				method: "POST",
				body: formEnvio,
			})
				.then((response) => response.text())
				.then((response) => {
					let valorpagado = valorRegPagos("datos/consultar/consulta_pagos_valor.php", data);
					valorpagado.then((respuesta) => {
						let valorret = parseFloat(respuesta[0].valor_pag);
						console.log("respnse 2 " + valorret);
					});
					console.log("respnse 1 " + response);
					//id_reteformAddRetencioness.reset();
					$("#tableCausacionPagos>tbody").prepend(response);
				})
				.catch((error) => {
					console.log("Error llegada:");
				});
		} else {
			mjeError("El descuento debe ser mayor a cero");
		}
	}
});

const valorRegPagos = async (url, datos) => {
	return await fetch(url, {
		method: "POST",
		body: JSON.stringify({ id: datos }),
	})
		.then((response) => response.json())
		.then((response) => {
			return response;
		});
};

// Eliminar centro de costo asignado a una causación
const eliminarFormaPago = (dato) => {
	let id_ctb_doc = id_doc.value;
	let data = [id_ctb_doc, 0];
	fetch("datos/eliminar/eliminar_forma_pago.php", {
		method: "POST",
		body: JSON.stringify({ id: dato }),
	})
		.then((response) => response.json())
		.then((response) => {
			if (response[0].value == "ok") {
				mje("Registro eliminado exitosamente");
				// Eliminar la fila de la tabla
				$("#" + dato).remove();
				let data = [id_docu, 0];
				let registrado = valorRegPagos("datos/consultar/consulta_pagos_valor.php", data);
				registrado.then((response) => {
					let valor_reg = parseFloat(response[0].valor_pag);
					valor_pag.value = total.toLocaleString("es-MX");
				});
			} else {
				mjeError("Error al eliminar");
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Genera movimiento cuando se hace procesamiento automatico del documento cxp
const generaMovimientoPag = (boton) => {
	InactivaBoton(boton);
	let id = id_ctb_doc.value;
	let id_cop = id_cop_pag.value;
	let tipo = $('#tipodato').val();
	// verificar si los tres valores son iguales
	let id_crp = $('#id_crp').length ? $('#id_crp').val() : 0;
	fetch("datos/registrar/registrar_mvto_libaux_auto_pag.php", {
		method: "POST",
		body: JSON.stringify({ id: id, id_crp: id_crp, id_cop: id_cop, tipo: tipo }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response.status == "ok") {
				mje("Movimiento generado con éxito ");
				$('#tableMvtoContableDetallePag').DataTable().ajax.reload(function (json) {
					// Obtener los datos del tfoot de la DataTable
					var tfootData = json.tfoot;
					// Construir el tfoot de la DataTable
					var tfootHtml = '<tfoot><tr>';
					$.each(tfootData, function (index, value) {
						tfootHtml += '<th>' + value + '</th>';
					});
					tfootHtml += '</tr></tfoot>';
					// Reemplazar el tfoot existente en la tabla
					$('#tableMvtoContableDetallePag').find('tfoot').remove();
					$('#tableMvtoContableDetallePag').append(tfootHtml);
				});
			} else {
				mjeError("Error: " + response.msg);
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
	ActivaBoton(boton);
};
const generaMovimientoCaja = () => {
	let id = id_ctb_doc.value;
	let id_cop = id_cop_pag.value;
	let tipo = $('#tipodato').val();
	// verificar si los tres valores son iguales
	let id_crp = $('#id_crp').length ? $('#id_crp').val() : 0;
	fetch("datos/registrar/registrar_mvto_libaux_auto_caja.php", {
		method: "POST",
		body: JSON.stringify({ id: id, id_crp: id_crp, id_cop: id_cop, tipo: tipo }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response.status == "ok") {
				mje("Movimiento generado con éxito ");
				$('#tableMvtoContableDetallePag').DataTable().ajax.reload(function (json) {
					// Obtener los datos del tfoot de la DataTable
					var tfootData = json.tfoot;
					// Construir el tfoot de la DataTable
					var tfootHtml = '<tfoot><tr>';
					$.each(tfootData, function (index, value) {
						tfootHtml += '<th>' + value + '</th>';
					});
					tfootHtml += '</tr></tfoot>';
					// Reemplazar el tfoot existente en la tabla
					$('#tableMvtoContableDetallePag').find('tfoot').remove();
					$('#tableMvtoContableDetallePag').append(tfootHtml);
				});
			} else {
				mjeError("Error: " + response.msg);
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};

const ImputacionCtasCajas = (id) => {
	let url = "lista_imputacion_caja.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
/*=================================   IMPRESION DE FORMATOS =====================================*/
const imprimirFormatoTes = (id) => {
	if (id == "") {
		id = id_ctb_doc.value;
	}
	let url = "soportes/imprimir_formato_pag.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};

const ImpConcBanc = (id) => {
	let mes = $("#slcMesConcBanc").length ? $("#slcMesConcBanc").val() : $('#cod_mes').val();
	let url = "soportes/imprimir_formato_conc.php";
	$.post(url, { id: id, mes: mes }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
// Cerrar documento contable
let cerrarDocumentoCtbTes = function (dato) {
	fetch("datos/consultar/consultaCerrar.php", {
		method: "POST",
		body: dato,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response.status == "ok") {
				//mje("Documento cerrado");
				location.reload();
			} else {
				mjeError("Documento no cerrado", "Verifique información ingresada" + response.msg);
			}
		});
};
const imprSelecTes = (nombre, id) => {
	if (id > 0) {
		cerrarDocumentoCtbTes(id);
	}
	var ficha = document.getElementById(nombre);
	var ventimp = window.open(" ", "popimpr");
	ventimp.document.write(ficha.innerHTML);
	ventimp.document.close();
	ventimp.print();
	ventimp.close();
};
const ConciliacionBancaria = (id) => {
	let mes = document.getElementById("slcMesConcBanc").value;
	$('<form action="detalle_conciliacion_bancaria.php" method="post">' +
		'<input type="hidden" name="id_cuenta" value="' + id + '" />' +
		'<input type="hidden" name="mes" value="' + mes + '" />' +
		'</form>').appendTo("body").submit();

};
var valoresid = [];
//=========================================== scrip para seleccion de varias casuaciones ===========================================
let cargarIdCAsuaciones = (id) => {
	let campo = "checLista_" + id;
	let isChecked = document.getElementById(campo).checked;
	if (isChecked) {
		valoresid.push(id);
	} else {
		let pos = valoresid.indexOf(id);
		valoresid.splice(pos, 1);
	}
	console.log(valoresid);
};

// ========================================= scrip para eliminar imputacion presupuestal del egreso ===============================
const eliminarImputacionPag = (comp) => {
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
		if (result.isConfirmed) {
			fetch("datos/eliminar/eliminar_mvto_imputacion_pag.php", {
				method: "POST",
				body: JSON.stringify({ id: comp }),
			})
				.then((response) => response.json())
				.then((response) => {
					console.log(response);
					if (response[0].value == "ok") {
						valor.value = 0;
						mje("Registro eliminado");
					} else {
						mjeError("Error al eliminar");
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};
// ========================================= scrip para realizar registro del ingreso recaudo =================
const registrarPresupuestoIng = async () => {
	// Datos adicionales
	let id_doc = id_ctb_doc.value;
	// validaciones
	if (tipoRubro.value == 0) {
		mjeError("Debe seleccionar un rubro de detalle");
		return false;
	}
	let formEnvio = new FormData(formAddFormaIng);
	formEnvio.append("id_doc", id_doc);
	formEnvio.append("id_manu", numDoc.value);
	formEnvio.append("fecha", fecha.value);
	formEnvio.append("objeto", objeto.value);

	for (var pair of formEnvio.entries()) {
		console.log(pair[0] + ", " + pair[1]);
	}
	try {
		const response = await fetch("datos/registrar/registrar_mvto_presupuesto.php", {
			method: "POST",
			body: formEnvio,
		});
		const data = await response.json();
		console.log(data);
		// resetera formulario
		formAddFormaIng.reset();
		id_pto_doc.value = data[0].id_pto;
		// recarga tabla

		$("#tableCausacionIng>tbody").prepend(data[0].tabla);
	} catch (error) {
		console.error(error);
	}
};
// ===================================================  SCRIPT PARA ABRIR DOCUMENTO =================================
let abrirDocumentoTes = function (id) {
	//let doc = id_ctb_doc.value;
	fetch("datos/consultar/consultaAbrir.php", {
		method: "POST",
		body: id,
	})
		.then((response) => response.text())
		.then((response) => {
			if (response == "ok") {
				mje("Documento abierto");
				$("#tableMvtoTesoreriaPagos").DataTable().ajax.reload(null, false);
			} else {
				mjeError("Error al abrir documento: " + response);
			}
		});
};

// ===================================================  SCRIPT PARA AUTOCOMPLENTE RUBRO INGRESOS =================================
// Autocomplete rubro cdp
document.addEventListener("keyup", (e) => {
	if (e.target.id == "rubroIng") {
		$("#rubroIng").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "datos/consultar/consultaRubrosIng.php",
					type: "post",
					dataType: "json",
					data: {
						search: request.term,
					},
					success: function (data) {
						response(data);
					},
				});
			},
			select: function (event, ui) {
				$("#rubroIng").val(ui.item.label);
				$("#id_rubroIng").val(ui.item.value);
				$("#tipoRubro").val(ui.item.tipo);
				return false;
			},
			focus: function (event, ui) {
				$("#rubroIng").val(ui.item.label);
				return false;
			},
		});
	}
});

// ========================================= scrip para buscar el valor de un movimiento  ===============================
const valorMovTeroreria = () => {
	valor_pag.value = valor_teso.value;
	if (arqueo_caja.value > 0) {
		valor_pag.value = arqueo_caja.value;
	}
};
// ========================================= scrip para modificar la fecha inicial del formulario  ===============================
const buscarFechaDoc = () => { };

//================================================ Script para autocompletado de cuentas =================================================
// Autocomplete para la selección del tercero que se asigna al registro presupuestal
document.addEventListener("keyup", (e) => {
	if (e.target.id == "codigocta_ini") {
		let valor = "";
		$("#codigocta_ini").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "datos/consultar/consultaPgcp.php",
					type: "post",
					dataType: "json",
					data: {
						search: request.term,
						valor: valor,
					},
					success: function (data) {
						response(data);
					},
				});
			},
			select: function (event, ui) {
				$("#codigocta_ini").val(ui.item.label);
				$("#id_codigoctaini").val(ui.item.value);

				return false;
			},
			focus: function (event, ui) {
				$("#id_codigoctaini").val(ui.item.label);
				return false;
			},
		});
	}
});
// Cuenta final
document.addEventListener("keyup", (e) => {
	if (e.target.id == "codigocta_fin") {
		let valor = "";
		$("#codigocta_fin").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "datos/consultar/consultaPgcp.php",
					type: "post",
					dataType: "json",
					data: {
						search: request.term,
						valor: valor,
					},
					success: function (data) {
						response(data);
					},
				});
			},
			select: function (event, ui) {
				$("#codigocta_fin").val(ui.item.label);
				$("#id_codigoctafin").val(ui.item.value);

				return false;
			},
			focus: function (event, ui) {
				$("#id_codigocta_fin").val(ui.item.label);
				return false;
			},
		});
	}
});
// ================================== ANULACION DE DCUMENTOS CONTABLES ======================================
const anularDocumentoTes = (id) => {
	let url = "form_fecha_anulacion.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
function changeEstadoAnulacionTes() {
	$('.is-invalid').removeClass('is-invalid');
	if ($('#fecha').val() == '') {
		$('#fecha').addClass('is-invalid');
		$('#fecha').focus();
		mjeError('Debe seleccionar una fecha');
	} else if ($('#objeto').val() == '') {
		$('#objeto').addClass('is-invalid');
		$('#objeto').focus();
		mjeError('Debe digitar un motivo de anulación');
	} else {
		var datos = $('#formAnulaDocTes').serialize();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: "datos/registrar/registrar_anulacion_tes.php",
			data: datos,
			success: function (r) {
				if (r.status == 'ok') {
					$('#divModalForms').modal('hide');
					$('#tableMvtoTesoreriaPagos').DataTable().ajax.reload(null, false);
					mje('Documento anulado correctamente');
				} else {
					mjeError('Error: ' + r.msg);
				}
			}
		});
	}
}
// ========================================== Gestión de chequeras ======================================================
// Enviar datos para anulacion
//Guardar datos de chequera
const GuardarChequera = (boton) => {
	InactivaBoton(boton);
	$('.is-invalid').removeClass('is-invalid');
	if ($('#banco').val() == '0') {
		$('#banco').addClass('is-invalid');
		$('#banco').focus();
		mjeError('Debe seleccionar un banco');
	} else if ($('#cuentas').val() == '0') {
		$('#cuentas').addClass('is-invalid');
		$('#cuentas').focus();
		mjeError('Debe seleccionar una cuenta');
	} else if ($('#num_chequera').val() == '') {
		$('#num_chequera').addClass('is-invalid');
		$('#num_chequera').focus();
		mjeError('Debe digitar un número de chequera');
	} else if ($('#fecha').val() == '') {
		$('#fecha').addClass('is-invalid');
		$('#fecha').focus();
		mjeError('Debe digitar una fecha');
	} else if (Number($('#inicial').val()) <= 0) {
		$('#inicial').addClass('is-invalid');
		$('#inicial').focus();
		mjeError('El valor inicial debe ser mayor a cero');
	} else if (Number($('#maximo').val()) <= 0) {
		$('#maximo').addClass('is-invalid');
		$('#maximo').focus();
		mjeError('El valor máximo debe ser mayor a cero');
	} else if (Number($('#inicial').val()) >= Number($('#maximo').val())) {
		$('#inicial').addClass('is-invalid');
		$('#inicial').focus();
		mjeError('El valor inicial debe ser mayor al valor máximo');
	} else {
		var data = $('#formNuevaChequera').serialize();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: "datos/registrar/registrar_chequera_nueva.php",
			data: data,
			success: function (r) {
				if (r.status == 'ok') {
					$('#divModalForms').modal('hide');
					$('#tableFinChequeras').DataTable().ajax.reload(null, false);
					mje('Chequera guardada con  éxito...');
				} else {
					mjeError('Error: ' + r.msg);
				}
			}
		});
	}
	ActivaBoton(boton);
};
const SSguardarChequera = async () => {
	let formEnvio = new FormData(formNuevaChequera);
	for (var pair of formEnvio.entries()) {
		console.log(pair[0] + ", " + pair[1]);
		// validar que el value del campo  fecha no sea menor a fecha_min
		if (formEnvio.get("fecha") == null) {
			mjeError("La fecha no puede estar vaciao", "");
			return false;
		}
	}
	try {
		const response = await fetch("datos/registrar/registrar_chequera_nueva.php", {
			method: "POST",
			body: formEnvio,
		});
		const data = await response.json();
		console.log(data);
		if (data[0].value == "ok") {
			// realizar un case para opciones 1.2.3
			if (data[0].tipo == 1) {
				let tabla = "tableFinChequeras";
				reloadtable(tabla);
				mje("Chequera guardada con  éxito...");
			}
			if (data[0].tipo == 2) {
				let tabla = "tableFinChequeras";
				reloadtable(tabla);
				mje("Datos de la chequera actualizados con  éxito...");
			}
			// cerrar modal
			$("#divModalForms").modal("hide");
		}
	} catch (error) {
		console.error(error);
	}
};

// Abre formulario para edición de datos de chequera
const editarDatosChequera = (id) => {
	let url = "form_chequera_nueva.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
// Eliminar chequera
const eliminarChequera = (id) => {
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
		if (result.isConfirmed) {
			fetch("datos/eliminar/eliminar_chequera.php", {
				method: "POST",
				body: JSON.stringify({ id: id }),
			})
				.then((response) => response.json())
				.then((response) => {
					console.log(response);
					if (response.status == "ok") {
						$('#tableFinChequeras').DataTable().ajax.reload(null, false);
						mje("Registro eliminado");
					} else {
						mjeError("Error:" + response.msg);
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};
// Buscar cheque para pago
const buscarCheque = (id) => {
	if (id == 2) {
		let cuenta = cuentas.value;
		let url = "form_buscar_cheque.php";
		// realizar consulta de cheque por fetch
		fetch("datos/consultar/consulta_cheques_uso.php", {
			method: "POST",
			body: JSON.stringify({ id: cuenta }),
		})
			.then((response) => response.json())
			.then((response) => {
				if (response[0].value == "ok") {
					documento.value = response[0].num_cheque;
				}
			})
			.catch((error) => {
				console.log("Error:");
			});
	}
};

// ========================================== Gestión de cuentas bancarias ================================================
// Enviar datos para anulacion
const guardarCuentaBanco = (boton) => {
	InactivaBoton(boton);
	$('.is-invalid').removeClass('is-invalid');
	if ($("#banco").val() == '0') {
		$("#banco").addClass("is-invalid");
		$("#banco").focus();
		mjeError("Debe seleccionar un banco");
	} else {
		if ($("#cuentas").val() == "0") {
			$("#cuentas").addClass("is-invalid");
			$("#cuentas").focus();
			mjeError("Debe seleccionar una cuenta");
		} else {
			if ($("#tipo_cuenta").val() == "0") {
				$("#tipo_cuenta").addClass("is-invalid");
				$("#tipo_cuenta").focus();
				mjeError("Debe seleccionar un tipo de cuenta");
			} else if ($("#numero").val() == "") {
				$("#numero").addClass("is-invalid'");
				$("#numero").focus();
				mjeError("Debe digitar un número de cuenta");
			} else {
				var data = $('#formGestionCuenta').serialize();
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: "datos/registrar/registrar_cuenta_nueva.php",
					data: data,
					success: function (r) {
						if (r.status == 'ok') {
							$("#tableCuentasBanco").DataTable().ajax.reload(null, false);
							mje("Proceso realizado con  éxito.");
							$("#divModalForms").modal("hide");
							$("#divModalForms").attr("aria-hidden", "false");
						} else {
							mjeError('Error:', r.msg);
						}

					}
				});
			}
		}
	}
	ActivaBoton(boton);
};

// Abre formulario para edición de datos de cuenta bancaria
const editarDatosCuenta = (id) => {
	let url = "form_cuenta_nueva.php";
	$.post(url, { id_tes_cuenta: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};

// Eliminar cuenta bancaria
const eliminarCuentaBancaria = (comp) => {
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
		if (result.isConfirmed) {
			fetch("datos/eliminar/eliminar_cuenta_banco.php", {
				method: "POST",
				body: JSON.stringify({ id: comp }),
			})
				.then((response) => response.json())
				.then((response) => {
					if (response.status == "ok") {
						$('#tableCuentasBanco').DataTable().ajax.reload(null, false);
						mje("Registro eliminado");
					} else {
						mjeError("Error: ", response.msg);
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};

// Cerrar cuenta bancaria
let cerrarCuentaBco = function (dato) {
	fetch("datos/consultar/consultaCerrarCuenta.php", {
		method: "POST",
		body: dato,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response[0].value == "ok") {
				//mje("Documento cerrado");
				console.log(response);
				let id = "tableCuentasBanco";
				reloadtable(id);
			} else {
				mjeError("Documento no cerrado", "Verifique sumas iguales y cuentas");
			}
		});
};
// Abrir cuenta bancaria
let abrirCuentaBco = function (dato) {
	//let doc = id_ctb_doc.value;
	fetch("datos/consultar/consultaAbriCuenta.php", {
		method: "POST",
		body: dato,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response[0].value == "ok") {
				mje("Documento activo");
				let id = "tableCuentasBanco";
				reloadtable(id);
			} else {
				mjeError("Documento no abierto", "Tiene pagos asociados");
			}
		});
};

//=========================================== scrip para generar referencia de pago =======================================
const definirReferenciaPago = () => {
	// determinar si el chekbox esta activo
	let isChecked = document.getElementById("checkboxId").checked;
	// Buscar cheque para pago
	if (isChecked) {
		fetch("datos/consultar/consulta_referencia_pago.php", {
			method: "POST",
		})
			.then((response) => response.json())
			.then((response) => {
				if (response[0].value == "ok") {
					referencia.value = response[0].num_ref;
				}
			})
			.catch((error) => {
				console.log("Error:");
			});
	} else {
		referencia.value = "";
	}
};
// Abre lista de referencias de pago
const cargaListaReferenciaPago = (id) => {
	let url = "lista_referencia_pagos.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};

// Cambio de estado de referencia de pago
const terminarReferenciaPago = (id) => {
	fetch("datos/consultar/consulta_cerrar_referencia.php", {
		method: "POST",
		body: id,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response[0].value == "ok") {
				//mje("Documento cerrado");
				console.log(response);
				//let id = "tableReferenciasPagos";
				//reloadtable(id);
			} else {
				mjeError("Documento no cerrado", "");
			}
		});
};

//=========================================== scrip para generación de informes ===========================================

const cargarReporteTesoreria = (id) => {
	let url = "";
	if (id == 1) {
		url = "informes/informe_tesoreria_libros.php";
	}
	if (id == 2) {
		url = "informes/informe_libros_auxiliares_form.php";
	}
	if (id == 3) {
		url = "informes/informe_reporte_terceros_cop_pag_form.php";
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

// Funcion para generar libros presupuestales
const generarInformeLibrosTesoreria = (id) => {
	let tipo = tipo_libro.value;
	let fecha_corte = fecha.value;
	let archivo = 0;
	if (tipo == 1) {
		archivo = window.urlin + "/tesoreria/informes/informe_libro_causaciones_xls.php";
	}
	if (tipo == 2) {
		archivo = window.urlin + "/tesoreria/informes/informe_libro_egresoscp_xls.php";
	}
	if (tipo == 3) {
		archivo = window.urlin + "/tesoreria/informes/informe_reporte_terceros_cop_pag_form_detallle.php";
	}
	let ruta = {
		url: archivo,
		name: "fecha",
		valor: fecha_corte,
	};
	redireccionar4(ruta);
};

// Funcion para generar archivo de pagos OPS
const imprimirReferenciaPago = (id) => {
	archivo = window.urlin + "/tesoreria/informes/informe_referencias_pago.php";
	let ruta = {
		url: archivo,
		name: "referencia",
		valor: id,
	};
	redireccionar6(ruta);
};

// Funcion para generar libros presupuestales
const generarReporteTerceros = (id) => {
	let tercero = id_tercero.value;
	let fecha_inicial = fecha_ini.value;
	let fecha_final = fecha_fin.value;
	let vacio = "";
	archivo = window.urlin + "/tesoreria/informes/informe_reporte_terceros_cop_pag_detallle.php";
	let ruta = {
		url: archivo,
		name1: "tercero",
		valor1: tercero,
		name2: "fecha_ini",
		valor2: fecha_inicial,
		name3: "fecha_fin",
		valor3: fecha_final,
		name4: "vacio1",
		valor4: vacio,
		name5: "vacio2",
		valor5: vacio,
	};
	redireccionar5(ruta);
};

// Funcion para redireccionar la recarga de la pagina
function redireccionar4(ruta) {
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

function redireccionar5(ruta) {
	setTimeout(() => {
		$(
			'<form action="' +
			ruta.url +
			'" method="post"><input type="hidden" name="' +
			ruta.name1 +
			'" value="' +
			ruta.valor1 +
			'" />    <input type="hidden" name="' +
			ruta.name2 +
			'" value="' +
			ruta.valor2 +
			'" />    <input type="hidden" name="' +
			ruta.name3 +
			'" value="' +
			ruta.valor3 +
			'" />    <input type="hidden" name="' +
			ruta.name4 +
			'" value="' +
			ruta.valor4 +
			'" />    <input type="hidden" name="' +
			ruta.name5 +
			'" value="' +
			ruta.valor5 +
			'" />    </form>'
		)
			.appendTo("body")
			.submit();
	}, 100);
}

// Funcion para redireccionar la recarga de la pagina
function redireccionar6(ruta) {
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
function GuardaSaldoExtracto() {
	let id_conciliacion = $('#id_conciliacion').val();
	let id_cuenta = $('#id_cuenta').val();
	let mes = $('#cod_mes').val();
	let saldo = $('#saldoExtracto').val();
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "datos/registrar/guarda_saldo_extracto.php",
		data: { id_cuenta: id_cuenta, mes: mes, saldo: saldo, id_conciliacion: id_conciliacion },
		success: function (r) {
			if (r.status == 'ok') {
				mje('Proceso realizado correctamente');
				$('#id_conciliacion').val(r.id_conciliacion);
			} else {
				mjeError('Error:', r.msg);
			}

		}
	});
}

function GuardaDetalleConciliacion(check) {
	var id_conciliacion = $('#id_conciliacion').val();
	var id_libaux = check.getAttribute('text');
	var mes = $('#cod_mes').val();
	var id_cuenta = $('#id_cuenta').val();
	if (id_conciliacion == '0') {
		mjeError('Debe guardar el saldo del extracto');
		return false;
	} else {
		var opc = check.checked ? 1 : 0;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: "datos/registrar/guarda_detalle_conciliacion.php",
			data: { id_conciliacion: id_conciliacion, id_libaux: id_libaux, opc: opc, mes: mes, id_cuenta: id_cuenta },
			success: function (r) {
				if (r.status == 'ok') {
					let salLib = $('#salLib').val();
					let salExt = $('#saldoExtracto').val();
					$('#tableDetConciliacion').DataTable().ajax.reload(function (json) {
						$('#tot_deb').val(json.tot_deb);
						$('#tot_cre').val(json.tot_cre);
						var valor = Number(salLib) + Number(json.tot_deb) - Number(json.tot_cre) - Number(salExt);
						$('#saldoConcilia').val(valor.toLocaleString('es-MX'));
					});
					mje('Proceso realizado correctamente');
				} else {
					mjeError('Error:', r.msg);
				}

			}
		});
	}


}

function SaldoCuenta(id) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: "datos/consultar/consulta_saldo_cuenta.php",
		data: { id: id },
		success: function (r) {
			if (r.status == 'ok') {
				var saldo = r.saldo;
				// poner signo de pesos y separador de miles
				$('#divSaldoDisp').html(pesos(saldo));
				$('#numSaldoDips').val(saldo);
			} else {
				$('#divSaldoDisp').html(pesos(saldo));
				$('#numSaldoDips').val(saldo);
				mjeError('Error:', r.msg);
			}

		}
	});
}