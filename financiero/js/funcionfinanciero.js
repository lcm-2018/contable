export default class Elimina {
  constructor() {
    // Propiedades de la clase
  }

  eliminar() {
    return Swal.fire({
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
        return "eliminar";
      } else {
        return "cancelar";
      }
    });
  }
}
