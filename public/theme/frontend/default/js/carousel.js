$(function(){
  $(".carousel-imgs a").first().css("display","block");
  var _imgs = $(".carousel-imgs a");
  var _tog = $(".carousel-tog li");
  var len = _imgs.length;
  var i = 0;

  _tog.mouseover(function(){
	i = $(this).index();
    changeImg(i);
  });
  
  function changeImg(n){
    _tog.eq(n).addClass('cur').siblings('li').removeClass('cur');
	_imgs.eq(n).stop().fadeIn(300).siblings("a").hide();
  }
  
  _imgs.hover(
    function () {
      clearInterval(changeTimer);
    },
    function () {
      changeTimer = setInterval(function() {
        changeImg(i);
        i++;
        if(i == len) {i = 0;}
      }, 4000)
    }
  );
  
  var changeTimer = setInterval(function() {
    changeImg(i);
    i++;
    if(i == len) {i = 0;}
  }, 4000);
  
})