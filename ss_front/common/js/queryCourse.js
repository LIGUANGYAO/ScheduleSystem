function getData(page,dataJson){
    $(".table-list").html("");
    var limit=6;
    if(dataJson&&dataJson!=="null"){
        dataJson=dataJson.split("&");
        var data={};
        for(var i=0;i<dataJson.length;i++){
            data[dataJson[i].split("=")[0]]=dataJson[i].split("=")[1];
        }
    }else{
        var data={};
        if($("#class_name").val()){
            data.class_name=$("#class_name").val();
        }
        if($("#academy").val()){
            data.academy=$("#academy").val();
        }
        if($("#name").val()){
            data.name=$("#name").val();
        }
        if($("#code").val()){
            data.code=$("#code").val();
        }
        if($("#teacher").val()){
            data.teacher=$("#teacher").val();
        }
    }
    data.limit=limit;
    data.page=page;
    System.get(System.base_url+"/Home/Course/queryCourse",data,function (res){
        var total_page=Math.ceil(res.data.sum/limit);
        var rows=res.data.course_data.length;
        var classList="<table border=1 class='table-index'><tr><th>班级名称</th><th>课程代号</th><th>课程名称</th><th>考核方式</th><th>总学时</th><th>理论学时</th><th>实践学时</th><th>任课教师</th><th style='width:81px'>上课时间</th><th style='width:81px'>上课周次</th><th style='width:81px'>上课地点</th>";
        if($.cookie('admin_level')==3){
            classList+="<th>操作</th>";
        }

        classList+="</tr>";
        for(var i=0;i<rows;i++){
            classList+="<tr>";
            classList+="<td>"+res.data.course_data[i].class_name+"</td>";
            classList+="<td>"+res.data.course_data[i].code+"</td>";
            classList+="<td>"+res.data.course_data[i].name+"</td>";
            classList+="<td>"+res.data.course_data[i].examine_way+"</td>";
            classList+="<td>"+res.data.course_data[i].time_total+"</td>";
            classList+="<td>"+res.data.course_data[i].time_theory+"</td>";
            classList+="<td>"+res.data.course_data[i].time_practice+"</td>";
            classList+="<td>"+res.data.course_data[i].teacher+"</td>";
            classList+="<td class='big-td' colspan=3  height='100%'>";
            classList+="<table height='100%'>";
            for(var j=0;j<res.data.course_data[i].classroom_time.length;j++){
                classList+="<tr>";
                classList+="<td>"+res.data.course_data[i].classroom_time[j].weekday+"</td>";
                classList+="<td>"+res.data.course_data[i].classroom_time[j].week+"</td>";
                classList+="<td>"+res.data.course_data[i].classroom_time[j].classroom+"</td>";
                classList+="</td></tr>";
            }
            classList+="</table>";
            classList+="</td>";
            if($.cookie('admin_level')==3){
                var str='';
                for(var j=0;j<res.data.course_data[i].classroom_time.length;j++){
                    str+=res.data.course_data[i].classroom_time[j].weekday+",";
                    str+=res.data.course_data[i].classroom_time[j].week_for_update+",";
                    str+=res.data.course_data[i].classroom_time[j].classroom+";\\n";
                }
                classList+="<td><button class='btn btn-info' data-toggle='modal' data-target='#openUpdateCourse' onclick='openUpdateCourse("+res.data.course_data[i].id+",\""+str+"\")'>修改</button></td>";
            }
            classList+="</tr>";
        }
        classList+="</table>";
        $(".table-list").html(classList);
        makeNavigation(page,total_page, 'getData', 'pagelist', 2,data);
    },function (res){
        $(".table-list").addClass("text-center");
        $(".table-list").html("<span class='text-tit text-center' style='color:red'>未查询到信息</sapn>");
    });
}