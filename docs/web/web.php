<?php
include('simple_html_dom.php');
function web_encode($newurl,$currenturl){
    //$currenturl=explode('?',explode('#',$currenturl)[0])[0];
    if (explode('?',explode('#',$currenturl)[0])[0]=="https://www.google.com/search") {
        if (mb_substr($newurl,0,15)=="/url?q=https://") {
            $newurl=explode('&',mb_substr($newurl,15))[0];
            //encode
            $turnto="";
            foreach(str_split($newurl) as $sm){
                $turnto = $sm.$turnto;
            }
            $turnto=str_replace(".","~",str_replace("%","@",rawurlencode($turnto)));
            $newurl="?i=0&url=".$turnto;
            return $newurl;
        }
    }
    if(strpos($newurl,'data:')===0||strpos($newurl,'javascript:')===0){
        return $newurl;
    }elseif (strpos($newurl,'https://')===0||strpos($newurl,'http://')===0||strpos($newurl,'//')===0) {
        $newurl=mb_substr($newurl,strpos($newurl,'//')+2);
    }elseif (strpos($newurl,'/')===0) {
        $newurl=explode('/',$currenturl)[2].$newurl;
    }elseif (strpos($newurl,'?')===0) {
        $newurl=explode('?',$currenturl)[0].$newurl;
    }elseif (strpos($newurl,'#')===0) {
        $newurl=explode('#',$currenturl)[0].$newurl;
    }else {
        $newurl=mb_substr($currenturl,0,mb_strlen($currenturl)-mb_strlen(explode('/',$currenturl)[sizeof(explode('/',$currenturl))-1])).$newurl;
    }
    //$newurl=explode('https://',$newurl)[1];//remove "https://"
    $turnto="";
    foreach(str_split($newurl) as $sm){
        $turnto = $sm.$turnto;
    }
    $turnto=str_replace(".","~",str_replace("%","@",rawurlencode($turnto)));
    $newurl="?i=0&url=".$turnto;
    return $newurl;
    /*
    http://c.biancheng.net/view/7412.html
    https://blog.csdn.net/w36680130/article/details/103709427
    */
}
try{
    //decode
    $myget = rawurldecode(str_replace("@","%",str_replace("~",".",$_GET['url'])));
    $turnget = "";
    foreach(str_split($myget) as $sm){
        $turnget = $sm.$turnget;
    }
    $url = "https://".$turnget;
    //r标识read,即标识只读
    $result=file_get_html($url);
    if($_GET['i']=='-1'){
        foreach($result->find('link') as $e)
            $e->href = web_encode($e->href,$url);
        foreach($result->find('meta') as $e)
            $e->content = web_encode($e->content,$url);
    }
    foreach($result->find('a') as $e){
        $e->href = web_encode($e->href,$url);
        $e->target = "_blank";
    }
    foreach($result->find('script') as $e)
        if($e->src!='')
            $e->src = web_encode($e->src,$url);
    foreach($result->find('form') as $e)
        $e->action = "google.html";
    if (explode('?',explode('#',$url)[0])[0]=="https://www.google.com/search") {
        $result->find('div.x',0)->innertext="×";//input cross
    }
    
    $result=$result."";
    echo $result;
}catch(Exception $e){
    echo '<script>location.href="google.html";</script>';
}
?>
<script type="text/javascript">
    var is_ctrl=false;
    var original_down=document.body.onkeydown;
    var original_up=document.body.onkeyup;
    var new_down=function (event) {
        if (event.keyCode==17&&!is_ctrl) {
            //is ctrl
            is_ctrl=true;
            var all_a=document.getElementsByTagName('a');
            for (let i = 0; i < all_a.length; i++) {
                var in_url=all_a[i].href;
                if (in_url.indexOf('javascript:')*in_url.indexOf('data:')!=0&in_url.indexOf(location.origin+location.pathname)==0) {
                    in_url=decodeURIComponent(in_url.substr((location.origin+location.pathname).length+9).replaceAll("~",".").replaceAll("@","%"));
                    var en_url='';
                    for (const j in in_url) {
                        en_url=in_url[j]+en_url;
                    }
                    all_a[i].href='//'+en_url;
                }
            }
        }
        original_down();
    }
    var new_up=function (event) {
        if (event.keyCode==17&&is_ctrl) {
            //not ctrl
            is_ctrl=false;
            var all_a=document.getElementsByTagName('a');
            for (let i = 0; i < all_a.length; i++) {
                var in_url=all_a[i].href;
                if (in_url.indexOf('javascript:')*in_url.indexOf('data:')!=0) {
                    var in_url=in_url.substr(in_url.indexOf('//')+2);//remove 'https://', 'http://', 'file://'
                    console.log(in_url);
                    var en_url="";
                    for (const i in in_url) {
                        en_url=in_url[i]+en_url;
                    }
                    en_url=encodeURIComponent(en_url).replaceAll("%","@").replaceAll(".","~");
                    all_a[i].href='?i=0&url='+en_url;
                }
            }
        }
        original_up();
    }
    document.body.onkeydown=new_down;
    document.body.onkeyup=new_up;
</script>
<script>document.body.innerHTML=document.body.innerHTML.replaceAll("�","&nbsp&nbsp");</script>