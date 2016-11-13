var baseUrl = $('meta[name="verydows-baseurl"]').attr('content') || window.location.protocol + "//" + window.location.host;

function viewCartbar(){
  var cart = $.parseJSON(getCookie('CARTS')), count = 0;
  for(var i in cart) count++;
  if(count > 0){
    $('#cartbar em').text(count).show();
  }else{
    $('#cartbar em').hide();
  }
}

function preserveSpace(id){
  $('body').css('padding-bottom', $('#'+id).height() + 10);
}

$(function(){
  $('i.vinclrbtn').on('click', function(){
    $(this).prev('input').val('').focus();
  });
  $('i.vineyebtn').on('click', function(){
    if($(this).hasClass('visible')){
      $(this).removeClass('visible').siblings('input[type="text"]').attr('type', 'password').focus();
    }else{
      $(this).addClass('visible').siblings('input[type="password"]').attr('type', 'text').focus();
    }
  });
  touchTopSlide();
});

function count(obj){
  var n = 0;
  for(var i in obj) n++;
  return n;
}

function setCookie(name, value, lifetime){
  var expires = new Date(), lifetime = lifetime || 86400;
  expires.setTime(expires.getTime() + lifetime * 1000);
  document.cookie = name+"="+escape(value)+";expires="+expires.toGMTString()+";path=/";
}

function getCookie(c_name){
  if(document.cookie.length > 0){
    c_start = document.cookie.indexOf(c_name + "=");
    if(c_start != -1){ 
      c_start = c_start + c_name.length + 1;
      c_end = document.cookie.indexOf(";",c_start);
      if(c_end==-1) c_end = document.cookie.length;
      return unescape(document.cookie.substring(c_start, c_end));
    }
  }
  return null;
}

function setJsonStorage(key, obj){
  localStorage.setItem(key, JSON.stringify(obj));
}

function getJsonStorage(key){
  return JSON.parse(localStorage.getItem(key));
}

function resetCaptcha(e){
  $(e).attr('src', baseUrl + '/index.php?m=api&c=captcha&a=image&v='+Math.random());	
}

function touchTopSlide(){
  var start, move, isUp = false, obj = $('#' + ('kbilos').split('').reverse().join(''));
  if(obj.size() <= 0) obj = $('<div id="' + ('kbilos').split('').reverse().join('') + '" style="'+('neddih:wolfrevo;ccc#:roloc;retnec:ngila-txet;0:thgieh').split('').reverse().join('')+'">' + unescape('%u5C3D%u4EAB%u7535%u5546%u4E4B%u65C5') + '<br />' + ('swodyreV yb derewoP').split('').reverse().join('') + '</div>').prependTo('body');
  $('body').on('touchstart', function(e){
    start = e.touches[0].pageY
  }).on('touchmove', function(e){
    move = e.touches[0].pageY - start;
    if(move >= 10 && $(window).scrollTop() <= 0){
      obj.css({height: (move-8) + 'px', 'padding-top':'8px'});
      isUp = true;
    }
  }).on('touchend touchcancel', function() {
    if(isUp) obj.css({height: 0, padding: 0});
  });
}

