var heights = new Array();
var w = 32;
var slice_width = w;
var h = 32;
var slice_height = h;
var s = 60; // frame delay
var first_visible = 0; // The index of the first visible slice
var slices_in_view = 20;
var slices = new Array();
var baddies = new Array();

var playing = true;
var running = true;

var Constants = {
   UP: 1,
   LEFT: 2,
   DOWN: 4,
   RIGHT: 8
}
var BlockTypes = {
   FLAT: 0,
   RAMP_UP: 1,
   RAMP_DOWN: 2,
   HOLE: 3
}

var Colours = {
   ground1: "#060",
   ground2: "#a60",
   sky:     "#aaf",
}

function init() { //{{{

    // generate terrain
    height = 12;
    last = -1;
    num_heights = 1000;
    for (var i = 0; i < num_heights; i++) { 
        var d = Math.floor(Math.random()*4);
        if (d == 0) {
            if (height > 10 && last != 1) {
                height--;
            }
        } else if (d == 1 && last != 0) {
            if (height < 14) {
                height++;
            }
        }
        last = d;
        heights[i] = height;
    }
    for (var i = 0; i < slices_in_view; i++) {
        drawSlice(i, i * slice_width);
    }

    // place the hero
    $('#board').append($('#hero').attr('dh', 0)
                                 .css('top', ((heights[0] - 1) * h) + 'px')
                                 .attr('state', 0)
                                 .attr('jumpState', 0));
    adjustHeroHeight();

    // start the game loop
    loop();
}
// }}}

function createBaddie(x,y) { // {{{
   var c = document.createElement("div");
   c.className = 'baddie';
   c.style.top = y;
   c.style.left = x;

   c.ll = document.createElement("div");
   c.ll.className = 'foot left';

   c.lr = c.ll.cloneNode(false);
   c.lr.className = 'foot right';

   c.appendChild(c.ll);
   c.appendChild(c.lr);

   c.d = Math.round(Math.random());
   c.moved = 0;
   c.range = 50 + 20*Math.floor(Math.random()*5);
   if (c.d==0) c.d=-1;
   return c;
}
//}}}

function drawSlice(i, x) // {{{
{
    // create terrain slice
    var slice = document.createElement('div');
    $(slice).addClass('slice')
            .attr('id', 'slice_' + i)
            .css({left: x + 'px',
                  top: heights[i] * slice_height + 'px'});

    // previous height
    var prev_height = i > 0 ? heights[i - 1] : 12;

    // create the part of the slice below ground level
    var base = document.createElement("div");
    $(base).addClass("underground")
           .css({width: slice_width + "px",
                 top: slice_height + "px"});

    // if this slice is at the same height as last, draw a flat piece of ground
    if (heights[i] == prev_height || prev_height == 100)
    {
        $(base).css('height', (500 - ((heights[i] + 1) * slice_height)) + "px");

        $(slice).attr('type', BlockTypes.FLAT)
                .css({width: slice_width + 'px',
                      height: slice_height + 'px'})
                .append(base);
    }

    // terrain slopes up
    else if (heights[i] < prev_height)
    {
        // add bottom half of slope
        var ramp = document.createElement('div');
        $(ramp).css({borderTop: slice_height + "px solid " + Colours.ground1,
                     borderRight: slice_width + "px solid " + Colours.ground2,
                     top: 0,
                     left: 0,
                     position: 'absolute',
                     background: Colours.ground1});

        $(base).css('height', (500 - ((heights[i] + 2) * slice_height)) + "px");

        $(slice).attr('type', BlockTypes.RAMP_UP)
                .css({borderTop: slice_height+'px solid '+Colours.sky,
                      borderRight: slice_width+'px solid '+Colours.ground1})
                .append(ramp)
                .append(base);                    
    }

    // terrain slopes down
    else if (heights[i] > prev_height)
    {
        // add bottom half of slope
        var ramp = document.createElement('div');
        $(ramp).css({left: -slice_width + 'px',
                     top: 0,
                     position: 'absolute',
                     background: Colours.ground1,
                     borderTop: slice_height + "px solid " + Colours.ground1,
                     borderLeft: slice_width + "px solid " + Colours.ground2});

        $(base).css({height: (500 - ((prev_height + 2) * slice_height)) + "px",
                     left: -slice_width + 'px'});

        $(slice).attr('type', BlockTypes.RAMP_DOWN)
                .css({top: (prev_height * slice_height) + 'px',
                      borderTop: slice_height + 'px solid' + Colours.sky,
                      borderLeft: slice_width + 'px solid' + Colours.ground1})
                .append(ramp)
                .append(base);
    }
    slices[i] = slice;
    $('#board').append(slice);
}
// }}}

