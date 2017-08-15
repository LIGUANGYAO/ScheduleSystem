alertify.dialog('myAlert',function factory(){
    return{
        build: function () {
            var header = '<span'+'style="vertical-align:middle;color:#e10000;">'+'</span> 管理员系统';
            this.setHeader(header);
        },
        main:function(message){
            this.message = message;
        },
        setup:function(){
            return {
                buttons:[{text: "确定", key:27}],/*Esc*/
                focus: { element:0 }
            };
        },
        prepare: function(){
            this.setContent(this.message);
        },
        callback: function () {
            window.location.href="managerLogin.html";
            return true;
        }
    };
});
$(function (){
	$("#header").load("header.html",function (){
		$("#password-sub").on("click",function (){
		$(".error-info").html("");
		$(".error-info").fadeIn();
		if($("#comfirmpwd").val()==""||$("#new_password").val()==""||$("#comfirmpwd").val()==""){
			$(".error-info").html("请输入完整信息");
			$(".error-info").fadeOut(4000);
			return;
		}
		if($("#comfirmpwd").val()!==$("#new_password").val()){
			$(".error-info").html("新密码和确认密码输入不一致");
			$(".error-info").fadeOut(4000);
			return;
		}
		var data={
			"pre_password":$("#pre_password").val(),
			"new_password":$("#new_password").val(),
			"admin_level":"3"
		};
		System.post(System.base_url+"/Admin/AdminBase/updatePassword",data,function (res){
			console.log(res);
			alertify.myAlert("<b style='display:block;margin:20px auto;font-size:24px;color:#000;text-align:center;'>修改成功</b>");
		},function (res){
			$(".error-info").html("修改密码失败，请确认输入的信息");
			$(".error-info").fadeOut(4000);
		});
		});
		$(".logout").on("click",function (){
		var data={
			"admin_level":"3"
		};
		$.cookie("name",null,{
            path: "/"
        });
		System.get(System.base_url+"/Admin/AdminBase/logout",data,function (res){

			alertify.myAlert("<b style='display:block;margin:20px auto;font-size:24px;color:#000;text-align:center;'>退出成功</b>");
		},function (res){
			alertify.alert(res.msg, function(){
			    alertify.message('OK');
			  });
		});
	});
	});
	$("#side").load("side.html",function (){
		 $('.inactive').click(function(){
		    if($(this).siblings('ul').css('display')=='none'){
		      $(this).siblings('ul').slideDown(600).children('li');
		    }else{
		      //控制自身菜单下子菜单隐藏
		      $(this).siblings('ul').slideUp(600);
		    
		    }
		  });
	});
});