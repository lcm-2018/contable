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

	var setIdioma = {
		decimal: "",
		emptyTable: "No hay información",
		info: "Mostrando _START_ - _END_ registros de _TOTAL_ ",
		infoEmpty: "Mostrando 0 to 0 of 0 Entradas",
		infoFiltered: "(Filtrado de _MAX_ entradas en total )",
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
	//================================================================================ DATA TABLES ========================================
	$(document).ready(function () {
		//dataTable de movimientos contables
		let id_doc = $("#id_ctb_doc").val();
		if (id_doc === "3") {
			setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
		}
		$("#tableMvtoContable").DataTable({
			dom: setdom,
			buttons: [
				{
					text: ' <span class="fas fa-plus-circle fa-lg"></span>',
					action: function (e, dt, node, config) {
						$.post("datos/registrar/formadd_mvto_contable.php", { id_doc: id_doc }, function (he) {
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
				url: "datos/listar/datos_mvto_contabilidad.php",
				data: function (d) {
					d.id_doc = id_doc;
				},
				type: "POST",
				dataType: "json",
			},
			columns: [{ data: "numero" }, { data: "rp" }, { data: "fecha" }, { data: "tercero" }, { data: "valor" }, { data: "botones" }],
			order: [[0, "desc"]],
		});
		$("#tableMvtoContable").wrap('<div class="overflow" />');
		// dataTable de movimientos contables
		$("#tableMvtoContableDetalle").DataTable({
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
		$("#tableMvtoContableDetalle").wrap('<div class="overflow" />');

		//dataTable ejecucion de presupuesto listado de reistros presupuestales
		$("#tableEjecPresupuestoCxp").DataTable({
			buttons: [
				{
					action: function (e, dt, node, config) {
						$.post("datos/registrar/formadd_ejecucion_presupuesto.php", { id_ejec: id_ejec }, function (he) {
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
			ajax: {
				url: "datos/listar/datos_ejecucion_presupuesto_cxp.php",
				type: "POST",
				dataType: "json",
			},
			columns: [{ data: "numero" }, { data: "cdp" }, { data: "fecha" }, { data: "tercero" }, { data: "valor" }, { data: "causacion" }, { data: "botones" }],
			order: [[0, "asc"]],
		});
		$("#tableEjecPresupuestoCxp").wrap('<div class="overflow" />');
		//.......................................... Tabla de plan de cuentas contable .............................................
		$("#tablePlanCuentas").DataTable({
			dom: setdom,
			buttons: [
				{
					text: ' <span class="fas fa-plus-circle fa-lg"></span>',
					action: function (e, dt, node, config) {
						$.post("form_plan_cuentas.php", function (he) {
							$("#divTamModalForms").removeClass("modal-xl");
							$("#divTamModalForms").removeClass("modal-sm");
							$("#divTamModalForms").addClass("modal-lg");
							$("#divModalForms").modal("show");
							$("#divForms").html(he);
						});
					},
				},
			],
			serverSide: true,
			processing: true,
			language: setIdioma,
			ajax: {
				url: "datos/listar/datos_plan_cuentas_list.php",
				data: function (d) {
					d.id_doc = id_doc;
				},
				type: "POST",
				dataType: "json",
			},
			columns: [{ data: "fecha" }, { data: "cuenta" }, { data: "nombre" }, { data: "tipo" }, { data: "nivel" }, { data: "estado" }, { data: "botones" }],
			order: [],
		});
		$("#tableCuentasBanco").wrap('<div class="overflow" />');
		// Fina plan de cuentas
		//.......................................... Tabla de documentos fuente  .............................................
		$("#tableDocumentosFuente").DataTable({
			dom: setdom,
			buttons: [
				{
					text: ' <span class="fas fa-plus-circle fa-lg"></span>',
					action: function (e, dt, node, config) {
						$.post("form_documentos_fuente.php", function (he) {
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
				url: "datos/listar/datos_documentos_fuente.php",
				data: function (d) {
					d.id_doc = id_doc;
				},
				type: "POST",
				dataType: "json",
			},
			columns: [{ data: "cod" }, { data: "nombre" }, { data: "contab" }, { data: "tesor" }, { data: "cxpagar" }, { data: "estado" }, { data: "botones" }],
			order: [],
		});
		$("#tableDocumentosFuente").wrap('<div class="overflow" />');
		// Fin documentos fuente
		//Fin dataTable
	});
})(jQuery);
/*========================================================================== Utilitarios ========================================*/
/*var recargartable = function (nom) {
  $(document).ready(function () {
  var table = $("#" + nom).DataTable();
  table.ajax.reload(function (json) {
	19;
	$("#id_ctb_doc").val(json.lastInput);
  });
  });
};
*/
// Mensaje

function mje(titulo) {
	Swal.fire({
		title: titulo,
		icon: "success",
		showConfirmButton: true,
		timer: 3000,
	});
}

function mjeError(titulo, texto) {
	Swal.fire({
		title: titulo,
		text: texto,
		icon: "error",
		showConfirmButton: true,
		timer: 3000,
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
    <input type="hidden" name="' +
			ruta.id_soporte +
			'" value="' +
			ruta.soporte +
			'" />\n\
    </form>'
		)
			.appendTo("body")
			.submit();
	}, ruta.time);
}

function valorMiles(id) {
	milesp(id);
}
/*  ========================================================= Modulo de contabilidad ==========================================*/
// Función para formaterar fecha Y-m-d
const formatDate = (date) => {
	let mes = date.getMonth() + 1;
	mes = ("0" + mes).slice(-2);
	let formatted_date = date.getFullYear() + "-" + mes + "-" + date.getDate();
	return formatted_date;
};
// Recargar a la tabla de documento contable  por acciones en el select
function cambiaListadoContable(dato) {
	$('<form action="lista_documentos_mov.php" method="POST">' +
		'+<input type="hidden" name="id_doc" value="' + dato + '" />' +
		'</form>').appendTo("body").submit();
}
/*
// Autocomplete para la selección del tercero que se asigna al registro presupuestal
document.addEventListener("keyup", (e) => {
  if (e.target.id == "terceromov") {
	let valor = "";
	$("#terceromov").autocomplete({
	  source: function (request, response) {
		$.ajax({
		  url: "../presupuesto/datos/consultar/buscar_terceros.php",
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
		$("#terceromov").val(ui.item.label);
		$("#id_tercero").val(ui.item.value);
		return false;
	  },
	  focus: function (event, ui) {
		$("#terceromov").val(ui.item.label);
		return false;
	  },
	});
  }
});
*/
document.addEventListener("keyup", (e) => {
	if (e.target.id == "terceromov") {
		$("#terceromov").autocomplete({
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
				$("#terceromov").val(ui.item.label);
				$("#id_tercero").val(ui.item.id);
				return false;
			},
			focus: function (event, ui) {
				$("#terceromov").val(ui.item.label);
				return false;
			},
		});
	}
});
// Registrar en la tabla documentos la parte general del movimiento contable
document.addEventListener("submit", (e) => {
	let id_doc = $("#id_ctb_doc").val();
	e.preventDefault();
	if (e.target.id == "formAddMvtoCtb") {
		let formEnvio = new FormData(formAddMvtoCtb);
		formEnvio.append("id_doc", id_doc);
		for (var pair of formEnvio.entries()) {
			console.log(pair[0] + ", " + pair[1]);
		}
		fetch("datos/registrar/registrar_mvto_contable_doc.php", {
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
				formAddMvtoCtb.reset();
				// Redirecciona documento para asignar valores por rubro
				setTimeout(() => {
					$(
						'<form action="lista_documentos_det.php" method="post">\n\
            <input type="hidden" name="id_doc" value="' +
						response[0].id +
						'" />\n\
            </form>'
					)
						.appendTo("body")
						.submit();
				}, 5);
			});
	}
});

$('#divModalForms').on('click', '#gestionarMvtoCtb', function () {
	var opcion = $(this).attr('text');
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
		var datos = $('#formGetMvtoCtb').serialize() + '&opcion=' + opcion;
		url = "datos/registrar/registrar_mvto_contable_doc.php";
		$.ajax({
			type: 'POST',
			url: url,
			data: datos,
			success: function (r) {
				if (r == 'ok') {
					let id_t = 'tableMvtoContable';
					reloadtable(id_t);
					$('#divModalForms').modal('hide');
					mje('Proceso realizado correctamente');
				} else {
					mjeError('Error:', r);
				}

			}
		});
	}
	return false;

});
// Cargar lista detalle de movimiento contables
function cargarListaDetalle(elemento) {
	let data = elemento.getAttribute("text");
	data = atob(data);
	let id_doc = data.split("|")[0];
	let tipo_dato = data.split("|")[1];
	$('<form action="lista_documentos_det.php" method="post">' +
		'<input type="hidden" name="id_doc" value="' + id_doc + '" />' +
		'<input type="hidden" name="tipo_dato" value="' + tipo_dato + '" />' +
		'</form>').appendTo("body").submit();
}
// Autocomplete para la selección del tercero que se asigna al registro presupuestal
document.addEventListener("keyup", (e) => {
	if (e.target.id == "codigoCta") {
		$("#codigoCta").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "datos/consultar/consultaPgcp.php",
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
				$("#codigoCta").val(ui.item.label);
				$("#id_codigoCta").val(ui.item.id);
				$("#tipoDato").val(ui.item.tipo_dato);
				return false;
			},
		});
	}
});
$("#bTercero").autocomplete({
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
		$('#idTercero').val(ui.item.id);
	}
});
//=================================== Registrar el documento y la tabla libaux el detalle del movimiento contable ============================

//
var movcta = false;
$("#divCuerpoPag").ready(function () {
	$("#numDoc").change(function () {
		movcta = true;
	});
	$("#fecha").change(function () {
		movcta = true;
	});
	$("#id_tercero").change(function () {
		movcta = true;
	});
	$("#objeto").change(function () {
		movcta = true;
	});
});

// Consultar la suma debito credito del documento contable
function consultarSumaDoc(id_doc) {
	fetch("datos/consultar/consultaSumas.php", {
		method: "POST",
		body: id_doc,
	})
		.then((response) => response.json())
		.then((response) => {
			let valorDebito = response[0].valordeb;
			let valorCredito = response[0].valorcrd;
			let diferencia = valorDebito - valorCredito;
			diferencia = Math.round((diferencia + Number.EPSILON) * 100) / 100;
			valor_dif.value = diferencia;
			debito.value = valorDebito.toLocaleString("es-MX");
			credito.value = valorCredito.toLocaleString("es-MX");
		});
}

// Funcion para agregar o editar registros contables en el libro auxiliar
function GestMvtoDetalle(elemento) {
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
		$('#valorCredito').classList.add('is-invalid');
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
					$('#tableMvtoContableDetalle').DataTable().ajax.reload(function (json) {
						// Obtener los datos del tfoot de la DataTable
						var tfootData = json.tfoot;
						// Construir el tfoot de la DataTable
						var tfootHtml = '<tfoot><tr>';
						$.each(tfootData, function (index, value) {
							tfootHtml += '<th>' + value + '</th>';
						});
						tfootHtml += '</tr></tfoot>';
						// Reemplazar el tfoot existente en la tabla
						$('#tableMvtoContableDetalle').find('tfoot').remove();
						$('#tableMvtoContableDetalle').append(tfootHtml);
					});
					mje('Registro exitoso');
				} else {
					mjeError('Error:', response);
				}
			});
	}
	return false;
};
// Funcion sumas iguales
let sumasIguales = function () {
	let id_doc = id_ctb_doc.value;
	fetch("datos/consultar/consultaSumas.php", {
		method: "POST",
		body: id_doc,
	})
		.then((response) => response.json())
		.then((response) => {
			let dif = response[0].valordeb - response[0].valorcrd;
			if (dif > 0) {
				$("#valorCredito").val(dif);
				$("#valorDebito").val(0);
			}
			if (dif < 0) {
				$("#valorDebito").val(Math.abs(dif));
				$("#valorCredito").val(0);
			}
		});
};
// Terminar de registrar movimientos de detalle  verificando sumas sumas iguales
let terminarDetalle = function (dato) {
	let dif = $('#total').val();
	if (dif != 0) {
		mjeError("Las sumas deben ser iguales..", "Puede usar doble click en la casilla para verificar");
	} else {
		cambiaListadoContable(dato);
	}
};
// Cerrar documento contable
let cerrarDocumentoCtb = function (dato) {
	fetch("datos/consultar/consultaCerrar.php", {
		method: "POST",
		body: dato,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response[0].value == "ok") {
				//mje("Documento cerrado");
				console.log(response);
				let id = "tableMvtoContable";
				reloadtable(id);
				document.getElementById("editar_" + dato).style.display = "none";
			} else {
				mjeError("Documento no cerrado", "Verifique sumas iguales y cuentas");
			}
		});
};
// Abrir documento contable
let abrirDocumentoCtb = function (dato) {
	//let doc = id_ctb_doc.value;
	fetch("datos/consultar/consultaAbrir.php", {
		method: "POST",
		body: dato,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response[0].value == "ok") {
				mje("Documento abierto");
				let id = "tableMvtoContable";
				reloadtable(id);
			} else {
				mjeError("Documento no abierto", "Tiene pagos asociados");
			}
		});
};
//Carga el listado de informes de actividades e interventoría
function CargarListadoCxp(dato) {
	$(
		'<form action="lista_ejecucion_pto_crp_cxp.php" method="post">\n\
    <input type="hidden" name="id_pto" value="' +
		dato +
		'" /></form>'
	)
		.appendTo("body")
		.submit();
}

// Cargar formulario formadd_mvto_contable.php para registrar movimientos contables
function cargarFormCxp(busqueda) {
	fetch("datos/registrar/formadd_mvto_contable.php", {
		method: "POST",
		body: busqueda,
	})
		.then((response) => response.text())
		.then((response) => {
			$("#divTamModalForms").removeClass("modal-xl");
			$("#divTamModalForms").removeClass("modal-sm");
			$("#divTamModalForms").addClass("modal-lg");
			$("#divModalForms").modal("show");
			divForms.innerHTML = response;
			// Llenar el formulario con los datos del registro
			fetch("datos/consultar/consultarDatosCrp.php", {
				method: "POST",
				body: busqueda,
			})
				.then((response) => response.json())
				.then((response) => {
					objeto.value = response.objeto;
					terceromov.value = response.id_tercero + " - " + response.nombre;
					id_tercero.value = response.id_tercero;
					var fecha2 = new Date(response.fecha);
					let fecha3 = formatDate(fecha2);
					fecha.min = fecha3;
				});
		})
		.catch((error) => {
			console.log("Error:");
		});
}

// Cargar lista de registros para obligar en contabilidad de
let CargaObligaCrp = function (dato) {
	$.post("lista_causacion_registros.php", { dato: dato }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
function CausaNomina(boton) {
	var cant = document.getElementById("total");
	var valor = Number(cant.value);
	var data = boton.value;
	data = data.split("|");
	var tipo = data[2];
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
						$('#tableMvtoContable').DataTable().ajax.reload();
						cant.value = valor - 1;
						document.getElementById("totalCausa").innerHTML = valor - 1;
						boton.innerHTML = '<span class="fas fa-thumbs-up fa-lg"></span>';
						$("#divModalForms").modal("hide");
						mje("Registro exitoso");
					} else {
						mjeError("Error: " + response);
					}
				});
		}
	});
}
// Cargar lista detalle de registros contables
function cargarListaDetalleCont(id_doc) {
	let tipo_dato = "NCXP";
	let tipo_mov = "RP";
	console.log(id_doc);
	$(
		'<form action="lista_documentos_det.php" method="post"><input type="hidden" name="id_crp" value="' +
		id_doc +
		'" /><input type="hidden" name="tipo_dato" value="' +
		tipo_dato +
		'" />input type="hidden" name="tipo_mov" value="' +
		tipo_mov +
		'" />/n</form>'
	)
		.appendTo("body")
		.submit();
}

