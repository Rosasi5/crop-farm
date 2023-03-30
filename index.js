
function load() {
    populateCountyCombo();
    document.getElementById("suggestions").value = "";
}
console.log("Hello World!");
function populateCountyCombo() {
    var comboCounty = document.getElementById("comboCounty");

    var ajaxRequest = new XMLHttpRequest();
    ajaxRequest.open("GET", "query.php?counties=load", true);
    ajaxRequest.onload = function () {
        if (ajaxRequest.status = 200) {
            var counties = ajaxRequest.responseText;

            var jCounties = JSON.parse(counties);
            console.log("Counties: ", jCounties.length);
            for (var i = 0; i < jCounties.length; i++) {
                var countyOption = new Option(jCounties[i][0], jCounties[i][0].toLowerCase());
                comboCounty.appendChild(countyOption);
            };
            populateConstituencyCombo();
        }
    }
    ajaxRequest.send();
}

function populateConstituencyCombo() {
    var selectorCounty = document.getElementById("comboCounty");
    var selectorConstituency = document.getElementById("comboConstituency");
    if (selectorCounty.value != "default") {
        document.getElementById("constituencyRow").hidden = false;
    } else {
        document.getElementById("constituencyRow").hidden = true;
    }
    var selectedCounty = selectorCounty.value;
    // Send data to the server for database query again without requiring to reload the page :)
    var ajaxRequest = new XMLHttpRequest();

    if (selectedCounty != "default") {
        ajaxRequest.open("GET", "query.php?selectedCounty=" + selectedCounty, true);

        ajaxRequest.onload = function () {
            if (ajaxRequest.status == 200) {
                var constituencies = JSON.parse(ajaxRequest.responseText);
                selectorConstituency.innerHTML = "";
                // this default select option ensures that we can have no specific constituency selected at any given time
                selectorConstituency.appendChild(new Option("SELECT CONSTITUENCY", "default"));
                for (var i = 0; i < constituencies.length; i++) {
                    var constituencyOption = new Option(constituencies[i]);
                    selectorConstituency.appendChild(constituencyOption);
                }
            }
        }
        ajaxRequest.send();
    }
    validateInputs();
}

function queryDatabase() {
    var suggestionBox = document.getElementById("suggestions");
    var county = document.getElementById("comboCounty").value;
    var constituency = document.getElementById("comboConstituency").value.toLowerCase();
    if (county == "default" || constituency == "default") {
        constituency = "";
        county = "";
    }
    var rainfall = document.getElementById("rainfall").value;
    var temperature = document.getElementById("temperature").value;
    var humidity = document.getElementById("humidity").value;

    var ajaxRequest = new XMLHttpRequest();

    console.log("Sending: ", county, constituency, rainfall, temperature, humidity);
    ajaxRequest.open("GET", "query.php?county=" + county + "&constituency=" + constituency + "&rainfall=" + rainfall + "&temperature=" + temperature + "&humidity=" + humidity, true);

    ajaxRequest.onload = function () {
        console.log(ajaxRequest.responseText);
        if (ajaxRequest.responseText.length > 0) {
            console.log("PlainText Data: ", ajaxRequest.responseText);
            var suggestions = JSON.parse(ajaxRequest.responseText);
            console.log("DecodedData: ", suggestions);
            var cropsSuggested = "";
            console.log(suggestions);
            for (var i = 0; i < suggestions.length; i++) {
                console.log("Crop ", i, ":", suggestions[i]);
                cropsSuggested += suggestions[i] + "\n";
            }
            suggestionBox.value = cropsSuggested;
        }
    }
    ajaxRequest.send();
}

/**
 * This function will make sure that the values are in the correct format before feeding them to the server
 * this ensures that the server does not do more *'work' of processing inputs
 */
function validateInputs() {
    var rainfallInput = document.getElementById("rainfall");
    var temperatureInput = document.getElementById("temperature");
    var humidityInput = document.getElementById("humidity");
    var rainfallValid = false;
    var temperatureValid = false;
    var humidityValid = false;

    var numerics = /^[0-9]+$/; // regular expression to make sure that the inputs that will be allowed are numerics only
    // validate rainfall
    if (rainfallInput.value == "") {
        rainfallValid = true;
    } else {
        if (numerics.test(rainfallInput.value) && (rainfallInput.value >= 100 && rainfallInput.value <= 1500)) {
            rainfallValid = true;
        } else {
            rainfallInput.value = "";
            rainfallValid = false;
            console.log("Invalid rainfall input");
        }
    }
    // validate temperature
    if (temperatureInput.value == "") {
        temperatureValid = true;
    } else {
        // ensure that the temperature range is within ranges in database
        if (numerics.test(temperatureInput.value) && (temperatureInput.value >= 6 && temperatureInput.value <= 45)) {
            temperatureInput.onerror = "Range error";
            temperatureValid = true;
        } else {
            temperatureInput.value = "";
            temperatureValid = false;
            console.log("Invalid temperature input");
        }
    }
    // validate humidity
    if (humidityInput.value == "") {
        humidityValid = true;
    } else {
        if (numerics.test(humidityInput.value) && (humidityInput.value >= 20 && humidityInput.value <= 89)) {
            humidityValid = true;
        } else {
            humidityInput.focus();
            humidityInput.value = "";
            humidityValid = false;
            console.log("Invalid humidity input");
        }
    }
    if (rainfallValid && temperatureValid && humidityValid) {
        queryDatabase();
    }
}