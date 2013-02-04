<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

function h($str){ return htmlentities($str, ENT_QUOTES, 'UTF-8'); }

if (isset($_POST['f'])) { $f = $_POST['f']; }
else if (isset($_GET['f'])) { $f = $_GET['f']; }
else { $f = ''; }
$t = time();
$token = md5($t.TOKEN_SALT);
$err = '';

while (true)
{
  if (!$f || !preg_match('/^[0-9a-z]+$/i', $f)) { break; }
  if (!isset($_POST['token'], $_POST['t'], $_POST['p']) || !preg_match('/^\d+[a-z0-9]{32}$/', $_POST['t'].$_POST['token'])) { break; }
  if (!is_numeric($_POST['t']) || $_POST['t'] < time()-60*5 || md5($_POST['t'].TOKEN_SALT) !== $_POST['token']) { $err = 'Access denied.'; break; }

  $p = $_POST['p'];
  $file = CABINET_DIR.$f;
  $info = parse_ini_file(CABINET_DIR.'files.ini', true);

  if (!is_file($file)) { $err = 'Not found.'; break; }
  if (!isset($info[$f], $info[$f]['name'], $info[$f]['pass'])) { $err = 'Could not found file info.'; break; }
  
  if (preg_match('/^(.+?):(.+)$/', $info[$f]['pass'], $m) && in_array($m[1], hash_algos()))
  {
    $p = $m[1].':'.hash_hmac($m[1], $p, TOKEN_SALT);
  }

  if ($p !== $info[$f]['pass']) { $err = 'Incorrect password.'; break; }

  $filesize = filesize($file);
  $disposition = "Content-Disposition: attachment; filename=".$info[$f]['name'];
  $length      = "Content-length: ".filesize($file);
  $type        = "Content-type: octet-stream";  
  if(!preg_match("/Opera/", $_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
    mb_convert_variables("SJIS-win", "UTF-8", $disposition);
  } else {
    $type .= '; Charset=UTF-8';
  }

  header($disposition);
  header($length);
  header($type);
  header("Cache-Control: public");
  header("Pragma: public");
  readfile($file);
  exit;
  break;
}
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
</style>
</head><body style="padding:10px">
<form action="<?php echo $f ?>" method="post">
<?php if (!$f): ?>
*File-key: <input type="text" class="text" name="f" value="" size="12" autocomplete="off"  /><br />
<?php endif ?>
<input type="hidden" name="token" value="<?php echo $token; ?>" />
<input type="hidden" name="t" value="<?php echo $t; ?>" />
*Password: <input class="password" type="password" name="p" autocomplete="off" size="12" />
<input type="submit" value="Download" class="submit"  /> 
<div style="color:red"><?php echo $err ? '[ERROR] '.$err : ''; ?></div>
</form>
</body></html>
