function addOption() {

    let formContainer = document.querySelector(".species-counter-form");
    let chosenSpeciesName = document.querySelector(".dropdown-menu-js").value;

    let newBox = document.createElement("div");
    let increaseButton = document.createElement("button");
    let reduceButton = document.createElement("button");
    let removeButton = document.createElement("button")
    let inputCounter = document.createElement("div");
    let counter = 1;

    increaseButton.type = "button";
    increaseButton.innerHTML = " + ";
    increaseButton.onclick = () => { inputCounter.innerHTML = `${chosenSpeciesName}: ${++counter}` };

    reduceButton.type = "button";
    reduceButton.innerHTML = " - ";
    reduceButton.onclick = () => {
        if (counter > 0) { inputCounter.innerHTML = `${chosenSpeciesName}: ${--counter}` }
    };

    removeButton.type = "button";
    removeButton.innerHTML = " x ";
    removeButton.onclick = () => {
        if (confirm("Eltávolítja ezt a fajt?")) {
            newBox.remove()
            if (document.getElementsByClassName("counter-element").length === 0) {
                submitButton = document.querySelector(".species-form-submit");
                submitButton.style.display = "none";
            }
        }
    }

    inputCounter.className = "input-counter";
    inputCounter.innerHTML = `${chosenSpeciesName}: ${counter}`;

    if (chosenSpeciesName !== "undefined") {
        newBox.className = "counter-element";
        newBox.appendChild(reduceButton);
        newBox.appendChild(inputCounter);
        newBox.appendChild(increaseButton);
        newBox.appendChild(removeButton);

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