// Establecer consecutivo para documento de contabilidad
let buscarConsecutivoCont = function (doc) {
	let fecha = $("#fecha").val();
	// verificar si ya exite numero de id_ctb_doc.value
	if (id_ctb_doc.value == 0) {
		fetch("datos/consultar/consulta_consecutivo_conta.php", {
			method: "POST",
			body: JSON.stringify({ fecha: fecha, documento: doc }),
		})
			.then((response) => response.json())
			.then((response) => {
				console.log(response);
				$("#numDoc").val(response[0].numero);
			});
	}
};

// Establecer consecutivo para documento de contabilidad
let buscarConsecutivoCont2 = function (doc) {
	let fecha = $("#fecha").val();
	// verificar si ya exite numero de id_ctb_doc.value
	fetch("datos/consultar/consulta_consecutivo_conta.php", {
		method: "POST",
		body: JSON.stringify({ fecha: fecha, documento: doc }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			$("#numDoc").val(response[0].numero);
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

// Autocomplete para seleccionar terceros informe de certificados
document.addEventListener("keyup", (e) => {
	if (e.target.id == "tercero_cert") {
		$("#tercero_cert").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "../datos/consultar/buscar_terceros.php",
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
				$("#tercero_cert").val(ui.item.label);
				$("#id_tercero_cert").val(ui.item.id);
				return false;
			},
			focus: function (event, ui) {
				$("#tercero_cert").val(ui.item.label);
				return false;
			},
		});
	}
});
// ********************************************************* AFECTACION PRESUPUESTAL DE CUENTAS POR PAGAR *************************

// Cargar lista de rubros para realizar la causación del valor
let cargaRubrosRp = function (dato) {
	let id_doc = id_ctb_doc.value;
	if (id_doc == 0) {
		mjeError("No puede seleccionar imputación presupuestal", "Primero guarde el documento");
		return false;
	} else {
		$.post("lista_causacion_registros_total.php", { id_crp: dato }, function (he) {
			$("#divTamModalForms").removeClass("modal-sm");
			$("#divTamModalForms").removeClass("modal-3x");
			$("#divTamModalForms").removeClass("modal-lg");
			$("#divTamModalForms").addClass("modal-xl");
			$("#divModalForms").modal("show");
			$("#divForms").html(he);
		});
	}
};

// Validar valor maximo en rubros
let validarValorMaximo = function (id) {
	let valor_max = document.querySelector("#" + id).getAttribute("max");
	let maximo = parseFloat(valor_max.replace(/\,/g, "", ""));
	let digitado = document.getElementById(id).value;
	let digitado2 = parseFloat(digitado.replace(/\,/g, "", ""));
	if (digitado2 > maximo) {
		mjeError("Valor digitado mayor al máximo", "Verifique");
		document.getElementById(id).value = valor_max.toLocaleString("es-MX");
	}
};

// Guardar los rubros y el valor de la afectación presupuestal asociada a la cuenta por pagar
let rubrosaObligar = function () {
	let formDatos = new FormData(rubrosObligar);
	let datos = {};
	// Genero array con datos de fromEmvio
	for (var pair of formDatos.entries()) {
		datos[pair[0]] = parseFloat(pair[1].replace(/\,/g, "", ""));
	}
	let id_crrp = id_pto_rp.value;
	let id_doc = id_ctb_doc.value;
	let formEnvio = new FormData();
	formEnvio.append("id_crrp", id_crrp);
	formEnvio.append("id_ctb_doc", id_doc);
	formEnvio.append("datos", JSON.stringify(datos));
	for (var pair of formEnvio.entries()) {
		console.log(pair[0] + ", " + pair[1]);
	}
	// Enviar a guardar afectación de rubros en mtto presupuesto como obligacion
	fetch("datos/registrar/registrar_mvto_cobp.php", {
		method: "POST",
		body: formEnvio,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response[0].value == "ok") {
				console.log(response);
				valor.value = response[0].total.toLocaleString("es-MX");
				mje("Afectación presupuestal registrada", "Exito");
				$("#divModalForms").modal("hide");
			} else {
				mjeError("Error al registrar afectación presupuestal", "Error");
			}
		});
};

