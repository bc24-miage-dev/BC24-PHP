
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionne tous les boutons avec la classe 'my-button'
    var buttons = document.querySelectorAll('.writer'); //fait une liste de tout les boutons avec la classe writer
    buttons.forEach(function(button) {
        button.addEventListener('click', async function() { //Quand tu cliques sur un bouton de la liste

            var parentDiv = button.closest('.input-div'); //Trouve la div parente de ce bouton
            var buttons2 = parentDiv.querySelectorAll('.writer-modal'); //trouve le bouton avec la classe writer-modal(bouton dans le modal)
            var loaderModal = parentDiv.querySelector('.loader-modal');
            loaderModal.style.display = 'flex';
            var loaderCircle = parentDiv.querySelector('.loader');
            loaderCircle.style.display = 'none';
            buttons2.forEach(function(button2) {
                button2.addEventListener('click', async function() {
                    loaderCircle.style.display = 'flex';
                    var hiddenInput = parentDiv.querySelector('.hidden-input');
                    hiddenInput.style.display = "none";
                    
                    var NFC = hiddenInput.value;
                    var paragraph = parentDiv.querySelector('.paragraph-contener').textContent = "En cours d'écriture..."
                    try {
                        // Use await to fetch data
                        let response = await fetch('http://127.0.0.1:8000/writeNFC/' + NFC);
                        loaderModal.style.display = 'none';
                        parentDiv.querySelector('.paragraph-contener').textContent = 'Présentez un tag NFC à proximité du scanner, puis cliquez sur le bouton "Ecriture" pour y associer le nouveau NFT crée';
                        if (response.status == 200) {
                            button.style.display = 'none';
                            document.getElementById('success-modal').style.display = 'flex';
                            var buttonSuccess = document.getElementById('success');
                            buttonSuccess.addEventListener('click', function() {
                                document.getElementById('success-modal').style.display = 'none';
                            });

                        }
                        else{
                            document.getElementById('failure-modal').style.display = 'flex';
                            var buttonfailure = document.getElementById('failure');
                            buttonfailure.addEventListener('click', function() {
                                document.getElementById('failure-modal').style.display = 'none';
                            });
                        }
                        

                    } catch (error) {
                        console.error('Error message:', error.message);
                        console.error('Error stack:', error.stack);

                        // Log the full response for debugging
                        console.error('Full error response:', error);

                        // Hide the loader modal in case of an error
                        loaderModal.style.display = 'none';
                    }
                });
            });
        });
    });
});