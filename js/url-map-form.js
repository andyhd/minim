map_row_template =
    '<tr><td><input type="checkbox" name="sel_$i"></td>' +
    '<td><input type="text" name="pattern_$i" value="$pattern"></td>' +
    '<td><input type="text" name="view_$i" value="$view"></td>' +
    '<td><input type="text" name="action_$i" value="$action"></td>' +
    '<td><a href="$delete" class="deletelink">Delete</a></td></tr>';

jQuery(function () {
    $('#add-new-map').click(function () {
        var row = $(this).parents('tr');
        var i = row.prev().find('input[type="checkbox"]').attr('name');
        i = parseInt(i.replace('sel_', '')) + 1;
        var pattern = row.find('input[name="pattern_new"]').val();
        var view = row.find('input[name="view_new"]').val();
        var action = row.find('action[name="action_new"]').val();
        var del = '';
        var template = map_row_template.replace(/\$i/g, i)
                                       .replace(/\$pattern/g, pattern)
                                       .replace(/\$view/g, view)
                                       .replace(/\$action/g, action)
                                       .replace(/\$delete/g, del);
        console.log(template);
        var new_row = $(template);
        row.before(new_row);
        return false;
    });
});

