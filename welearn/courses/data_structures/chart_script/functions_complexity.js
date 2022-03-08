functionPlot({
  target: '#functions_complexity',
  yAxis: {domain: [0, 1000]},
  xAxis: {domain: [0, 100]},
  disableZoom:true,
  data: [
          {fn:'log(x)',color:'black'},
          {fn:'x',color:'red'},
          {fn:'x*log(x)',color:'blue'},
          {fn:'x*x',color:'green'},
          {fn:'x*x*x',color:'brown'}
]
})

