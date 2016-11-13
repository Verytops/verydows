$(function(){
  $.getScript('http://pv.sohu.com/cityjson?ie=utf-8', function(){
    if(typeof(baseUrl) == 'undefined'){
       baseUrl = $('meta[name="verydows-baseurl"]').attr('content') || window.location.protocol + "//" + window.location.host;
    }
    $.post(baseUrl+'/index.php?m=api&c=stats&a=count', {ip:returnCitySN.cip, area:returnCitySN.cname, referrer:parseHost(document.referrer), platform:getPlatform(), browser:getBrowser()});
  });
});

function getPlatform(){
  var agent = window.navigator.userAgent, platform = 0;
  if(agent.match(/windows|win32/i)) platform = 1;
  else if(agent.match(/macintosh|mac os x/i)) platform = 2;
  else if(agent.match(/linux/i)) platform = 3;
  return platform;
}

function getBrowser(){
  var agent = window.navigator.userAgent, browser = 0;
  if((agent.match(/msie/i) && !agent.match(/opera/i)) || agent.match(/trident/i)) browser = 1;
  else if(agent.match(/chrome/i)) browser = 2;
  else if(agent.match(/firefox/i)) browser = 3;
  else if(agent.match(/safari/i)) browser = 4;
  else if(agent.match(/opera/i)) browser = 5;
  return browser;
}

function parseHost(url){
  if(typeof(url) == 'undefined' || null == url) return '';
  var matches = url.match(/.*\:\/\/([^\/]*).*/);
  if(matches != null) return matches[1]; else return '';
}