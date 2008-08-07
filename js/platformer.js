var heights = new Array();
var b = $('#board');
var w = 32;
var slice_width = w;
var h = 32;
var slice_height = h;
var s = 60; // frame delay
var lx = 0; // The index of the first visible slice
var hero = $('#hero');
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
    var slices_in_view = 20;
    for (var i = 0; i < slices_in_view; i++) {
        drawSlice(i);
    }

    // place the hero
    hero.attr('dh', 0)
        .css('top', ((heights[0]-1)*h - hero.attr('dh')) + 'px')
        .attr('state', 0)
        .attr('jumpState', 0);
    b.append(hero);
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

function drawSlice(i) // {{{
{
    var x = i * slice_width;

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
    $('#board').append(slice);
}
// }}}

function handleKeyUp(e) { //{{{
   var keynum;
   var keychar;
   if(window.event) {
      keynum = e.keyCode;
   } else if(e.which) {
      keynum = e.which;
   }
   keychar = String.fromCharCode(keynum).toLowerCase();
   switch (keychar) {
   case 'a':
      if ((hero.state&Constants.LEFT)==Constants.LEFT) {
         hero.state -= Constants.LEFT;
      }
      break;
   case 'd':
      if ((hero.state&Constants.RIGHT)==Constants.RIGHT) {
         hero.state -= Constants.RIGHT;
      }
      break;
   case 'w':
      hero.state -= Constants.UP;
      break;

   }      
   return true;
}
//}}}

function handleKeyDown(e) { //{{{
   var keynum;
   var keychar;
   if(window.event) {
      keynum = e.keyCode;
   } else if(e.which) {
      keynum = e.which;
   }
   keychar = String.fromCharCode(keynum).toLowerCase();
   switch (keychar) {
   case 'a':
      if ((hero.state&Constants.RIGHT)!=Constants.RIGHT) {
         hero.state |= Constants.LEFT;
      }      
      break;
   case 's':
      break;
   case 'd':
      if ((hero.state&Constants.LEFT)!=Constants.LEFT) {
         hero.state |= Constants.RIGHT;
      }
      break;
   case 'e':
      break;
   case 'w':
      if ((hero.state&Constants.UP)!=Constants.UP) {
         hero.state |= Constants.UP;
         jump();
      }
      break;
   case 'q':
      break;
   }      
   return true;
}

//}}}

function loop() { //{{{
   if (running) {
      if ((hero.state&Constants.LEFT)==Constants.LEFT) {
         stepLeft();
      } else if ((hero.state&Constants.RIGHT)==Constants.RIGHT) {
         stepRight();
      }
      adjustHeroHeight();
      moveBaddies();
      //hero.innerHTML = getLandHeight(hero.offsetLeft+12);
      window.setTimeout("loop()",s);
   }
}
//}}}