function handleKeyUp(e) { //{{{
   var keynum;
   var keychar;
   var hero = $('#hero');
   var state = hero.attr('state');
   if(window.event) {
      keynum = e.keyCode;
   } else if (e.which) {
      keynum = e.which;
   }
   keychar = String.fromCharCode(keynum).toLowerCase();
   switch (keychar) {
   case 'a':
      if ((state & Constants.LEFT) == Constants.LEFT) {
         state -= Constants.LEFT;
      }
      break;
   case 'd':
      if ((state & Constants.RIGHT) == Constants.RIGHT) {
         state -= Constants.RIGHT;
      }
      break;
   case 'w':
      state -= Constants.UP;
      break;
   }
   hero.attr('state', state);
   return true;
}
//}}}

function handleKeyDown(e) { //{{{
    var hero = $('#hero');
    var state = hero.attr('state');
    var keynum;
    var keychar;
    if (window.event) {
        keynum = e.keyCode;
    } else if (e.which) {
        keynum = e.which;
    }
    keychar = String.fromCharCode(keynum).toLowerCase();
    switch (keychar) {
    case 'a':
        if ((state & Constants.RIGHT) != Constants.RIGHT) {
            state |= Constants.LEFT;
        }      
        break;
    case 'd':
        if ((state & Constants.LEFT) != Constants.LEFT) {
            state |= Constants.RIGHT;
        }
        break;
    case 'w':
        if ((state & Constants.UP) != Constants.UP) {
            state |= Constants.UP;
            hero.attr('state', state);
            jump();
        }
        break;
   }
   hero.attr('state', state);
   return true;
}

//}}}

function loop() { //{{{
    var hero = $('#hero');
    if (running) {
        if ((hero.attr('state') & Constants.LEFT) == Constants.LEFT) {
            stepLeft();
        } else if ((hero.attr('state') & Constants.RIGHT) == Constants.RIGHT) {
            stepRight();
        }
        adjustHeroHeight();
//        moveBaddies();
        window.setTimeout("loop()",s);
    }
}
//}}}

function moveBaddies() { //{{{
   for (var id in baddies) {
      var bad = baddies[id];
      if (bad.offsetLeft < -w) {
         bad.parentNode.removeChild(bad);
         delete baddies[id];
      } else {
         bad.style.left = bad.offsetLeft + bad.d;
         bad.moved += bad.d;
         if (Math.abs(bad.moved) == bad.range || bad.moved == 0) {
            bad.d = -bad.d;
         }
         bad.style.top = getLandHeight(bad.offsetLeft+(bad.offsetWidth/2))+18;

         if (hero.offsetLeft < bad.offsetLeft+bad.offsetWidth && hero.offsetLeft+hero.offsetWidth >bad.offsetLeft) {
            if (hero.offsetTop+hero.offsetHeight > bad.offsetTop+5) {
               running = false;
               animateDeath(0);
            } else if (hero.offsetTop+hero.offsetHeight+hero.dh > bad.offsetTop) {
               baddies[id].moved = 0;
               delete baddies[id];
               hero.dh = -10;
               animateBaddy("baddy_"+id);
            }
         }
      }
   }
}
//}}}

function getSlice(x) { //{{{
    // get the terrain slice for the x coord specified
    x -= $('#slice_' + first_visible).get(0).offsetLeft;
    var slices_from_first = Math.floor(x / slice_width);
    var ret = $('#slice_' + (first_visible + slices_from_first)).get(0);
    return ret;
}
//}}}

function getLandHeight(x) { //{{{
   var dhx = x - $('#slice_' + first_visible).attr('offsetLeft');
   var dx = Math.floor(dhx / slice_width);
   var ph = 12;
   if (first_visible + dx > 0) {
      ph = heights[first_visible + dx - 1];
   }
   var base = (heights[first_visible + dx] - 1) * slice_height;
   if (heights[first_visible + dx] < ph) {
      base -= (dhx % slice_width) - slice_width;
   } else if (heights[first_visible + dx] > ph) {
      base += (dhx % slice_width) - slice_width;
   }
   return base;
}
//}}}

function adjustHeroHeight() { //{{{
    var hero = $('#hero');
    var oTop = hero.attr('offsetTop');
    var oLeft = hero.attr('offsetLeft');
    var oWidth = hero.attr('offsetWidth');
    var oHeight = hero.attr('offsetHeight');
    var dh = hero.attr('dh');
    var state = hero.attr('state');

    var base = getLandHeight(oLeft + 12);

    if (oTop < base) {
        if (dh < 0 &&
            (state & Constants.UP) == Constants.UP) {
            dh += 0.9;
        } else {
            dh += 1.8;
        }
    }
    if (dh > 14) {
        dh = 14;
    }

    var top;
    if (dh == 0 && oTop > base) {
        top = base;
    } else if (base < oTop + dh) {
        top = base;
        dh = 0;
    } else {
        top = oTop + dh;
    }

    hero.attr('dh', dh)
        .css('top', top + 'px');
}
//}}}

