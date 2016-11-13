/********** 相册图片 **********/
function addAlbum(){ //添加
  var tpl = $('#add-album-tpl').html();
  $('#album').append(tpl).find('a.blue').click(function(){$(this).parent().remove()});
}
function removeAlbumImg(e, id){ //删除
  var removed_ids = $('#album_removed').val();
  removed_ids = removed_ids + id + ',';
  $(e).closest('dl').remove();
  $('#album_removed').val(removed_ids);
}

/********** 商品可选项 **********/
function selectedOptType(e){
  var type_id = $('#opt-type').val(),
      type_name = $('#opt-type option:selected').text(),
      container = $('#opt-container'),
      exist = 0;
  if(type_id != 0){
    container.find('dt font').each(function(){
      if($(this).data('id') == type_id){
        exist = 1;
        return false;
      }
    });
    if(exist == 0){
      var tpl = $('#opt-tpl').html();
      tpl = tpl.replace(/{{type_id}}/g, type_id);
      tpl = tpl.replace(/{{type_name}}/, type_name);
      container.append(tpl);
    }else{
      $('body').vdsAlert({msg:'已存在此选项, 请直接在该选项下添加可选值!', time:2}); 
    }
  }
  else{
    $('body').vdsAlert({msg:'请选择一个选项类型', time:1});
  }
}
function removeOpt(e){ //删除选项
  $(e).closest('dl').remove();
}
function addOptVal(e){ //增加选项内容
  var type_id = $(e).prev('font').data('id'), tpl = $('#opt-val-tpl').html();
  tpl = tpl.replace(/{{type_id}}/, type_id);
  $(e).closest('dl').append(tpl);
}
function removeOptVal(e){ //删除选项内容
  $(e).parent().remove();
}