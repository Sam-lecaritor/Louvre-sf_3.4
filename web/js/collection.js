$('.billet-collection').collection({
    allow_duplicate: false,
    allow_up: false,
    allow_down: false,
    add: '<a href="#" id="addBillets" class="btn btn-default btn-add-billet margin-top-half" title="Add element"><i class="fa fa-plus-square margin-top margin-top" style=""></i>Ajouter un billet &nbsp;</a>',
    'add_at_the_end': true,

    // here is the magic!
    elements_selector: 'tr.item',
    elements_parent_selector: '%id% tbody'
});

$(document).ready(function () {
    var tailleTable = 0;
    var nbrReservationData = $('#js-nbrOptions-conf');
    var nbrReservation = nbrReservationData.data("nbroptionsConf");

    var dateconfig = $('#js-demijour-conf');
    var halfDay = dateconfig.data("demijourConf");

    if (halfDay) {
        $('#addBillets').click(function () {
            setTimeout(function () {
                $('.demiJourInput').attr({
                    "checked": "checked",
                    "disabled": "disabled"
                });
            }, 100);
        });
    }

    $('#addBillets').click(function () {
        setTimeout(function () {
            tailleTable = $('#louvrebundle_ticketscollection_billets tbody tr').length;
            if (tailleTable >= nbrReservation) {
                $('#addBillets').fadeOut();
            }
        }, 100);
    });

    $('.btn-suppr-min').click(function () {
        setTimeout(function () {
            tailleTable = $('#louvrebundle_ticketscollection_billets tbody tr').length;
            if (tailleTable <= nbrReservation) {
                $('#addBillets').fadeIn();
            }
        }, 100);
    });




});