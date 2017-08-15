var curWwwPath = window.document.location.href;
var pathName = window.document.location.pathname;
var pos = curWwwPath.indexOf(pathName);
var localhostPaht = curWwwPath.substring(0, pos);
var src = localhostPaht + '/Schedulesystem/ss_front/common/js/base.js';
document.write("<script language='JavaScript' src='" + src + "'></script>");
//var script = document.createElement('script');
//script.src = src;
//document.head.appendChild(script);

var System = {};

if(!$.cookie("name")&&window.location.href.indexOf('teacherLogin.html')==-1&&window.location.href.indexOf('forgetPasswordSendEmail.html')==-1&&window.location.href.indexOf('../changePassword.html')==-1){
    
    window.location.href="./teacherLogin.html";
}
System.post=function (url,data,success_handler,error_handler){
    $.ajax({
        type : "POST",
        url : url,
        data : data,
        dataType : "json",
        xhrFields : {
            withCredentials : true
        },
        success:function (resJson){
            //console.log(resJson);
            if(resJson.status == 200){
                success_handler?success_handler(resJson):'';
            }else if(resJson.status == 422){
                error_handler?error_handler(resJson):'';
            }
        },
        error:function (res){
            //console.log(res);
            alert("网络发生错误了！o");
            return false;
        }
    });
};
System.get = function (url,data,success_handler,error_handler){
    $.ajax({
        type : "GET",
        url : url,
        data : data,
        dataType : "json",
        xhrFields : {
            withCredentials : true
        },
        success:function (resJson){
            console.log(resJson);
            if(resJson.status == 200){
                success_handler?success_handler(resJson):'';
            }else if(resJson.status == 422){
                error_handler?error_handler(resJson):'';
            }
        },
        error:function (res){
            console.log(res);
            alert("网络发生错误了！");
            return false;
        }

    });
};
var formFile={};
//上传文件
formFile.post=function (url,data,success_handler,error_handler){
    $.ajax({
        type : "POST",
        url : url,
        data : data,
        dataType : "json",
        xhrFields : {
            withCredentials : true
        },
        contentType: false,  
        processData: false,
        success:function (resJson){
            console.log(resJson);
            if(resJson.status == 200){
                success_handler?success_handler(resJson):'';
            }else if(resJson.status == 422){
                error_handler?error_handler(resJson):'';
            }
        },
        error:function (res){
            console.log(res);
            alert("网络发生错误了！");
            return false;
        }
    });
};

var formFile={};
//上传文件
formFile.post=function (url,data,success_handler,error_handler){
    $.ajax({
        type : "POST",
        url : url,
        data : data,
        dataType : "json",
        xhrFields : {
            withCredentials : true
        },
        async:false,
        contentType: false,  
        processData: false,
        success:function (resJson){

            if(resJson.status == 200){
                success_handler?success_handler(resJson):'';
            }else if(resJson.status == 422){
                error_handler?error_handler(resJson):'';
            }
        },
        error:function (res){
            console.log(res);
            alert("网络发生错误了！");
            return false;
        }
    });
};