//********************************************** CAUSACION DE CUENTAS POR PAGAR POR CENTROS DE COSTO ***************/

// Cargar lista de centros de costo para realizar la causación del valor
let cargaCentrosCosto = async (datos) => {
	let id_docu = id_ctb_doc.value;
	if (id_docu > 0) {
		let valor2 = parseFloat(valor.value.replace(/\,/g, "", ""));
		if (valor2 != "") {
			$.post("lista_causacion_ccostos.php", { id_doc: id_docu }, function (he) {
				$("#divTamModalForms").removeClass("modal-sm");
				$("#divTamModalForms").removeClass("modal-xl");
				$("#divTamModalForms").removeClass("modal-lg");
				$("#divTamModalForms").addClass("modal-3x");
				$("#divModalForms").modal("show");
				$("#divForms").html(he);
				let data = [datos, 0];
				let registrado = valorRegCostos("datos/consultar/consulta_costos_valor.php", data);
				registrado.then((response) => {
					let valor_reg = parseFloat(response[0].valorcc);
					console.log(response);
					let total = valor2 - valor_reg;
					valor_cc.value = total.toLocaleString("es-MX");
				});
			});
		} else {
			document.querySelector("#valor").focus();
			mjeError("No ha seleccionado un valor de la obligación");
		}
	} else {
		mjeError("No puede causar centros de costo", "Primero guarde el documento");
	}
};
// consultar valor cargado en centro de costos
let sumaRegCostos = async (data) => {
	let url = "";
	let response = await fetch(url, {
		method: "POST",
		body: JSON.stringify(data),
		headers: {
			"Content-Type": "application/json",
		},
	});
	let datos = await response.json();
	return datos;
};

