var baseUrl = 'http://127.0.0.1:8000/';

function goBack(currentUrl) {

    if(currentUrl.includes("/categories")){
        window.location.replace(baseUrl);
    }

    if(currentUrl.includes("/category/")){
        window.location.replace(baseUrl+"categories");
    }
    
    if(currentUrl.includes("/meals/")){
        window.location.replace(localStorage.getItem('categoryId'));
    }

    if(currentUrl.includes("/matches/")){
        window.location.replace(localStorage.getItem('mealId'));
    }
}

// ----------------------- price filter -------------------

$(function () {
    $('#producten').slider({
        range: true,
        min: 0,
        max: 5000,
        values: [0, 5000],
        create: function() {
            $("#amount").val("€0 - €5000");
        },
        slide: function (event, ui) {
            $("#amount").val("€" + ui.values[0] + " - €" + ui.values[1]);
            var mi = ui.values[0];
            var mx = ui.values[1];
            filterSystem(mi, mx);
        }
    })
});

function filterSystem(minPrice, maxPrice) {
    $("#producten").hide().filter(function () {
        var price = parseInt($(this).data("price"), 10);
        return price >= minPrice && price <= maxPrice;
    }).show();
}

//   --------------------- end price filter -----------------