const species = [
    { id: -1, value: undefined, name: "Válasszon egy fajt...", onScreen: true },
    { id: 0, value: "parduc", name: "Párduc", onScreen: false },
    { id: 1, value: "oroszlan", name: "Oroszlán", onScreen: false },
    { id: 2, value: "gorilla", name: "Gorilla", onScreen: false },
    { id: 3, value: "makako", name: "Makákó", onScreen: false }];

species.forEach(item => {
    let dropdownForm = document.querySelector(".dropdown-menu-js");
    let option = document.createElement("option");
    option.value = item.value;
    option.innerHTML = item.name;
    dropdownForm.appendChild(option);
});