function moveBaddies() { //{{{
   for (var id in baddies) {
      //console.log(id);
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
   var dhx = x-document.getElementById('slice_'+lx).offsetLeft;
   var dx = Math.floor(dhx/w);
   return document.getElementById('slice_'+(lx+dx));
}
//}}}

function getLandHeight(x) { //{{{
   var dhx = x-document.getElementById('slice_'+lx).offsetLeft;
   var dx = Math.floor(dhx/w);
   var ph = 12;
   if (lx+dx > 0) {
      ph = heights[lx+dx-1];
   }
   var base = (heights[lx+dx]-1)*h;
   if (heights[lx+dx] < ph) {
      base -= (dhx%w)-w;
   } else if (heights[lx+dx] > ph) {
      base += (dhx%w)-w;
   }
   return base;
}
//}}}

function adjustHeroHeight() { //{{{
   var sliceL = getSlice(hero.offsetLeft);
   var sliceR = getSlice(hero.offsetLeft+hero.offsetWidth);
   var base = getLandHeight(hero.offsetLeft+12);
   var blockLBottom = 0;
   var blockLTop = 0;
   var blockLLeft = 0;
   var blockLRight = 0;
   var blockRBottom = 0;
   var blockRTop = 0;
   var blockRLeft = 0;
   var blockRRight = 0;
   if (sliceL.block) {
      blockLTop = sliceL.offsetTop+sliceL.block.offsetTop;
      blockLBottom = blockLTop + sliceL.block.offsetHeight;
      blockLLeft = sliceL.offsetLeft + sliceL.block.offsetLeft;
      blockLRight = blockLLeft + sliceL.block.offsetWidth;
   }
   if (sliceR.block) {
      blockRTop = sliceR.offsetTop+sliceR.block.offsetTop;
      blockRBottom = blockRTop + sliceR.block.offsetHeight;
      blockRLeft = sliceR.offsetLeft + sliceR.block.offsetLeft;
      blockRRight = blockRLeft + sliceR.block.offsetWidth;
   }

   if (hero.offsetTop < base) {
      if (!sliceL.block || hero.offsetLeft > blockLRight || hero.offsetTop+hero.offsetHeight != blockLTop) {
         if (!sliceR.block || hero.offsetLeft+hero.offsetWidth < blockRLeft || hero.offsetTop+hero.offsetHeight != blockRTop) {
            if (hero.dh < 0 && (hero.state&Constants.UP)==Constants.UP) {
               hero.dh += 0.9;
            } else {
               hero.dh += 1.8;
            }
         }
      }
   }
   if (hero.dh > 14) {
      hero.dh = 14;
   }
   if (hero.dh == 0 && hero.offsetTop > base) {
      hero.style.top = base;
   } else if (base < hero.offsetTop + hero.dh) {
      hero.style.top = base;
      hero.dh = 0;
   } else {
      if (sliceL.block) {
         if (hero.offsetTop+hero.offsetHeight <= blockLTop && hero.offsetTop+hero.offsetHeight+hero.dh >= blockLTop) {
            hero.style.top = blockLTop - hero.offsetHeight;
            hero.dh = 0;
         } else if (hero.offsetTop>=blockLBottom && hero.offsetTop+hero.dh<=blockLBottom) {
            if (hero.offsetTop+hero.offsetHeight > sliceL.offsetTop+sliceL.block.offsetTop) {
               if (hero.offsetLeft <= blockLRight) {
                  hitBlock(sliceL.block);
                  hero.dh = sliceL.offsetTop+sliceL.block.offsetTop+sliceL.block.offsetHeight - hero.offsetTop;
               }
            }
         }
      }
      if (sliceR.block) {
         if (hero.offsetTop+hero.offsetHeight <= blockRTop && hero.offsetTop+hero.offsetHeight+hero.dh >= blockRTop) {
            if (hero.offsetLeft+hero.offsetWidth >= blockRLeft) {
               hero.style.top = blockRTop - hero.offsetHeight;
               hero.dh = 0;
            }
         } else if (hero.offsetTop>=blockRBottom && hero.offsetTop+hero.dh<=blockRBottom) {
            if (hero.offsetTop+hero.offsetHeight > blockRTop) {
               if (hero.offsetLeft+hero.offsetWidth >= blockRLeft) {
                  hitBlock(sliceR.block);
                  hero.dh = sliceR.offsetTop+sliceR.block.offsetTop+sliceR.block.offsetHeight - hero.offsetTop;
               }
            }
         }
      }
      hero.style.top = hero.offsetTop + hero.dh;
   }
}
//}}}

function stepLeft() { //{{{
   if ((hero.state&Constants.LEFT)==Constants.LEFT) {
      if (hero.offsetLeft > 8) {
         var sliceL = getSlice(hero.offsetLeft-5);
         var blockLTop = 0;
         var blockLBottom = 0;
         var blockLLeft = 0;
         var blockLRight = 0;

         if (sliceL.block) {
            blockLTop = sliceL.offsetTop+sliceL.block.offsetTop;
            blockLBottom = blockLTop + sliceL.block.offsetHeight;
            blockLLeft = sliceL.offsetLeft + sliceL.block.offsetLeft;
            blockLRight = blockLLeft + sliceL.block.offsetWidth;
         }
         if (!sliceL.block || hero.offsetTop >= blockLBottom || hero.offsetTop+hero.offsetHeight <= blockLTop) {
            hero.style.left = hero.offsetLeft - 5;
         } else if (sliceL.block) {
            if (hero.offsetTop < blockLBottom && hero.offsetTop+hero.offsetHeight > blockLTop) {
               if (hero.offsetLeft - 5 < blockLRight) {
                  hero.style.left = blockLRight;
               }
            }
         }
      }
   }
}
//}}}

function stepRight() { //{{{
   if ((hero.state&Constants.RIGHT)==Constants.RIGHT) {
      var sliceR = getSlice(hero.offsetLeft+hero.offsetWidth+5);
      var blockRTop = 0;
      var blockRBottom = 0;
      var blockRLeft = 0;
      var blockRRight = 0;

      if (sliceR.block) {
         blockRTop = sliceR.offsetTop+sliceR.block.offsetTop;
         blockRBottom = blockRTop + sliceR.block.offsetHeight;
         blockRLeft = sliceR.offsetLeft + sliceR.block.offsetLeft;
         blockRRight = blockRLeft + sliceR.block.offsetWidth;
      }
      var step = 0;
      hero.innerHTML = "";
      if (!sliceR.block || hero.offsetTop >= blockRBottom || hero.offsetTop+hero.offsetHeight <= blockRTop) {
         step = 5;
      } else if (sliceR.block) {
         if (hero.offsetTop < blockRBottom && hero.offsetTop+hero.offsetHeight > blockRTop) {
            if (hero.offsetLeft+hero.offsetWidth + 5 > blockRLeft) {
               step = blockRLeft-(hero.offsetLeft+hero.offsetWidth+1);
            } else {
               step = 5;
            }
         }
      }
      if (step != 0) {
         if (hero.offsetLeft < 350) {
            hero.style.left = hero.offsetLeft + step;
         } else {
            if (lx<980) {
               for (var i = lx;i<lx+20;i++) {
                  var slice = document.getElementById("slice_"+i);
                  if (slice.offsetLeft < -w) {
                     slice.parentNode.removeChild(slice);
                     drawSlice(document.getElementById("slice_"+(lx+19)).offsetLeft+w,lx+20);
                     lx++;
                  } else {
                     slice.style.left = slice.offsetLeft-step;
                  }
               }

               for (var id in baddies) {
                  var bad = baddies[id];
                  bad.style.left = bad.offsetLeft-step;
               }
            }
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
   //console.log(id);
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

