const species = [
    { id: -1, value: undefined, name: "Válasszon egy fajt...", onScreen: true },
    { id: 0, value: "hazimeh", name: "házi méh", onScreen: false },
    { id: 1, value: "vadmeh", name: "vad méh", onScreen: false },
    { id: 2, value: "poszmeh", name: "poszméh", onScreen: false },
    { id: 4, value: "zengolegy", name: "zengőlégy", onScreen: false },
    { id: 5, value: "legy", name: "légy", onScreen: false },
    { id: 6, value: "darazs", name: "darázs", onScreen: false },
    { id: 7, value: "fadongo", name: "fadongó", onScreen: false },
    { id: 8, value: "bogar", name: "bogár", onScreen: false },
    { id: 9, value: "pillango", name: "pillangó", onScreen: false }];

species.forEach(item => {
    let dropdownForm = document.querySelector(".dropdown-menu-js");
    let option = document.createElement("option");
    option.value = item.value;
    option.innerHTML = item.name;
    dropdownForm.appendChild(option);
});
