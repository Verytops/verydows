$(function(){
  //商品图片展示
  var each_thumb = $('#thumb-container a'), each_img = $('#goods-imgsrc li'), img_area = $('#goods-imgarea');
  img_area.data('zoom', each_img.eq(0).data('zoom')).zoom({url: img_area.data('zoom')});
  each_thumb.mouseover(function(){
    var i = $(this).index();
    each_thumb.removeClass('cur');
    $(this).addClass('cur');
    img_area.data('zoom', each_img.eq(i).data('zoom'));
    img_area.empty().html(each_img.eq(i).html()).trigger('zoom.destroy').zoom({url: img_area.data('zoom')});
  });
  img_area.hover(
    function(){$(this).siblings('i.zoom').addClass('over');},
    function(){$(this).siblings('i.zoom').removeClass('over');}
  );
	
  //缩略图滚动
  var thumb = $('#thumb-container'), //缩略图容器
      thumb_qty = $('#thumb-container a').size(), //缩略图总数
      forward_btn = $('#tmb-forward-btn'), //前滚按钮
      back_btn = $('#tmb-back-btn'), //后滚按钮
      move_dist = '62px', //每次滚动距离
      move_count = 0; //滚动次数
      
  if(thumb_qty > 5) forward_btn.removeClass('disabled');
  forward_btn.click(function(){
    if((thumb_qty - move_count) > 5){
      back_btn.removeClass('disabled');
      thumb.animate({left: '-='+move_dist}, 300);
      move_count++;
    }else{
      forward_btn.addClass('disabled');
    }
  });
	
  back_btn.click(function(){
    if(move_count > 0){
      forward_btn.removeClass('disabled');
      thumb.animate({left: '+='+move_dist}, 300);
      move_count--;
    }else{
      back_btn.addClass('disabled');
    }
  });
	
  //商品内容选项卡切换
  $('#contabs li').click(function(){
    var i = $(this).index();
    $(this).addClass('cur').siblings('.cur').removeClass('cur');
    $('.content').eq(i).removeClass('hide').siblings('.content').addClass('hide');
  });
  $('.speci table tr:even').addClass('even');
  $('.speci table tr').vdsRowHover({hoverClass:'hover'});
	
  //改变数量
  $('#buy-qty button').click(function(){
    var $input = $(this).siblings('input'), qty = parseInt($input.val());
    $input.parent().find('font.red').remove();
    if($(this).index() == 0){
      if(qty > 1) $input.val(qty - 1);
    }else{
      var stock = $input.data('stock');
      if($input.val() < stock){
        $input.val(qty + 1);
      }else{
        exceededStock(stock);
      }
    }
  });
  
  //商品可选项选择
  $('dd.opt a').click(function(){
    $(this).siblings('.cur').removeClass('cur').remove('i');
    $(this).addClass('cur').append("<i class=\"icon\"></i>").parent().data('checked', $(this).data('key')).parent().removeClass('warning');
    var added_price = 0;
    var now_price = $('#nowprice').data('price');
    $('dd.opt a.cur').each(function(i) {added_price += +$(this).data('price')});	
    now_price = parseFloat(Number(now_price) + Number(added_price)).toFixed(2);
    $('#nowprice').text(now_price);
  });
  
  //商品数量输入
  $('#buy-qty input').keyup(function(){
    var qty = $(this).val(), stock = $(this).data('stock');
    if(!/(^[1-9]\d*$)/.test(qty)){
      alert('请输入一个正确格式的购买数量！');
      $(this).focus().val(1);
      return false;
    }else if(qty > stock){
      exceededStock(stock);
      $(this).focus().val(stock);
    }
  });
})

function exceededStock(stock){
  var container = $('#buy-qty');
  container.find('font.warning').remove();
  $("<font class='warning red ml10'></font>").text("此商品最多只能购买 "+stock+" 件").appendTo(container);
}

function addToCart(btn, id){
  var optsGroup = $('.gatt dd.opt'), key = parseInt(id), optids = [];
  if(optsGroup.length > 0){
    optsGroup.each(function(i, e){
      if(!$(e).data('checked') || $(e).data('checked') == ''){
        $(e).parent().addClass('warning');
      }else{
        optids[i] = $(e).data('checked');   
        key += '_' + $(e).data('checked');
      }
    });
  }
  
  if($('.gatt dl.warning').size() > 0) return false; //检查是否有需要选择的商品选项
  if($('#buy-qty font.warning').size() > 0) return false; //检查是否超过库存限制

  var dialog = $('#tocart-dialog'), cart = $.parseJSON(getCookie('CARTS')) || {};
  dialog.css({left: $(btn).offset().left, top:$(btn).offset().top - dialog.height() - 50}).show();
  
  if(cart.hasOwnProperty(key)){  
    dialog.find('p').addClass('err').find('font').text('购物车中已存在此商品！');
    return false;
  }else{
    cart[key] = {id:id, qty:$('#buy-qty input[name="qty"]').val(), opts:optids};
    setCookie('CARTS', JSON.stringify(cart), 604800);
    incrCartNum();
    dialog.find('p').find('font').text('加入购物车成功！');
  }
}

function toBuy(id, target){
  var optsGroup = $('.gatt dd.opt'), key = id, optids = [], cart = $.parseJSON(getCookie('CARTS')) || {};
  if(optsGroup.length > 0){
    optsGroup.each(function(i, e){
      if(!$(e).data('checked') || $(e).data('checked') == ''){
        $(e).parent().addClass('warning');
      }else{
        optids[i] = $(e).data('checked');   
        key += '_' + $(e).data('checked');
      }
    });
  }
  if($('.gatt dl.warning').size() == 0){
    if(!cart.hasOwnProperty(key)){
       cart[key] = {id:id, qty:$('#buy-qty input[name="qty"]').val(), opts:optids};
       setCookie('CARTS', JSON.stringify(cart), 604800);
    }
    window.location.href = target;
  }
}

function cancelTocartDialog(){
  $('#tocart-dialog').hide();
}