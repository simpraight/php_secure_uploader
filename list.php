<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

function h($str){ return htmlentities($str, ENT_QUOTES, 'UTF-8'); }

$err = "";
$info = false;

if (isset($_POST['t'], $_POST['token'], $_POST['p']))
{
  while (true)
  {
    if (!is_numeric($_POST['t']) || $_POST['t'] < time()-60*5) { $err = 'Access denied.'; break; }
    if ($_POST['token'] != md5(TOKEN_SALT.$_POST['t'])) { $err = 'Incorrect token.'; break; }
    if ($_POST['p'] != ADMIN_PASS) { $err = 'Incorrect password.'; break; }
    if (!$info = parse_ini_file(CABINET_DIR.'files.ini', true)){ $err = 'Could not read files info.'; break; }

    if (isset($_POST['delete'], $info[$_POST['delete']]))
    {
      $did = $_POST['delete'];
      unset($info[$did]);

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
      @unlink(CABINET_DIR.$did);
    }

    $info = array_reverse($info);
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
table { border-collapse: collapse; }
table th, table td { text-align: left; border:1px solid #bbb;padding: 3px; }
table thead th { background: #ccc; }
</style>
</head><body style="padding:10px;">
<?php if (!$info): ?>
<form action="list.php" method="post" enctype="multipart/form-data" style="margin:10px">
<input type="hidden" name="token" value="<?php echo $token; ?>" />
<input type="hidden" name="t" value="<?php echo $t; ?>" />
*Password: <input type="password" class="password" name="p" size="10" />
<input type="submit" class="submit" value="Show" />
<div style="color:red"><?php echo $err ? '[ERROR] '.$err : ''; ?></div>
</form>
<?php else: ?>
<form action="list.php" method="post" enctype="multipart/form-data" style="margin:10px">
<input type="hidden" name="token" value="<?php echo $token; ?>" />
<input type="hidden" name="t" value="<?php echo $t; ?>" />
*File-ID: <input type="text" class="text" name="delete" size="12" id="delete_id" />
*Password: <input type="password" class="password" name="p" size="10" />
<input type="submit" class="submit" value="Delete" />
<div style="color:red"><?php echo $err ? '[ERROR] '.$err : ''; ?></div>
</form>

<table>
  <col style="width:180px" />
  <col style="width:400px" />
  <col style="width:100px" />
  <col style="width:140px" />
  <thead>
    <tr>
      <th>ID<span style="color:#666;font-size:8pt"> (Click to enter the ID)</span></th>
      <th>NAME<span style="color:#666;font-size:8pt"> (Click to download)</span></th>
      <th>SIZE</th>
      <th>TIMESTAMP</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($info as $id => $file): ?>
    <tr>
      <td><a href="javascript:void(0)" onclick="set_delete_id(this)"><?php echo h($id); ?></a></td>
      <td>
        <a href="<?php echo DOWNLOAD_URL_BASE; ?><?php echo h($id); ?>"><?php echo h($file['name']); ?></a>
        <div style="font-size:8pt;color:gray"><?php echo DOWNLOAD_URL_BASE; ?><?php echo h($id); ?></div>
      </td>
      <td style="text-align:right"><?php echo number_format(filesize(CABINET_DIR.$id)/1024); ?> KB</td>
      <td style="text-align:right;font-size:9pt"><?php echo date('y/m/d H:i:s', $file['time']); ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
[ <a href="up">upload file</a> ]

<script type="text/javascript">
function set_delete_id(el) {
  var d = document.getElementById('delete_id');
  d.value = el.innerHTML;
}
</script>
<?php endif; ?>
</body></html>
