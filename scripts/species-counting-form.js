var specieslist = {};

function speciesCounter(chosenSpeciesName,direction) {

    let inputCounter = document.querySelector("#" + chosenSpeciesName);
    counter = inputCounter.getAttribute('data-counter');
    name = inputCounter.getAttribute('data-name');
    if (direction == 'plus') {
        ++counter;
        inputCounter.innerHTML = `${name}: ${counter}`;
        inputCounter.setAttribute('data-counter',counter);
    }
    else {
        --counter;
        if (counter > 0) {
            inputCounter.innerHTML = `${name}: ${counter}`;
            inputCounter.setAttribute('data-counter',counter);
        } else {
            if (confirm("Biztosan eltávolítod a listából a " + name + "?")) {
                inputCounter.parentElement.remove();
                delete specieslist[chosenSpeciesName];
                if (document.getElementsByClassName("counter-element").length === 0) {
                    submitButton = document.querySelector(".species-form-submit");
                    submitButton.style.display = "none";
                }
            }
        }
    }

}
function addOption() {

    let chosenSpecies = document.getElementById('fajok');
    let chosenSpeciesName = document.querySelector(".dropdown-menu-js").value;
    let chosenSpeciesNameLabel = fajok.options[fajok.selectedIndex].text;

    let n = Object.keys(specieslist).length;
    specieslist[chosenSpeciesName] = 1;

    if (Object.keys(specieslist).length == n) {
        speciesCounter(chosenSpeciesName,'plus');
        return;
    }

    let formContainer = document.querySelector(".species-counter-form");

    let newBox = document.createElement("div");
    let increaseButton = document.createElement("button");

    let reduceButton = document.createElement("button");
    let removeButton = document.createElement("button")
    let inputCounter = document.createElement("div");
    inputCounter.setAttribute('id',chosenSpeciesName);
    inputCounter.setAttribute('data-counter',1)
    inputCounter.setAttribute('data-name',chosenSpeciesNameLabel)

    increaseButton.type = "button";
    increaseButton.setAttribute('style','width:4em');
    increaseButton.innerHTML = " + ";
    increaseButton.onclick = () => speciesCounter(chosenSpeciesName,'plus');

    reduceButton.type = "button";
    reduceButton.setAttribute('style','width:4em');
    reduceButton.innerHTML = "  -  ";
    reduceButton.onclick = () => speciesCounter(chosenSpeciesName,'minus');

    inputCounter.className = "input-counter";
    inputCounter.innerHTML = `${chosenSpeciesNameLabel}: 1`;

    if (chosenSpeciesName !== "undefined") {
        newBox.className = "counter-element";
        newBox.appendChild(reduceButton);
        newBox.appendChild(inputCounter);
        newBox.appendChild(increaseButton);

        formContainer.appendChild(newBox);
    }

    if (document.getElementsByClassName("counter-element").length > 0) {
        submitButton = document.querySelector(".species-form-submit");
        submitButton.style.display = "block";
    }
    $("#fajok").val(['undefined']);
}

function showSpeciesForm() {
    let formContainer = document.querySelector(".form-container");
    formContainer.style.display = "flex";
    $("#open-form-button").hide();
}

function hideSpeciesForm() {
    let formContainer = document.querySelector(".form-container");
    formContainer.style.display = "none";
    $("#open-form-button").show();
}

function submitData() {
    let speciesCounters = document.getElementsByClassName("input-counter");
    let result = [];
    for (let i = 0; i < speciesCounters.length; i++) {
        let data = speciesCounters[i].innerHTML.split(": ");
        let name = data[0];
        let value = parseInt(data[1]);

        result.push({ name: `${name}`, value: value });
    }
    console.log(result);
    return result;
}

function resizeFormWindow() {
    let formWindow = document.querySelector(".form-container");
    let resizer = document.querySelector(".resizer");
    console.log("added listener");

    const mouseDownHandler = (e) => {
        document.addEventListener("mousemove", mouseMoveHandler);
        document.addEventListener("mouseup", mouseUpHandler);
    }

    const mouseMoveHandler = (e) => {
        let mouseY = e.clientY;
        formWindow.style.height = `calc(100% - ${mouseY}px)`;
    }

    const mouseUpHandler = () => {
        document.removeEventListener("mouseup", mouseUpHandler);
        document.removeEventListener("mousemove", mouseMoveHandler);
    }

    resizer.addEventListener("mousedown", mouseDownHandler);
}
