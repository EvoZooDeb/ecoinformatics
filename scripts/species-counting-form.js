var specieslist = {};

$(document).ready(function(){
    $('body').on('click','.input-counter',function(){
        $(this).val('');
    });
});

function nameUpdate(chosenSpeciesName) {
    let inputCounter = document.querySelector("#" + chosenSpeciesName);
    let inputCounter_value = inputCounter.value;
    let counter = inputCounter.getAttribute('data-counter');
    let name = inputCounter.getAttribute('data-name');
    let id = inputCounter.value.replace(" ","_");
    
    if (counter == 1) {
        inputCounter.setAttribute('data-name',inputCounter.value);
        inputCounter.setAttribute('value',`${inputCounter_value}: ${counter}`);
        inputCounter.setAttribute('id',id);
        inputCounter.value =`${inputCounter_value}: ${counter}`;

        increaseButton = inputCounter.nextElementSibling;
        reduceButton = inputCounter.previousElementSibling;
        increaseButton.onclick = () => speciesCounter(id,'plus');
        reduceButton.onclick = () => speciesCounter(id,'minus');

        delete specieslist[chosenSpeciesName];
        specieslist[id] = 1;

    } else {
        inputCounter.value =`${name}: ${counter}`;
        addOption(inputCounter_value);
    }
}
function speciesCounter(chosenSpeciesName,direction) {

    let inputCounter = document.querySelector("#" + chosenSpeciesName);
    let inputCounter_value = inputCounter.value;
    let counter = inputCounter.getAttribute('data-counter');
    let name = inputCounter.getAttribute('data-name');

    if (direction == 'plus') {
        ++counter;
        inputCounter.value = `${name}: ${counter}`;
        //inputCounter.setAttribute('value',`${name}: ${counter}`);
        inputCounter.setAttribute('data-counter',counter);
    }
    else {
        --counter;
        if (counter > 0) {
            //inputCounter.setAttribute('value',`${name}: ${counter}`);
            inputCounter.value =`${name}: ${counter}`;
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
function addOption(value=null) {

    let chosenSpeciesName;
    let chosenSpeciesNameLabel;
    if (value === null) {
        //let chosenSpecies = document.getElementById('fajok');
        chosenSpeciesName = document.querySelector(".dropdown-menu-js").value;
        chosenSpeciesNameLabel = fajok.options[fajok.selectedIndex].text;
    } else {
        chosenSpeciesNameLabel = value;
        chosenSpeciesName = chosenSpeciesNameLabel.replace(" ","_");
    }

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
    let inputCounter = document.createElement("input");
    inputCounter.setAttribute('id',chosenSpeciesName);
    inputCounter.setAttribute('data-counter',1)
    inputCounter.setAttribute('data-name',chosenSpeciesNameLabel)
    inputCounter.setAttribute('value',`${chosenSpeciesNameLabel}: 1`);
    inputCounter.setAttribute('list',`${chosenSpeciesName}-list`);
    inputCounter.className = "input-counter";
    inputCounter.onchange = () => nameUpdate(chosenSpeciesName);

    increaseButton.type = "button";
    increaseButton.className = "input-plus";
    increaseButton.setAttribute('style','width:4em');
    //increaseButton.setAttribute('data-id',chosenSpeciesName)
    increaseButton.innerHTML = " + ";
    increaseButton.onclick = () => speciesCounter(chosenSpeciesName,'plus');

    reduceButton.type = "button";
    reduceButton.className = "input-minus";
    //reduceButton.setAttribute('data-id',chosenSpeciesName)
    reduceButton.setAttribute('style','width:4em');
    reduceButton.innerHTML = "  -  ";
    reduceButton.onclick = () => speciesCounter(chosenSpeciesName,'minus');

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