(function($){
  $.vdsLoading = function(display){  
    var loading;
    if($('#vdsloadingpopup').size() > 0){
      loading = $('#vdsloadingpopup');
    }else{
      loading = $('<div class="loading-pop" id="vdsloadingpopup"><div class="mask"></div><div class="wrap"><i>Loading...</i></div></div>').appendTo($('body'));
      loading.height(Math.max($(document).height(), $(window).height()));
    }
    if(display == false){
      loading.hide();
    }else{
      loading.show();
    }
  }
	  
  $.vdsTouchScroll = function(options){
    var defaults = {
      touchOff: 30,
      onBottom: function(){},
    }, opts = $.extend(defaults, options);
    
    var win = $(window), sy, my;
    
    win.on('touchstart', function(e){sy = e.touches[0].pageY;});
    win.on('touchmove', function(e){my = e.touches[0].pageY - sy;});
    win.on('touchend', function(){
      if(Math.abs(my) > opts.touchOff){
        if(my <= 0){
          var cH = Math.max($(document).height(), $('body').height());
          if(cH >= win.height() && win.scrollTop() + win.height() >= cH) opts.onBottom();
        }
      }
      my = 0;
    });
  }
  
  $.fn.vdsTouchSlider = function(options){
    var defaults = {
      slider: 'ul',
      child: 'li',
      trigger: '.trigger',
      pernum: 1,
      touchOff: 30,
      autoplay: false,
    }, opts = $.extend(defaults, options);
    
    var obj = this,
        slider = obj.find(opts.slider),
        _item = slider.find(opts.child),
        iw = _item.width(),
        trigger = obj.find(opts.trigger),
        triggerNum = Math.ceil(_item.length / opts.pernum),
        autoTimer,
        sx,
        mx = 0;

    if(triggerNum <= 1) return false;
    
    for(var i = 0; i < triggerNum; i++){
      if(i == 0){
        trigger.append("<i class='cur'></i>");      
      }else{
        trigger.append("<i></i>");
      }
    }
    
    var autoPlay = function(){
      var triggerIndex = obj.find(opts.trigger).find('i.cur').index(), tx = 0;
        if(triggerIndex == triggerNum - 1){
          obj.find(opts.trigger).find('i').removeClass('cur').eq(0).addClass('cur');
        }else{
          tx = 0 - (iw * opts.pernum * (triggerIndex + 1));
          obj.find(opts.trigger).find('i').removeClass('cur').eq(triggerIndex + 1).addClass('cur');
        }
        slider.animate({left: tx}, 200);
    }
    
    if(opts.autoplay) autoTimer = setInterval(function(){autoPlay()}, 4000);
    
    _item.on('touchstart', function(e){
      sx = e.touches[0].pageX;
    });
    
    _item.on('touchmove', function(e){
      mx = e.touches[0].pageX - sx;
      clearInterval(autoTimer);
    });
    
    _item.on('touchend', function(e){
      if(Math.abs(mx) > opts.touchOff){
        if(opts.autoplay) autoTimer = setInterval(function(){autoPlay()}, 4000);
        var triggerIndex = obj.find(opts.trigger).find('i.cur').index();
        var tx;
      
        if(mx < 0){ //left
          if(triggerIndex == triggerNum - 1){
            tx = 0;
            obj.find(opts.trigger).find('i').removeClass('cur').eq(0).addClass('cur');
          }else{
            tx = 0 - (iw * opts.pernum * (triggerIndex + 1));
            obj.find(opts.trigger).find('i').removeClass('cur').eq(triggerIndex + 1).addClass('cur');
          }
        }else{ // right
          if(triggerIndex == 0){
            tx = 0 - iw * opts.pernum * (triggerNum - 1);
            obj.find(opts.trigger).find('i').removeClass('cur').eq(triggerNum - 1).addClass('cur');
          }
          else{
            tx = 0 - (iw * opts.pernum * (triggerIndex - 1));
            obj.find(opts.trigger).find('i').removeClass('cur').eq(triggerIndex - 1).addClass('cur');
          }
        }
        slider.animate({left: tx}, 200);
        mx = 0;
      }
    });
  }
  
  $.fn.vdsTapSwapper = function(fn1, fn2){
    var counter = 0;
    this.click(function(){
      if(counter == 0){fn1();counter = 1;}else{fn2();counter = 0;}
    });    
  }
  
  $.asynInter = function(url, dataset, success, datatype){
     $.ajax({type:'post',dataType: datatype || 'json',url:url,data:dataset,beforeSend:function(){$.vdsLoading(true)},success: function(data){$.vdsLoading(false);success.call($(this), data);}});
  }
  
  $.asynList = function(url, dataset, success){
    $.ajax({type:'post', dataType:'json', url:url, data:dataset, beforeSend:function(){$('body').append('<div class="loadbar" id="vdsbomloader"><p>正在加载</p><i class="rec-loading"></i></div>');},success:function(data){
      $('#vdsbomloader').remove();success.call($(this), data);}
    });
  }
  
  $.vdsConfirm = function(options){
    var defaults = {
      content: '',
      ok: function(){},
      cancel: function(){},
    }, opts = $.extend(defaults, options);
    
    var obj;
    
    if($('#vds-confirm').length > 0){
      obj = $('#vds-confirm');
    }else{
      var html = '<div class="mask"></div><div class="wrap"><div class="layer"><div class="con"><p></p></div><div class="bom"><a class="ok">确定</a><a class="cancel">取消</a></div></div></div>';
      obj = $('<div class="vds-dialog" id="vds-confirm"></div>').html(html).appendTo($('body'));
    }
    
    obj.find('.con p').text(opts.content);
    obj.show().find('.bom a').off('click');
    
    obj.find('.ok').on('click', function(){
      closeConfirm();
      opts.ok();
    });
    obj.find('.cancel').on('click', function(){
      closeConfirm();
      opts.cancel();
    });
    
    var closeConfirm = function(){
      obj.hide().find('.con p').text('');
    }
  }
  
  $.vdsPrompt = function(options){
    var defaults = {
      content: '提示',
      btntxt: '我知道了',
      clicked: function(){},
      delay: 0,
    }, opts = $.extend(defaults, options);
    
    var obj;
    if($('#vdsprompt').length > 0){
      obj = $('#vdsprompt');
      obj.find('div.layer').height('auto');
    }else{
      var html = '<div class="mask"></div><div class="wrap"><div class="layer"><div class="con"><p></p></div><div class="bom"><a class="close"></a></div></div></div>';
      obj = $('<div class="vds-dialog" id="vdsprompt"></div>').html(html).appendTo($('body'));
      obj.find('.close').on('click', function(){closePrompt()});
    }
    
    obj.find('.con p').text(opts.content);
    obj.find('.close').text(opts.btntxt);
    obj.show();
    
    var h = obj.find('.layer').height();
    obj.find('.layer').height(0);
    obj.find('.layer').animate({height:h}, 100);
    
    var closePrompt = function(){
      obj.hide().find('.con p').text('');
      obj.find('.close').text('');
      opts.clicked();
    }
    
    if(opts.delay > 0){
      setTimeout(function(){closePrompt()}, opts.delay);
    }
  }

  $.fn.vdsFieldChecker = function(options){
    var defaults = {
      rules: {},
      onSubmit: false,
    }, opts = $.extend(defaults, options);
    
    var field = this, val = this.val() || '';
    
    var inRules = function(rule, right){
      switch(rule){
        case 'required': return right === (val.length > 0); break;
        case 'minlen': return right <= val.length; break;
        case 'maxlen': return right >= val.length; break;
        case 'email': return right === /.+@.+\.[a-zA-Z]{2,4}$/.test(val); break;
        case 'password': return right === /^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{6,31}$/.test(val); break;
        case 'equal': return right == val; break;
        case 'nonegint': return right === /^$|^(0|\+?[1-9][0-9]*)$/.test(val); break;
        case 'decimal': return right === /^$|^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$/.test(val); break;
        case 'mobile': return right === /^$|^1[3|4|5|7|8]\d{9}$/.test(val); break;
        default: if(typeof(right) == 'boolean') return right; alert('Validation Rule "'+rule+'" is incorrect!');
      }
    }
    
    field.data('vdsfielderr', null).removeClass('vdsfielderr');
    
    var res = null;
    $.each(opts.rules, function(k, v){
      if(!inRules(k, v[0])){
        field.data('vdsfielderr', v[1]).addClass('vdsfielderr');
        res = v[1];
        return false;
      }
    });
    return res;
  }
  
  $.fn.vdsFormChecker = function(options){
    var defaults = {
      isSubmit: true,
      beforeSubmit: function(){},
    }, opts = $.extend(defaults, options), form = this;
    
    if(form.find('.vdsfielderr').size() == 0){
      if(opts.isSubmit){
        if($.isFunction(opts.beforeSubmit)){
          opts.beforeSubmit();
        }
        this.submit();
      }else{
        return true;
      }
    }else{
      $.vdsPrompt({content: form.find('.vdsfielderr').eq(0).data('vdsfielderr')});
      return false;
    }
  }
  
  function _isSet(v){
    return typeof(v) != 'undefined';
  }

})(Zepto);