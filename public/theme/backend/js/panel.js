$(function(){
  var ph = $('body').height() - $('#header').outerHeight() - $('#footer').outerHeight() - 10;
  $('#nav').height(ph);
  $('#main').height(ph);
  $('#nav h3').click(function(){
    if($(this).hasClass('on')) $(this).removeClass('on').next('ul').slideUp(); else $(this).addClass('on').next('ul').slideDown();
  });
  $('#nav li').click(function(){
    $('#nav li.on').removeClass('on');
    $(this).addClass('on');
  });
});

function closeUser(){
  $.vdsMasker(false);
  $('#pop-user').hide();
  $('#pwd').hide();
  $('#pop-user div.ta-c button').eq(0).hide();
  $('#pop-user div.ta-c button').eq(1).show();
}

function resetPwd(){
  $('#pwd').slideDown(200);
  $('#pop-user div.ta-c button').eq(0).show();
  $('#pop-user div.ta-c button').eq(1).hide();
}

function popAc(id){
  $.vdsMasker(true);
  $('#'+id).vdsMidst({wrapper:$(window), gotop:-100});
  $('#'+id).show();
}

function closeAc(id){
  $.vdsMasker(false);
  $('#'+id).hide();
}

function checkAllClean(){
  var allBtn = $('#clean-select li:first input');
  if(allBtn.prop('checked')){
    $('#clean-select').find('input[type="checkbox"]').prop('checked', true);
  }else{
    $('#clean-select').find('input[type="checkbox"]').prop('checked', false);
  }
}

function cleanCache(url){
  var selected = $('#clean-select input[name="clean"]:checked');
  if(selected.size() < 1){
    $('body').vdsAlert({msg:'请选择至少一种您需要清理的类型', time:2});
    return false;
  }
  var clean = [];
  selected.each(function(){  
    clean.push($(this).val());  
  });
  $.ajax({
    type: 'post',
    dataType: 'json',
    url: url,
    data: {clean:clean},
    beforeSend: function(){$('#clean-select').hide();$('#cleaning').show();},
    success: function(res){
      closeAc('pop-clean');
      $('#clean-select').show();
      $('#cleaning').hide();
      if(res.status == 'success'){
        $('body').vdsAlert({msg:'清理完成', time:1});
      }else{
        $('body').vdsAlert({msg:res.msg, time:2}); 
      }
    },
    error: function(){ 
      $('#clean-select').show();
      $('#cleaning').hide();
      $('body').vdsAlert({msg:"处理请求时发生错误"});
    }
  });
}

function submitPwd(){
  $('#old_password').vdsFieldChecker({rules: {required:[true, '请输入旧密码']}, tipsPos:'br'});
  $('#new_password').vdsFieldChecker({rules: {required:[true, '请设置新密码'], password:[true, '新密码不符合格式要求']}, tipsPos:'br'});
  $('#repassword').vdsFieldChecker({rules: {equal:[$('#new_password').val(), '两次密码不一致']}, tipsPos:'br'});
  $('#pwd form').vdsFormChecker();
}