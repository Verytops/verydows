$(function(){
  //删除购物车商品
  $('.remove-row').click(function(){
    var row = $(this).closest('tr.cart-row');
    $(this).vdsConfirm({
      text: '您确定要删除此商品吗?',
      left: -35,
      top: -15,
      confirmed: function(){
        $.ajax({
          type: 'post',
          dataType: 'text',
          url: baseUrl+'/index.php?c=cart&a=index&step=remove',
          data: {key: row.data('key')},
          beforeSend:function(){$('body').vdsLoading({text:'正在删除...'});},	
          success: function(data){
            $('body').vdsLoading({sw:false});
            if(data == 1){
              row.remove();
              total_cart();
              if($('.cart-row').size() < 1) $('.container').empty().append("<div class='cart-empty cut'><p class='c666'>您的购物车是空的！<a href='"+hostUrl+"'>快去逛一逛</a>，找到您喜欢的商品放进购物车吧。</p></div>");
            }else{
              alert('删除失败，请重试!');
            }
          },
          error:function(){$('body').vdsLoading({sw:false});alert('请求出错！');}
        });
      },
    });
  });
  //清空购物车
  $('#clear-cart').click(function(){
    $(this).vdsConfirm({
      text: '您确定要清除购物车中全部商品吗?',
      left: 260,
      top: -15,
      confirmed: function(){
        $.ajax({
          type: "post",
          dataType: "text",
          url: baseUrl+"/index.php?c=cart&a=index&step=clear",
          beforeSend:function(){
            $('body').vdsLoading({text:'正在清空您的购物车...'});
          },
          success: function(data){
            $('body').vdsLoading({sw:false});
            if(data == 1){
              $('.container').empty().append("<div class='cart-empty cut'><p class='c666'>您的购物车是空的！<a href='"+hostUrl+"'>快去逛一逛</a>，找到您喜欢的商品放进购物车吧。</p></div>");
            }else{
              alert('清空购物车失败，请重试！');
            }
          },
          error:function(){$('body').vdsLoading({sw:false});alert('请求出错！');}
        });
      },
    });	
  });
});

function showCartList(url){
  var container = $('#cart');
  $.ajax({
    type: 'get',
    dataType: 'json',
    url: url,
    beforeSend:function(){container.vdsInnerLoading({text:'购物车加载中', sw:true})},	
    success: function(res){
      container.vdsInnerLoading({sw:false});
      if(res.status == 'success'){
        container.append(juicer($('#cart-tpl').html(), res.cart));
        bindOperates();
      }else if(res.status == 'nodata'){
        container.append($('#nodata-tpl').html());
      }else{
        container.append('<p class="aln-c f14 red borderframe pad10">购物车数据错误</p>');
      }
    }
  });
}

function bindOperates(){
  var container = $('#cart');
  //增加或减少 
  container.find('.qty button').on('click', function(){ 
    var input = $(this).siblings('input'), qty = parseInt(input.val());
    if($(this).index() == 0){
      if(qty > 1) input.val(qty - 1);
    }else{
      var stock = input.data('stock');
      if(qty < stock){
        input.val(qty + 1);
      }else{
        alert("此商品最多只能购买 "+stock+" 件");
        return false;
      }
    }
    totalCart();
  });
  //直接输入数量
  container.find('.qty input').on('keyup', function(){
    var qty = $(this).val(), stock = $(this).data('stock');
    if(!/(^[1-9]\d*$)/.test(qty)){
      alert('请输入一个正确格式的购买数量！');
      $(this).focus().val(1);
    }else if(qty > stock){
      alert("此商品最多只能购买 "+stock+" 件");
      $(this).focus().val(stock);
    }else{
      totalCart();
    }
  });
  //移除购物车商品数据
  container.find('.remove-row').on('click', function(){
    var row = $(this).closest('tr.cart-row');
    $(this).vdsConfirm({
      text: '您确定要删除此商品吗?',
      left: -35,
      top: -15,
      ok: function(){
        row.closest('tr.cart-row').remove();
        updateCart();
      },
    });
  });
}

function totalCart(){
  var $total = 0.00;
  $('.cart-row').each(function(i, e){
    var $unit = parseFloat($(e).find('.unit-price').text()),
        $qty = parseInt($(e).find('.qty').find('input').val()),
        $subtotal = ($unit * $qty).toFixed(2);		
    $(e).find('.subtotal').text($subtotal);
    $total = $total + parseFloat($subtotal);
  });
  $('#item-count').text($('.cart-row').size());
  $('#total').text($total.toFixed(2)); 
}

function updateCart(){
  var container = $('#cart'), rows = container.find('.cart-row');
  if(rows.size() > 0){
    var cookie = {};
    rows.each(function(){
      cookie[$(this).data('key')] = $(this).data('json');
    });
    setCookie('CARTS', JSON.stringify(cookie), 604800);
    totalCart();
  }else{
    setCookie('CARTS', '', -1);
    container.html($('#nodata-tpl').html());
  }
}

function clearCart(btn){
  $(btn).vdsConfirm({
    text: '您确定要清空购物车吗?',
    left: -25,
    top: -20,
    ok: function(){
      $('#cart').empty();
      updateCart();
    },
  });
}