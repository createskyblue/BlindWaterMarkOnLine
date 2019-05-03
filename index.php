<!DOCTYPE html >
<?php
set_time_limit(0);
ob_implicit_flush();
ob_end_clean();
header('X-Accel-Buffering: no'); // 关键是加了这一行。
?>
<html lang="en">
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/style.css" media="screen" type="text/css" />
<link rel="stylesheet" href="css/buttons.css"/>
<link rel="stylesheet" href="css/jquery-labelauty.css">
<link rel="stylesheet" type="text/css" href="css/default.css" />
<link rel="stylesheet" type="text/css" href="css/component.css" />
<script src="js/modernizr.custom.js"></script>
<title>盲水印在线制作</title>
</head>
<?php
function random($length){
  $captchaSource = "0123456789abcdefghijklmnopqrstuvwxyz这是一个随机打印输出字符串的例子";
  $captchaResult = "2019"; // 随机数返回值
  $captchaSentry = ""; // 随机数中间变量
  for($i=0;$i<$length;$i++){
    $n = rand(0, 35); #strlen($captchaSource));
    if($n >= 36){
      $n = 36 + ceil(($n-36)/3) * 3;
      $captchaResult .= substr($captchaSource, $n, 3);
    }else{
      $captchaResult .= substr($captchaSource, $n, 1);
    }
  }
  return $captchaResult . ".png";
}
function RunCmd($cmd,$BoolBr){
    echo '<br>> ';
    echo $cmd;
    $output = shell_exec($cmd);
    $array = explode(',', $output);
    foreach ($array as $value) {
      echo $value;
      if ($BoolBr) echo "<br>";
    }
  return ;
}
/* 检查是否为锁定状态 */
$_createpath = iconv('utf-8', 'gb2312', 'server.lock');
if (file_exists($_createpath) == true||shell_exec("python cpu.py")>20) {
?>
<body>
    <div class="md-content">
		<h3>提示</h3>
		<div>
			<p>qwq! 服务器正在处理其他用户请求 建议3分钟后刷新页面</p>
		</div>
	</div>
</body>
<?php
}else{
    
if (isset($_FILES['SourcePhoto']) && isset($_FILES['watermark']) && is_uploaded_file($_FILES['SourcePhoto']['tmp_name'])&& is_uploaded_file($_FILES['watermark']['tmp_name']))
    {
            $imgFile_SourcePhoto = $_FILES['SourcePhoto'];
            $upErr_SourcePhoto = $imgFile['error'];
            $imgFile_watermark = $_FILES['watermark'];
            $upErr_watermark = $imgFile['error'];
            if ($upErr_SourcePhoto == 0 && $upErr_watermark==0) {   
            $imgType_SourcePhoto = $imgFile_SourcePhoto['type']; //文件类型。
            $imgType_watermark = $imgFile_watermark['type'];
            /* 判断文件类型*/
            if ($imgType_watermark == 'image/png'&& $imgType_SourcePhoto == 'image/png'||$imgType_watermark == 'image/jepg'&& $imgType_SourcePhoto == 'image/jepg'||$imgType_watermark == 'image/jpg'&& $imgType_SourcePhoto == 'image/jpg'||1)
            {    
                $imgFileName_SourcePhoto = random(16);
                $imgSize_SourcePhoto = $imgFile_SourcePhoto['size'];
                $imgTmpFile_SourcePhoto = $imgFile_SourcePhoto['tmp_name'];
                
                $imgFileName_watermark = random(16);
                $imgSize_watermark = $imgFile_watermark['size'];
                $imgTmpFile_watermark = $imgFile_watermark['tmp_name'];
                /* 将文件从临时文件夹移到上传文件夹中。*/
                move_uploaded_file($imgTmpFile_SourcePhoto, '/tmp/'.$imgFileName_SourcePhoto);
                move_uploaded_file($imgTmpFile_watermark, '/tmp/'.$imgFileName_watermark);
                ?>
                
                <head>
		        	<div class="md-content">
		    		<h3>控制台</h3>
		    		<div>
		    		    <?php
		    		    /*显示上传后的文件的信息。*/
                            $ImgOutputName=random(16);
                            $strPrompt = sprintf("原图%s 以及 水印%s 上传成功<br>"
                            . "生成图片: %s<br>"
                            . "原图大小: %s字节<br>"
                            . "水印大小: %s字节<br>"
                            , $imgFileName_SourcePhoto, $imgFileName_watermark, $ImgOutputName, $imgSize_SourcePhoto,$imgSize_watermark);
                            echo $strPrompt;
                            $OutputMode=$_POST['mode'];
                            echo '<br>> 正在处理图像!';
                            echo '<br>  开源组件来自：https://github.com/chishaxie/BlindWaterMark';
                            RunCmd(sprintf("python bwm.py %s /tmp/%s /tmp/%s tmp/%s 2>&1",$OutputMode, $imgFileName_SourcePhoto, $imgFileName_watermark, $ImgOutputName),1);
                            /*输出顺序分成两个  为了调节用户心态*/
                            if (file_exists(sprintf("tmp/%s" ,$ImgOutputName))) {
                                echo '<br>> 清理缓存区图片';
                                RunCmd(sprintf("rm /tmp/%s",$imgFileName_SourcePhoto),0);
                                RunCmd(sprintf("rm /tmp/%s",$imgFileName_watermark),0);
                                $imgFileName_out = sprintf("<img src='tmp/%s'>" ,$ImgOutputName);
                                echo '<br>> 操作成功完成';
                                $down_host = $_SERVER['HTTP_HOST'].'/'; //当前域名
                                echo '<br> 下载链接： '.'http://'.$down_host.'tmp/'.$ImgOutputName;
								echo '<br>';
								echo $imgFileName_out;
                            }else{
                                echo '<br>> 操作失败';
                                echo '<br>> 清理缓存区图片';
                                RunCmd(sprintf("rm /tmp/%s",$imgFileName_SourcePhoto),0);
                                RunCmd(sprintf("rm /tmp/%s",$imgFileName_watermark),0);
                            }
		    		    ?>
			    		<ul>
			    		</ul>
			    	</div>
			        </div>
	        
                </head>
                <?php
            }else{
		?>
		<div class="md-content">
				<h3>提示</h3>
				<div>
					<p>类型错误</p>
					<ul>
						<li>只接受png或jpeg类型的图片</li>
					</ul>
				</div>
			</div>
		<?php
            }
            }else{    
                echo "文件上传失败。<br>";
                $ERRCODE = sprintf("$s-$s", $upErr_SourcePhoto, $upErr_watermark);
                echo "错误代码:";
                echo $ERRCODE;
                }
              
}else{
/*显示表单。*/
?>
<style type="text/css">                 
            *{
                margin:0;
                padding:0;
                font-size:12px;
            }
            .wrap{
                position:relative;
                overflow:hidden;
                margin-right:4px;
                display:inline-block;
                padding:4px 10px;
                line-height:18px;
                text-align:center;
                vertical-align:middle;
                cursor:pointer;
                background:#D8450B;
                border:1px dotted #D8450B;
                border-radius:4px;
                -webkit-border-radius:4px;
                -moz-border-radius:4px;
            }
            .wrap span{             
                color:#FFF;
            } 
            .wrap .file{
                position:absolute;
                top:0;
                right:0;
                margin:0;
                border:solid transparent;
                opacity:0;
                filter:alpha(opacity=0);
                cursor: pointer;
            } 
            ul { list-style-type: none;}
            li { display: inline-block;}
            li { margin: 10px 0;}
input.labelauty + label { font: 12px "Microsoft Yahei";}
</style>
<body>
<center>
		<div class="md-modal md-effect-1" id="modal-1">
			<div class="md-content">
				<h3>提示</h3>
				<div>
					<p>分辨率请小于1080P 否则可能溢出</p>
					<ul>
						<li><strong>提示：</strong>上传后大概会卡顿几分钟没有反应 这是正常现象，请不要刷新画面，否则无输出</li>
						<li><strong>制作：</strong>上传原图片和你所希望的水印图片 水印图片要小于原图片 确保格式为png 否则无输出</li>
						<li><strong>提取：</strong>上传原图片和被处理后的水印图片 两张图片分辨率必须相同 否则无输出</li>
					</ul>
					<button class="md-close">关闭</button>
				</div>
			</div>
		</div>
		<div class="md-modal md-effect-1" id="modal-2">
			<div class="md-content">
				<h3>提示</h3>
				<div>
					<p>正在执行操作</p>
					<ul>
						<li>请勿关闭或刷新页面 大约需要1分钟</li>
					</ul>
					<button class="md-close">确认</button>
				</div>
			</div>
		</div>
		
		
				<div style="text-align:center;clear:both;">
<script src="/gg_bd_ad_720x90.js" type="text/javascript"></script>
<script src="/follow.js" type="text/javascript"></script>
</div>
		<div class="main clearfix">
		<button class="button button-block button-rounded button-primary button-large md-trigger" data-modal="modal-1">盲水印在线制作</button>
</div>
<form action="" method="post" enctype="multipart/form-data" name="upload_form">
 <br/><br/>
  <ul class="dowebok">
	<li><input type="radio" name="mode" value="encode" data-labelauty="制作水印" checked></li>
	<li><input type="radio" name="mode" value="decode" data-labelauty="提取水印 "></li>
	<li><input type="radio" name="mode" disabled data-labelauty="不可用"></li>
</ul>
  <br/><br/>
          <div class="wrap">
             <span>原图片</span>
             <input class="file" name="SourcePhoto" type="file" accept=""/>
          </div>&nbsp;&nbsp;&nbsp;&nbsp;
          
             
         <div class="wrap">
             <span>水印图</span>
             <input class="file" name="watermark" type="file" accept=""/>
         </div>
     <br/><br/>
    <button name="Upload" class="button button-glow button-rounded button-caution md-trigger"  data-modal="modal-2">大快人心</button>
</form>

<script src="js/jquery-1.8.3.min.js"></script>
<script src="js/jquery-labelauty.js"></script>
<div class="md-overlay"></div>
		<script src="js/classie.js"></script>
		<script src="js/modalEffects.js"></script>
<script>
$(function(){
	$(':input').labelauty();
});
var polyfilter_scriptpath = '/js/';
</script>
</center>
<script src="js/index.js"></script>
<script src="js/cssParser.js"></script>
<script src="js/css-filters-polyfill.js"></script>
</body>
<?php
 }
}
?>
</html>
