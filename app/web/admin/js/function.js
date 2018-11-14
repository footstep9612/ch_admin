/**
 * Created by jintao on 2017/7/3.
 */
function filter_empty(data){
    for(var i = 0;num=data.length,i<num;i++){
        if($.trim(data[i])==''){
            data.splice(i,1);
            i=i-1;
        }
    }
    return data;
}

function checkCredit(data){
    $.post("?c=loan&a=checkCreditId",{
        'credit_id':data,
    },function( response ){
        if(response.error==100) {
            throwExc(response.msg);
            $("#credit_name").val("");
            return false;
        }else if(response.error==200) {
            $("#credit_name").val(response.data.credit_name);
        }
    },"json");
    return;
}

function goBack(){
    window.history.back();
}

function redirect(url){
    window.location.href=url;
}

function del(url,id){
    if(confirm('你确定要删除吗?'))
    $.post(url,{
        'id':id,
    },function( response ){
        if(response.error==100) {
            throwExc('删除失败');
            return false;
        }else if(response.error==200) {
            showSucc('删除成功');
            window.location.reload();
        }
    },"json");
}