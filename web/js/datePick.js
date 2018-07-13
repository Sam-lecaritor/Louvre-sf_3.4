$.get("../json/configDatepicker.json", function (data, status) {

    var configDatepicker = {
        days: data.days,
        daysShort: data.daysShort,
        daysMin: data.daysMin,
        months: data.months,
        monthsShort: data.monthsShort,
        today: "Aujourd'hui",
        monthsTitle: "Mois",
        clear: "Effacer",
        weekStart: 1,
        format: "dd-MM-yyyy"
    };

    ! function (a) {

        a.fn.datepicker.dates.fr = configDatepicker;
    }(jQuery);
});

function decrementeRange() {

    valeur = parseInt($('#nbrTicket').text()) - 1;
    if (valeur >= 1) {
        document.getElementById("louvrebundle_billetsoption_nombre").stepDown(1);
        $('#nbrTicket').text(valeur);
    }

}

function incrementeRange() {
    valeur = parseInt($('#nbrTicket').text()) + 1;
    if (valeur <= 50) {
        document.getElementById("louvrebundle_billetsoption_nombre").stepUp(1);
        $('#nbrTicket').text(valeur);
    }

}

Date.prototype.ddmmyyyy = function (n) {
    var mm = (this.getMonth() + 1).toString();
    var dd = this.getDate().toString();
    var yyyy = (this.getFullYear() + n).toString();
    return [
        dd.length === 2 ? '' + dd : '0' + dd,
        mm.length === 2 ? '' + mm : '0' + mm,
        yyyy
    ].join('/');
};

var date = new Date();
var currentYear = (date.getFullYear()).toString();
var nextYear = (date.getFullYear() + 1).toString();
var todayString = date.ddmmyyyy(0).toString();
var nextYearString = date.ddmmyyyy(1).toString();

console.log(todayString);
console.log(nextYearString);

$(document).ready(function () {

    var options = {
        language: 'fr',
        calendarWeeks: true,
        autoclose: true,
        daysOfWeekDisabled: [2],
        todayHighlight: true,
        datesDisabled: [
            "1/5/" + currentYear,
            "1/11/" + currentYear,
            "25/12/" + currentYear,
            "1/5/" + nextYear,
            "1/11/" + nextYear,
            "25/12/" + nextYear
        ],
        endDate: nextYearString,
        startDate: todayString
    }
    $('.datepicker').datepicker(options);
    $('#louvrebundle_billetsoption_nombre').change(function (value) {
        console.log($('#louvrebundle_billetsoption_nombre').val());
        $('#nbrTicket').text($('#louvrebundle_billetsoption_nombre').val());
    });
    $('.datepicker').on('changeDate', function () {

        var chooseDate = $('.datepicker').datepicker('getDate');

        console.log('valueeeeeeeeee ****** ' + $('.datepicker').val());

        chooseDate = (chooseDate.getFullYear() + "-" + (
            (chooseDate.getMonth() + 1) > 9 ? (chooseDate.getMonth() + 1) : ("0" + (
                chooseDate.getMonth() + 1))) + "-" + (
            chooseDate.getDate() > 9 ? chooseDate.getDate() : "0" + chooseDate.getDate()));

        console.log(typeof (chooseDate) + " ==> date " + chooseDate);

        $.get("/testbillets", {
            date: chooseDate
        }, function (data, status) {
            console.log("Data: " + data.placesRestantes + "\nStatus: " + status);
            console.log("date choisie: " + chooseDate + " valeur " + data.date);
            //$('#places-rest').toggleClass('hidden-alert', false);
            $('#places-rest').fadeIn(100, "linear", function () {
                $('#places-rest').text('Places disponibles pour cette date: ' + data.placesRestantes);
            });

        });
    });
});