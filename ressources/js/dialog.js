let dialogElement = document.querySelector("dialog");
let dialogTitreCarte = dialogElement.querySelector("#titreCarte");
let dialogDescriptifCarte = dialogElement.querySelector("#descriptifCarte");
let dialogCouleurCarte = dialogElement.querySelector("#couleurCarte");
let idCarte = dialogElement.querySelector("#idCarte")
let carte;

function openModal (element){
    dialogTitreCarte.value = element.querySelector(".titre").innerText;
    dialogDescriptifCarte.value = element.querySelector(".corps").innerText;
    dialogCouleurCarte.value = element.querySelector(".couleurCarte").innerText;
    idCarte.value = element.querySelector(".idCarte").innerText;
    carte = element;
    dialogElement.showModal();
}

async function closeModal() {
    let url = apiBase + "carte/mettreAJourCarte";

    let obj = {
        idCarte : idCarte.value,
        titre:dialogTitreCarte.value,
        descriptif:dialogDescriptifCarte.value,
        couleur:dialogCouleurCarte.value
    }

    let response = await fetch(url , {
        method: "PATCH",
        body: JSON.stringify(obj),
        headers:{
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    if (response.status !== 200) {
        return;
    }

    carte.querySelector(".titre").innerText = dialogTitreCarte.value;
    carte.querySelector(".corps").innerText = dialogDescriptifCarte.value ;
    carte.style.backgroundColor = dialogCouleurCarte.value;
    dialogElement.close();
}

dialogElement.addEventListener('click',function (e){
    if (!e.target.closest('form')) {
        if (dialogTitreCarte.value.length>1 && dialogTitreCarte.value.length<50) {
            closeModal();
        }
    }
})

async function supprimer() {
    let url = apiBase + "carte/supprimerCarte/"+idCarte.value;
    let response = await fetch(url , {
        method: "DELETE",
        headers:{
            'Accept': 'application/json'
        }
    })
    if (response.status !== 200) {
        console.log(response.status)
        return;
    }
    carte.parentElement.removeChild(carte);
    dialogElement.close();
}

async function ajouter() {
    let url = apiBase + "carte/ajouterCarte/"+"";
}

