document.addEventListener("DOMContentLoaded", function() {
    var script = document.createElement('script');
    script.src = 'scripts/jquery.autocomplete.multiselect.js';
    script.onload = function() {
        initializeAutocompleteMultiselect();
    };
    document.body.appendChild(script);
});

function initializeAutocompleteMultiselect() {
    const availableItems = ["Apple", "Banana", "Orange", "Mango", "Pineapple", "Grapes", "Strawberry"];
    const selectedItems = [];
    console.log("sdssdsdssdswewewewe")
    const input = document.getElementById("autocomplete-input");
    const autocompleteList = document.getElementById("autocomplete-list");
    const tagsContainer = document.getElementById("tags");

    input.addEventListener("input", function() {
        const query = this.value;
        autocompleteList.innerHTML = "";

        if (!query) return;

        availableItems.forEach(item => {
            if (item.toLowerCase().includes(query.toLowerCase())) {
                const listItem = document.createElement("div");
                listItem.innerText = item;
                listItem.addEventListener("click", () => selectItem(item));
                autocompleteList.appendChild(listItem);
            }
        });
    });

    function selectItem(item) {
        if (!selectedItems.includes(item)) {
            selectedItems.push(item);
            renderTags();
        }
        input.value = "";
        autocompleteList.innerHTML = "";
    }

    function renderTags() {
        tagsContainer.innerHTML = "";
        selectedItems.forEach(item => {
            const tag = document.createElement("div");
            tag.classList.add("multiselect-tag");
            tag.innerText = item;
            const closeBtn = document.createElement("span");
            closeBtn.innerText = "x";
            closeBtn.addEventListener("click", () => removeItem(item));
            tag.appendChild(closeBtn);
            tagsContainer.appendChild(tag);
        });
    }

    function removeItem(item) {
        const index = selectedItems.indexOf(item);
        if (index > -1) {
            selectedItems.splice(index, 1);
        }
        renderTags();
    }

    document.addEventListener("click", function(e) {
        if (!e.target.closest(".autocomplete-container")) {
            autocompleteList.innerHTML = "";
        }
    });
}