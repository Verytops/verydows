function getAreaSelect(changed){
  var dataset = {}, container;
  if(changed == 'province'){
    dataset.province = parseInt($('#areaslt-province').val());
    $('#areaslt-borough').children().first().siblings().remove();
    container = $('#areaslt-city');
    if(!dataset.province){
      container.children().first().siblings().remove();
      return;
    }
  }else if(changed == 'city'){
    dataset.province = parseInt($('#areaslt-province').val());
    dataset.city = parseInt($('#areaslt-city').val());
    container = $('#areaslt-borough');
    if(!dataset.city){
      container.children().first().siblings().remove();
      return;
    }
  }else{
    container = $('#areaslt-province');
  }
  
  $.getJSON(areaApi, dataset, function(data){
    var opts = '';
    for(i in data) opts += "<option value='"+i+"'>"+data[i]+"</option>";
    container.children().first().siblings().remove();
    container.append(opts);
  });
}

function setArea(type, dataset, selected){
  var container = $('#areaslt-'+type);
  $.getJSON(areaApi, dataset, function(data){
    var opts = '';
    for(i in data){
      if(selected == i) opts += "<option value='"+i+"' selected='selected'>"+data[i]+"</option>"; else opts += "<option value='"+i+"'>"+data[i]+"</option>";
    }
    container.children().first().siblings().remove();
    container.append(opts);
  });
}

function checkCsnForm(form){
  form.find('input[name="receiver"]').vdsFieldChecker({rules:{required:[true, '收件人不能为空'], maxlen:[20, '收件人不能超过20个字符']}});
  form.find('select[name="province"]').vdsFieldChecker({rules:{required:[true, '请选择省份']}});
  form.find('select[name="city"]').vdsFieldChecker({rules:{required:[true, '请选择城市']}});
  form.find('select[name="borough"]').vdsFieldChecker({rules:{required:[true, '请选择区县']}});
  form.find('input[name="address"]').vdsFieldChecker({rules:{required:[true, '详细地址不能为空'], maxlen:[240, '详细地址不能超过240个字符']}});
  form.find('input[name="zip"]').vdsFieldChecker({rules:{zip:[true, '邮政编码格式不正确']}});
  form.find('input[name="mobile"]').vdsFieldChecker({rules:{required:[true, '手机号码不能为空'], mobile:[true, '手机号码格式不正确']}});
  return form.vdsFormChecker({isSubmit:false});
}