var NORTH = 1;
var SOUTH = 2;
var EAST = 4;
var WEST = 8;

var step = 10;

jQuery(function () {
    place_user_avatar({
        "img": "images/hero.png",
        "state": 0,
        "x": 100,
        "y": 100
    });
    $('#say-form').hide().change(function() {
        $.post('http://localhost/~andy.driver/pagezero/mud', {
            "msg": $(this).val()
        }, mud_refresh);
    });
    $('#say a').click(function () {
        $('#say-form').toggle();
    });
    $(window).keydown(handle_key_down).keyup(handle_key_up);
//    mud_update();
    game_loop();
});

function game_loop()
{
    var user = $('#user');
    var oTop = user.attr('offsetTop');
    var oLeft = user.attr('offsetLeft');
    var state = user.attr('state');
    if (state & NORTH)
    {
        if (oTop > 20)
        {
            oTop -= step;
        }
    }
    else if (state & SOUTH)
    {
        if (oTop < window.innerHeight - 20)
        {
            oTop += step;
        }
    }
    else if (state & EAST)
    {
        if (oLeft < window.innerWidth - 20)
        {
            oLeft += step;
        }
    }
    else if (state & WEST)
    {
        if (oLeft > 20)
        {
            oLeft -= step;
        }
    }
    user.css({
        left: oLeft + 'px',
        top: oTop + 'px'
    });
    setTimeout(game_loop, 60);
}

function place_user_avatar(data)
{
    var avatar = document.createElement('div');
    var bg = "transparent url(" + data.img + ") no-repeat " +
                             get_frame_x(data.state) + " 0";
    $(avatar).addClass('avatar')
             .attr('id', 'user')
             .attr('state', data.state)
             .css({
                 background: bg,
                 left: data.x + 'px',
                 top: data.y + 'px'
             })
             .appendTo('body');
}

function get_frame_x(state, frame)
{
    if (!frame)
    {
        frame = 0;
    }
    return frame;
}

function mud_update()
{
    jQuery.get('http://localhost/~andy.driver/pagezero/mud', {},
        mud_refresh);
}

function mud_refresh(json)
{
    $('#output').val(json);
}

function speech_bubble(avatar, text)
{
    if (!avatar || !text)
    {
        return FALSE;
    }
    if (!avatar_in_view(avatar))
    {
        return FALSE;
    }
    avatar.speech_bubble.text.val(text);
    avatar.speech_bubble.show();
}

function avatar_in_view(avatar, text)
{
    return TRUE;
}

function handle_key_down(e)
{
    state = $('#user').attr('state');
    key = String.fromCharCode(e.keyCode).toLowerCase();
    switch (key) {
        case 'q':
            if (!(state & SOUTH))
            {
                state |= NORTH;
            }
            break;
        case 'a':
            if (!(state & ~NORTH))
            {
                state |= SOUTH;
            }
            break;
        case 'o':
            if (!(state & ~EAST))
            {
                state |= WEST;
            }
            break;
        case 'p':
            if (!(state & ~WEST))
            {
                state |= EAST;
            }
    }
    $('#user').attr('state', state);
    return true;
}

function handle_key_up(e)
{
    state = $('#user').attr('state');
    key = String.fromCharCode(e.keyCode).toLowerCase();
    switch (key) {
        case 'q':
            if (state & NORTH)
            {
                state ^= NORTH;
            }
            break;
        case 'a':
            if (state & SOUTH)
            {
                state ^= SOUTH;
            }
            break;
        case 'o':
            if (state & WEST)
            {
                state ^= WEST;
            }
            break;
        case 'p':
            if (state & EAST)
            {
                state ^= EAST;
            }
    }
    $('#user').attr('state', state);
    return true;
}
