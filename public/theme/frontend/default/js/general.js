var baseUrl = $('meta[name="verydows-baseurl"]').attr('content') || window.location.protocol + "//" + window.location.host;

function setCookie(name, value, lifetime){
  var expires = new Date(), lifetime = lifetime || 604800;
  expires.setTime(expires.getTime() + lifetime * 1000);
  document.cookie = name+"="+escape(value)+";expires="+expires.toGMTString()+";path=/";
}

function getCookie(name){
  if(document.cookie.length > 0){
    var $start = document.cookie.indexOf(name + "=");
    if($start != -1){ 
      $start = $start + name.length + 1;
      var $end = document.cookie.indexOf(";", $start);
      if($end == -1) $end = document.cookie.length;
      return unescape(document.cookie.substring($start, $end));
    }
  }
  return null;
}

$(function(){
  var _timer = null;
  //加载顶部用户信息栏
  viewTopUserbar();
  //加载购物车信息栏
  viewCartbar();
  //分类导航
  $('#cateth').hover(
    function(){
      clearTimeout(_timer);
      _timer = setTimeout(function(){
        $('#cateth .catebar').slideDown();
        $('#cateth i').addClass('up');
      }, 300);
    },
    function(){
      clearTimeout(_timer);
      _timer = setTimeout(function(){
        $('#cateth .catebar').slideUp();
        $('#cateth i').removeClass('up');
      }, 500);
    }
  );
  $('#catebar li.haschild').hover(
    function(){$(this).addClass('hover');},
    function(){$(this).removeClass("hover");}
  );
});

function viewTopUserbar(){
  var username = getCookie('LOGINED_USER'), container = $('#top-userbar');
  if(!container.length) return;
  if(username != null){
    container.html(juicer($('#logined-userbar-tpl').html(),{username:username, avatar:getCookie('USER_AVATAR')})).hover(
      function(){
        container.find('a.m').addClass('hover').find('i.icon').hide();
        container.find('div.m').show();
      },
      function(){
        container.find('a.m').removeClass('hover').find('i.icon').show();
        container.find('div.m').hide();
      }
    );
  }else{
    container.html($('#unlogined-userbar-tpl').html());
  }
}

function viewCartbar(){
  var cookie = getCookie('CARTS'), count = 0;
  if(cookie != null){
    var cart = $.parseJSON(cookie);
    for(var i in cart) count++;
  }
  $('#cartbar b').text(count);
}

function incrCartNum(){
  var n = parseInt($('#cartbar b').text());
  $('#cartbar b').text(n + 1);
}

