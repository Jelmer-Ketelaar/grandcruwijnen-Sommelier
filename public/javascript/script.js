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