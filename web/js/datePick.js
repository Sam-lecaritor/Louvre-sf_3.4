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

var rangeOn = true;
var rangeMax = 50;

function decrementeRange() {

    valeur = parseInt($('#nbrTicket').text()) - 1;
    if (valeur >= 1 && rangeOn) {
        document.getElementById("louvrebundle_billetsoption_nombre").stepDown(1);
        $('#nbrTicket').text(valeur);
    }

}

function incrementeRange() {
    valeur = parseInt($('#nbrTicket').text()) + 1;
    if (valeur <= rangeMax && rangeOn) {
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



$(document).ready(function () {
    var dateconfig = $('#js-date-conf');
    var fullDays = dateconfig.data("dateConf");
    fullDays = fullDays.split(",");


    var datesDisabled = [
        "01/05/" + currentYear,
        "01/11/" + currentYear,
        "25/12/" + currentYear,
        "01/05/" + nextYear,
        "01/11/" + nextYear,
        "25/12/" + nextYear
    ].concat(fullDays);


    var options = {
        language: 'fr',
        calendarWeeks: true,
        autoclose: true,
        daysOfWeekDisabled: [2],
        todayHighlight: true,
        datesDisabled: datesDisabled,
        endDate: nextYearString,
        startDate: todayString
    }
    $('.datepicker').datepicker(options);

    $('#louvrebundle_billetsoption_nombre').change(function (value) {

        $('#nbrTicket').text($('#louvrebundle_billetsoption_nombre').val());
    });
    $('.datepicker').on('changeDate', function () {

        var chooseDate = $('.datepicker').datepicker('getDate');



        chooseDate = (chooseDate.getFullYear() + "-" + (
            (chooseDate.getMonth() + 1) > 9 ? (chooseDate.getMonth() + 1) : ("0" + (
                chooseDate.getMonth() + 1))) + "-" + (
            chooseDate.getDate() > 9 ? chooseDate.getDate() : "0" + chooseDate.getDate()));



        $.get("/testbillets", {
            date: chooseDate
        }, function (data, status) {

            $('#places-rest').fadeIn(100, "linear", function () {
                $('#places-rest').text('Places disponibles pour cette date: ' + data.placesRestantes);

                if (data.placesRestantes < 50 && data.placesRestantes > 0) {
                    rangeMax = data.placesRestantes;
                    rangeOn = true;
                    $('#places-rest').addClass("alert-danger");
                    $('#places-rest').removeClass("alert-success");
                    $('#louvrebundle_billetsoption_Valider').fadeIn(100);
                    $('#louvrebundle_billetsoption_nombre').attr('min', 1);
                    $('#louvrebundle_billetsoption_nombre').attr('max', rangeMax);

                } else if (data.placesRestantes <= 0) {
                    $('#places-rest').addClass("alert-danger");
                    $('#places-rest').removeClass("alert-success");
                    $('#louvrebundle_billetsoption_Valider').fadeOut(100);
                    $('#louvrebundle_billetsoption_nombre').attr('max', 0);
                    rangeMax = 0;
                    rangeOn = false;
                    $('#nbrTicket').text(0);
                    $('#louvrebundle_billetsoption_nombre').attr('max', 0);
                } else {
                    $('#places-rest').addClass("alert-success");
                    $('#places-rest').removeClass("alert-danger");
                    $('#louvrebundle_billetsoption_Valider').fadeIn(100);
                    $('#louvrebundle_billetsoption_nombre').attr('min', 1);
                    $('#louvrebundle_billetsoption_nombre').attr('max', 50);
                    rangeMax = 50;
                    rangeOn = true;

                }

            });

        });
    });
});