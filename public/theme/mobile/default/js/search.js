$(function(){
  $('#latsw').vdsTapSwapper(function(){$('#top-menus').show();},function(){$('#top-menus').hide();});
  $.vdsTouchScroll({
    touchOff: 40,
    onBottom: function(){
      var obj = $('#srli');
      if(obj.data('cur') != obj.data('next')){
        showFalls();
      }else{
        $('#nomore').show();
      }
    },
  });
});

function showFilters(){
  $('html').css({overflow:'hidden'});
  $('body').css({height:$(window).height(), overflow:'hidden'});
  var container = $('#filters');
  container.show().animate({left: 0}, 100);
  container.find('.elm .ek').click(function(){
    $(this).addClass('cur').siblings('.cur').removeClass('cur');
    container.find('.elm .ev').hide();
    $(this).next('.ev').show();
  });
}

function closeFilters(){
  $('html').css({overflow:'auto'});
  $('body').css({height:'auto', overflow:'auto'});
  $('#filters').animate({left:'100%'}, 100, function(){$(this).hide()});
}

function outSearch(){
  $('#searcher').hide();
  $('#wrapper').show();
}

function showFalls(){
  var container = $('#srli'), dataset = {page:container.data('next'), pernum:10};
  
  $.asynList(searchApi, dataset, function(res){
    if(res.list){
      container.append(juicer($('#goods-tpl').html(), res));
      if(res.paging){
        container.data('cur', dataset.page);
        container.data('next', res.paging.next_page);
      }
    }
  });
}