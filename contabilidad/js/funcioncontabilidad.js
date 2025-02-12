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
		var tableMvtoCtb = $("#tableMvtoContable").DataTable({
			dom: setdom,
			autoWidth: false,
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
			serverSide: true,
			processing: true,
			ajax: {
				url: "datos/listar/datos_mvto_contabilidad.php",
				data: function (d) {
					d.id_doc = id_doc;
				},
				type: "POST",
				dataType: "json",
			},
			columns: [
				{ data: "numero" },
				{ data: "rp" },
				{ data: "fecha" },
				{ data: "tercero" },
				{ data: "valor" },
				{ data: "botones" }],
			columnDefs: [
				{ class: 'text-wrap', targets: [3] },
				{ orderable: false, targets: 5 },
				{ targets: -1, width: "160px", className: "text-nowrap" }
			],
			order: [
				[2, "desc"],
			],
		});
		// Control del campo de búsqueda
		$('#tableMvtoContable_filter input').unbind(); // Desvinculamos el evento por defecto
		$('#tableMvtoContable_filter input').bind('keypress', function (e) {
			if (e.keyCode == 13) { // Si se presiona Enter (código 13)
				tableMvtoCtb.search(this.value).draw(); // Realiza la búsqueda y actualiza la tabla
			}
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
	$('#cargaExcelPuc').on('click', function () {
		$.post("datos/registrar/form_cargar_puc.php", function (he) {
			$('#divTamModalForms').removeClass('modal-xl');
			$('#divTamModalForms').removeClass('modal-lg');
			$('#divTamModalForms').removeClass('modal-sm');
			$('#divModalForms').modal('show');
			$("#divForms").html(he);
		});
	});
	$('#divModalForms').on('click', '#btnAddPucExcel', function () {
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
			var btns = '<button class="btn btn-primary btn-sm" id="btnConfirCargaPuc">Aceptar</button><button type="button" class="btn btn-secondary  btn-sm"  data-dismiss="modal">Cancelar</button>'
			$("#divModalConfDel").modal("show");
			$("#divMsgConfdel").html('Esta acción eliminará el cargue del plan de cuentas.<br> Confirmar.');
			$("#divBtnsModalDel").html(btns);
			$('#divModalConfDel').on('click', '#btnConfirCargaPuc', function () {
				$("#divModalConfDel").modal("hide");
				let datos = new FormData();
				datos.append('file', $('#file')[0].files[0]);
				datos.append('idPto', $('#idPtoEstado').val());
				$('#btnAddPtoExcel').attr('disabled', true);
				$('#btnAddPtoExcel').html('<i class="fas fa-spinner fa-pulse"></i> Cargando...');
				$.ajax({
					type: 'POST',
					url: 'datos/registrar/cargar_puc_excel.php',
					contentType: false,
					data: datos,
					processData: false,
					cache: false,
					success: function (r) {
						$('#btnAddPtoExcel').attr('disabled', false);
						$('#btnAddPtoExcel').html('Subir');
						if (r == 'ok') {
							reloadtable('tablePlanCuentas');
							$('#divModalForms').modal('hide');
							$('#divModalDone').modal('show');
							$('#divMsgDone').html('Plan de cuentas Cargado Correctamente');
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
	var id = $(this).attr('text');
	GuardaDocCtb(id);

});
$('#GuardaDocCtb').on('click', function () {
	var id = $(this).attr('text');
	GuardaDocCtb(id);

});
function GuardaDocCtb(id) {
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
	} else if ($('#objeto').val().length > 200) {
		$('#objeto').addClass('is-invalid');
		$('#objeto').focus();
		mjeError('El objeto no puede tener mas de 200 caracteres');
	} else {
		var datos = $('#formGetMvtoCtb').serialize() + '&id=' + id;
		url = "datos/registrar/registrar_mvto_contable_doc.php";
		$.ajax({
			type: 'POST',
			url: url,
			data: datos,
			dataType: 'json',
			success: function (r) {
				if (r.status == 'ok') {
					$('#tableMvtoContable').DataTable().ajax.reload();
					$('#divModalForms').modal('hide');
					mje('Proceso realizado correctamente');
					setTimeout(() => {
						if ($('#tableMvtoContableDetalle').length) {
							$('<form action="lista_documentos_det.php" method="post">' +
								'<input type="hidden" name="id_doc" value="' + r.id_doc + '" />' +
								'<input type="hidden" name="tipo_dato" value="' + r.t_dato + '" />' +
								'</form>').appendTo("body").submit();
						}
					}, 300);
				} else {
					function mjeError(titulo, mensaje) {
						Swal.fire({
							title: titulo,
							html: mensaje, // Renderiza el HTML en el mensaje
							icon: "error"
						});
					}
					mjeError('Error:', r.msg);
				}

			}
		});
	}
	return false;
};

$('#divModalForms').on('keyup', '#valor_pagar', function () {
	$('#valor_base').val($('#valor_pagar').val());

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
$("#tableMvtoContableDetalle").on("input", ".bTercero", function () {
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
$('#areaReporte').on('click', '#bTercero', function () {
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
			$('#id_tercero').val(ui.item.id);
		}
	});
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
					if ($('#tipodato').length && $('#tipodato').val() == '1') {
						var id = idTercero.value;
						var trc = bTercero.value;
					} else {
						var id = '0';
						var trc = '';
					}
					if (opc == '0') {
						$('#codigoCta').val('');
						$('#id_codigoCta').val('0');
						$('#tipoDato').val('0');
						$('#bTercero').val(trc);
						$('#idTercero').val(id);
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
			if (response.status == "ok") {
				$('#tableMvtoContable').DataTable().ajax.reload();
				$('#tableMvtoTesoreriaPagos').DataTable().ajax.reload();
			} else {
				mjeError("Documento no cerrado", "Verifique información ingresada" + response.msg);
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
		.then((response) => response.text())
		.then((response) => {
			if (response == "ok") {
				mje("Documento abierto");
				let id = "tableMvtoContable";
				reloadtable(id);
			} else {
				mjeError("Error:", response);
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
$('#tableMvtoContable').on('click', '.editar', function () {
	var id_detalle = $(this).attr('text');
	let id_doc = $("#id_ctb_doc").val();
	$.post("datos/registrar/formadd_mvto_contable.php", { id_doc: id_doc, id_detalle: id_detalle }, function (he) {
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});

});
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
	var fila = boton.closest("tr");
	var fecha = fila.querySelector("input[name='fec_doc[]']").value;
	if (fecha == "") {
		mjeError("La fecha no puede estar vacia");
		return false;
	}
	var data = boton.value + "|" + fecha;
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
	let tipo_dato = "3";
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
let cargaRubrosRp = function (id_doc) {
	if (id_doc == '0') {
		mjeError("No puede seleccionar imputación presupuestal", "Primero guarde el documento");
		return false;
	} else {
		$.post("lista_causacion_registros_total.php", { id_doc: id_doc }, function (he) {
			$("#divTamModalReg").removeClass("modal-sm");
			$("#divTamModalReg").removeClass("modal-3x");
			$("#divTamModalReg").removeClass("modal-lg");
			$("#divTamModalReg").addClass("modal-xl");
			$("#divModalReg").modal("show");
			$("#divFormsReg").html(he);
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
var DetalleImputacionCtasPorPagar = function () {
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
		var data = $('#formImputacion').serialize();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'datos/registrar/registrar_mvto_cobp.php',
			data: data,
			success: function (r) {
				if (r.status == 'ok') {
					mje('Proceso realizado correctamente');
					ImputacionCtasPorPagar($('#id_ctb_doc').val());
					$('#valImputacion').html(r.acumulado);
				} else {
					mjeError('Error:', r.msg);
				}
			}
		});
	}
};
/*
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
*/
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
var guardarCostos = function () {
	var valor = Number($('#valor_cc').val().replace(/\,/g, "", ""));
	var max = Number($('#valor_cc').attr('max').replace(/\,/g, "", ""));

	$('.is-invalid').removeClass('is-invalid');
	if ($('#id_municipio').val() == '0') {
		$('#municipio').addClass('is-invalid');
		$('#municipio').focus();
		mjeError('Debe seleccionar un municipio');
	} else if ($('#id_sede').val() == '0') {
		$('#id_sede').addClass('is-invalid');
		$('#id_sede').focus();
		mjeError('Debe seleccionar una sede');
	} else if ($('#id_cc').val() == '0') {
		$('#id_cc').addClass('is-invalid');
		$('#id_cc').focus();
		mjeError('Debe seleccionar un centro de costo');
	} else if (valor <= 0 || valor > max) {
		$('#valor_cc').addClass('is-invalid');
		$('#valor_cc').focus();
		mjeError('El valor del centro de costo debe ser mayor a cero y menor a ' + max.toLocaleString("es-MX"));
	} else {
		var data = $('#formGuardaCentroCosto').serialize();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'datos/registrar/registrar_mvto_costos.php',
			data: data,
			success: function (r) {
				if (r.status == 'ok') {
					mje('Proceso realizado correctamente');
					CentroCostoCtasPorPagar($('#id_ctb_doc').val(), 0);
					$('#valCentroCosto').html(r.acumulado);
				} else {
					mjeError('Error:', r.msg);
				}
			}
		});
	}

};
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
//Editar centro de costo asignado a una causación
const editarCentroCosto = (dato) => {
	CentroCostoCtasPorPagar($('#id_ctb_doc').val(), dato);
};
// Eliminar centro de costo asignado a una causación
const eliminarCentroCosto = (dato) => {
	//confirmar eliminación
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
			fetch("datos/eliminar/eliminar_mvto_costos.php", {
				method: "POST",
				body: JSON.stringify({ id: dato, id_doc: $('#id_ctb_doc').val() }),
			})
				.then((response) => response.json())
				.then((response) => {
					if (response[0].value == "ok") {
						mje("Registro eliminado exitosamente");
						CentroCostoCtasPorPagar($('#id_ctb_doc').val(), 0);
						$('#valCentroCosto').html(response[0].acumulado);
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
	//neto_pago.value = base.toLocaleString("es-MX");
};

// Muestra el select según el tipo de retención seleccionado
const mostrarRetenciones = (dato) => {
	let id_doc = $("#id_ctb_doc").val();
	let tipo = $("#tipo_rete").val();
	$('#divTamModalForms').removeClass('modal-xl');
	$('#divTamModalForms').addClass('modal-lg');
	fetch("datos/consultar/consulta_retenciones.php", {
		method: "POST",
		body: JSON.stringify({ id: dato, tipo: tipo }),
	})
		.then((response) => response.text())
		.then((response) => {
			if (tipo == 3) {
				response = '';
			} else {
				$('#divRetIca').html('');
			}
			$('#divRete').html(response);
		})
		.catch((error) => {
			console.log("Error:");
		});
	if (tipo == 3) {
		// Enviar y consultar el valor causado por cada sede ======= Valor causado por sede
		if ($('#factura_des').val() != '0|0') {
			let valores = $('#factura_des').val();
			fetch("datos/consultar/consulta_baseica_sede.php", {
				method: "POST",
				body: JSON.stringify({ id_doc: id_doc, valores: valores }),
			})
				.then((response) => response.text())
				.then((response) => {
					$('#divTamModalForms').removeClass('modal-lg');
					$('#divTamModalForms').addClass('modal-xl');
					$('#divRetIca').html(response);
				})
				.catch((error) => {
					console.log("Error:");
				});
		} else {
			mjeError("Debe seleccionar una factura");
			$('#tipo_rete').val('0');
		}
	} else {
		valor_rte.value = 0;
	}
	id_terceroapi.value = "";
};

// Aplica tarifa de acuerdo a la retención seleccionada
const aplicaDescuentoRetenciones = (retencion) => {
	let valor = parseFloat(valor_base.value.replace(/\,/g, "", ""));
	let iva = parseFloat(valor_iva.value.replace(/\,/g, "", ""));
	let tipoRetencion = document.querySelector("#tipo_rete").value;
	let band = true;
	$('.is-invalid').removeClass('is-invalid');
	if (valor > 0) {
		if (tipoRetencion == 3) {
			let datos = $("#id_rete_sede").val();
			let datos2 = datos.split("_");
			valor = datos2[1];
			id_terceroapi.value = datos2[0];
			if ($('#id_rete_sede').val() == '0') {
				$('#id_rete_sede').addClass('is-invalid');
				$('#id_rete_sede').focus();
				$('#id_rete').val('0');
				mjeError('Debe seleccionar una sede');
				band = false;
			}
		}
		if (band) {
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
						id_rango.value = response[0].id_rango;
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
		}
	} else {
		mjeError("El valor base debe ser mayor a cero", 'Verifique: Seleccione factura');
		$("#id_rete").val("0");
	}
};
// Aplica tarifa de acuerdo a la retención seleccionada
const aplicaDctoRetIca = (elemento, id, op) => {
	var fila = elemento.parentNode.parentNode;
	var bs = Number(op) == 1 ? 'base' : 'valor_rte';
	var base = parseFloat(fila.querySelector("input[name='" + bs + "[]']").value.replace(/\,/g, "", ""));
	if (Number(op) == 2 && base == 0) {
		fila.querySelector("select[name='id_rete_sobre[]']").value = 0;
		mjeError('El valor base para la sobretasa no puede ser cero');
		return false
	}
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "datos/consultar/consulta_tarifa_ret.php",
		data: { id: id, base: base },
		success: function (r) {
			if (r.status == "ok") {
				let campo = Number(op) == 1 ? 'valor_rte' : 'valor_sob';
				fila.querySelector("input[name='" + campo + "[]']").value = r.pesos;
			} else {
				mjeError("Error: ", r.msg);
			}
		},
	});
};
var ValorBase = function (data) {
	data = data.split("|");
	$("#valor_base").val(data[0]);
	$("#valor_iva").val(data[1]);
};
// Guardar valor de la retención
var GuardarRetencion = function () {
	$('.is-invalid').removeClass('is-invalid');
	if ($('#tipo_rete').val() == '3') {
		enviaRetenciones();
	} else {
		if ($('#tipo_rete').val() == '0') {
			$('#tipo_rete').addClass('is-invalid');
			$('#tipo_rete').focus();
			mjeError('Debe seleccionar un tipo retención');
		} else if ($('#id_rete').val() == '0') {
			$('#id_rete').addClass('is-invalid');
			$('#id_rete').focus();
			mjeError('Debe seleccionar una retención');
		} else if (Number($('#valor_rte').val()) < 0) {
			$('#valor_rte').addClass('is-invalid');
			$('#valor_rte').focus();
			mjeError('Debe ingresar un valor de retención');
		} else {
			enviaRetenciones();
		}
	}
};
function enviaRetenciones() {
	$("#formAddRetencioness").find(":disabled").prop("disabled", false);
	var data = $('#formAddRetencioness').serialize();
	$("#formAddRetencioness").find(":disabled").prop("disabled", true);
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: 'datos/registrar/registrar_mvto_retenciones.php',
		data: data,
		success: function (r) {
			if (r.status == 'ok') {
				mje('Proceso realizado correctamente');
				DesctosCtasPorPagar($('#id_ctb_doc').val(), $('#factura_des').val());
				$('#valDescuentos').html(r.acumulado);
			} else {
				mjeError('Error:', r.msg);
			}
		}
	});
}
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
		body: JSON.stringify({ id: id, id_doc: $('#id_ctb_doc').val() }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response[0].value == "ok") {
				mje("Registro eliminado");
				DesctosCtasPorPagar($('#id_ctb_doc').val(), $('#factura_des').val());
				$('#valDescuentos').html(response[0].acumulado);
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

const ProcesaFacturas = (boton) => {
	$('.is-invalid').removeClass('is-invalid');
	id = boton.getAttribute("text");
	if ($('#tipoDoc').val() == '0') {
		$('#tipoDoc').addClass('is-invalid');
		$('#tipoDoc').focus();
		mjeError("Debe seleccionar un tipo de documento");
	} else if (Number($('#numFac').val()) <= 0) {
		$('#numFac').addClass('is-invalid');
		$('#numFac').focus();
		mjeError("Debe ingresar el número de factura");
	} else if ('#fechaDoc' == '') {
		$('#fechaDoc').addClass('is-invalid');
		$('#fechaDoc').focus();
		mjeError("Debe ingresar la fecha del documento");
	} else if ($('#fechaDoc').attr('min') > $('#fechaDoc').val() || $('#fechaDoc').attr('max') < $('#fechaDoc').val()) {
		$('#fechaDoc').addClass('is-invalid');
		$('#fechaDoc').focus();
		mjeError("La fecha del documento debe estar entre " + $('#fechaDoc').attr('min') + " y " + $('#fechaDoc').attr('max'));
	} else if ($('#fechaVen').val() == '') {
		$('#fechaVen').addClass('is-invalid');
		$('#fechaVen').focus();
		mjeError("Debe ingresar la fecha de vencimiento");
	} else if ($('#fechaVen').attr('min') > $('#fechaVen').val() || $('#fechaVen').attr('max') < $('#fechaVen').val()) {
		$('#fechaVen').addClass('is-invalid');
		$('#fechaVen').focus();
		mjeError("La fecha de vencimiento debe estar entre " + $('#fechaVen').attr('min') + " y " + $('#fechaVen').attr('max'));
	} else if ($('#fechaVen').val() < $('#fechaDoc').val()) {
		$('#fechaVen').addClass('is-invalid');
		$('#fechaVen').focus();
		mjeError("La fecha de vencimiento debe ser mayor a la fecha del documento");
	} else if (Number($('#valor_pagar').val()) <= 0) {
		$('#valor_pagar').addClass('is-invalid');
		$('#valor_pagar').focus();
		mjeError("Debe ingresar el valor a pagar");
	} else if (Number($('#valor_iva').val()) < 0) {
		$('#valor_iva').addClass('is-invalid');
		$('#valor_iva').focus();
		mjeError("El valor del IVA no puede ser menor a cero");
	} else if (Number($('#valor_base').val()) <= 0) {
		$('#valor_base').addClass('is-invalid');
		$('#valor_base').focus();
		mjeError("Debe ingresar el valor base");
	} else if ($('#detalle').val() == '' || $('#detalle').val().length > 200) {
		$('#detalle').addClass('is-invalid');
		$('#detalle').focus();
		mjeError("Debe ingresar un detalle válido o menor a 200 caracteres");
	} else {
		$.ajax({
			type: "POST",
			url: "datos/registrar/registrar_mvto_contable_doc_cxp.php",
			data: $('#formFacturaCXP').serialize() + "&id_doc=" + id,
			dataType: "json",
			success: function (response) {
				if (response.status == "ok") {
					FacturarCtasPorPagar(id);
					$('#valFactura').html(response.acumulado);
					mje("Registro guardado");
				} else {
					mjeError("Error: " + response.msg);
				}
			}
		});
	}
	return false;
};
const FacturarCtasPorPagar = (id) => {
	let url = "lista_facturas_cxp.php";
	var objeto = $('#objeto').length ? $('#objeto').val() : '';
	$.post(url, { id: id, objeto: objeto }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
const ImputacionCtasPorPagar = (id) => {
	let url = "lista_imputacion_cxp.php";
	$.post(url, { id: id }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
const CentroCostoCtasPorPagar = (id, id_detalle) => {
	let url = "lista_centro_costo_cxp.php";
	$.post(url, { id: id, id_detalle: id_detalle }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-lg");
		$("#divTamModalForms").addClass("modal-xl");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};
const DesctosCtasPorPagar = (id, fc) => {
	let url = "lista_descuentos_cxp.php";
	$.post(url, { id: id, fc: fc }, function (he) {
		$("#divTamModalForms").removeClass("modal-sm");
		$("#divTamModalForms").removeClass("modal-xl");
		$("#divTamModalForms").addClass("modal-lg");
		$("#divModalForms").modal("show");
		$("#divForms").html(he);
	});
};

const editarFactura = (boton) => {
	var data = atob(boton.getAttribute("text"));
	FacturarCtasPorPagar(data);
	$('#tipoDoc').focus();

};
const eliminarFactura = (boton) => {
	var id = boton.getAttribute("text");
	Swal.fire({
		title: '¿Está seguro de eliminar el registro?',
		text: "No podrá revertir esta acción",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Si, eliminar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				type: "POST",
				url: "datos/eliminar/eliminar_mvto_contable_doc_cxp.php",
				data: { id: id },
				dataType: "json",
				success: function (response) {
					if (response.status == "ok") {
						mje("Registro eliminado");
						FacturarCtasPorPagar(response.id);
						$('#valFactura').html(response.acumulado);
					} else {
						mjeError("Error: " + response.msg);
					}
				}
			});
		}
	});
};
// Genera movimiento cuando se hace procesamiento automatico del documento cxp
const generaMovimientoCxp = () => {
	var val_fac = $('#valFactura').text().replace(/[\s$]+/g, "").replace(/\,/g, "");
	var val_inp = $('#valImputacion').text().replace(/[\s$]+/g, "").replace(/\,/g, "");
	var val_cos = $('#valCentroCosto').text().replace(/[\s$]+/g, "").replace(/\,/g, "");
	// verificar si los tres valores son iguales
	if (val_fac == val_inp && val_fac == val_cos) {
		let id_crp = $('#id_crpp').val();
		let id_doc = $('#id_ctb_doc').val();
		fetch("datos/registrar/registrar_mvto_libaux_auto_cxp.php", {
			method: "POST",
			body: JSON.stringify({ id_doc: id_doc, id_crp: id_crp }),
		})
			.then((response) => response.json())
			.then((response) => {
				console.log(response);
				if (response.status == "ok") {
					mje("Movimiento generado con éxito ");
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
					mjeError("Error al guardar:" + response.msg);
				}
			})
			.catch((error) => {
				console.log("Error:");
			});
	} else {
		mjeError("Los valores de Facturación, imputacion y centro de costo no son iguales");
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
				.then((response) => response.text())
				.then((response) => {
					console.log(response);
					if (response == "ok") {
						mje("Registro eliminado");
						$('#tableMvtoContable').DataTable().ajax.reload();
					} else {
						mjeError("Error al eliminar: " + response);
					}
				})
				.catch((error) => {
					console.log("Error:");
				});
		}
	});
};

/*=================================   IMPRESION DE PFORMATOS =====================================*/
function ValidaValoresIguales(id, callback) {
	var ruta = "datos/consultar/consulta_valores_doc.php";
	$.ajax({
		url: ruta,
		method: 'POST',
		dataType: 'json',
		data: { id: id },
		success: function (response) {
			if (response.res == 'ok') {
				callback(true);
			} else {
				callback(false);
			}
		},
		error: function (xhr, status, error) {
			console.error("Error en la solicitud AJAX:", error);
			callback(false);
		}
	});
}
function CierraDocCtb(id) {
	let tipo = $("#tipodato").length ? $("#tipodato").val() : $("#id_ctb_doc").val();
	if (tipo == '3') {
		ValidaValoresIguales(id, function (resultado) {
			if (resultado === true) {
				cerrarDocumentoCtb(id);
				mje("Documento cerrado correctamente");
			} else {
				mjeError("Verificar igualdad en valores y cuentas contables vacias");
			}
		});
	} else {
		cerrarDocumentoCtb(id);
		mje("Documento cerrado correctamente");
	}
}
const imprimirFormatoDoc = (id) => {
	let tipo = $("#tipodato").length ? $("#tipodato").val() : $("#id_ctb_doc").val();
	if (tipo == '3') {
		ValidaValoresIguales(id, function (resultado) {
			if (resultado === true) {
				ImprimirD(id, tipo);
			} else {
				mjeError("Verificar igualdad en valores y cuentas contables vacias");
			}
		});
	} else {
		ImprimirD(id, tipo);
	}

};

function ImprimirD(id, tipo) {
	let url = "soportes/imprimir_formato_doc.php";
	$.post(url, { id: id, tipo: tipo }, function (he) {
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
	if ($('#tableMvtoContableDetalle').length) {
		window.location.reload();
	}
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
const consecutivoDocumento = (id) => {
	fetch("datos/consultar/consultarConsecutivo.php", {
		method: "POST",
		body: JSON.stringify({ id: id }),
	})
		.then((response) => response.json())
		.then((response) => {
			console.log(response);
			if (response.status == "ok") {
				$('#numFac').val(response.consecutivo);
			} else {
				mjeError("Error: " + response.msg);
			}
		})
		.catch((error) => {
			console.log("Error:");
		});
};

const EnviaDocumentoSoporte = (boton) => {
	boton.disabled = true;
	let id = boton.value;
	boton.value = "";
	boton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
	$.ajax({
		type: "POST",
		url: "soportes/equivalente/enviar_factura.php",
		data: { id: id },
		dataType: "json",
		success: function (response) {
			if (response[0].value == "ok") {
				boton.innerHTML = '<span class="fas fa-thumbs-up fa-lg"></span>';
				$('#tableMvtoContable').DataTable().ajax.reload();
				mje("Documento enviado correctamente");
			} else {
				boton.disabled = false;
				boton.value = id;
				boton.innerHTML = '<span class="fas fa-paper-plane fa-lg"></span>';
				mjeError(response.msg);
			}
		},
		error: function (xhr, status, error) {
			console.error("Error en la solicitud AJAX:", error);
		}
	});

	return false
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
		$("#codigoctaini").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "../datos/consultar/consultaPgcp.php",
					dataType: "json",
					type: 'POST',
					data: { term: request.term },
					success: function (data) {
						response(data);
					}
				});
			},
			minLength: 2,
			select: function (event, ui) {
				$("#id_codigoctaini").val(ui.item.id);
			}
		});
	}
});
// Cuenta final
document.addEventListener("keyup", (e) => {
	if (e.target.id == "codigoctafin") {
		$("#codigoctafin").autocomplete({
			source: function (request, response) {
				$.ajax({
					url: "../datos/consultar/consultaPgcp.php",
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
				$("#id_codigoctafin").val(ui.item.id);
			}
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
function changeEstadoAnulaCtb() {
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
		var datos = $('#formAnulaDocCtb').serialize();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: "datos/registrar/registrar_anulacion_ctb.php",
			data: datos,
			success: function (r) {
				if (r.status == 'ok') {
					$('#divModalForms').modal('hide');
					$('#tableMvtoContable').DataTable().ajax.reload();
					mje('Documento anulado correctamente');
				} else {
					mjeError('Error: ' + r.msg);
				}
			}
		});
	}
}
// ================================== INFORMES CONTABILIDAD =================================================

const cargarReporteContable = (id) => {
	let url = "";
	if (id == 1) {
		url = "informe_contaduria_cgn_form.php";
	}
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
const generarInformeCtb = (boton) => {
	var id = boton.value;
	var fecha_inicial = $("#fecha_ini").length ? $("#fecha_ini").val() : 0;
	var fecha_final = $("#fecha_fin").length ? $("#fecha_fin").val() : 0;
	var cta_inicial = 0;
	var cta_final = 0;
	var id_tercero = 0;
	var tp_doc = 0;
	var xtercero = 0;
	var band = false;
	if ($("#codigoctaini").length) {
		if ($("#id_codigoctaini").val() == '0' || $("#id_codigoctafin").val() == '0') {
			mjeError("Debe seleccionar una cuenta inicial y final");
			band = true;
		} else {
			cta_inicial = $("#id_codigoctaini").val();
			cta_final = $("#id_codigoctafin").val();
		}
	}
	if ($("#slcTpDoc").length) {
		tp_doc = $("#slcTpDoc").val();
		id_tercero = $("#id_tercero").val();
	}
	if ($("#xTercero").length) {
		xtercero = $("#xTercero").is(":checked") ? 1 : 0;
	}
	if (band) {
		return false;
	}
	var data = {
		id: id,
		fecha_inicial: fecha_inicial,
		fecha_final: fecha_final,
		cta_inicial: cta_inicial,
		cta_final: cta_final,
		id_tercero: id_tercero,
		tp_doc: tp_doc,
		xtercero: xtercero,
	}

	var ruta = window.urlin + "/contabilidad/informes/";

	if (id == 1) {
		ruta = ruta + "informe_impuestos_mpio_resumen.php";
	} else if (id == 2) {
		ruta = ruta + "informe_impuestos_mpio_detalle.php";
	} else if (id == 3) {
		ruta = ruta + "informe_impuestos_mpio_exogena.php";
	} else if (id == 4) {
		ruta = ruta + "informe_impuestos_dian_resumen.php";
	} else if (id == 5) {
		ruta = ruta + "informe_impuestos_dian_detalle.php";
	} else if (id == 6) {
		ruta = ruta + "informe_impuestos_otros_resumen.php";
	} else if (id == 7) {
		ruta = ruta + "informe_impuestos_otros_detalle.php";
	} else if (id == 8) {
		ruta = ruta + "informe_impuestos_otros_detalle.php";
	} else if (id == 9) {
		ruta = ruta + "informe_libros_auxiliares_rango.php";
	} else if (id == 10) {
		ruta = ruta + "informe_impuestos_estampillas_resumen.php";
	} else if (id == 11) {
		ruta = ruta + "informe_impuestos_estampillas_detalle.php";
	} else if (id == 12) {
		ruta = ruta + "informe_balance_prueba_detalle.php";
	} else if (id == 13) {
		ruta = ruta + "informe_contaduria_detalle.php";
	}
	boton.disabled = true;
	var span = boton.querySelector("span")
	span.classList.add("spinner-border", "spinner-border-sm");
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

$('#areaReporte').on('dblclick', '#tbBalancePrueba tr', function () {
	var cuenta = $(this).find('td:eq(0)').text();
	var tipo = $(this).find('td:eq(2)').text();
	var saldo = $(this).find('td:eq(6)').text();
	var f_ini = $('#fecha_ini').val();
	var f_fin = $('#fecha_fin').val();
	$('#divModalEspera').modal('show');
	$.ajax({
		url: window.urlin + "/contabilidad/informes/informe_libros_auxiliares_detalle.php",
		type: "POST",
		data: { cuenta: cuenta, tipo: tipo, saldo: saldo, f_ini: f_ini, f_fin: f_fin },
		success: function (r) {
			setTimeout(function () {
				hideModalEspera()
				let encodedTable = btoa(unescape(encodeURIComponent(r)));
				$('<form action="' + window.urlin + '/financiero/reporte_excel.php" method="post">' +
					'<input type="hidden" name="xls" value="' + encodedTable + '" />' +
					'<input type="hidden" name="head" value="1" />' +
					'</form>').appendTo('body').submit();
			}, 1000);
		}, error: function (error) {
			console.log("Error:" + error);
			hideModalEspera()
		}
	});
	function hideModalEspera() {
		$('#divModalEspera').modal('hide');
		$('.modal-backdrop').remove();
	}

});
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

function obtenerNumeroSemana(fecha) {
	let fechaAuxiliar = new Date("2024-05-02"); // necesito traer una fecha para hacer el calculo 
	let numeroDia = (fecha.getDay() + 6) % 7;

	fechaAuxiliar.setDate(fechaAuxiliar.getDate() - numeroDia + 1);
	let primerJueves = fechaAuxiliar.valueOf();

	fechaAuxiliar.setMonth(0, 1);

	if (fechaAuxiliar.getDay() !== 4) {
		fechaAuxiliar.setMonth(0, 1 + ((4 - fechaAuxiliar.getDay()) + 7) % 7);
	}

	return 1 + Math.ceil((primerJueves - fechaAuxiliar) / 604800000);
}
function CausaAuCentroCostos() {
	var id_crp = $('#id_crpp').val();
	var id_doc = $('#id_ctb_doc').val();
	var valor = parseFloat($('#valFactura').text().replace(/[\$,]/g, ''));
	$.ajax({
		url: 'datos/registrar/registrar_mvto_costos_auto.php',
		type: 'POST',
		data: { id_crp: id_crp, id_doc: id_doc, valor: valor },
		dataType: 'json',
		success: function (r) {
			if (r.status == 'ok') {
				mje('Proceso realizado correctamente');
				$('#valCentroCosto').html(r.acumulado);
			} else {
				mjeError(r.msg);
			}
		}
	});
}