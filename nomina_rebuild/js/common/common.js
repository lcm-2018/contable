const modalForms = new bootstrap.Modal(document.getElementById('divModalForms'));
const modalFormsSize = document.getElementById('divTamModalForms');
const modalFormsBody = document.getElementById('divForms');

const HtmlPost = (url, data, modal, size) => {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data)
    })
        .then(response => response.text())
        .then(he => {
            SetModal(modal, size, he);
        })
        .catch(error => console.error('Error en la petición:', error));
    modalForms.show();
};

const SendPost = async (url, data) => {
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: data
        });
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return await response.json();
    } catch (error) {
        console.error('Error en la petición:', error);
        return { status: 'error', msg: 'Error en la petición' };
    }
};

const SetModal = (modal, size, he) => {
    if (modal === modalForms) {
        modalFormsBody.innerHTML = he;
        modalFormsSize.classList.remove('modal-xl', 'modal-lg', 'modal-sm');
        if (size !== '') {
            modalFormsSize.classList.add('modal-' + size);
        }
    }
    modal.show();
};
const ValueInput = (campo) => {
    var input = document.getElementById(campo);
    return input.value;
};

const MuestraError = (campo, mensaje) => {
    var input = document.getElementById(campo);
    input.focus();
    input.classList.add('is-invalid');
    mjeError(mensaje);
};

const LimpiaInvalid = () => {
    var inputs = document.querySelectorAll('.is-invalid');
    inputs.forEach(function (input) {
        input.classList.remove('is-invalid');
    });
};

const DelParams = {
    title: "¿Confirmar para eliminar este registro?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#00994C",
    cancelButtonColor: "#d33",
    confirmButtonText: "SI",
    cancelButtonText: "NO",
};

const Serializa = (formulario) => {
    const datos = new FormData();
    const form = document.getElementById(formulario);
    const inputs = form.querySelectorAll('input, select');

    for (const input of inputs) {
        if (input.type === 'radio' && input.checked) {
            datos.append(input.name, input.value);
        } else if (input.type !== 'radio') {
            if (input.type === 'file') {
                const file = input.files[0];
                datos.append(input.name, file);
            } else {
                datos.append(input.name, input.value);
            }
        };
    }
    return datos;
};