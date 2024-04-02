let objectByName = new Map();
const registeredEffects = new Set();

function applyAndRegister(effect) {
    effect();
    registeredEffects.add(effect);
}

function reactive(passiveObject, name) {
    //objectByName.set(name, passiveObject);
    //return passiveObject;
    const handler = {
        set(target, key, value){
            target[key] = value;
            trigger();
            return true;
        }
    }
    let reactiveObjet = new Proxy(passiveObject , handler);
    objectByName.set(name,reactiveObjet);
}

function startReactiveDom() {
    for (let elementClickable of document.querySelectorAll("[data-onclick]")) {
        const [nomObjet, methode, argument] = elementClickable.dataset.onclick.split(/[.()]+/);
        elementClickable.addEventListener('click', (event) => {
            const objet = objectByName.get(nomObjet);
            objet[methode](argument);
        })
    }

    /*for (let elementTextFun of document.querySelectorAll("[data-textfun]")){
      const [nomObjet, methode, argument] = elementTextFun.dataset.textfun  .split(/[.()]+/);
      const objet = objectByName.get(nomObjet);
      elementTextFun.textContent = objet[methode](argument);
    }*/

    for (let rel of document.querySelectorAll("[data-textfun]")) {
        const [obj, fun, arg] = rel.dataset.textfun.split(/[.()]+/);
        applyAndRegister(() => {
            rel.textContent = objectByName.get(obj)[fun](arg)
        });
    }

    for (let rel of document.querySelectorAll("[data-textvar]")) {
        const [obj, prop] = rel.dataset.textvar.split('.');
        applyAndRegister(() => {
            rel.textContent = objectByName.get(obj)[prop]
        });
    }

}

function trigger () {
    for (let effect of registeredEffects) effect();
}

window.trigger=trigger;

export {applyAndRegister, reactive, startReactiveDom};
