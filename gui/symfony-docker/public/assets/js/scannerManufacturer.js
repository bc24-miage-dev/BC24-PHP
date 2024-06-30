document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.scanner-button-manufacturer').forEach(button => {
        button.addEventListener('click', function() {

            var parentDiv = button.closest('.input-div'); //Trouve la div parente de ce bouton
            // Show the loader modal
            var loaderModal = document.getElementById('loaderModal');
            loaderModal.style.display = 'flex';
            fetch('/start-reader', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
            })
            .then(response => response.json())
            .then(data => {
                // Hide the loader modal
                loaderModal.style.display = 'none';
                if (!data.error) {
                    // Manipuler les données JSON récupérées
                    console.log('Scanner data:', data.data);
                    // Vérifiez le type et la valeur du champ de saisie avant de le définir
                    const searchInput = parentDiv.querySelector('.search-input');
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
                // Hide the loader modal
                loaderModal.style.display = 'none';
                console.error('Erreur lors de la récupération des données:', error);
            });
        });
    });
});