document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.scanner-button').forEach(button => {
        button.addEventListener('click', function() {
            fetch('/start-reader', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    // Manipuler les données JSON récupérées
                    console.log('Scanner data:', data.data);
                    // Vérifiez le type et la valeur du champ de saisie avant de le définir
                    const searchInput = document.querySelector('.search-input');
                    console.log('Type of searchInput:', searchInput.type);
                    console.log('Current value of searchInput:', searchInput.value);
                    console.log('Setting searchInput value to:', data.data.NFT_tokenID);
                    
                    // Assurez-vous que le champ de saisie est de type number
                    if (searchInput && searchInput.type === 'number') {
                        searchInput.value = data.data.NFT_tokenID;
                    } else {
                        console.error('searchInput is not a number input');
                    }
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des données:', error);
            });
        });
    });
});
