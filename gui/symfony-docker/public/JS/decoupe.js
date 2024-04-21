var tagCount = 1; // Initialiser le compteur de tag
function ajouterLigne() {
    tagCount =  String(parseInt(tagCount) +1) ;
    var tableBody = document.getElementById('table-body');
    var copyRow = document.querySelector('.copy-row'); // Sélectionner la ligne à copier
    var clone = copyRow.cloneNode(true); // Cloner la ligne
    
    tableBody.appendChild(clone);
    clone.querySelector('#tag1').value = '';
    clone.querySelector('#tag1').name = "list["+tagCount+"][NFC]";
    clone.querySelector('#name1').name = "list["+tagCount+"][name]";
    clone.querySelector('#weight1').name = "list["+tagCount+"][weight]";
    clone.querySelector('#weight1').value = '';

}

function supprimerLigne() {
    if (tagCount > 1) {
        var tableBody = document.getElementById('table-body');
        var lastRow = tableBody.lastElementChild;
        tableBody.removeChild(lastRow);
        tagCount-- ;
    }
}