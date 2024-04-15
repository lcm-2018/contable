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
    let id_ejec = 0;
    let id_doc = $("#id_ctb_tipo").val();
    let id_var = $("#var_tip").val();
    // obtener el value de id_ctb_tipo
    tabla = $("#tableMvtoTesoreriaPagos").DataTable({
      dom: setdom,
      buttons: [
        {
          text: ' <span class="fas fa-plus-circle fa-lg"></span>',
          action: function (e, dt, node, config) {
            let ruta = {
              url: "lista_documentos_pag.php",
              name1: "tipo_dato",
              valor1: id_doc,
              name2: "tipo_var",
              valor2: id_var,
            };
            redireccionar2(ruta);
          },
        },
      ],
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_mvto_tesoreria.php",
        data: function (d) {
          d.id_doc = id_doc;
        },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "numero" }, { data: "fecha" }, { data: "ccnit" }, { data: "tercero" }, { data: "valor" }, { data: "botones" }],
      order: [[0, "desc"]],
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
      columns: [{ data: "cuenta" }, { data: "debito" }, { data: "credito" }, { data: "botones" }],
      order: [[0, "desc"]],
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
            $.post("form_cuenta_nueva.php", function (he) {
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
        url: "datos/listar/datos_cuentas_list.php",
        data: function (d) {
          d.id_doc = id_doc;
        },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "banco" }, { data: "tipo" }, { data: "nombre" }, { data: "numero" }, { data: "cuenta" }, { data: "estado" }, { data: "botones" }],
      order: [[0, "desc"]],
    });
    $("#tableCuentasBanco").wrap('<div class="overflow" />');
    //Fin dataTable
  });
})(jQuery);
/*========================================================================== Utilitarios ========================================*/
// Recargar a la tabla de documento contable  por acciones en el select
function cambiaListadoTesoreria(dato) {
  $(
    '<form action="lista_documentos_com.php" method="post">\n\
    <input type="hidden" name="tipo_doc" value="' +
    dato +
    '" /></form>'
  )
    .appendTo("body")
    .submit();
}
// Cargar lista de registros para obligar en contabilidad de
let CargaObligaPago = function (dato) {
  $.post("lista_causacion_obligaciones.php", {}, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-lg");
    $("#divTamModalForms").addClass("modal-xl");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
};
//--- EGRESO Tesoreria nómina
function CegresoNomina() {
  $.post("lista_causacion_registros.php", {}, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-lg");
    $("#divTamModalForms").addClass("modal-xl");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
}
function CausaCENomina(boton) {
  var cant = document.getElementById("total");
  var valor = Number(cant.value);
  var data = boton.value;
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
            let tabla = "tableMvtoTesoreriaPagos";
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

// Carga el listado de imputación presupuestal para ingresos
let cargaPresupuestoIng = function (dato) {
  let id_pto_do = id_ctb_doc.value;
  $.post("lista_causacion_presupuesto.php", { id_doc: id_pto_do }, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-lg");
    $("#divTamModalForms").addClass("modal-xl");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
};

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
let cargaListaCausaciones = function (dato) {
  // obtener el id del id_ctb_cop
  let id_cop_add = id_cop_pag.value;
  let ccnit = id_tercero.value;
  $.post("lista_causacion_listas_ter.php", { id_cop: id_cop_add, ccnit: ccnit }, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-lg");
    $("#divTamModalForms").addClass("modal-xl");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
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
function cargarListaDetallePago(id_doc) {
  let tipo_dato = $("#id_ctb_tipo").val();
  let tipo_movi = $("#var_tip").val();
  console.log(id_doc);
  $(
    '<form action="lista_documentos_pag.php" method="post"><input type="hidden" name="id_cop" value="' +
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
  let tipo_dato = $("#id_ctb_tipo").val();
  let tipo_movi = $("#var_tip").val();

  $(
    '<form action="lista_documentos_pag.php" method="post"><input type="hidden" name="id_doc" value="' +
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
  let dif = valor_dif.value;
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
let cargaRubrosPago = function (dato) {
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
let rubrosaPagar = function (doc) {
  let formDatos = new FormData(rubrosPagar);
  let id_cop = 0;
  let datos = {};
  // Genero array con datos de fromEmvio
  for (var pair of formDatos.entries()) {
    datos[pair[0]] = parseFloat(pair[1].replace(/\,/g, "", ""));
  }
  let id_crrp = id_pto_rp.value;
  let id_doc = id_ctb_doc.value;
  if (doc > 0) {
    id_cop = doc;
  } else {
    id_cop = id_cop_pag.value;
  }
  let formEnvio = new FormData();
  formEnvio.append("id_crrp", id_crrp);
  formEnvio.append("id_ctb_doc", id_doc);
  formEnvio.append("id_ctb_cop", id_cop);
  formEnvio.append("datos", JSON.stringify(datos));
  for (var pair of formEnvio.entries()) {
    console.log(pair[0] + ", " + pair[1]);
  }
  // Enviar a guardar afectación de rubros en mtto presupuesto como obligacion
  fetch("datos/registrar/registrar_mvto_pago.php", {
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

// Procesar causación de cuentas por pagar con boton guardar
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
};
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
document.addEventListener("submit", (e) => {
  e.preventDefault();
  if (e.target.id == "formAddDetallePag") {
    let boton = document.getElementById("registrarMvtoDetalle");
    let opcion = boton.textContent;
    url = "datos/registrar/registrar_mvto_contable_det.php";
    let formEnvio = new FormData(formAddDetallePag);
    if (movcta) {
      formEnvio.append("estado", 0);
    }
    for (var pair of formEnvio.entries()) {
      console.log(pair[0] + ", " + pair[1]);
      // Validación de formulario
      if (id_codigoCta.value == "") {
        document.querySelector("#codigoCta").focus();
        mjeError("La cuenta contable no puede estar vacia");
        return false;
      }
      if (id_codigoCta.value == "") {
        document.querySelector("#codigoCta").focus();
        mjeError("La cuenta contable no puede estar vacia");
        return false;
      }
    }
    let cuenta = id_codigoCta.value;
    // Verificar tipo de cuenta
    fetch("datos/consultar/consultarTipoCuenta.php", {
      method: "POST",
      body: JSON.stringify({ cuenta: cuenta }),
    })
      .then((response) => response.json())
      .then((response) => {
        console.log(response);
        let tipo = response[0].tipo;
        if (tipo == "M") {
          mjeError("La cuenta seleccionada no es de tipo detalle", "");
          codigoCta.focus();
        } else {
          // Realizar registro en la base de datos
          fetch(url, {
            method: "POST",
            body: formEnvio,
          })
            .then((response) => response.json())
            .then((response) => {
              if (response[0].value == "ok") {
                //formAddDetallePag.reset();
                codigoCta.value = "";
                id_codigoCta.value = "";
                valorDebito.value = "";
                valorCredito.value = "";
                consultarSumaDoc(id_ctb_doc.value);
                id = "tableMvtoContableDetallePag";
                reloadtable(id);
              } else {
                //formAddDetallePag.reset();
                codigoCta.value = "";
                id_codigoCta.value = "";
                valorDebito.value = "";
                valorCredito.value = "";
                id_editar.value = "";
                consultarSumaDoc(id_ctb_doc.value);
                id = "tableMvtoContableDetallePag";
                reloadtable(id);
                boton.textContent = "Agregar";
              }
            });
        }
      })
      .catch((error) => {
        console.log("Error:");
      });
  }
});
// Eliminar un registro de detalles
const eliminarRegistroDetalletes = (id) => {
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
            consultarSumaDoc(id_ctb_doc.value);
            id = "tableMvtoContableDetallePag";
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
          console.log(response);
          if (response[0].value == "ok") {
            mje("Registro eliminado");
            id = "tableMvtoTesoreriaPagos";
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

//============================================  FORMA DE PAGO ============================================*/

// Cargar lista de centros de costo para realizar la causación del valor
let cargaFormaPago = (datos) => {
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
      $.post("lista_causacion_formapago.php", { id_doc: id_docu, id_cop: id_cop, valor: valor_pago }, function (he) {
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
};

// ==========================================================  ARQUEO DE CAJA ============================================*/
// Cargar lista de arqueo de caja para registro diario
let cargaArqueoCaja = (datos) => {
  let valor_pago = 0;
  let id_docu = id_ctb_doc.value;
  let id_cop = id_cop_pag.value;
  let fecha_doc = fecha.value;
  if (id_cop == 0) {
    valor_pago = 1;
  } else {
    valor_pago = parseFloat(valor.value.replace(/\,/g, "", ""));
  }

  if (id_docu > 0) {
    if (valor_pago != "") {
      $.post("lista_causacion_arqueo.php", { id_doc: id_docu, id_cop: id_cop, valor: valor_pago, fecha: fecha_doc }, function (he) {
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
};

// Calcular copagos por cajero
const calcularCopagos2 = async (postData) => {
  try {
    const response = await fetch("datos/consultar/consulta_copagos.php", {
      method: "POST",
      body: JSON.stringify({ tercero: postData.value, fecha: fecha_arqueo.value }),
    });
    const data = await response.json();
    valor_fact.value = data[0].valor;
    console.log(data);
  } catch (error) {
    console.error(error);
  }
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
//Eliminar arqueo de caja en la tabla seg_tes_causa_arqueo
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
const generaMovimientoPag = () => {
  let id = id_ctb_doc.value;
  let id_cop = id_cop_pag.value;
  // verificar si los tres valores son iguales
  let id_crp = id_crpp.value;
  fetch("datos/registrar/registrar_mvto_libaux_auto_pag.php", {
    method: "POST",
    body: JSON.stringify({ id: id, id_crp: id_crp, id_cop: id_cop }),
  })
    .then((response) => response.json())
    .then((response) => {
      console.log(response);
      if (response[0].value == "ok") {
        mje("Movimiento generado con éxito ");
        let id = "tableMvtoContableDetallePag";
        reloadtable(id);
      } else {
        mjeError("Error al guardar");
      }
    })
    .catch((error) => {
      console.log("Error:");
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
const imprSelecTes = (nombre, id) => {
  if (id > 0) {
    cerrarDocumentoCtb(id);
  }
  var ficha = document.getElementById(nombre);
  var ventimp = window.open(" ", "popimpr");
  ventimp.document.write(ficha.innerHTML);
  ventimp.document.close();
  ventimp.print();
  ventimp.close();
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
let abrirDocumentoTes = function (dato) {
  //let doc = id_ctb_doc.value;
  fetch("datos/consultar/consultaAbrir.php", {
    method: "POST",
    body: dato,
  })
    .then((response) => response.json())
    .then((response) => {
      if (response[0].value == "ok") {
        mje("Documento abierto");
        let id = "tableMvtoTesoreriaPagos";
        reloadtable(id);
      } else {
        mjeError("Documento no abierto", "Tiene pagos asociados");
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

// Enviar datos para anulacion
const changeEstadoAnulacionCtb = async () => {
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
    const response = await fetch("datos/registrar/registrar_anulacion_ctb.php", {
      method: "POST",
      body: formEnvio,
    });
    const data = await response.json();
    console.log(data);
    if (data[0].value == "ok") {
      // realizar un case para opciones 1.2.3
      if (data[0].tipo == 1) {
        let tabla = "tableMvtoTesoreriaPagos";
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
// ========================================== Gestión de chequeras ======================================================
// Enviar datos para anulacion
const guardarChequera = async () => {
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
const eliminarChequera = (comp) => {
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
        body: JSON.stringify({ id: comp }),
      })
        .then((response) => response.json())
        .then((response) => {
          console.log(response);
          if (response[0].value == "ok") {
            let tabla = "tableFinChequeras";
            reloadtable(tabla);
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
const guardarCuentaBanco = async () => {
  let formEnvio = new FormData(formNuevaCuenta);
  for (var pair of formEnvio.entries()) {
    console.log(pair[0] + ", " + pair[1]);
    // validar que el value del campo  fecha no sea menor a fecha_min
  }
  try {
    const response = await fetch("datos/registrar/registrar_cuenta_nueva.php", {
      method: "POST",
      body: formEnvio,
    });
    const data = await response.json();
    console.log(data);
    if (data[0].value == "ok") {
      // realizar un case para opciones 1.2.3
      if (data[0].tipo == 1) {
        let tabla = "tableCuentasBanco";
        reloadtable(tabla);
        mje("Cuenta guardada con  éxito...");
      }
      if (data[0].tipo == 2) {
        let tabla = "tableCuentasBanco";
        reloadtable(tabla);
        mje("Cuenta actualizada con  éxito...");
      }
      // cerrar modal
      $("#divModalForms").modal("hide");
    }
  } catch (error) {
    console.error(error);
  }
};

// Abre formulario para edición de datos de cuenta bancaria
const editarDatosCuenta = (id) => {
  let url = "form_cuenta_nueva.php";
  $.post(url, { id: id }, function (he) {
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
          console.log(response);
          if (response[0].value == "ok") {
            let tabla = "tableCuentasBanco";
            reloadtable(tabla);
            mje("Registro eliminado");
          } else {
            mjeError("Error al eliminar", "La cuenta puede tener movimientos asociados");
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
