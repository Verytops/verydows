var baseUrl = $('meta[name="verydows-baseurl"]').attr('content') || window.location.protocol + "//" + window.location.host;

//格式化Unix时间戳
function formatTimestamp(time, format) {
  var d = new Date(parseInt(time) * 1000), month = d.getMonth() + 1, day = d.getDate(), hour = d.getHours(), minute = d.getMinutes(), second = d.getSeconds();
  format = format.replace(/y/, d.getFullYear());
  if(month < 10) month = '0' + month;
  format = format.replace(/m/, month);
  if(day < 10) day = '0' + day;
  format = format.replace(/d/, day);
  if(hour < 10) hour = '0' + hour;
  format = format.replace(/h/, hour);
  if(minute < 10) minute = '0' + minute;
  format = format.replace(/i/, minute);
  if(second < 10) second = '0' + second;
  format = format.replace(/s/, second);
  return format;
}

//随机字符串
function random_chars(length){
  var words = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@%#*_=',
  m = words.length,
  chars = '';
  length = length || 20;
  for (i = 0;i < length;i++) chars += words.charAt(Math.floor(Math.random()*m));
  return chars;
}

(function($){
  //字段验证
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
        case 'email': return right === /.+@.+\.[a-zA-Z]{2,4}$/.test(val); break;
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
      tips.css({
        'margin-left': 0,
        position: 'absolute',
        left: field.offset().left + field.outerWidth() + 5,
        top: field.offset().top,
        'z-index': 999,
      });
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
  
  //列表请求
  $.asynList = function(url, dataset, success){
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: url,
      data: dataset,
      beforeSend: function(){$.vdsLoadingBar(true)},
      success: function(data){$.vdsLoadingBar(false);$('#rows').empty();success.call($(this), data);},
      error: function(){ 
        $.vdsLoadingBar(false);
        $('body').vdsAlert({msg:'处理请求时发生错误'});
      }
    });
  }
  
  //进度条窗口
  $.vdsLoadingBar = function(sw){
    if(sw){
      var loading = $('<div id="vdsloadingbar" class="loading absol"></div>');
      loading.css({'box-shadow':'0 0 8px #888'});
      loading.appendTo($('body')).vdsMidst();
      $.vdsMasker(true);
    }else{
      $('div#vdsloadingbar').remove();
      $.vdsMasker(false);
    }
  }
  
  //遮罩层
  $.vdsMasker = function(sw){
    if(sw){
      var masker = $('<div id="vdsmasker" class="masker"></div>');
      masker.css({width: $(window).width(),height: Math.max($(window).height(), $('body').height())});
      $('body').append(masker);
    }else{
      $('div#vdsmasker').remove();
    }
  }
	
  //横竖居中于窗口
  $.fn.vdsMidst = function(options){
    var defaults = {   
      position: 'fixed', gotop: 0, goleft: 0
    }, opts = $.extend(defaults, options);
		
    this.css({
      position: opts.position, 
      top: ($(window).height() - this.outerHeight()) /2 + opts.gotop,
      left: ($(window).width() - this.outerWidth()) / 2 + opts.goleft,
    });
    return this;
  }
	
  //提示窗口
  $.fn.vdsAlert = function(options){
    var defaults = {    
      msg: null,
      time: 3,
    }, opts = $.extend(defaults, options);
		
    opts.time = opts.time * 1000;
		
    this.remove('#vds-alert');
    $("<div id='vds-alert'></div>").html(opts.msg).appendTo(this).css({ 
      position: 'absolute',
      width: 300,
      'text-align': 'center',
      top: $(document).scrollTop() + 100,
      left: ($(window).width() - 300) / 2,
      color: '#CC3300',
      'font-size': '14px',
      padding: '30px 20px',
      'line-height': '150%',
      border: '3px solid #ffcc33',
      background: '#fff',
      'box-shadow': '2px 2px 2px #ccc',
      'z-index': 9999
    }).delay(opts.time).fadeOut(1000);
  }
	
  //确认窗口
  $.fn.vdsConfirm = function(options){
    var defaults = {text: '', left: 0, top: 0, confirmed: function(){}}, opts = $.extend(defaults, options), btn = this, obj;

    if($('#vds-confirm').size() == 0){
      var html = "<p class='pad5'>"+opts.text+"</p><div class='mt10'><button type='button' class='ubtn sm btn'>确定</button><span class='sep10'></span><button type='button' class='fbtn sm btn'>取消</button></div>";
      obj = $('<div></div>', {'class':'vds-confirm cut', 'id':'vds-confirm'}).html(html).appendTo($('body'));
    }
    else{
      obj = $('#vds-confirm');
      obj.find('p').html(opts.text);
    }
		
    obj.css({ 
      left: btn.offset().left - obj.width() + opts.left,
      top: btn.offset().top - btn.height() - obj.height() + opts.top,
    }).show().find('button').on('click', function(){
      if($(this).index() == 0) opts.confirmed();
      obj.hide();
    });
  }

  //弹出展示媒体文件窗口
  $.fn.vdsPopMedia = function(options){
    var defaults = {type: 'image', src: null}, opts = $.extend(defaults, options), 
        html = "<a class='close'></a><div class='media'></div>",
        media,
        popup;
	
    if($('#vds-pop-media').size() == 0){
      popup = $('<div></div>', {'class':'pop-media', 'id':'vds-pop-media'}).html(html).appendTo($('body'));
    }else{
      popup = $('#vds-pop-media');
    }
    switch(opts.type){
      case 'image':
        media = $('<img />', {'src':opts.src,'border':0});
      break;
      case 'flash':
        media = $('<embed></embed>', {
          'src':opts.src,
          'quality':'high',
          'pluginspage':'http://www.macromedia.com/go/getflashplayer',
          'type':application/x-shockwave-flash
        });
      break;
      default: return false;
    }
    popup.hide().find('div.media').empty().append(media);
    media.load(function(){
      popup.css({ 
        left: ($(window).width() - popup.width()) / 2,
        top: ($(window).height() - popup.height()) /2,
      }).show();
    });
		
    //关闭
    popup.find('a.close').on('click', function(){
      $(this).closest('#vds-pop-media').hide();
    });
  }
	
  //行变换class
  $.fn.vdsRowHover = function(cls){
    cls = cls || 'hover';
    this.hover(function(){$(this).addClass(cls);}, function(){$(this).removeClass(cls);}); 
  }
	
  //选项卡切换
  $.fn.vdsTabsSwitch = function(options){
    var defaults = {sw: 'li', maps: '.swcon'}, opts = $.extend(defaults, options);
    this.find(opts.sw).click(function(){
      var i = $(this).index();
      $(this).addClass('cur').siblings().removeClass('cur');
      $(opts.maps).hide().eq(i).show();
    });
  }
	
})(jQuery);