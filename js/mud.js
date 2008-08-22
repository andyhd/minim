var NORTH = 1;
var SOUTH = 2;
var EAST = 4;
var WEST = 8;

var step = 10;
var user_id = 1;

jQuery(function () {
    place_avatar({
        "id": user_id,
        "sprite": "hero.png",
        "state": 0,
        "x": 100,
        "y": 200,
    });
    $(window).bind('keydown.mud', handle_key_down)
             .bind('keyup.mud', handle_key_up);
    var input = document.createElement('input');
    $(input).attr('id', 'user-input')
            .attr('type', 'text')
            .appendTo('body');

//    mud_update();
    game_loop();
});

function game_loop()
{
    var user = $('#avatar-' + user_id);
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

function place_avatar(data)
{
    var avatar = document.createElement('div');
    var bg = "transparent url(images/" + data.sprite + ") no-repeat " +
                             get_frame_x(data.state) + " 0";
    $(avatar).addClass('avatar')
             .attr('id', 'avatar-' + data.id)
             .attr('state', data.state)
             .css({
                 background: bg,
                 left: data.x + 'px',
                 top: data.y + 'px'
             })
             .appendTo('body');
    $(avatar).append(make_speech_bubble());
}

function make_speech_bubble()
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
}

function say(user, text)
{
    var bubble = user.find('.speech-bubble');
    bubble.find('.text').text(text);
    bubble.show();
    var delay = 3000 + (text.split(' ').length * 250);
    setTimeout(function () { bubble.hide(); }, delay);
}

function enter_text()
{
    var bubble = $('#avatar-' + user_id + ' .speech-bubble');
    var text = bubble.find('.text');
    text.text('');
    bubble.show();
    $(window).unbind('keydown.mud')
             .unbind('keyup.mud');
    var first = true;
    var ui = $('#user-input');
    ui.val('')
      .keypress(function (e) {
          if (first)
          {
            // discard first keypress event, because it's the 's'
            first = false;
            return true;
          }
          if (e.keyCode == 13)
          {
            $.ajax({
                'type': 'POST',
                'url': 'http://localhost/~andy.driver/pagezero/mud',
                'data': {'user': 1, 'says': $(this).val()},
                'dataType': 'json',
                'success': refresh
            });
            $(this).blur();
            $(window).bind('keydown.mud', handle_key_down)
                     .bind('keyup.mud', handle_key_up);
            setTimeout(function() { bubble.hide(); }, 3000);
          }
          text.text($(this).val() + String.fromCharCode(e.which));
          return true;
      })
      .focus();
}

function get_frame_x(state, frame)
{
    if (!frame)
    {
        frame = 0;
    }
    return frame;
}

function update()
{
    jQuery.get('http://localhost/~andy.driver/pagezero/mud', {},
        refresh);
}

function refresh(json)
{
    for (i in json.neighbours)
    {
        var avatar = json.neighbours[i];
        var sprite = $('#avatar-' + avatar.id);
        if (sprite.length == 0)
        {
            place_avatar(avatar);
            sprite = $('#avatar-' + avatar.id);
        }
        if (avatar.says)
        {
            say(sprite, avatar.says);
        }
    }
}

function handle_key_down(e)
{
    state = $('#avatar-' + user_id).attr('state');
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
            enter_text();
            break;
        default:
            return true;
    }
    $('#avatar-' + user_id).attr('state', state);
    return false;
}

function handle_key_up(e)
{
    state = $('#avatar-' + user_id).attr('state');
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
    $('#avatar-' + user_id).attr('state', state);
    return false;
}
