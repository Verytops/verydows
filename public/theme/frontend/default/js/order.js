//隐藏收件人表单
function hideConsigneeBox(){
  $('#consignee-box').slideUp('normal', function(){
    $('#newadrbtn').show();
    $(this).find('select[name="province"] option:first').prop('selected', 'selected');
    $(this).find('select[name="city"]').children().not(':first').remove();
    $(this).find('select[name="borough"]').children().not(':first').remove();
  }).find('form')[0].reset();
}

//编辑收件人地址信息
function editConsignee(btn){
  var container = $('#consignee-box'), data = $(btn).closest('li').data('json');
  $('#newadrbtn').hide();
  container.slideDown().find('span.vdsfielderr').remove();
  container.find('input[name="id"]').val(data.id);
  container.find('input[name="receiver"]').val(data.receiver);
  container.find('input[name="address"]').val(data.address);
  container.find('input[name="zip"]').val(data.zip);
  container.find('input[name="mobile"]').val(data.mobile);
  presAreaSelect(container.find('select[name="province"]'), null, data.province);
  presAreaSelect(container.find('select[name="city"]'), {province:data.province}, data.city);
  presAreaSelect(container.find('select[name="borough"]'), {province:data.province, city:data.city}, data.borough);
}

//触发选换收件人
function onChangeConsignee(){
  $('#consignee-list input[type="radio"]').change(function(){
    $(this).vdsConfirm({
      text: '您确定要更换收件人地址吗?',
      ok: function(){
        $('#consignee-list li.cur').removeClass('cur').find('input[type="radio"]').prop('checked', false);
        $(this).prop('checked', true).closest('li').addClass('cur');
        countFreight();
      },
      no: function(){
        $('#consignee-list li.cur').find('input[type="radio"]').prop('checked', true);
        $(this).prop('checked', false);
      }
    });	  
  });
}

//触发选换配送方式
function onChangeShipping(){
  $('#shipping_list input[type="radio"]').change(function(){
    $(this).vdsConfirm({
      text: '您确定要更换配送方式吗?',
      ok: function(){
        $('#shipping_list li.cur').removeClass('cur').find('input[type="radio"]').prop('checked', false);
        $(this).prop('checked', true).closest('li').addClass('cur');
        countFreight();
      },
      no: function(){
        $('#shipping_list li.cur').find('input[type="radio"]').prop('checked', true);
        $(this).prop('checked', false);
      }
    });	  
  });
}

//计算运费
function countFreight(){
  var csn_id = $('#consignee-list input[type="radio"]:checked').val(), shipping_id = $('#shipping_list input[type="radio"]:checked').val();
  $.getJSON(freightApi, {csn_id:csn_id, shipping_id:shipping_id}, function(res){
    if(res.status == 'success'){
      $('#shipping_amount').text(res.amount);
      var totals = parseFloat($('#goods_amount').text()) + parseFloat(res.amount);
      $('#order_amount').text(totals.toFixed(2));
    }
  });
}

//提交订单
function submitOrder(){
  var form = $('#order-form'), error = false;
  var csn_id = $('#consignee-list label input[type="radio"]:checked').val(),
      shipping_id = $('#shipping_list label input[type="radio"]:checked').val(),
      payment_id = $('#payment_list label input[type="radio"]:checked').val(),
      memos = $('#memos').val();
  
  form.find('input[name="csn_id"]').val(csn_id);
  form.find('input[name="shipping_id"]').val(shipping_id);
  form.find('input[name="payment_id"]').val(payment_id);
  form.find('input[name="memos"]').val(memos);
  
  form.find('input[type="hidden"]').not('input[name="memos"]').each(function(){
    if(this.value == 0 || this.value == '' || typeof(this.value) == 'undefined'){
      error = true;
      $.vdsPopDialog({type:'err', text:$(this).data('error')});
      return false;
    }
  });
  if(!error) form.submit();
}