// Autocomplete para seleccionar municipios
document.addEventListener("keyup", (e) => {
	if (e.target.id == "municipio") {
		$("#municipio").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "datos/consultar/consulta_municipio.php",
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
				$("#municipio").val(ui.item.label);
				$("#id_municipio").val(ui.item.value);
				return false;
			},
			focus: function (event, ui) {
				$("#municipio").val(ui.item.label);
				return false;
			},
		});
	}
});

// Mostrar sedes por municipio
let mostrarSedes = function (dato) {
	let id_mpio = id_municipio.value;
	fetch("datos/consultar/consulta_sedes.php", {
		method: "POST",
		body: JSON.stringify({ id: id_mpio }),
	})
		.then((response) => response.text())
		.then((response) => {
			divSede.innerHTML = response;
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Mostrar centros de costo  por sede
let mostrarCentroCostos = function (dato) {
	fetch("datos/consultar/consulta_costos.php", {
		method: "POST",
		body: JSON.stringify({ id: dato }),
	})
		.then((response) => response.text())
		.then((response) => {
			divCosto.innerHTML = response;
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Guardar datos de causación de costos
document.addEventListener("submit", (e) => {
	e.preventDefault();
	if (e.target.id == "formAddCentroCosto") {
		// Valida que valor_cc no sea mayor valor
		let id_ctb_doc = id_doc.value;
		let valor_total = parseFloat(valor.value.replace(/\,/g, "", ""));
		let valor_cos = parseFloat(valor_cc.value.replace(/\,/g, "", ""));
		let data = [id_ctb_doc, 0];
		let registrado = valorRegCostos("datos/consultar/consulta_costos_valor.php", data);
		registrado.then((response) => {
			let valor_reg = parseFloat(response[0].valorcc);
			let total = valor_total - (valor_reg + valor_cos);
			total = parseFloat(total.toFixed(2));
			console.log("Total " + total + " valor_total: " + valor_total + " valor_reg: " + valor_reg + " valor_cos: " + valor_cos);
			if (total < 0) {
				mjeError("El valor del centro de costo no puede ser mayor al valor de la CXP");
				return false;
			} else {
				let formEnvio = new FormData(formAddCentroCosto);
				for (var pair of formEnvio.entries()) {
					console.log(pair[0] + ", " + pair[1]);
				}
				fetch("datos/registrar/registrar_mvto_costos.php", {
					method: "POST",
					body: formEnvio,
				})
					.then((response) => response.text())
					.then((response) => {
						let sumacosto = valorRegCostos("datos/consultar/consulta_costos_valor.php", data);
						sumacosto.then((response) => {
							let valortotal = parseFloat(response[0].valorcc);
							valor_costo.value = valortotal.toLocaleString("es-MX");
						});
						formAddCentroCosto.reset();
						valor_cc.value = total.toLocaleString("es-MX");

						$("#tableCausacionCostos>tbody").prepend(response);
					});
			}
		});
	}
});

const valorRegCostos = async (url, datos) => {
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
const eliminarCentroCosto = (dato) => {
	let id_ctb_doc = id_doc.value;
	let data = [id_ctb_doc, 0];

	fetch("datos/eliminar/eliminar_mvto_costos.php", {
		method: "POST",
		body: JSON.stringify({ id: dato }),
	})
		.then((response) => response.json())
		.then((response) => {
			if (response[0].value == "ok") {
				mje("Registro eliminado exitosamente");
				// Eliminar la fila de la tabla
				$("#" + dato).remove();
				let sumacosto = valorRegCostos("datos/consultar/consulta_costos_valor.php", data);
				sumacosto.then((response) => {
					let valortotal = parseFloat(response[0].valorcc);
					valor_costo.value = valortotal.toLocaleString("es-MX");
				});
			} else {
				mjeError("Error al eliminar");
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Ajustar causación de centros de costo por cambio en el valor a pagar
const ajustarCausacionCostos = (dato) => {
	let valor_pago = parseFloat(valor.value.replace(/\,/g, "", ""));
	let valor_cc = parseFloat(valor_costo.value.replace(/\,/g, "", ""));
	if (valor_pago != valor_cc) {
		fetch("datos/experto/ajustar_costos_valor.php", {
			method: "POST",
			body: JSON.stringify({ id: dato, total: valor_pago }),
		})
			.then((response) => response.json())
			.then((response) => {
				console.log(response);
				if (response[0].value == "ok") {
					mje("Registro modificado");
					let nuevo_valor = response[0].valorcc;
					valor_costo.value = nuevo_valor.toLocaleString("es-MX");
				} else {
					mjeError("Error al modificar");
				}
			})
			.catch((error) => {
				console.log("Error:");
			});
	} else {
		mje("El valor a pagar y la causación de centros de costo son iguales");
	}
};

//*********************************DESCUENTOS EN CAUSACIÓN DE CUENTAS POR PAGAR ******************************//

// Cargar formulario para asignar descuentos
let cargaDescuentos = function (dato) {
	let id_docu = id_ctb_doc.value;
	let valor2 = valor.value;
	let fecha_doc = fecha.value;
	if (id_docu > 0) {
		if (valor2 != "") {
			$.post("lista_causacion_descuentos.php", { id_doc: id_docu, valor: valor2, fechar: fecha_doc }, function (he) {
				$("#divTamModalForms").removeClass("modal-sm");
				$("#divTamModalForms").removeClass("modal-lg");
				$("#divTamModalForms").removeClass("modal-xl");
				$("#divTamModalForms").addClass("modal-3x");
				$("#divModalForms").modal("show");
				$("#divForms").html(he);
			});
		} else {
			document.querySelector("#valor").focus();
			mjeError("No ha seleccionado un valor de la obligación");
		}
	} else {
		mjeError("No puede causar descuentos", "Primero guarde el documento");
	}
};

// Calculo del valor base a partir del IVA
let calculoValorBase = function () {
	let pago = parseFloat(valor_pagar.value.replace(/\,/g, "", ""));
	let iva = parseFloat(valor_iva.value.replace(/\,/g, "", ""));
	let base = pago - iva;
	valor_base.value = base.toLocaleString("es-MX");
};

// Calcular el iva por tarifa comun de 19%
let calculoIva = function () {
	let pago = parseFloat(valor_pagar.value.replace(/\,/g, "", ""));
	let iva = pago * 0.19;
	let base = pago - iva;
	valor_iva.value = iva.toLocaleString("es-MX");
	valor_base.value = base.toLocaleString("es-MX");
	neto_pago.value = base.toLocaleString("es-MX");
};

// Muestra el select según el tipo de retención seleccionado
const mostrarRetenciones = (dato) => {
	console.log(dato);
	let id_doc = id_ctb_doc.value;
	tarifa.value = "";
	fetch("datos/consultar/consulta_retenciones.php", {
		method: "POST",
		body: JSON.stringify({ id: dato }),
	})
		.then((response) => response.text())
		.then((response) => {
			divRete.innerHTML = response;
		})
		.catch((error) => {
			console.log("Error:");
		});
	if (dato == 3) {
		// Enviar y consultar el valor causado por cada sede ======= Valor causado por sede
		fetch("datos/consultar/consulta_baseica_sede.php", {
			method: "POST",
			body: JSON.stringify({ id_doc: id_doc }),
		})
			.then((response) => response.text())
			.then((response) => {
				divSede.innerHTML = response;
			})
			.catch((error) => {
				console.log("Error:");
			});
		// Treer consulta de sobretasa bomberil
		fetch("datos/consultar/consulta_retenciones_sobre.php", {
			method: "POST",
			body: JSON.stringify({ id_doc: id_doc }),
		})
			.then((response) => response.text())
			.then((response) => {
				divSobre.innerHTML = response;
			})
			.catch((error) => {
				console.log("Error:");
			});
	} else {
		// ocultar div   id="divSobre"
		divSobre.innerHTML = "";
		divSede.innerHTML = "";
		valor_rte.value = 0;
	}
	id_terceroapi.value = "";
};

// Aplica tarifa de acuerdo a la retención seleccionada
const aplicaDescuentoRetenciones = (retencion) => {
	let valor = parseFloat(valor_base.value.replace(/\,/g, "", ""));
	let iva = parseFloat(valor_iva.value.replace(/\,/g, "", ""));
	let tipoRetencion = document.querySelector("#tipo_rete").value;
	if (tipoRetencion == 3) {
		let datos = document.querySelector("#id_rete_sede").value;
		let datos2 = datos.split("_");
		valor = datos2[1];
		id_terceroapi.value = datos2[0];
	}
	fetch("datos/consultar/aplica_retenciones.php", {
		method: "POST",
		body: JSON.stringify({ id: retencion, base: valor, iva: iva }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response[0].value == "ok") {
				let descuento = response[0].desc;
				valor_rte.value = descuento.toLocaleString("es-MX");
				tarifa.value = response[0].tarifa;
				if (tipoRetencion == 3) {
					id_terceroapi.value = datos2[0];
				} else {
					id_terceroapi.value = response[0].terceroapi;
				}
			} else {
				mjeError("Error al modificar");
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Guardar valor de la retención
document.addEventListener("submit", (e) => {
	e.preventDefault();
	if (e.target.id == "formAddRetencioness") {
		// Valida que descuento sea mayor a cero
		let id_ctb_doc = id_docr.value;
		let descuento = parseFloat(valor_rte.value.replace(/\,/g, "", ""));
		let base = valor_base.value;
		let iva = valor_iva.value;
		let data = [id_ctb_doc, 0];
		let tipoRetencion = document.querySelector("#tipo_rete").value;
		if (descuento > 0) {
			let formEnvio = new FormData(formAddRetencioness);
			if (tipoRetencion == 3) {
				let datos = document.querySelector("#id_rete_sede").value;
				let datos2 = datos.split("_");
				base = datos2[1];
			}
			if (tipoRetencion == 6) {
				let id_ter = id_tercero.value;
				formEnvio.set("id_terceroapi", id_ter);
			}

			formEnvio.append("base", base);
			formEnvio.append("iva", iva);
			for (var pair of formEnvio.entries()) {
				console.log(pair[0] + ", " + pair[1]);
			}
			fetch("datos/registrar/registrar_mvto_retenciones.php", {
				method: "POST",
				body: formEnvio,
			})
				.then((response) => response.text())
				.then((response) => {
					let valorRetenido = valorRegRetenciones("datos/consultar/consulta_retenciones_valor ", data);
					valorRetenido.then((response) => {
						let valorret = parseFloat(response[0].valor_ret);
						descuentos.value = valorret.toLocaleString("es-MX");
					});
					console.log(response);
					//id_reteformAddRetencioness.reset();
					$("#id_rete").val("0");
					valor_rte.value = 0;
					$("#tableCausacionRetenciones>tbody").prepend(response);
				})
				.catch((error) => {
					console.log("Error:");
				});
		} else {
			mjeError("El descuento debe ser mayor a cero");
		}
	}
});
// Guardar valor sobretasa
const guardaSobretasa = () => {
	let id_ctb_doc = id_docr.value;
	let descuento = parseFloat(valor_rte.value.replace(/\,/g, "", ""));
	let base = descuento;
	let iva = valor_iva.value;
	let data = [id_ctb_doc, 0];
	let tipoRetencion = document.querySelector("#id_rete_sobre").value;
	if (descuento > 0) {
		let formEnvio = new FormData(formAddRetencioness);
		if (tipoRetencion == 3) {
			let datos = document.querySelector("#id_rete_sede").value;
			let datos2 = datos.split("_");
			//base = datos2[1];
		}

		formEnvio.append("base", base);
		formEnvio.append("iva", iva);
		formEnvio.set("id_rete", tipoRetencion);
		formEnvio.set("tipo_rete", 4);
		for (var pair of formEnvio.entries()) {
			console.log(pair[0] + ", " + pair[1]);
		}

		fetch("datos/registrar/registrar_mvto_retenciones.php", {
			method: "POST",
			body: formEnvio,
		})
			.then((response) => response.text())
			.then((response) => {
				let valorRetenido = valorRegRetenciones("datos/consultar/consulta_retenciones_valor ", data);
				valorRetenido.then((response) => {
					let valorret = parseFloat(response[0].valor_ret);
					descuentos.value = valorret.toLocaleString("es-MX");
				});
				console.log(response);
				//id_reteformAddRetencioness.reset();
				$("#tableCausacionRetenciones>tbody").prepend(response);
			})
			.catch((error) => {
				console.log("Error:");
			});
	} else {
		mjeError("El descuento debe ser mayor a cero");
	}
};

// Eliminar retenciones
const eliminarRetencion = (id) => {
	let id_ctb_doc = id_docr.value;
	let data = [id_ctb_doc, 0];

	fetch("datos/eliminar/eliminar_mvto_retenciones.php", {
		method: "POST",
		body: JSON.stringify({ id: id }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response[0].value == "ok") {
				mje("Registro eliminado");
				$("#" + id).remove();
				let valorRetenido = valorRegRetenciones("datos/consultar/consulta_retenciones_valor ", data);
				valorRetenido.then((response) => {
					let valorret = parseFloat(response[0].valor_ret);
					descuentos.value = valorret.toLocaleString("es-MX");
				});
			} else {
				mjeError("Error al eliminar");
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Consultar el valor total causado de las retenciones
const valorRegRetenciones = async (url, datos) => {
	return await fetch(url, {
		method: "POST",
		body: JSON.stringify({ id: datos }),
	})
		.then((response) => response.json())
		.then((response) => {
			return response;
		});
};
//*********************************REGISTROS CONTABLES DE CUENTAS POR PAGAR ******************************//

// Procesar causación de cuentas por pagar con boton guardar
const procesaCausacionCxp = (id) => {
	let tipo_dato = tipodato.value;
	let formEnvio = new FormData(formAddDetalleCtb);
	var guardarButton = document.getElementById("bottonGuardarCxp");
	guardarButton.disabled = true;
	for (var pair of formEnvio.entries()) {
		console.log(pair[0] + ", " + pair[1]);
		// Espacio para validaciones
		if (formEnvio.get("fechaDoc") == "") {
			document.querySelector("#fechaDoc").focus();
			mjeError("Debe digitar un valor valido para el documento ", "");
			return false;
		}
		if (tipo_dato == "NCXP") {
			if (formEnvio.get("tipoDoc") == "") {
				document.querySelector("#tipoDoc").focus();
				mjeError("Debe seleccionar un tipo de documento ", "");
				return false;
			}
			if (formEnvio.get("tipoDoc") == "3" && formEnvio.get("detalle") == "") {
				document.querySelector("#detalle").focus();
				mjeError("Para documento equivalente, se debe ingresar el detalle", "");
				return false;
			}
			let valor = parseFloat(formEnvio.get("valor").replace(/\,/g, "", ""));
			let iva = parseFloat(formEnvio.get("valor_iva").replace(/\,/g, "", ""));
			if (iva > valor) {
				document.querySelector("#valor_iva").focus();
				mjeError("El valor del IVA no puede ser mayor al valor a pagar ", "");
				return false;
			}
			if (formEnvio.get("numFac") == "") {
				document.querySelector("#numFac").focus();
				mjeError("Debe digitar un número de documento soporte ", "");
				return false;
			}
			if (formEnvio.get("valor_pagar") == "") {
				document.querySelector("#valor").focus();
				mjeError("Debe digitar un valor valido para el documento ", "");
				return false;
			}
		}
	}
	fetch("datos/registrar/registrar_mvto_contable_doc_cxp.php", {
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
			guardarButton.disabled = false;
		})
		.catch((error) => {
			console.log("Error:");
		});
};

// Genera movimiento cuando se hace procesamiento automatico del documento cxp
const generaMovimientoCxp = () => {
	let id = id_ctb_doc.value;
	let valor_fac = parseFloat(valor_pagar.value.replace(/\,/g, "", ""));
	let valor_inp = parseFloat(valor.value.replace(/\,/g, "", ""));
	let valor_cos = parseFloat(valor_costo.value.replace(/\,/g, "", ""));
	// verificar si los tres valores son iguales
	if (valor_fac == valor_inp && valor_fac == valor_cos) {
		let id_crp = id_crpp.value;
		fetch("datos/registrar/registrar_mvto_libaux_auto_cxp.php", {
			method: "POST",
			body: JSON.stringify({ id: id, id_crp: id_crp }),
		})
			.then((response) => response.json())
			.then((response) => {
				console.log(response);
				if (response[0].value == "ok") {
					mje("Movimiento generado con éxito ");
					let id = "tableMvtoContableDetalle";
					reloadtable(id);
				} else {
					mjeError("Error al guardar");
				}
			})
			.catch((error) => {
				console.log("Error:");
			});
	} else {
		mjeError("Falta registrar imputación presupuestal y costos");
		return false;
	}
};

// llenar cero en el input
const llenarCero = (id) => {
	let valor = document.querySelector("#" + id).value;
	valor = parseFloat(valor.replace(/\,/g, "", ""));
	if (valor > 0) {
		if (id == "valorDebito") {
			valorCredito.value = 0;
		} else {
			valorDebito.value = 0;
		}
	}
};

// Eliminar un registro de detalles
const eliminarRegistroDetalle = (id) => {
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
					console.log(response);
					if (response[0].value == "ok") {
						mje("Registro eliminado");
						$('#tableMvtoContableDetalle').DataTable().ajax.reload(function (json) {
							// Obtener los datos del tfoot de la DataTable
							var tfootData = json.tfoot;
							// Construir el tfoot de la DataTable
							var tfootHtml = '<tfoot><tr>';
							$.each(tfootData, function (index, value) {
								tfootHtml += '<th>' + value + '</th>';
							});
							tfootHtml += '</tr></tfoot>';
							// Reemplazar el tfoot existente en la tabla
							$('#tableMvtoContableDetalle').find('tfoot').remove();
							$('#tableMvtoContableDetalle').append(tfootHtml);
						});
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

// Editar un registro de detalles de ctb_libaux
$("#modificartableMvtoContableDetalle").on('click', '.editar', function () {
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

// Eliminar documento contable ctb_doc
const eliminarRegistroDoc = (id) => {
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
					console.log(response);
					if (response[0].value == "ok") {
						mje("Registro eliminado");
						id = "tableMvtoContable";
						reloadtable(id);
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

/*=================================   IMPRESION DE PFORMATOS =====================================*/
const imprimirFormatoDoc = (id) => {
	let url = "soportes/imprimir_formato_doc.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
const imprSelecDoc = (nombre, id) => {
	cerrarDocumentoCtb(id);
	var ficha = document.getElementById(nombre);
	var ventimp = window.open(" ", "popimpr");
	ventimp.document.write(ficha.innerHTML);
	ventimp.document.close();
	ventimp.print();
	ventimp.close();
};

// Imprimir certificado de ingresos y retenciones
// enviar datos a imprimir con dataform
const imprimirCertificadoIngresos = () => {
	if (id_tercero_cert.value == "") {
		mjeError("Debe seleccionar un tercero");
		return false;
	}

	let id_tercero = id_tercero_cert.value;
	let fecha_i = fecha_ini.value;
	let fecha_f = fecha_fin.value;
	let cert_ret = "";
	let cert_iva = "";
	let cert_ica = "";
	let cert_estap = "";
	let cert_otros = "";
	let retefeunte = document.getElementById("retefuente");
	if (retefeunte.checked) {
		cert_ret = 1;
	}
	let reteiva = document.getElementById("reteiva");
	if (reteiva.checked) {
		cert_iva = 2;
	}
	let reteica = document.getElementById("reteica");
	if (reteica.checked) {
		cert_ica = "3,4";
	}
	let retestampillas = document.getElementById("retestampillas");
	if (retestampillas.checked) {
		cert_estap = 5;
	}
	let reteotras = document.getElementById("reteotras");
	if (reteotras.checked) {
		cert_otros = 6;
	}

	// convertir en json las anteriores variables
	let dataform = {
		id_tercero: id_tercero,
		fecha_i: fecha_i,
		fecha_f: fecha_f,
		cert_ret: cert_ret,
		cert_iva: cert_iva,
		cert_ica: cert_ica,
		cert_estap: cert_estap,
		cert_otros: cert_otros,
	};
	let url = "informe_certificado_ingresos_soporte.php";
	$.post(url, dataform, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};

// Parametrizacion documento equivalente
const consecutivoDocEqui = (id) => {
	if (id == 3) {
		fetch("datos/consultar/consultarDocEquivalente.php", {
			method: "POST",
			body: JSON.stringify({ id: id }),
		})
			.then((response) => response.json())
			.then((response) => {
				console.log(response);
				if (response[0].value == "ok") {
					numFac.value = parseInt(response[0].tipo) + 1;
				} else {
					mjeError("Error al cargar");
				}
			})
			.catch((error) => {
				console.log("Error:");
			});
	}
};

const EnviaDocumentoSoporte = (boton) => {
	boton.disabled = true;
	let id = boton.value;
	boton.value = "";
	boton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
	fetch("soportes/equivalente/enviar_factura.php", {
		method: "POST",
		body: JSON.stringify({ id: id }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response[0].value == "ok") {
				boton.innerHTML = '<span class="fas fa-thumbs-up fa-lg"></span>';
				id = "tableMvtoContable";
				reloadtable(id);
				mje("Documento enviado correctamente");
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
const VerSoporteElectronico = (id) => {
	fetch("soportes/equivalente/ver_html.php", {
		method: "POST",
		body: JSON.stringify({ id: id }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response[0].value == "ok") {
				var url = "https://api.taxxa.co/documentGet.dhtml?hash=" + response[0].msg;
				url = url.replace(/["']/g, "");
				var win = window.open(url, "_blank");
				win.focus();
			} else {
				mjeError(response[0].msg);
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};
//======================================= ELIMINAR IMPUTACION PRESUPUESTAL DE CAUSACION ========================================
const eliminarImputacionDoc = (id) => {
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
			fetch("datos/eliminar/eliminar_mvto_imputacion.php", {
				method: "POST",
				body: JSON.stringify({ id: id }),
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

// ================================== AUTOMATIZACION DE CENTRO DE COSTOS =================================================
const consultaCentrosCosto = () => {
	let id_doc = id_ctb_doc.value;
	let valor = valor_pagar.value;
	// consultar los centros de costos asociados al documento
	fetch("datos/consultar/consulta_centros_costo.php", {
		method: "POST",
		body: JSON.stringify({ id_doc: id_doc, valor: valor }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response[0].value == "ok") {
				mje("Centros de costo cargados");
			} else {
				mjeError("Error al cargar");
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};
// ================================== AUTOCOMPLET PARA INFERMES CONTABLES =================================================
// Autocomplete para la selección del tercero que se asigna al registro presupuestal
document.addEventListener("keyup", (e) => {
	if (e.target.id == "codigoctaini") {
		let valor = "";
		$("#codigoctaini").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "../datos/consultar/consultaPgcp.php",
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
				$("#codigoctaini").val(ui.item.label);
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
	if (e.target.id == "codigoctafin") {
		let valor = "";
		$("#codigoctafin").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "../datos/consultar/consultaPgcp.php",
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
				$("#codigoctafin").val(ui.item.label);
				$("#id_codigoctafin").val(ui.item.value);

				return false;
			},
			focus: function (event, ui) {
				$("#id_codigoctafin").val(ui.item.label);
				return false;
			},
		});
	}
});
// ====================================== GESTION DE PLAN DE CUENTAS ========================================
// Funcion para digitar solo campos numerico de la cuenta
const soloNumeros = (e) => {
	let key = window.Event ? e.which : e.keyCode;
	if ((key >= 48 && key <= 57) || (key >= 96 && key <= 105)) {
		return true;
	} else if (key === 8 || key === 46) {
		return true;
	} else {
		e.preventDefault();
		return false;
	}
};
// Buscar una cuenta en el plan contable
const buscaCuentaPgcp = async (codigo) => {
	// enviamos los datos al servidor para consultar
	try {
		const response = await fetch("datos/consultar/consulta_cuentas_pgcp.php", {
			method: "POST",
			body: JSON.stringify({ codigo: codigo }),
		});
		const data = await response.json();
		console.log(data);
		if (data[0].datos == "ok") {
			nombre.value = data[0].nombre;
			nombre.readOnly = true;
			controlid.value = 1;
		} else {
			nombre.value = "";
			nombre.readOnly = false;
			controlid.value = "";
		}
	} catch (error) {
		console.log("Error js try:");
	}
};
// Funcion para calcualr el nivel de una cuenta contable
const calcularNivel = (codigoCuenta) => {
	// pasar varable a texto
	let codigo = codigoCuenta.toString();
	let nivel = 0;
	// Contar los dos primeros dígitos como niveles independientes
	if (codigo.length >= 2) {
		nivel += 2;
	} else {
		return 1;
	}
	// contar los caracteres restantes para definir si son pares o de a tres
	let pares = parseInt(codigo.length - 2) % 2;
	// Contar los pares de dígitos restantes
	if (pares == 0) {
		nivel += Math.floor((codigo.length - 2) / 2);
	}
	return nivel;
};

// Funcion para verificar nivel
const verificarNivel = (codigo) => {
	let cod = calcularNivel(codigo);
	numero.value = cod;
};

// funcion para buscar cuenta en el plan de cuentas con los datos registrados
const buscarCuentaPlan = async () => {
	let codigo = cuentas.value;
	// enviamos los datos al servidor para consultar
	try {
		const response = await fetch("datos/consultar/consulta_cuentas_nuevas.php", {
			method: "POST",
			body: JSON.stringify({ codigo: codigo }),
		});
		const data = await response.json();
		console.log(data);
		if (data[0].datos == "vacio") {
			console.log(data[0].datos);
		}
		if (data[0].datos == "ok") {
			cuentas.value = data[0].cuenta;
			nombre.value = "";
			nombre.readOnly = false;
			tipo.value = "D";
			controlid.value = "";
			let nivel = calcularNivel(data[0].cuenta);
			numero.value = nivel;
			nombre.focus();
		}
	} catch (error) {
		console.log("Error js try:");
	}
};
// Guardar cuenta en plan contable
const guardarPlanCuentas = async () => {
	let formEnvio = new FormData(formNuevaCuentaContable);
	for (var pair of formEnvio.entries()) {
		console.log(pair[0] + ", " + pair[1]);
		// validar que el value del campo  fecha no sea menor a fecha_min
		if (formEnvio.get("cuentas") == "") {
			document.querySelector("#cuentas").focus();
			mjeError("Debe digitar una cuenta valida ", "");
			return false;
		}
		if (formEnvio.get("nombre") == "") {
			document.querySelector("#nombre").focus();
			mjeError("Debe digitar un mombre valido ", "");
			return false;
		}
		if (formEnvio.get("controlid") == 1) {
			document.querySelector("#cuentas").focus();
			mjeError("La cuenta contable ya existe ", "");
			return false;
		}
	}
	try {
		const response = await fetch("datos/registrar/registrar_cuenta_pgcp.php", {
			method: "POST",
			body: formEnvio,
		});
		const data = await response.json();
		console.log(data);
		if (data.value == "ok") {
			$("#divModalForms").modal("hide");
			$('#tablePlanCuentas').DataTable().ajax.reload();
			mje("Proceso realiado con  éxito...");
		} else {
			mjeError("Error:" + data.msg);
		}
		// cerrar modal
	} catch (error) {
		console.error(error);
	}
};
// Abre formulario para edición de datos de cuenta contable
const editarDatosPlanCuenta = (id) => {
	let url = "form_plan_cuentas.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};

// Cerrar cuenta contable
let cerrarCuentaPlan = function (dato) {
	fetch("datos/consultar/consultaCerrarCuentaPlan.php", {
		method: "POST",
		body: dato,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response.value == "ok") {
				$('#tablePlanCuentas').DataTable().ajax.reload();
				mje("Documento cerrado");
			} else {
				mjeError("Error: " + response.msg, "Verificar");
			}
		});
};
// Abrir cuenta contable
let abrirCuentaPlan = function (dato) {
	//let doc = id_ctb_doc.value;
	fetch("datos/consultar/consultaAbriCuentaPlan.php", {
		method: "POST",
		body: dato,
	})
		.then((response) => response.json())
		.then((response) => {
			if (response.value == "ok") {
				mje("Documento activado");
				$('#tablePlanCuentas').DataTable().ajax.reload();
			} else {
				mjeError("Error: " + response.msg);
			}
		});
};

// Eliminar cuenta contable
const eliminarCuentaContable = (comp) => {
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
			fetch("datos/eliminar/eliminar_cuenta_contable.php", {
				method: "POST",
				body: JSON.stringify({ id: comp }),
			})
				.then((response) => response.json())
				.then((response) => {
					console.log(response);
					if (response.value == "ok") {
						$('#tablePlanCuentas').DataTable().ajax.reload();
						mje("Registro eliminado");
					} else {
						mjeError("Error: " + response.msg, "Verifique si la cuenta tiene movimientos asociados o cuentas dependientes");
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};
const guardarDocFuente = async () => {
	$('.is-invalid').removeClass('is-invalid');
	let formEnvio = new FormData(formDocFuente);
	for (var pair of formEnvio.entries()) {
		console.log(pair[0] + ", " + pair[1]);
		// validar que el value del campo  fecha no sea menor a fecha_min
		if (formEnvio.get("txtCodigo") == "") {
			document.querySelector("#txtCodigo").classList.add("is-invalid");
			document.querySelector("#txtCodigo").focus();
			mjeError("Debe digitar una codigo", "");
			return false;
		}
		if (formEnvio.get("txtNombre") == "") {
			document.querySelector("#txtNombre").classList.add("is-invalid");
			document.querySelector("#txtNombre").focus();
			mjeError("Debe digitar un mombre valido ", "");
			return false;
		}
	}
	try {
		const response = await fetch("datos/registrar/registrar_doc_fuente.php", {
			method: "POST",
			body: formEnvio,
		});
		const data = await response.json();
		console.log(data);
		if (data.value == "ok") {
			$("#divModalForms").modal("hide");
			$('#tableDocumentosFuente').DataTable().ajax.reload();
			mje("Proceso realiado con  éxito...");
		} else {
			mjeError("Error:" + data.msg);
		}
		// cerrar modal
	} catch (error) {
		console.error(error);
	}
};
const editarDocFuente = (id) => {
	let url = "form_documentos_fuente.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
const eliminarDocFuente = (comp) => {
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
			fetch("datos/eliminar/eliminar_documento_fuente.php", {
				method: "POST",
				body: JSON.stringify({ id: comp }),
			})
				.then((response) => response.json())
				.then((response) => {
					console.log(response);
					if (response.value == "ok") {
						$('#tableDocumentosFuente').DataTable().ajax.reload();
						mje("Registro eliminado");
					} else {
						mjeError("Error: " + response.msg, "Verifique si la cuenta tiene movimientos asociados o cuentas dependientes");
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};
function EstadoDocFuente(id, estado) {
	fetch("datos/consultar/consultaEstadoDocFuente.php", {
		method: "POST",
		body: JSON.stringify({ id: id, estado: estado }),
	})
		.then((response) => response.json())
		.then((response) => {
			if (response.value == "ok") {
				$('#tableDocumentosFuente').DataTable().ajax.reload();
				mje(response.msg, "Proceso realizado con éxito...");
			} else {
				mjeError("Error: " + response.msg, "Verificar");
			}
		});
}
function abrirFuente(id) {
	EstadoDocFuente(id, 1);
}
function cerrarFuente(id) {
	EstadoDocFuente(id, 0);
}
// ================================== ANULACION DE DOCUMENTOS =================================================
// Abre formulario para datos de anulación
const anularDocumentoCont = (id) => {
	let url = "form_fecha_anulacion.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
// Enviar datos para anulacion
const changeEstadoAnulaCtb = async () => {
	let formEnvio = new FormData(formAnulacionCtb);
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
		const response = await fetch("datos/registrar/registrar_anula_ctb.php", {
			method: "POST",
			body: formEnvio,
		});
		const data = await response.json();
		console.log(data);
		if (data[0].value == "ok") {
			// realizar un case para opciones 1.2.3
			if (data[0].tipo == 1) {
				let tabla = "tableMvtoContable";
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
// ================================== INFORMES CONTABILIDAD =================================================

const cargarReporteContable = (id) => {
	let url = "";
	if (id == 21) {
		url = "informe_descuentos_mpio_form.php";
	}
	if (id == 22) {
		url = "informe_descuentos_dian_form.php";
	}
	if (id == 23) {
		url = "informe_descuentos_otros_form.php";
	}
	if (id == 11) {
		url = "informe_libros_auxiliares_form.php";
	}
	if (id == 24) {
		url = "informe_descuentos_estampillas_form.php";
	}
	if (id == 12) {
		url = "informe_balance_prueba_form.php";
	}
	if (id == 25) {
		url = "informe_certificado_ingresos_form.php";
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
const generarInformeCtb = (id) => {
	let cta_inicial = 0;
	let cta_final = 0;
	let fecha_inicial = fecha_ini.value;
	let fecha_final = fecha_fin.value;
	if (id == 9) {
		cta_inicial = id_codigoctaini.value;
		cta_final = id_codigoctafin.value;
	}
	let sede = tipo_sede.value;
	let archivo = 0;
	if (sede == 0) {
		mjeError("Debe seleccionar una sede");
		return;
	} else {
		if (id == 1) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_mpio_resumen.php";
		}
		if (id == 2) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_mpio_detalle.php";
		}
		if (id == 3) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_mpio_exogena.php";
		}
		if (id == 4) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_dian_resumen.php";
		}
		if (id == 5) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_dian_detalle.php";
		}
		if (id == 6) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_otros_resumen.php";
		}
		if (id == 7) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_otros_detalle.php";
		}
		if (id == 8) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_otros_detalle.php";
		}
		if (id == 9) {
			archivo = window.urlin + "/contabilidad/informes/informe_libros_auxiliares_detalle.php";
		}
		if (id == 10) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_estampillas_resumen.php";
		}
		if (id == 11) {
			archivo = window.urlin + "/contabilidad/informes/informe_impuestos_estampillas_detalle.php";
		}
		if (id == 12) {
			archivo = window.urlin + "/contabilidad/informes/informe_balance_prueba_detalle.php";
		}
		let ruta = {
			url: archivo,
			name1: "fec_inicial",
			valor1: fecha_inicial,
			name2: "fec_final",
			valor2: fecha_final,
			name3: "mpio",
			valor3: sede,
			name4: "cta_inicial",
			valor4: cta_inicial,
			name5: "cta_final",
			valor5: cta_final,
		};
		redireccionar5(ruta);
	}
};

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
