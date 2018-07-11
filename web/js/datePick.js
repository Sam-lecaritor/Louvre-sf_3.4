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
        endDate: endDate,
        startDate: now
    };
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
        $.get('./billet_test', {
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