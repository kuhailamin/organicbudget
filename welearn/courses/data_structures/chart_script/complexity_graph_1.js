functionPlot({
  target: '#complexity_graph_1',
  yAxis: {domain: [0, 3]},
  xAxis: {domain: [0, 2]},
  disableZoom:true,
  data: [
          {fn:'1',color:'lightgray'},
 { fn: 'x*x', color: 'red' },
    { fn: '0.5*x*x+0.5*x',color:'blue' }
]
})

