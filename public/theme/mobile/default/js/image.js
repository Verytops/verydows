(function($){
  $.fn.vdsGetImage = function(options){
    var defaults = {
      input: null,
      width: null,
      height: null,
      quality: 1,
      minWidth: 60,
      minHeight: 60,
      maxWidth: null,
      maxHeight: null,
      success: function(res){},
    }, opts = $.extend(defaults, options);
    
    var $file = opts.input.files[0];
    
    if(!/image\/\w+/.test($file.type)){
      $.vdsPrompt({content:'请选择图片文件'});
      return false;
    }
    
    var $_URL = window.URL || window.webkitURL;
    $im = new Image();
    $im.src = $_URL.createObjectURL($file);
    
    $im.onload = function(){
      if(opts.maxWidth != null && $im.width > opts.maxWidth){
        $.vdsPrompt({content:'图片宽度不能超过'+ opts.maxWidth +'px'});
        return false;
      }
      if(opts.maxHeight != null && $im.height > opts.maxHeight){
        $.vdsPrompt({content:'图片高度不能超过'+ opts.maxHeight +'px'});
        return false;
      }
      if(opts.minWidth != null && $im.width < opts.minWidth){
        $.vdsPrompt({content:'图片宽度不能小于'+ opts.minWidth +'px'});
        return false;
      }
      if(opts.minHeight != null && $im.height < opts.minHeight){
        $.vdsPrompt({content:'图片高度不能小于'+ opts.minHeight +'px'});
        return false;
      }
      
      var _canvas = document.createElement('canvas'), $w, $h, $res = {};
      if(opts.width != null){
        $w = opts.width;
      }else{
        $w = this.width;
      }
      if(opts.height != null){
        $h = opts.height;
      }else{
        $h = $w / ($w / this.height);
      }
      
      $(_canvas).attr({width:$w, height:$h});
      _canvas.getContext('2d').drawImage(this, 0, 0, $w, $h);
      $res.base64 = _canvas.toDataURL('image/jpeg', opts.quality);
      opts.success($res);
    }
  }
})(Zepto);