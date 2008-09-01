// constants & globals {{{
var NORTH = 1;
var SOUTH = 2;
var EAST = 4;
var WEST = 8;

var step = 10;

var input_timeout = null;
var refresh_timeout = null;
// }}}

// {{{ init
jQuery(function () {
    place_avatar(user);
    $(window).bind('keydown.mud', handle_key_down)
             .bind('keyup.mud', handle_key_up);
    var input = document.createElement('input');
    $(input).attr('id', 'user-input')
            .attr('type', 'text')
            .appendTo('body');

    update_loop();
    game_loop();
}); // }}}

function game_loop() // {{{
{
    var avatar = $('#avatar-' + user.user);
    var oTop = avatar.attr('offsetTop');
    var oLeft = avatar.attr('offsetLeft');
    var state = avatar.attr('state');
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
    if (state & EAST)
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
    avatar.css({
        left: oLeft + 'px',
        top: oTop + 'px'
    });
    user.x = oLeft;
    user.y = oTop;

    if (input_timeout)
    {
        clearTimeout(input_timeout);
    }
    input_timeout = setTimeout(game_loop, 60);
} // }}}

function update_loop()
{
    update();
    refresh_timeout = setTimeout(update_loop, 500);
}

function place_avatar(data) // {{{
{
    var avatar = document.createElement('div');
    var bg = "transparent url(images/" + data.sprite + ") no-repeat " +
                             get_frame_x(data.state) + " 0";
    $(avatar).addClass('avatar')
             .attr('id', 'avatar-' + data.user)
             .attr('state', data.state)
             .css({
                 background: bg,
                 left: data.x + 'px',
                 top: data.y + 'px'
             })
             .appendTo('body');
    $(avatar).append(make_speech_bubble());
} // }}}

function make_speech_bubble() // {{{
{
    var t = document.createElement('div');
    $(t).addClass('t')
        .append(document.createElement('div'));
    var b = document.createElement('div');
    $(b).addClass('b')
        .append(document.createElement('div'));
    var c = document.createElement('div');
    var d = document.createElement('div');
    var p = document.createElement('p');
    $(p).addClass('text');
    $(d).append(p);
    $(c).addClass('c')
        .append(d);
    var bubble = document.createElement('div');
    $(bubble).addClass('speech-bubble')
             .append(t)
             .append(c)
             .append(b)
             .hide();
    return bubble;
} // }}}

function say(avatar, msg) // {{{
{
    var bubble = $('#avatar-' + avatar + ' .speech-bubble');
    console.log(avatar + ' says "' + msg + '" (' + (msg ? 'true' : 'false') + ')');
    if (msg)
    {
        bubble.find('.text').text(msg);
    }
    bubble.show();
    console.log(bubble);
    if (msg)
    {
        // hide the message after enough time to read it
        var delay = 3000 + (text.split(' ').length * 250);
        setTimeout(function () { bubble.hide(); }, delay);
        return;
    }

    // temporarily remove keyboard controls to allow typing
    $(window).unbind('keydown.mud')
             .unbind('keyup.mud');
    
    // enable user input field
    var ui = $('#user-input');
    ui.val('')
      .unbind('keypress');
    ui.keypress(enter_text)
      .focus()
      .attr('fresh', 'true');
} // }}}

function enter_text(e) {
    console.log(e.which);

    if ($(this).attr('fresh') == 'true' && e.which == 115)
    {
        // discard first keypress event, because it's the 's'
        $(this).attr('fresh', 'false');
        return false;
    }

    // when the user is done typing, post to server
    if (e.which == 13)
    {
        $.ajax({
            'type': 'POST',
            'url': 'http://localhost/~andy.driver/pagezero/mud-say',
            'data': {'user': user.user, 'says': $(this).val()},
        });
  
        // re-enable game keyboard controls
        $(this).blur();
        $(window).bind('keydown.mud', handle_key_down)
                 .bind('keyup.mud', handle_key_up);
        setTimeout(function() {
            $('#avatar-' + user.user + ' .speech-bubble').hide();
        }, 3000);
    }

    // update speech bubble text
    $('#avatar-' + user.user + ' .speech-bubble .text').text($(this).val() +
        String.fromCharCode(e.which));
    return true;
}

function get_frame_x(state, frame) // {{{
{
    if (!frame)
    {
        frame = 0;
    }
    return frame;
} // }}}

function update()
{
    $.ajax({
        'type': 'GET',
        'url': 'http://localhost/~andy.driver/pagezero/mud-update',
        'data': {'user': user.user, 'x': user.x, 'y': user.y},
        'dataType': 'json',
        'success': refresh
    });
}

function refresh(json)
{
    for (i in json.neighbours)
    {
        var avatar = json.neighbours[i];
        var sprite = $('#avatar-' + avatar.user);
        if (sprite.length == 0)
        {
            place_avatar(avatar);
            sprite = $('#avatar-' + avatar.user);
        }
        var oLeft = sprite.attr('offsetLeft');
        oLeft = (oLeft == avatar.x ? oLeft : avatar.x);
        var oTop = sprite.attr('offsetTop');
        oTop = (oTop == avatar.y ? oTop : avatar.y);
        sprite.animate({
            'left': oLeft + 'px',
            'top': oTop + 'px'
        }, 'slow');
    }
    var out = [];
    for (i in json.chat)
    {
        var msg = json.chat[i];
        out.push(msg.user + ': "' + msg.msg + '"');
        say(msg.user, msg.msg);
    }
    //console.log(json.last_update + ': ' + out);
}

function handle_key_down(e) // {{{
{
    state = $('#avatar-' + user.user).attr('state');
    switch (e.keyCode) {
        case 38: // up arrow
            if (!(state & SOUTH))
            {
                state |= NORTH;
            }
            break;
        case 40: // down arrow
            if (!(state & ~NORTH))
            {
                state |= SOUTH;
            }
            break;
        case 37: // left arrow
            if (!(state & ~EAST))
            {
                state |= WEST;
            }
            break;
        case 39: // right arrow
            if (!(state & ~WEST))
            {
                state |= EAST;
            }
            break;
        case 83: // 's'
            say(user.user);
            break;
        default:
            return true;
    }
    $('#avatar-' + user.user).attr('state', state);
    return false;
} // }}}

function handle_key_up(e) // {{{
{
    state = $('#avatar-' + user.user).attr('state');
    switch (e.keyCode) {
        case 38:
            if (state & NORTH)
            {
                state ^= NORTH;
            }
            break;
        case 40:
            if (state & SOUTH)
            {
                state ^= SOUTH;
            }
            break;
        case 37:
            if (state & WEST)
            {
                state ^= WEST;
            }
            break;
        case 39:
            if (state & EAST)
            {
                state ^= EAST;
            }
            break;
        default:
            return true;
    }
    $('#avatar-' + user.user).attr('state', state);
    return false;
} // }}}
