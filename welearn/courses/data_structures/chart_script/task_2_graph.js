functionPlot({
  target: '#task_2_graph',
  yAxis: {domain: [0, 7]},
  xAxis: {domain: [0, 2]},
  disableZoom:true,
  data: [
          {fn:'4',color:'lightgray'},
 { fn: '4*x*x*x', color: 'red' },
    { fn: 'x*x*x+2*x*x+1',color:'blue' }
]
})

