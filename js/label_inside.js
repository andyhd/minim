function label_inside(field) {
    var id = field.id;
    var initial = jQuery('label[for="'+id+'"]').text()
    var input = jQuery(field);
    input.focus(function () {
        input.css('color', '#000');
        if (input.val() == initial) {
            input.val('');
        }
    }).blur(function() {
        if (input.val() == '') {
            input.val(initial).css('color', '#aaa');
        }
    });
    if (input.val() == '')
    { 
        input.val(initial).css('color', '#aaa');
    }
}
jQuery(function() {
    $('#mh_search :submit').hide();
    var input = $('#mh_search_input');
    label_inside(input);
});
