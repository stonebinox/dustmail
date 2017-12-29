var app=angular.module("dust",[]);
app.controller("mail",function($scope,$compile,$http){
    $scope.map=null;
    $scope.loadMap=function(){
        var mapProp= {
            center:new google.maps.LatLng(51.508742,-0.120850),
            zoom:3,
        };
        $scope.map=new google.maps.Map(document.getElementById("map"),mapProp);
        var width=screen.availWidth;
        var height=screen.availHeight;
        $("#map").css({
            width: width+"px",
            height: height+"px"
        });
    };
});