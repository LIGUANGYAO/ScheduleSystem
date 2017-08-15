function makeNavigation(
            current,     //当前页数
            total_page,    //总页数
            getData,        //ajax请求函数
            show_id,        //分页条展示div ID
            num_links     //分页条长度的一半
    ) {

        if(total_page < 2) return;

        num_links = num_links? num_links: 3;  //导航条长度

        //存放导航条
        navicate = '<ul class="pagination">'; //返回第一页按钮

        //判断第一页是否有效
        if(current == 1)
            navicate += '<li class="disabled">';
        else
            navicate += '<li>';

        navicate +=         '<a onclick="' + getData + '(1)"> ' +
                                '<span><<</span>' +
                            '</a>' +
                        '</li>';


        //数字导航
        for(i = current - num_links; i <= current + num_links; i++) {
            if(i > 0 && i <= total_page) {
                if(i == current)
                    navicate += '<li class="active"><a onclick="' + getData + '(' + i + ')">' + i + '</a></li>';
                else
                    navicate += '<li><a onclick="' + getData + '(' + i + ')">' + i + '</a></li>';
            }
        }

        //判断最后一页是否有效
        if(current == total_page)
            navicate += '<li class="disabled">';
        else
            navicate += '<li>';

        navicate +=         '<a onclick="' + getData + '(' + total_page + ')">' +
                                '<span>>></span>' +
                            '</a>' +
                        '</li>' ;   //跳到最后一页按钮




        navicate +=     '<li>' +
                            '<span>第' + current + '页/共' + total_page + '页</span>' +
                        '</li>' +
                    '</ul>';


        $('#' + show_id).html(navicate);
    }