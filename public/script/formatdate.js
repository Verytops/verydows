function formatTime(timestamp, format) {
  var d = new Date(parseInt(timestamp) * 1000), month = d.getMonth() + 1, day = d.getDate(), hour = d.getHours(), minute = d.getMinutes(), second = d.getSeconds();
  format = format.replace(/y/, d.getFullYear());
  if(month < 10) month = '0' + month;
  format = format.replace(/m/, month);
  if(day < 10) day = '0' + day;
  format = format.replace(/d/, day);
  if(hour < 10) hour = '0' + hour;
  format = format.replace(/h/, hour);
  if(minute < 10) minute = '0' + minute;
  format = format.replace(/i/, minute);
  if(second < 10) second = '0' + second;
  format = format.replace(/s/, second);
  return format;
}

function transtime(timestamp, format){
  var d = new Date(), now = parseInt(d.getTime() / 1000), distance = now - timestamp, string = '';
  if(distance < 300){
    string = '刚刚';
  }else if(distance < 3600){
    string = Math.floor(distance / 60) + '分钟前';
  }else if(distance < 86400){
    string = Math.floor(distance / 3600) + '小时前';
  }else if(distance < 604800){
    string = Math.floor(distance / 86400) + '天前';
  }else{
    string = formatTime(timestamp, format);
  }
  return string;
}