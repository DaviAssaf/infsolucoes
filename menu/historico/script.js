document.addEventListener('DOMContentLoaded', function () {
    window.historicoDetalhes = function (id) {
        // Cria o overlay do popup
        const overlay = document.createElement('div');
        overlay.className = 'popup-overlay';

        // Cria o container do popup
        const popup = document.createElement('div');
        popup.className = 'popup-container';
        popup.innerHTML = 'Carregando...';

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // Busca os detalhes via AJAX
        fetch(`detalhes.php?id=${encodeURIComponent(id)}`)
            .then(response => response.text())
            .then(html => {
                popup.innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao carregar detalhes:', error);
                popup.innerHTML = 'Erro ao carregar detalhes.';
            });
    };
});