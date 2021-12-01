const urlPieces = [location.protocol, '//', location.host, '/']
let url = urlPieces.join('');
let baseUrl = url;

function goBack(currentUrl) {

    if (currentUrl.includes("/categories")) {
        window.location.replace(baseUrl);
    }

    if (currentUrl.includes("/category/")) {
        window.location.replace(baseUrl + "categories");
    }

    if (currentUrl.includes("/meals/")) {
        window.location.replace(localStorage.getItem('categoryId'));
    }

    if (currentUrl.includes("/matches/")) {
        window.location.replace(localStorage.getItem('mealId'));
    }
}

// ----------------------- meer informatie tabs ----------------------------------

window.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll('[role="tab"]');
    const tabList = document.querySelector('[role="tablist"]');

    // Add a click event handler to each tab
    tabs.forEach(tab => {
        tab.addEventListener("click", changeTabs);
    });
});

function changeTabs(e) {
    const target = e.target;
    const parent = target.parentNode;
    const grandparent = parent.parentNode;

    // Remove all current selected tabs
    parent
        .querySelectorAll('[aria-selected="true"]')
        .forEach(t => t.setAttribute("aria-selected", false));

    // Set this tab as selected
    target.setAttribute("aria-selected", true);

    // Hide all tab panels
    grandparent
        .querySelectorAll('[role="tabpanel"]')
        .forEach(p => p.setAttribute("hidden", true));

    // Show the selected panel
    grandparent.parentNode
        .querySelector(`#${target.getAttribute("aria-controls")}`)
        .removeAttribute("hidden");
}

// ---------------------- einde meer informatie tabs -----------------------------