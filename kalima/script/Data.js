var DATA = new Data(); //DATA object

function Data() {
    
    this.COUNTRIES=[
    {id:"US",name:"United States",has_states:true},
    {id:"CA",name:"Canada",has_states:true},
    {id:"PS",name:"Palestine",has_states:false},
    {id:"UK",name:"United Kingdom",has_states:false},
    {id:"DE",name:"Germany",has_states:false},
    {id:"DK",name:"Denmark",has_states:false}
];

this.STATES=[
    {id:"TX",name:"Texas",country:"US"},
    {id:"MO",name:"Missouri",country:"US"}
];

this.CITY=[
    {name:"Kansas City",state:"MO",country:"US"},
    {name:"Kansas City",state:"KS",country:"US"},
    {name:"Dallas",state:"TX",country:"US"},

];

}


