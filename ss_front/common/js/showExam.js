function showExam(post_data){
	if(post_data){
		var kind = post_data.kind;
	}
	if(kind == "update_monitor_teacher") {
		var data = {
			open_class_academy_id:post_data.open_class_academy_id,
			order:post_data.order
		};
	}else{
		$(".table-list").html("");
		$("#errorInfo").html("");
		var data = {
	   		"week":$("#week").val(),
	   		"class_name":$("#class_name").val(),
	   		// "open_class_academy":$("#open_class_academy").val(),
	   		// "have_class_academy":$("#have_class_academy").val(),
	   		// "code":$("#code").val(),
	   		"name":$("#name").val(),
	   		"teacher_name":$("#teacher").val(),
	   		"order":"class"
	   };
	}
   System.post(System.base_url+"/Admin/Exam/showExam",data,function (res){
   		if(res.data.length<=0){
   			$("#errorInfo").html("<p class='text-center text-danger text-index'>未查询到</p>");
   			return;
   		}
   		var examList="<table class='table table-bordered'><tr><th>开课学院名称</th><th>上课学院名称</th><th>班级名称</th><th>学生人数</th><th>课程代号</th><th>课程名称</th><th>考核方式</th><th>总学时</th><th>任课教师</th><th>上课时间</th><th>上课地点</th><th>监考老师</th>";
   		if(kind == "update_monitor_teacher"){
   			examList+="<th>填写监考老师</th>";
   		}
   		
   		examList+="</tr>";
   		var rows=res.data;
		for(var i=0;i<rows.length;i++){
			examList+="<tr>";
			examList+="<td>"+rows[i].open_class_academy+"</td>";
			examList+="<td>"+rows[i].have_class_academy+"</td>";
			examList+="<td>"+rows[i].class_name+"</td>";
			examList+="<td>"+rows[i].student_sum+"</td>";
			examList+="<td>"+rows[i].code+"</td>";
			examList+="<td>"+rows[i].name+"</td>";
			examList+="<td>"+rows[i].examine_way+"</td>";
			examList+="<td>"+rows[i].time_total+"</td>";
			examList+="<td>"+rows[i].teacher_name+"</td>";
			examList+="<td>"+rows[i].time+"</td>";
			examList+="<td>"+rows[i].classroom+"</td>";
			examList+="<td>"+rows[i].monitor_teacher_name+"</td>";

			if(kind == "update_monitor_teacher"){
				examList+="<td><button class='btn btn-info' data-toggle='modal' data-target='#writeTeacher' onclick=writeTea("+rows[i].id+") >填写</button></td>";
			}
			examList+="</tr>";
		}
		examList+="</table>";
		$(".table-list").html(examList);
   },function (res){
   		$("#errorInfo").html("<p class='text-center text-danger'>"+res.msg+"</p>");
   });
}
function writeTea(id){
	$("#classID").val(id);
}