// constants & globals {{{
var NORTH = 1;
var SOUTH = 2;
var EAST = 4;
var WEST = 8;

var step = 10;
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

    // place neighbours
    for (i in neighbours)
    {
        place_avatar(neighbours[i]);
    }

    setInterval(game_loop, 60);
    update();
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
} // }}}

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
    if (msg)
    {
        // hide the message after enough time to read it
        var delay = 3000 + (msg.split(' ').length * 250);
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

function enter_text(e) // {{{
{
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
            "type": "GET",
            "url": "http://localhost/~andy.driver/pagezero/mud-say",
            "data": "user=" + user.user + "&says=\"" + $(this).val() + "\"",
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
} // }}}

function get_frame_x(state, frame) // {{{
{
    if (!frame)
    {
        frame = 0;
    }
    return frame;
} // }}}

function update() // {{{
{
    console.log('updating...');
    $.ajax({
        "type": "GET",
        "url": "http://localhost/~andy.driver/pagezero/mud-update",
        "data": "user=" + user.user,
        "dataType": "json",
        "success": function (o) { react(o); update(); },
        "error": function (xhr, msg, e) { console.log('error: ' + msg); update(); },
    });
} // }}}

function react(json) // {{{
{
    last_id = json.last_id;
    console.log(last_id + ': ' + json.result.length);
    for (i in json.result)
    {
        var msg = json.result[i];
        switch (msg.type)
        {
            case 0: // avatar move
                console.log('user ' + msg.user + ' moved to ' + msg.msg);
                eval('var coords = ' + msg.msg);
                var avatar = $('#avatar-' + msg.user);
                avatar.animate({
                    'left': coords[0] + 'px',
                    'top': coords[1] + 'px'
                }, 'slow');
                break;
            case 1: // avatar say
                console.log('user ' + msg.user + ' said ' + msg.msg);
                say(msg.user, eval(msg.msg));
                break;
            case 2: // avatar enter
                console.log('user ' + msg.user + ' entered');
                eval('var avatar = ' + msg.msg);
                place_avatar(avatar);
                break;
            case 3: // avatar exit
                console.log('user ' + msg.user + ' exited');
                $('#avatar-' + msg.user).remove();
                break;
        }
    }
} // }}}

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
    $.ajax({
        "type": "GET",
        "url": "http://localhost/~andy.driver/pagezero/mud-move",
        "data": {"user": user.user, "x": user.x, "y": user.y},
    });
    return false;
} // }}}
