#ScheduleSystem

>By lyt123

`base_url`:http://119.29.77.37/scheduleSystem
Api AdminBase(管理员共同接口)
===
`ps`
下面的admin_level参数值统一为：1-教师，2-学院教务员，3-管理员
`ps`
###登录

`POST`
`/Admin/AdminBase/login`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
account|账号|Y
password|密码|Y
admin_level||Y
**Response**
`ps`
first_login参数值：1-第一次登录（调填写邮箱的接口），2-以前已登录过
`ps`
```json
{
    "status":200,
    "msg"："",
    "data":{//admin_level==1
        "id": "1",//有用（下载教师自己的课程表）
        "first_login": 1//有用
        "name": "柯贵文",//有用
        "account": "001000002",
        "position": "",
        "academy_value": "",
        "academy_id": "4",
    },
    "data":{//admin_level==2
        "name": "计算机学院教务员",//有用
        "first_login": 2,//有用
        "academy_id": "9"//有用（下载本学院课程表）
        "id": "1",
        "account": "admin",
        "academy_name" : "计算机学院"
    },
    "data":{//admin_level==3
        "first_login": 1,//有用
        "name": "admin"//有用
        "id": "2",
        "account": "admin",
    },
}
```
###修改密码

`POST`
`/Admin/AdminBase/updatePassword`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
pre_password|旧密码|Y
new_password|新密码|Y
admin_level||Y
**Response**
```json
{
    "status":200,
    "msg"："修改密码成功"
    "data":""
}
```
###第一次登录填写邮箱

`POST`
`/Admin/AdminBase/fillInEmail`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
email|邮箱|Y
admin_level|级别|Y|1-教师，2-学院教务员，3-管理员
**Response**
```json
{
    "status":200,
    "msg"："",
    "data":"ok"
}
```

###退出登录

`GET`
`/Admin/AdminBase/logout`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_level|级别|Y|1-教师，2-学院教务员，3-管理员
**Response**
```json
{
    "status":200,
    "msg"："退出登录成功"
    "data":""
}
```
###忘记密码
####1.发送邮件到邮箱

`POST`
`/Admin/AdminBase/forgetPasswordSendEmail`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
account|账号|Y
admin_level|管理员级别|Y|1-教师，2-学院教务员，3-管理员
**Response**
```json
{
    "status":200,
    "msg"："验证码已发送到您的邮箱：",
    "data":{
        "email": "11@11.com"//可以将此邮箱拼接到msg参数的末尾
  }
}
```
####2.检测验证码

`POST`
`/Admin/AdminBase/forgetPasswordCheckCode`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
security_code|验证码|Y
**Response**
```json
{
    "status":200,
    "msg"："验证码正确",
    "data":""
}
```
####3.修改密码

