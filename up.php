<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

$err = "";
$url = "";

if (isset($_POST['t'], $_POST['token'], $_POST['p'], $_POST['uppass'], $_FILES['upfile']))
{
  while (true)
  {
    if (!is_numeric($_POST['t']) || $_POST['t'] < time()-60*5) { $err = 'Access denied.'; break; }
    if ($_POST['token'] != md5(TOKEN_SALT.$_POST['t'])) { $err = 'Incorrect token.'; break; }
    if ($_POST['uppass'] != ADMIN_PASS) { $err = 'Incorrect upload password.'; break; }
    $f = $_FILES['upfile'];
    if (!is_array($f) || !isset($f['tmp_name'], $f['name'], $f['size']) || !is_uploaded_file($f['tmp_name'])) { $err = 'File uploading has failed.'; break; }

    $key = substr(md5(uniqid(rand())), 0, 16);
    while (is_file(CABINET_DIR.$key)) { $key = substr(md5(uniqid(rand())), 0, 16); }
    $path = CABINET_DIR.$key;
    if (!@move_uploaded_file($f['tmp_name'], $path)) { $err = 'File saving has failed.'; break; }

    $info = parse_ini_file(CABINET_DIR.'files.ini', true);
    $info[$key] = array('name' => $f['name'], 'time' => time(), 'pass' => 'sha256:'.hash_hmac('sha256', $_POST['p'], TOKEN_SALT));
    $ini_str = array();
    foreach ($info as $k => $v)
    {
      $ini_str[] = '['.$k.']';
      foreach ($v as $j => $d)
      {
        $ini_str[] = sprintf('%s = %s', $j, $d);
      }
      $ini_str[] = '';
    }

    if (!@file_put_contents(CABINET_DIR.'files.ini', join("\r\n", $ini_str))) { $err = 'Could not save file info.'; break; }
    $url = DOWNLOAD_URL_BASE.$key;
    break;
  }
}

$t = time();
$token = md5(TOKEN_SALT.$t);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-script-type" content="text/javascript; charset=UTF-8" />
<meta http-equiv="content-style-type" content="text/css; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow,noarchive" />
<title></title>
<style type="text/css">
body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,textarea,select,option,p,blockquote,table,th,td,address,caption,cite,code,dfn,em,strong,var{margin:0;padding:0;font-size:100%;font-style:normal;font-weight:normal;word-break:break-all;word-wrap:break-word;}
body {font-family: "メイリオ","ＭＳ Ｐゴシック","ヒラギノ丸ゴ Pro W4", Osaka, sans-serif; font-size:10pt;}
input,textarea,th,td,select,option,optgroup{font-family: "メイリオ","ＭＳ Ｐゴシック", Tahoma, Osaka,"ヒラギノ丸ゴ Pro W4", sans-serif;font-size:10pt;}
input.text,input.password,textarea{margin:2px;padding:2px;border:1px solid #666;border-color:#666 #bbb #bbb #666;}
input.submit { margin:5px;padding:2px 7px; }
textarea {display:block;overflow:auto}
th,td,img,input,select{vertical-align:middle;}
table th, table td { text-align: left; }
</style>
</head><body>
<form action="up" method="post" enctype="multipart/form-data" style="margin:10px">
<input type="hidden" name="token" value="<?php echo $token; ?>" />
<input type="hidden" name="t" value="<?php echo $t; ?>" />
<table>
  <tr><th>*Upload file:</th><td><input type="file" name="upfile" /></td></tr>
  <tr><th>*Password for upload:</th><td><input type="password" name="uppass" size="10" class="password"/></td></tr>
  <tr><th>*Password for download:</th><td><input type="password" name="p" size="10" class="password" /></td></tr>
  <tr><th></th><td><input type="submit" class="submit" value="Upload" /></td></tr>
</table>
<div style="color:red"><?php echo $err ? '[ERROR] '.$err : ''; ?></div>
<?php if ($url): ?>
<div style="margin:5px;padding:10px;background:#ffdbd6;border:1px solid #ff6b51;font-size:12pt;">
  <strong style="font-weight:bold">Uploading success.</strong> <span style="font-size:10pt">Download-URL has generated. Please copy the following URL.</span><br />
  <input type="text" value="<?php echo $url; ?>" style="font-size:12pt;width:600px" class="text" readonly onclick="this.select()" />
</div>
<?php endif; ?>
[ <a href="list">list of files</a> ]
</form>
</body>
</html>
