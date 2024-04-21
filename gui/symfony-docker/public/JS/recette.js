var tagCount = 1; // Initialiser le compteur de tag
function ajouterLigneChoixIngredient() {
    tagCount =  String(parseInt(tagCount) +1) ;
    var tableBody = document.getElementById('table-body');
    var copyRow = document.querySelector('.copy-row'); // Sélectionner la ligne à copier
    var clone = copyRow.cloneNode(true); // Cloner la ligne
    
    tableBody.appendChild(clone);
    
    clone.querySelector('#ingredient').name = "list["+tagCount+"][ingredient]";
    clone.querySelector('#quantity').name = "list["+tagCount+"][quantity]";
    clone.querySelector('#quantity').value = "";

}

function supprimerLigneChoixIngredient() {
    if (tagCount > 1) {
        var tableBody = document.getElementById('table-body');
        var lastRow = tableBody.lastElementChild;
        tableBody.removeChild(lastRow);
        tagCount-- ;
    }
}