`POST`
`/Admin/AdminBase/forgetPasswordNewPassword`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
password|新密码|Y
admin_level|管理员级别|Y|1-教师，2-学院教务员，3-管理员
**Response**
```json
{
    "status":200,//验证成功，将用户的账号以及新密码（cookie保存）去请求登录接口，获得数据，跳转到已登录界面
    "msg"："修改密码成功",
    "data":""
}
```
Api Admin（管理员）
===
#一.开课管理
###0.1导入班级表
`POST`
`/Admin/SelectBook/importClassExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
excel|表格|Y|formData上传
**Response**
```json
{
    "status":200
}
```
###0.2导入开课一览表
`POST`
`/Admin/SelectBook/importSelectBook`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_confirmed|管理员确认|Y|1-未确认，2-已确认
excel|表格|Y|formData上传
**Response**
```json
//success
{
    "status":200
}
//fail
{
    "status": 422,
    "msg": "第n个课程没有匹配到班级",
    "detail": [
        "班级找不到：第1个课程（行政管理(村官班)-二年级专科-思想道德修养与法律基础）",
        "班级找不到：第4个课程（服装设计-二年级专科-思想道德修养与法律基础）",
        "班级找不到：第8个课程（机械制造及自动化-二年级专科-思想道德修养与法律基础）"]
}
```
###0.3开课一览表状态控制
`ps`
调用Api Admin 五、其他 6.管理员权限控制 接口
`ps`
###0.4为课程安排教师
####0.4.1查看已安排和未安排教师课程
`ps`
调用Api Student 课程查询课程接口，将此字段带过去
`ps`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
other_query|1|N|1-未安排，2-已安排
####0.4.1为未安排教师的课程安排教师
`ps`
调用Api Teacher&AcademyAdmin 教务员填写教师及职称、安排合班接口
`ps`
###0.5教师普教上课的时间
####0.5.1获取教师普教上课的时间
`GET`
`/Admin/SelectBook/getTeacherExistingClass`
**Response**
```json
{
    "status":200
}
```
####0.5.2查看教师普教上课时间
`GET`
`/Admin/SelectBook/showTeacherExistingClass`
teacher_name|教师姓名|N
**Response**
```json
{
    "status":200,
    "msg": "ok",
    "data": [
    {
        "id": "1635",
        "existing_class": "",
        "teacher_id": "9929",
        "semester": "0",
        "name": "王则蒿"
    },
    {
        "id": "1637",
        "existing_class": "第5-19周星期二晚上\第5-13周星期四晚上\第3-6周星期一晚上\第7-10周星期一晚上",
        "teacher_id": "9930",
        "semester": "0",
        "name": "朱涛"
    }
  ]
}
```
###0.7导出一个学期的开课一览表
`GET`
`/Admin/SelectBook/outputSelectBookOfOneSemester`
**Response**
网页提示下载
###0.8导出课程表模板
`GET`
`/Admin/SelectBook/outputCourseOfOneSemester`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
course_type|课程表类型|Y|1-非村官班，2-村官班
**Response**
网页提示下载
#二.课程管理
##1.导入excel表
###1.1导入可用教室表

`POST`
`/Admin/Admin/importClassroomTimeAvailable`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
excel|表格|Y|formData上传
**Response**
```json
{
    "status":200,
    "msg"："教室导入成功"
}
```
###1.2导入非村官班课程表

`POST`
`/Admin/Admin/importCourseExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
excel|表格|Y|formData上传
**Response**
```json
{
    "status":200,
    "msg"："导入课表成功"
}
```

###1.3导入村官班课程表

