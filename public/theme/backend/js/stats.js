function labelFormatter(label, series) {
  return "<div style='font-size:12px;text-align:center;padding:3px;color:#fff;'>" + label + "<br/>" + Math.round(series.percent) + "%</div>";
}