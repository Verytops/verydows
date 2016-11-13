function showCartList(url){
  $.asynInter(url, null, function(res){
    if(res.status == 'success'){
      $('#cart').append(juicer($('#item-tpl').html(), res.cart)).append($('#footact-tpl').html());
      totalCart();
      preserveSpace('footfixed');
      bindOperates();
    }else{
      $('#cart').append($('#nodata-tpl').html());
    }
  });
}

function bindOperates(){
  //删除商品
  $('#cart .remove').on('click', function(){
    var obj = $(this);
    $.vdsConfirm({
      content: '您确定要移除该商品吗?',
      ok: function(){obj.closest('li.cart-row').remove();updateCart();}
    });
  });
  //清空购物车
  $('#footfixed a.clear').on('click', function(){
    $.vdsConfirm({
      content: '您确定清空购物车吗?',
      ok: function(){$('#cart').empty();updateCart();}
    });
  });
  //改变数量
  $('#cart .qty a').on('click', function(){
    var qty = $(this).siblings('input'), qty_val = parseInt(qty.val());
    if($(this).hasClass('minus')){
      if(qty_val > 1){
        qty.val(qty_val - 1);
      }
      else{
        $.vdsPrompt({content:'购买数量不能少于1件'});
      }
    }else{
      var stock = qty.data('stock');
      if(qty.val() < stock){
        qty.val(qty_val + 1);
      }else{
        $.vdsPrompt({content:"购买数量不能超过 "+stock+" 件"});
        return false;
      }
    }
    totalCart();
  });
}

function updateCart(){
  if($('#cart .cart-row').size() > 0){
    var cookie = {};
    $('#cart .cart-row').each(function(){
      cookie[$(this).data('key')] = $(this).data('json');
    });
    setCookie('CARTS', JSON.stringify(cookie), 604800);
    totalCart();
  }else{
    setCookie('CARTS', '', -1);
    $('#cart').html($('#nodata-tpl').html());
    $('#footfixed').remove();
  }
}

function totalCart(){
  var amount = 0.00;
  $('#cart .cart-row').each(function(i, e){
    var price = parseFloat($(e).find('.unit-price').text()), qty = parseInt($(e).find('.qty').find('input').val());
    amount += parseFloat(price * qty);
  });
  $('#cart-kinds').text($('.cart-row').size());
  $('#cart-amount').text(amount.toFixed(2));
}