`POST`
`/Admin/Admin/importOfficialClassExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
excel|表格|Y|formData上传
**Response**
```json
{
    "status":200,
    "msg"："导入课表成功"
}
```
##2.查询课程表
`ps`
管理员可根据班级/学院/教师/课程名称查询课程，调用API Student的接口
`ps`
##3.导出课程表
`ps`
管理员可根据班级/教师/学院导出课程表，调用Api Student的导出课程表接口
`ps`
##4修改课程表
`POST`
`/Home/Course/updateCourse`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|课程id|Y
classroom_time_data||Y|如："周五晚上:2节，第18周，学楼203;周日下午:4节，第14、17周，马兰芳教学楼201;"
**Response**
```json
{
    "status":200,
}
```
##5.导出教室使用情况表
`GET`
`/Admin/Admin/outputClassroomUseConditionExcel`
**Response**
    网页直接提示下载
    
#三.考试管理
##1.节假日管理
###1.1添加
`POST`
`/Admin/Admin/addHoliday`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
week|第几周|Y
weekday|星期几|Y|数组形式，如[2, 5]代表星期2和星期五
**Response**
```json
{
    "status":200,
}
```
###1.2删除
`POST`
`/Admin/Admin/deleteHoliday`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|节假日id|Y
**Response**
```json
{
    "status":200,
}
```
###1.3修改
`POST`
`/Admin/Admin/updateHoliday`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|节假日id|Y
week|第几周|N
weekday|星期几|N|数组形式，如[2, 5]代表星期2和星期五
**Response**
```json
{
    "status":200,
}
```
###1.4查找
`GET`
`/Admin/Admin/showHoliday`
**Response**
```json
{
    "status":200,
    "data" : [
        {
            "id" : 1,
            "week" : "3",//第三周
            "weekday" : [3, 4]//星期三和星期四
        },
    ]
}
```
##2.公共课管理
###2.1添加
`POST`
`/Admin/Admin/addPublicCourse`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
name|公共课名称|Y
semester|学期|Y|1/2
period|考试时间|Y|单选，共11个选项，发送文字到该接口（星期一晚上，星期二晚上，星期三晚上，星期四晚上，星期五晚上，星期六上下晚，星期天上下晚）
**Response**
```json
{
    "status":200,
}
```
###2.2.删除
`POST`
`/Admin/Admin/deletePublicCourse`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|公共课id|Y
**Response**
```json
{
    "status":200,
}
```
###2.3.修改
`POST`
`/Admin/Admin/updatePublicCourse`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|公共课id|Y
name|公共课名称|Y
semester|学期|Y|1/2
period|考试时间|Y|同上
**Response**
```json
{
    "status":200,
}
```
###2.4.查找
`GET`
`/Admin/Admin/showPublicCourse`
**Response**
```json
{
    "status":200,
    "data" : [
        {
            "id" : 1,
            "name" : "毛泽东思想与邓小平理论"
            "period" : "星期六上午"
        },
    ]
}
```
##3.标记课程为已考试
###3.1标记
`POST`
`/Admin/Exam/markCourseIsExamed`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|课程id|Y|
**Response**
```json
{
    "status":200,
}
```
###3.2撤销标记
`POST`
`/Admin/Exam/markCourseNotExamed`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|课程id|Y|
**Response**
```json
{
    "status":200,
}
```
###3.3显示已标记课程
`GET`
`/Admin/Exam/showMarkExamedCourse`
**Response**
```json
{
    "status":200,
    "data" : [
        {
            "id": "409",
            "code": "003L3010",
            "name": "市场营销学",
            "teacher": "柯贵文",
            "class_name": "BY160301",
        },
    ]
}
```
##4.安排考试
###4.1自动安排考试
`POST`
`/Admin/Exam/automaticArrangeExam`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
week|第几周|Y|13或19
**Response**
```json
{
    "status":200,
}
```
###4.2清空考试安排
`POST`
`/Admin/Exam/clearExamArrangeData`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
week|第几周|Y|13或19
**Response**
```json
{
    "status":200,
}
```
##5.修改考试信息
###5.1修改教室
####5.1.1获得可排教室
`POST`
`/Admin/Exam/getAvailableClassroom`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|考试id|Y|从查询考试获得
**Response**
```json
{
    "status": 200,
    "msg": "ok",
    "data": [
        {
            "classroom_id": "8522",
            "classroom": "马兰芳教学楼103"
        },
    ]
}
```
####5.1.2修改考试教室
`POST`
`/Admin/Exam/updateExamClassroom`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|考试id|Y|从查询考试获得
classroom_id|教室id|N|上个接口获得的classroom_id
other_classroom|马兰芳教学楼以外的其他教室名称N
**Response**
```json
{
    "status":200,
}
```
###5.2修改时间
####5.2.1获得可排时间
`POST`
`/Admin/Exam/getAvailableTime`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|考试id|Y|
**Response**
```json
{
    "status": 200,
    "msg": "ok",
    "data": {
        "course_id": "1815",
        "week": "13",
        "time": [
            {
                "period": 1,
                "date": "11月21日晚上7:30"
            },
        ]
    }
}
```
####5.2.2修改时间
`POST`
`/Admin/Exam/updateExamTime`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
course_id|上面接口获得|Y|
week|上面接口获得|Y
period|上面接口,管理员选择的时间|Y
**Response**
```json
{
    "status":200,
}
```
##6.查询或导出课程考试
调Api Student考试接口
#四.工作量管理
##1.导入工作量汇总表
`POST`
`/Admin/OtherThing/importWorkQualityGatherExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_confirmed|管理员确认|Y|1-未确认，2-已确认
excel|表格|Y|formData上传
**Response**
```json
//success
{
    "status":200
}
//fail
{
    "status": 422,
    "msg": "第n个课程没有匹配到班级",
    "detail": [
        "班级找不到：第1个课程（行政管理(村官班)-二年级专科-思想道德修养与法律基础）",
        "班级找不到：第4个课程（服装设计-二年级专科-思想道德修养与法律基础）",
        "班级找不到：第8个课程（机械制造及自动化-二年级专科-思想道德修养与法律基础）"]
}
```
##2.查询工作量
`GET`
`/Admin/OtherThing/queryWorkQualityGather`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
teacher_name|教师名称|N|
academy_id|学院id|N|
**Response**
```json
{
    "status":200,
    "data" : [
        {
            "id": "1511",
            "academy_value": "",
            "academy_id": "6",
            "class": "AH06101、1001",
            "student_sum": "32",
            "paper_undergraduate": "0",
            "paper_special": "0",
            "year": "2006",
            "semester": "2",
            "name": "竞赛数学",
            "examine_way": "",
            "time_total": "46",
            "time_theory": "0",
            "time_practice": "0",
            "teacher_name": "张平",
            "teacher_position": "",
            "comment": "恩平",
            "work_quality": "64.4",
            "work_quality_sum": "",
            "academy": "数学与计算科学学院",
            "year_and_semester": "06-07-2",
            "time": "2007年上"
        },
    ]
}
```
##3.下载工作量
`GET`
`/Admin/OtherThing/outputWorkQualityGather`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
teacher_name|教师名称|N|
academy_id|学院id|N|
**Response**
网页提示下载
#五.其他
##1.学院管理
###1.1添加
`POST`
`/Admin/Admin/addAcademy`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
name|学院名称|Y
**Response**
```json
{
    "status":200,
}
```
###1.2删除
`POST`
`/Admin/Admin/deleteAcademy`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|学院id|Y
**Response**
```json
{
    "status":200,
}
```
###1.3修改
`POST`
`/Admin/Admin/updateAcademy`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|学院id|Y
name|学院名称|Y
**Response**
```json
{
    "status":200,
}
```
###1.4查找
`GET`
`/Admin/Admin/showAcademy`
**Response**
```json
{
    "status":200,
    "data" : [
        {
            "id" : 1,
            "name" : "计算机学院"
        },
    ]
}
```
##2.教师和教务员账号管理
`ps`
admin_level值 ： 1-教师，2-学院教务员
`ps`
###2.1添加
`POST`
`/Admin/Admin/addAdmin`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_level||Y
name|姓名|Y|
account|账号|Y
password|密码|Y|在文本框里默认填上123456
academy_id|学院id|N|将所有学院放在下拉框里
position|教师职称|N|学院教务员没有此字段
**Response**
```json
{
    "status":200,
    "msg"：""
}
```
###2.2修改
`ps`
让管理员输入教师账号，用账号去查询教师，取得数据之后显示在文本框里，让管理员修改
`ps`
`POST`
`/Admin/Admin/updateAdmin`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_level||Y
account|账号|Y|
name|姓名|N|
password|密码|N|在文本框里默认填上123456
academy_id|学院id|N|将所有学院放在下拉框里
position|教师职称|N|学院教务员没有此字段
**Response**
```json
{
    "status":200,
    "msg"：""
}
```
###2.3查询
`ps`
教师和教务员查询到的字段有不同
`ps`
`POST`
`/Admin/Admin/searchAdmin`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_level||Y
account|账号|N|
name|姓名|N|
academy_id|学院id|N|将所有学院放在下拉框里
page|当前页|Y
limit|限制条数|Y
**Response**
```json
{
    "status":200,
    "msg"：""
    "data" : {
        "sum" : "1000",
        "teacher_data" : [
            {
                "id": "904",
                "name": "林宇堂",
                "academy": "文学院"
                "account": "23324123",
                "position": "大文豪",
                "academy": "计算机学院",
                "academy_value": "",
                "email": "",
                "first_login": "1",
            },
        ]
    }
}
```
###2.4删除
`POST`
`/Admin/Admin/deleteAdmin`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_level||Y
account|账号|Y|
**Response**
```json
{
    "status":200,
    "msg"：""
    "data" : ""
}
```
###2.5导入表
`POST`
`/Admin/Admin/importAdminExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_level||Y
excel|教师表或学院教务员表，跟admin_level对应|Y|formData上传
**Response**
```json
{
    "status":200,
    "msg"：""
    "data" : ""
}
```
###2.6导出数据
`GET`
`/Admin/Admin/outputAdminExcel`
**Response**
网页提供下载
###2.7清空数据
`POST`
`/Admin/Admin/clearAdminData`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
admin_level||Y|清空教师或教务员在数据库中的数据
**Response**
```json
{
    "status":200,
    "msg"：""
    "data" : ""
}
```
##3.导出工作量汇总表
`GET`
`/Admin/Admin/outputWorkQualityGatherExcel`
**Response**
    网页直接提示下载

##4.年份、学期
###4.1填写当前年份、学期
`POST`
`/Admin/Admin/fillInYearAndSemester`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
year||Y|如2017
semester||Y|1或2
**Response**
```json
{
    "status":200
}
```
###4.2查看当前年份、学期
`GET`
`/Admin/Admin/showYearAndSemester`
**Response**
```json
{
    "status": 200,
    "msg": "ok",
    "data": {
        "year": 2017,
        "semester": 1
    }
}
```
##5.教学周次表
###5.1上传教学周次表
`POST`
`/Admin/OtherThing/uploadTeachingWeekExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
excel|表格|Y|formData上传
**Response**
```json
{
    "status":200
}
```
###5.2下载教学周次表
`GET`
`/Admin/OtherThing/downloadTeachingWeekExcel`
**Response**
网页提示下载
##6.管理员权限控制
###6.1.查看当前权限
`GET`
`/Admin/Admin/showStatus`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
type|类型|Y|1-开课一览表权限控制，2-考试安排权限控制
**Response**
```json
{
    "status": 200,
    "msg": "ok",
    "data": {
        "status": "3"
        //第一种情况（type=1）: 1-不让教师和教务员进行操作，2-让上课院系教务员安排教师和合班，让教师填写排课要求。
        第二种情况（type=2）: 1-不让开课院系教务员填写监考老师，2-让开课院系教务员填写十三周考试监考老师，3-让开课院系教务员填写十九周考试监考老师。
    }
}
```
###6.2.修改权限
`POST`
`/Admin/Admin/updateStatus`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
type||Y|同上
status||Y|
**Response**
```json
{
    "status":200
}
```
##7.清空当前课程和考试数据
`GET`
`/Admin/OtherThing/clearCourseDataOfOneSemester`
**Response**
```json
{
    "status":200
}
```
Api Teacher & AcademyAdmin
===
`ps`
 教师和教务员共用