(function($){  
  $.fn.vdsFieldChecker = function(options){
    var defaults = {
      rules: {},
      tipsPos: '',
    }, opts = $.extend(defaults, options);
    
    var field = this, val = this.val() || '';
    
    var inRules = function(rule, right){
      switch(rule){
        case 'required': return right === (val.length > 0); break;
        case 'minlen': return right <= val.length; break;
        case 'maxlen': return right >= val.length; break;
        case 'email': return right === /^$|.+@.+\.[a-zA-Z]{2,4}$/.test(val); break;
        case 'password': return right === /^$|^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{6,31}$/.test(val); break;
        case 'equal': return right == val; break;
        case 'nonegint': return right === /^$|^(0|\+?[1-9][0-9]*)$/.test(val); break;
        case 'decimal': return right === /^$|^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$/.test(val); break;
        case 'mobile': return right === /^$|^1[3|4|5|7|8]\d{9}$/.test(val); break;
        case 'zip': return right === /^$|^[0-9]{6}$/.test(val); break;
        case 'seq': return right === /^$|^([1-9]\d|\d)$/.test(val); break;
        default: if(typeof(right) == 'boolean') return right; alert('Validation Rule "'+rule+'" is incorrect!');
      }
    }
    
    var tips = $("<span class='vdsfielderr'></span>").css({
      display: 'inline-block',
      'margin-left': '5px',
      'line-height': '12px',
      border: '1px solid #ff3366',
      'border-radius': '3px',
      background: '#ffdfdf',
    });

    if(opts.tipsPos == 'abs'){
      tips.css({'margin-left': 0, position:'absolute', left:field.offset().left + field.outerWidth() + 5, top:field.offset().top, 'z-index':9999});
    }else if(opts.tipsPos == 'fixed'){
      tips.css({'margin-left': 0, position:'fixed', left:field.offset().left + field.outerWidth() + 5, top:field.offset().top - $(document).scrollTop(), 'z-index':9999});	    
    }else if(opts.tipsPos == 'br'){
      tips.css({display:'table', margin:'8px 0 0 0', 'border-collapse':'separate'});
    }else if(opts.tipsPos == 'cr'){
      tips.css({display:'table', margin:'8px auto 0 auto', 'border-collapse':'separate'});
    }
			
    field.next('span.vdsfielderr').remove();

    var res = null;
    $.each(opts.rules, function(k, v){
      if(!inRules(k, v[0])){
        var font = $("<font></font>").css({display:'block', color:'#911', 'font-size':'12px', padding:'6px 10px'});
        font.text(v[1]).appendTo(tips);
        field.after(tips);
        res = v[1];
        return false;
      }
    });
    return res;
  }
  //表单验证
  $.fn.vdsFormChecker = function(options){
    var defaults = {
      isSubmit: true,
      beforeSubmit: function(){},
    }, opts = $.extend(defaults, options);
    
    var form = this;
    
    if(form.find('span.vdsfielderr').size() == 0){
      if(opts.isSubmit){
        if($.isFunction(opts.beforeSubmit)){
          opts.beforeSubmit.apply(this, arguments);
        }
        this.submit();
      }else{
        return true;
      }
    }
    return false;
  }

  $.vdsPopDialog = function(options){
    var defaults = {
      type: 'ok', //or err
      title: '提示',
      text: '',
      callback: function(){}
    }, opts = $.extend(defaults, options), dialog;
		
    var html = "<h2>"+opts.title+"</h2><dl><dt class='"+opts.type+"'><i class='icon'></i><font>"+opts.text+"</font></dt><dd><button type='button' class='sm-blue'>确定</button></dd></dl><a class='close'><i class='icon'></i></a>";
		
    if($('#vdspopdialog').size() == 0){
      dialog = $('<div></div>', {class:'vds-dialog', id:'vdspopdialog'}).html(html).appendTo($('body'));
      dialog.css({left:($(window).width() - dialog.outerWidth()) / 2, top:($(window).height() - dialog.outerHeight()) /2}).show();
    }else{
      dialog = $('#vdspopdialog');
      dialog.empty().html(html).show();
    }
    
    var closer = function(){
      dialog.hide();
      opts.callback();
    }

    dialog.find('.close').on('click', function(){closer()});
    dialog.find('button').on('click', function(){closer()});
  }
	
  $.fn.vdsConfirm = function(options){
    var defaults = {
      text: '',
      left: 0,
      top: 0,
      ok: function(){},
      no: function(){},
    }, opts = $.extend(defaults, options), btn = this, $confirm;

    if($('#vdspopconfirm').size() == 0){
      var html = '<p>'+opts.text+'</p><div class="mt15">';
      html += '<button type="button" class="sm-blue">确定</button><span class="sep"></span><button type="button" class="sm-gray">取消</button></div>';
      $confirm = $('<div></div>', {class:'vds-sure radius4 cut', id:'vdspopconfirm'}).html(html).appendTo($('body'));
    }else{
      $confirm = $('#vdspopconfirm');
      $confirm.find('p').text(opts.text);
    }
    $confirm.css({left:btn.offset().left - $confirm.width() + opts.left, top:btn.offset().top - btn.height() - $confirm.height() + opts.top}).show().find('button').on('click', function(){
      if($(this).index() == 0){
        opts.ok();
      }else{
        opts.no();
      }
      $confirm.hide();
    });
  }
  
  $.fn.vdsInnerLoading = function(options){
    var defaults = {
      id: 'vdsinnerloading',
      class: 'innerloading x-auto',
      text: '',
      sw: true,
    }, opts = $.extend(defaults, options), container = $(this);
    
    if(opts.sw){
      var loading = $('<div id="'+opts.id+'"></div>').addClass(opts.class);
      if(opts.text != ''){
        loading.append('<p>'+opts.text+'</p>');
      }
      container.append(loading);
    }else{
      container.find('#'+opts.id).remove();
    }	
  }
  
  $.vdsPopWaiting = function(options){
    var defaults = {text: '', sw: true}, opts = $.extend(defaults, options);
    $('div#vdspopwaiting').remove();
    if(opts.sw){
      var waiting = $('<div id="vdspopwaiting" class="popwaiting"><i></i><p>'+opts.text+'</p></div>').appendTo($('body')), width = waiting.width(), height = waiting.height();
      var left = ($(window).width() - waiting.outerWidth(true)) / 2, top = ($(window).height() - waiting.outerHeight(true)) / 2;
      $.vdsMasker(true);
      waiting.show().css({width:0, height:0}).animate({width:width, height:height, left:left, top:top}, 'fast');
    }else{
      $.vdsMasker(false);
    }
  }
  
  //隔行变色
  $.fn.vdsRowHover = function(classname){
    classname = classname || 'hover';
    this.hover(function(){$(this).addClass(classname);}, function(){$(this).removeClass(classname);}); 
  };
  
  //遮罩层
  $.vdsMasker = function(sw){
    if(sw){
      var masker = $('<div id="vdsmasker" class="vds-mask"></div>');
      masker.css({width: $(window).width(),height: Math.max($(window).height(), $('body').height())});
      $('body').append(masker);
    }else{
      $('div#vdsmasker').remove();
    }
  }
  
  //横竖居中于窗口
  $.fn.vdsMidst = function(options){
    var defaults = {position: 'fixed', gotop: 0, goleft: 0}, opts = $.extend(defaults, options);		
    this.css({
      position: opts.position, 
      top: ($(window).height() - this.outerHeight()) /2 + opts.gotop,
      left: ($(window).width() - this.outerWidth()) / 2 + opts.goleft,
    });
    return this;
  }
  
  $.fn.vdsArrVal = function(){
    var vals = [], obj = $(this);
    if(obj.size() > 0) obj.each(function(i, e){vals[i] = $(e).val()});
    return vals;
  }
	
})(jQuery);