function maskCurrency(value) {
    value = value.replace(/\D/g, ''); // Remove tudo que não é dígito
    value = (parseInt(value, 10) / 100).toFixed(2) + '';
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    return value;
}

// Exemplo de uso:
document.getElementById('inputCurrency').addEventListener('input', function (e) {
    e.target.value = maskCurrency(e.target.value);
});