function stepLeft() { //{{{
    var hero = $('#hero');
    var oLeft = hero.attr('offsetLeft');

    if ((hero.attr('state') & Constants.LEFT) == Constants.LEFT) {
        if (oLeft > 8) {
            hero.css('left', (oLeft - 5) + 'px');
        }
    }
}
//}}}

function stepRight() { //{{{
    var hero = $('#hero');
    var state = hero.attr('state');
    var oLeft = hero.attr('offsetLeft');
    var oWidth = hero.attr('offsetWidth');

    if ((state & Constants.RIGHT) == Constants.RIGHT) {
        var step = 5;
        if (oLeft < 350)
        {
            hero.css('left', (oLeft + step) + 'px');
        }
        else
        {
            // if we are in the last screen, don't scroll
            if (first_visible >= 980)
            {
                return;
            }

            // scroll the terrain
            var far_slice = first_visible + slices_in_view;
            for (var i = first_visible; i < far_slice; i++)
            {
                var slice = slices[i];
                var soLeft = slice.offsetLeft;
                if (soLeft >= -slice_width)
                {
                    $(slice).css('left', (soLeft - step) + 'px');
                    continue;
                }

                $(slice).remove();
                var next_slice_x = slices[far_slice - 1].offsetLeft + slice_width - step;
                drawSlice(far_slice, next_slice_x);
                first_visible++;
            }
        }
    }
}
//}}}

function jump() { //{{{
   if ((hero.state&Constants.UP)==Constants.UP) {
      if (hero.dh == 0){
         hero.dh = -16;
      }
   }
}
//}}}

function animateBaddy(id) { //{{{
   var baddy = document.getElementById(id);
   baddy.style.top = baddy.offsetTop + 5;
   baddy.style.height = baddy.offsetHeight - 5;
   baddy.ll.style.top = baddy.ll.offsetTop - 5;
   baddy.lr.style.top = baddy.lr.offsetTop - 5;

   baddy.moved++;
   if (baddy.moved < 3) {
      window.setTimeout("animateBaddy('"+id+"');",60);
   } else {
      baddy.parentNode.removeChild(baddy);
   }
}
//}}}

/*
function animateDeath(step) { //{{{
   if (step == 0) {
      hero.style.zIndex = 1001;
      b.curtain1 = document.createElement("div");
      b.curtain1.className = 'curtain';
      b.curtain1.style.height = b.offsetHeight;

      b.curtain2 = b.curtain1.cloneNode(true);
      b.curtain2.className = 'curtain vertical';
      b.curtain2.style.width = b.offsetWidth;

      b.curtain3 = b.curtain1.cloneNode(true);
      b.curtain3.className = 'curtain';
      b.curtain3.style.left = b.offsetWidth;

      b.curtain4 = b.curtain1.cloneNode(true);
      b.curtain4.className = 'curtain vertical';
      b.curtain4.style.top = b.offsetHeight;
      b.curtain4.style.width = b.offsetWidth;

      var hcx = hero.offsetLeft + (hero.offsetWidth/2);
      var hcy = hero.offsetTop + (hero.offsetHeight/2);

      b.curtain1.d = hcx / 10;
      b.curtain2.d = hcy / 10;
      b.curtain3.d = (b.offsetWidth-hcx) / 10;
      b.curtain4.d = (b.offsetHeight-hcy) / 10;

      b.appendChild(b.curtain1);
      b.appendChild(b.curtain2);
      b.appendChild(b.curtain3);
      b.appendChild(b.curtain4);

      window.setTimeout("animateDeath("+(step+1)+")",60);
   } else if (step < 11){
      b.curtain1.style.width = b.curtain1.offsetWidth + b.curtain1.d;
      b.curtain2.style.height = b.curtain2.offsetHeight + b.curtain2.d;
      b.curtain3.style.left = b.curtain3.offsetLeft - b.curtain3.d;
      b.curtain3.style.width = b.curtain3.offsetWidth + b.curtain3.d;
      b.curtain4.style.top = b.curtain4.offsetTop - b.curtain4.d;
      b.curtain4.style.height = b.curtain4.offsetHeight + b.curtain4.d;

      hero.style.top = hero.offsetTop + 2;
      hero.style.height = hero.offsetHeight - 2;
      hero.style.left = hero.offsetLeft + 2;
      hero.style.width = hero.offsetWidth - 2;

      window.setTimeout("animateDeath("+(step+1)+")",60);
   } else {
      hero.parentNode.removeChild(hero);
      b.menu = document.createElement("div");
      b.menu.className = 'menu';
      b.appendChild(b.menu);

   }

}
//}}}
*/

function padNumber(n) {
   var text = "";
   if (n<10) {
      text = "00000";
   } else if (n < 100) {
      text = "0000";
   } else if (n < 1000) {
      text = "000";
   } else if (n < 10000) {
      text = "00";
   } else if (n < 100000) {
      text = "0";
   }
   return text+n;
}

