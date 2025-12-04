document.addEventListener('DOMContentLoaded', function () {
    const kmSaida = document.querySelector('input[name="km_saida"]');
    const kmRetorno = document.querySelector('input[name="km_retorno"]');
    const maletaCheckboxes = document.querySelectorAll('.maleta-checkbox');
    const masterCheckbox = document.querySelector('#master-check');

    [kmSaida, kmRetorno].forEach(input => {
        if (input) {
            input.addEventListener('change', function () {
                const defaultValue = parseFloat(this.getAttribute('data-default'));
                const currentValue = parseFloat(this.value);
                if (currentValue < 0) {
                    alert('O valor de KM não pode ser negativo.');
                    this.value = defaultValue;
                }
            });
        }
    });

    function updateInputState(checkbox) {
        const row = checkbox.closest('tr');
        if (!row) return;
        const inputRetorno = row.querySelector('input[type="number"]');
        if (inputRetorno) {
            inputRetorno.disabled = checkbox.checked;
            if (checkbox.checked) {
                inputRetorno.value = inputRetorno.max;
            } else {
                inputRetorno.value = 0;
            }
        }
    }

    maletaCheckboxes.forEach(input => {
        input.addEventListener('change', function () {
            const idMaleta = this.getAttribute('data-id-maleta');
            const miniTable = this.closest('tr').nextElementSibling?.querySelector('.mini-table-content');
            if (miniTable) {
                const ferramentaCheckboxes = miniTable.querySelectorAll('input[type="checkbox"][name^="retornado"]');
                ferramentaCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    updateInputState(checkbox);
                });
            }
            updateMasterCheckboxState();
        });
    });

    function updateMasterCheckboxState() {
        const allCheckboxes = document.querySelectorAll('.maleta-checkbox, .checkbox');
        const allChecked = Array.from(allCheckboxes).every(checkbox => checkbox.checked);
        masterCheckbox.checked = allChecked;
    }

    if (masterCheckbox) {
        masterCheckbox.addEventListener('change', function () {
            const allCheckboxes = document.querySelectorAll('.maleta-checkbox, .checkbox');
            allCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                updateInputState(checkbox);
                if (!this.checked) {
                    const row = checkbox.closest('tr');
                    const inputRetorno = row ? row.querySelector('input[type="number"]') : null;
                    if (inputRetorno) {
                        inputRetorno.value = 0;
                    }
                }
            });
        });
    }

    document.querySelectorAll('.checkbox, .maleta-checkbox').forEach(checkbox => {
        updateInputState(checkbox);
        checkbox.addEventListener('change', () => {
            updateInputState(checkbox);
            updateMasterCheckboxState();
        });
    });

    document.querySelectorAll('.toggle-btn').forEach(button => {
        button.addEventListener('click', () => {
            const miniTable = button.closest('tr').nextElementSibling.querySelector('.mini-table-content');
            const icon = button.querySelector('i');
            const isHidden = miniTable.style.display === 'none' || miniTable.style.display === '';
            if (isHidden) {
                miniTable.style.display = 'table';
                icon.classList.remove('fa-angle-down');
                icon.classList.add('fa-angle-up');
            } else {
                miniTable.style.display = 'none';
                icon.classList.remove('fa-angle-up');
                icon.classList.add('fa-angle-down');
            }
        });
    });

    const saveButton = document.getElementById('saveChecklist');
    const form = document.querySelector('form');
    saveButton.addEventListener('click', (event) => {
        event.preventDefault();
        if (confirm('Deseja realmente salvar o checklist?')) {
            form.submit();
        }
    });

    document.querySelectorAll('.create-occurrence').forEach(link => {
        link.addEventListener('click', (event) => {
            const itemName = link.getAttribute('data-name');
            const confirmAction = confirm(`Deseja realmente criar uma ocorrência para "${itemName}"?`);
            if (!confirmAction) {
                event.preventDefault();
            }
        });
    });


});