`ps`
#一.开课管理
##1.下载教学周次表
`GET`
`/Admin/OtherThing/downloadTeachingWeekExcel`
**Response**
网页提示下载
##2.教务员
###1.上课院系教务员填写教师及职称、安排合班
`ps`
先调查询课程接口(查询字段为open_class_academy_id,字段值从登录时返回的academy_id获得,other_query字段跟管理员接口一样)，提供按照课程名称、教师、班级排序
`ps`
`POST`
`/Admin/SelectBook/academyAdminArrangeCombineClass`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
teacher|教师,可多个|Y|如:[{"name" : "刘佳", "position" : "讲师"},{"name" : "刘佳", "position" : "讲师"}]
course_ids|班级id,可多个|N|如:["1233","3214"]
**Response**
```json
{
    "status":200,
}
```
###2.上课院系教务员取消课程合班安排
`ps`
在上面的接口每行后面加个取消安排的按钮
`ps`
`POST`
`/Admin/SelectBook/academyAdminCancelCombineClass`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|课程id|Y
**Response**
```json
{
    "status":200,
}
```
##3.教师
###3.1获取教师课程
调用Api Student查询课程接口
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
teacher_name_for_teaching_task|教师姓名|Y|从登录时返回的name字段获得
page|设置为1
limit|设置为20
###3.2教师填写排课要求
`POST`
`/Admin/SelectBook/teacherFillInCourseRequire`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
course_id|课程id|Y|
check_way|考核方式|Y|（1）笔试；（2）口试；（3）机试；（4）课程论文；（5）大作业；（6）其它
time_media|使用多媒体教学学时数|Y|int
time_in_class|课内实验学时数|Y|int
require|备注                     （需要使用机房或专用教室的任课教师请自行联系相关管理部门，并在此处注明具体时间、地点）|Y
**Response**
```json
{
    "status":200,
}
```
##4.下载教学任务书
`ps`
学院教务员下载教学任务书不需要发送参数，后台根据session确定当前学院
`ps`
`GET`
`/Admin/OtherThing/downloadTeachingTaskExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
teacher_name|教师姓名|N|
**Response**
网页提示下载
#二.课程管理

##1.查询课程表
`ps`
管理员可根据班级/学院/教师/课程名称查询课程，调用API Student的接口
`ps`
##2.导出课程表
`ps`
管理员可根据班级/教师/学院导出课程表，调用Api Student的导出课程表接口
`ps`
#三.考试管理
##1.开课院系教务员填写监考教师
`调用查询课程考试接口，发送的字段为open_class_academy_id显示相应学院的考试安排`
`POST`
`/Admin/Exam/updateMonitorTeacher`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
id|该条考试信息的id|Y|
monitor_teacher_name|监考教师姓名|Y
**Response**
```json
{
    "status":200,
}
```
##2.查询考试
调Api Student考试接口
##3.导出考试
调Api Student考试接口
#四.其他
##1.查询工作量
`GET`
`/Admin/OtherThing/queryWorkQualityGather`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
teacher_name|教师名称|Y|
academy_id|学院id|N
**Response**
```json
{
    "status":200,
    "data" : [
    {
        "id": "56",
        "academy": "机电系",
        "class": "AY05081",
        "student_sum": "45",
        "paper_undergraduate": "0",
        "paper_special": "0",
        "semester": "06-07-2",
        "name": "机械基础实践",
        "examine_way": "",
        "time_total": "0",
        "time_theory": "0",
        "time_practice": "0",
        "teacher_id": "6778",
        "teacher_position": "",
        "student_sum_factor": "",
        "time_factor": "",
        "comment": "",
        "work_quality": "61.5",
        "work_quality_sum": "",
        "time": "2007年上"
    },
}
```
##2.下载工作量
`GET`
`/Admin/OtherThing/outputWorkQualityGather`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
teacher_name|教师名称|N|
academy_id|学院id|N
**Response**
网页提示下载
Api Student(学生，不用登录)
===
#根据班级类型获取班级
`GET`
`/Home/Course/getClassByClassType`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
type|班级类别|Y|专升本-1,专科-2,村官班-3
**Response**
```json
{
    "status":200,
    "msg"："operate successfully"
    "data" : ["AY150101","AY160101"]
}
```
#根据院系获取班级及课程名称
`GET`
`/Home/Course/getClassCourseByAcademy`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
academy_id|院系id|Y|
**Response**
```json
{
    "code":200,
    "msg"："operate successfully"
    "data" : [
        {
            "id" : "1",
            "name" : "国际贸易实务",//课程名称
            "class_name" : "AY150101"
        },
    ]
}
```
#课程
##1.查询课程
`GET`
`/Home/Course/queryCourse`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
class_name|班级名称|N|
academy|学院名称|N|
academy_id|学院id|N|从教务员登录时获取
code|课程代号|N
name|课程名称|N
teacher|教师姓名|N
page|当前页|Y|
limit|限制条数|Y|
order|排序|N|name/class_id/teacher_id/academy/code
open_class_academy_id|开课院系|N|从教务员登录时获取
other_query|1|N|1-未安排，2-已安排
**Response**
```json
{
    "code":200,
    "msg"："operate successfully"
    "data" : {
        "sum" : "189",
        "course_data" : [
         {
            "id": "11124",
            "class_id": "1258",
            "code": "006L1800",
            "name": "自动控制系统",
            "examine_way": "考查",
            "time_total": "40",
            "time_theory": "36",
            "time_practice": "4",
            "combine_status": "",
            "comment": "",
            "academy_id": "8",
            "teacher_id": "10323",
            "exam_status": "1",
            "week_section_ids": "1,2",
            "open_class_academy_id": "0",
            "teacher_require_id": "4918",
            "semester": "1",
            "open_class_academy_value": "",
            "have_class_academy_value": "信息工程学院",
            "profession": "",
            "teacher_require_data": {
                "id": "4918",
                "check_way": "笔试",
                "time_media": "3",
                "time_in_class": "6",
                "require": "排周一到周五"
            },
            "open_class_academy": null,
            "academy": null,
            "class_name": "BY150401",
            "student_sum": "14",
            "teacher": "贺跃帮",
            "teacher_position": "",
            "teacher_existing_class": "",
            "classroom_time": [
                {
                    "week": "第5,7-16,18周",
                    "weekday": "周一晚上：3节",
                    "classroom": "马兰芳教学楼203"
                },
            ]
        },
    ]
    }
}
```
##2.导出课程表
`GET`
`/Home/Course/outputCourseExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
teacher_id|教师id|Y|这里让管理员输入教师姓名，去调上面的‘教师和教务员管理’的查找接口，列举出教师，管理员选中姓名，再调此接口（考虑到教师重名）
academy_id|学院id|Y|
name|班级名称，如“AY150101”|Y|
#考试
##1.查询考试信息
`ps`
这里没做分页
`ps`
`POST`
`/Admin/Exam/showExam`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
week|考试周数|Y|13或19
class_name|班级名称|N|
open_class_academy|开课学院名称|N|
have_class_academy|上课学院名称|N|
code|课程代号|N
name|课程名称|N
teacher_name|教师姓名|N
order|排序|Y|teacher,class,course_name
open_class_academy_id|开科学院id|N
**Response**
```json
{
    "status":200,
    "data":[
        {
            "id": "12962",
            "open_class_academy": "经济管理学院",
            "have_class_academy": "经济管理学院",
            "class_name": "BY160301",//修改考试显示
            "student_sum": "23",//修改考试显示
            "code": "003L3030",//修改考试显示
            "name": "基础会计学",//修改考试显示
            "examine_way": "考试",
            "time_total": "32",
            "teacher_name": "刘昭满",//修改考试显示
            "time": "01月08日晚上7:30",//修改考试显示
            "classroom": "马兰芳教学楼103",//修改考试显示
            "monitor_teacher_name" : "凌德全"
            "is_public_course" : 1,//1-不是，2-是
        },
    ]
}
```
##2.导出考试安排表
`GET`
`/Admin/Exam/outputExamExcel`
字段 | 描述 | 是否必须 | 备注 
------------- | ---------------- | ----------------- | ------------ 
week|考试周数|Y|13或19
class_name|班级名称|N|
open_class_academy|开课学院名称|N|
have_class_academy|上课学院名称|N|
teacher_name|教师姓名|N
**Response**
网页提示下载


