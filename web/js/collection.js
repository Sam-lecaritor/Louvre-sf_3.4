$('.billet-collection').collection({
    allow_duplicate: false,
    allow_up: false,
    allow_down: false,
    add: '<a href="#" class="btn btn-default btn-add-billet margin-top-half" title="Add element"><i class="fa fa-plus-square margin-top margin-top" style=""></i>Ajouter un billet &nbsp;</a>',
    'add_at_the_end': true,

    // here is the magic!
    elements_selector: 'tr.item',
    elements_parent_selector: '%id% tbody'
});