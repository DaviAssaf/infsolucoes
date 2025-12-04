function confirmDelete(event) {
    event.preventDefault(); // Impede o envio imediato do formulário

    // Obter o formulário pai do botão clicado
    const form = event.target.closest('form');
    if (!form) return;

    // Solicitar o motivo da exclusão
    const motivo = prompt('Por favor, informe o motivo da exclusão:', '');
    if (motivo === null || motivo.trim() === '') {
        alert('Motivo é obrigatório para prosseguir com a exclusão.');
        return;
    }

    // Criar campo oculto para o motivo
    let motivoInput = form.querySelector('input[name="motivo_exclusao"]');
    if (!motivoInput) {
        motivoInput = document.createElement('input');
        motivoInput.type = 'hidden';
        motivoInput.name = 'motivo_exclusao';
        form.appendChild(motivoInput);
    }
    motivoInput.value = motivo.trim();

    // Enviar o formulário
    form.submit();
}

