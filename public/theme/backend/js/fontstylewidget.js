(function($){
  $.fn.vwFontSize = function(config){
    var defaults = {   
          opts: [12, 14, 16, 18, 24, 32],
          size: '12px',
        }, params = $.extend(defaults, config), vwbtn = this, panel_opts = '';
		
    $.each(params.opts, function(k, v){      
      panel_opts += "<a style='font-size:"+v+"px'>"+v+"</a>";
    });
		
    var panel = $('<div></div>', {'class':'widget-size-panel'}).appendTo('body').html(panel_opts),
        bg = $('<div></div>', {'class':'widget-bg-lock'}).appendTo('body');

    vwbtn.click(function(){
      bg.css({width: document.body.clientWidth, height: document.body.clientHeight}).show();
      panel.css({left: vwbtn.offset().left, top: vwbtn.offset().top + vwbtn.outerHeight()}).show();
      panel.find('a').on('click', function(){
        vwbtn.val($(this).text()).data('style', 's:'+$(this).text());
        panel.hide();
        bg.hide();
      });
    //关闭面板
    bg.on('click', function(){panel.hide();bg.hide();});
  });
}
	
$.fn.vwFontFace = function(k){
  var e = this;
  e.click(function(){
    if(e.hasClass('checked')){
      e.removeClass('checked').data('style', k+':0');
    }else{
      e.addClass('checked').data('style', k+':1');
    }
  });
}
})(jQuery);

!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):a(jQuery)}(function(a){a.cxColor=function(){var b,c,d,h,i,e={dom:{},api:{}},f=function(a){return a&&("function"==typeof HTMLElement||"object"==typeof HTMLElement)&&a instanceof HTMLElement?!0:a&&a.nodeType&&1===a.nodeType?!0:!1},g=function(a){return a&&a.length&&("function"==typeof jQuery||"object"==typeof jQuery)&&a instanceof jQuery?!0:!1};for(h=0,i=arguments.length;i>h;h++)g(arguments[h])?b=arguments[h]:f(arguments[h])?b=a(arguments[h]):"function"==typeof arguments[h]?d=arguments[h]:"object"==typeof arguments[h]&&(c=arguments[h]);if(!(b.length<1))return e.init=function(){var e=this;e.dom.el=b,e.settings=a.extend({},a.cxColor.defaults,c),e.dom.el.val().length>0&&(e.settings.color=e.dom.el.val()),e.build(),e.api={show:function(){e.show()},hide:function(){e.hide()},color:function(){return e.setColor.apply(e,arguments)},reset:function(){e.reset()},clear:function(){e.clear()}},"function"==typeof d&&d(e.api)},e.build=function(){var f,g,h,i,b=this,c=["00","33","66","99","cc","ff"],d=["ff0000","00ff00","0000ff","ffff00","00ffff","ff00ff"],e="";for(e='<div class="panel_hd"><a class="reset" href="javascript://" rel="reset">默认颜色</a><a class="clear" href="javascript://" rel="clear">清除</a></div>',b.dom.colorPane=a("<div></div>",{"class":"cxcolor"}).appendTo("body").html(e),e="",f=0;2>f;f++)for(g=0;6>g;g++)for(e+="<tr>",e+='<td title="#000000" style="background-color:#000000">',e+=0==f?'<td title="#'+c[g]+c[g]+c[g]+'" style="background-color:#'+c[g]+c[g]+c[g]+'">':'<td title="#'+d[g]+'" style="background-color:#'+d[g]+'">',e+='<td title="#000000" style="background-color:#000000">',h=0;3>h;h++)for(i=0;6>i;i++)e+='<td title="#'+c[h+3*f]+c[i]+c[g]+'" style="background-color:#'+c[h+3*f]+c[i]+c[g]+'">';b.dom.colorTable=a("<table></table>").html(e).appendTo(b.dom.colorPane),b.dom.lockBackground=a("<div></div>",{"class":"cxcolor_lock"}).appendTo("body"),b.dom.colorPane.delegate("a","click",function(){if(this.rel)switch(this.rel){case"reset":return b.reset(),!1;case"clear":return b.clear(),!1}}),b.dom.colorTable.on("click","td",function(){b.change(this.title)}),b.dom.el.on("click",function(){b.show()}),b.dom.lockBackground.on("click",function(){b.hide()}),b.change(b.settings.color)},e.show=function(){var a=this,b=document.body.clientWidth,c=document.body.clientHeight,d=a.dom.colorPane.outerWidth(),e=a.dom.colorPane.outerHeight(),f=a.dom.el.offset().top,g=a.dom.el.offset().left,h=a.dom.el.outerWidth(),i=a.dom.el.outerHeight();f=f+e+i>c?f-e:f+i,g=g+d>b?g-(d-h):g,a.dom.colorPane.css({top:f,left:g}).show(),a.dom.lockBackground.css({width:b,height:c}).show()},e.hide=function(){this.dom.colorPane.hide(),this.dom.lockBackground.hide()},e.change=function(a){this.colorNow=a,this.dom.el.val(a).css("backgroundColor",a),this.dom.el.trigger("change"),this.hide()},e.setColor=function(a){return a?(this.change(a),void 0):this.colorNow},e.reset=function(){this.change(this.settings.color)},e.clear=function(){this.change("")},e.init(),this},a.cxColor.defaults={color:"#000000"},a.fn.cxColor=function(b,c){return this.each(function(){a.cxColor(this,b,c)}),this}});