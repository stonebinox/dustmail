var app=angular.module("dust",[]);
app.config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});
app.controller("mail",function($scope,$compile,$http){
    $scope.map=null;
    $scope.loadMap=function(){
        var mapProp= {
            center:new google.maps.LatLng(51.508742,-0.120850),
            zoom:3,
            mapTypeId: 'satellite'
        };
        $scope.map=new google.maps.Map(document.getElementById("map"),mapProp);
        var width=$(window).width();
        var height=$(window).height();
        $("#map").css({
            width: width+"px",
            height: height+"px"
        });
        $(".form").css("height",(height-51)+"px");
        var text='<table width="100%" cellpadding=0 cellspacing=10 align="center">';
        for(var i=1;i<=15;i++){
            if((i==1)||(i==6)||(i==11)){
                text+='<tr>';
            }
            text+='<td align="center" width="20%"><img src="images/credentials/'+i+'.png" class="img-responsive" style="width:80px;margin-bottom:10px;"></td>';
            if((i==5)||(i==10)||(i==15)){
                text+='</tr>';
            }
        }
        text+='</table>';
        $("#credlist").html(text);
    };
});
window.resize=function(){
    var width=$(window).width();
    var height=$(window).height();
    $("#map").css({
        width: width+"px",
        height: height+"px"
    });
    $("#form").css("height",(height-51)+"px");
}