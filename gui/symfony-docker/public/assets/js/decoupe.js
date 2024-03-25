var tagCount = 1; // Initialiser le compteur de tag
function ajouterLigne() {
    tagCount =  String(parseInt(tagCount) +1) ;
    var tableBody = document.getElementById('table-body');
    var copyRow = document.querySelector('.copy-row'); // Sélectionner la ligne à copier
    var clone = copyRow.cloneNode(true); // Cloner la ligne
    
    tableBody.appendChild(clone);

    // Modifier les attributs de la ligne clonée
    // clone.querySelector('#tag1').id = 'tag'+tagCount;
    clone.querySelector('#tag1').value = '';
    clone.querySelector('#tag1').name = 'tag'+tagCount;


    clone.querySelector('#name1').name = 'name' + tagCount;
    // alert(clone.querySelector('#name1').name);
    // alert("poids" + tagCount);
    clone.querySelector('#weight1').name = 'weight' + tagCount;
    clone.querySelector('#weight1').value = '';

}

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