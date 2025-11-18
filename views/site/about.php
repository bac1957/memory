<?php
/*
**********************************************************************
*			               М Е М О Р И А Л
*         Мемориал участников во Второй мировой войне 
* ==================================================================
*                     О программе ....
* 
* @file views/site/about.php
* @version 0.0.2
*
* @author Александр Васильков
* @author Home Lab, Пенза (с), 2025
* @author E-Mail bac@sura.ru
* @var yii\web\View $this
* 
* @date 06.09.2025
*
* @p Изменения
* @date 17.11.2025 Добавлены авторы
*
**********************************************************************
*/

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'О программе ...';
$this->params['breadcrumbs'][] = $this->title;
$rec = Yii::$app->db->createCommand( "SELECT VERSION() vers")
            ->queryOne();
$MySQLVers = (!$rec) ? "unknown" :  $rec['vers'];
$aMySQLVers = explode("-", $MySQLVers);
if (count($aMySQLVers) == 1) {$MySQLVers = "MySQL v." . $MySQLVers;}
include("include/getServerId.inc");
?>

<div class="site-about">
    <table width='800'>
      <tr>
        <td><?= Html::img('@web/images/about.png', ['alt' => 'О программе Мемориал']) ?></td>
        <td align='center'><h1>МЕМОРИАЛ</h1><hr>(Версия <?= Yii::$app->params['version']; ?>)
		  <h3>
			Здесь собрана информация о жителях села Потьма Мокшанского района Пензенской области,<br> 
			ушедших защищать нашу Родину, вернувшися после Победы,<br> 
			погибших при выполнении воинского долга и пропавших без вести.<br>
			Память о них в наших сердцах! 
		  </h3>
          <p align='left'>
          <table> 
            <tr>
              <td>Авторы:</td>
              <td>А.Васильков<br>С.Данилин<br>Д.Ракчеев
			</td>
            </tr>
            <tr>
              <td>E-Mail:</td>
              <td>vassilkov@mail.ru</td>
            </tr>
            <tr>
              <td>Моб. телефон:</td>
              <td>+7 902 204 87 49</td>
            </tr>
          </table> 
          </p>
        </td>
      </tr>
    </table>
    <p><br>
	<table width="80%" ><tr align="left">
      <td width=60%><?=$serverOS_Version?></td>
      <td width=40%><a href='<?=$serverOS_URL?>'><?=$serverOS_URL?></a></td></tr>
      <tr align="left">
        <td><span id="webSrvVers">&nbsp;</span></td><td><span id="urlSrv">&nbsp;</span></td></tr>
      <tr align="left">
		<td><span id="Sql">&nbsp;</span></td><td><span id="urlSql">&nbsp;</span></td></tr>
      <tr align="left">
		<td>PHP v.<?= PHP_VERSION; ?></td><td><a href=http://www.php.net>http://www.php.net</a></td></tr>
      <tr align="left">
		<td>Yii2 v.<?= Yii::getVersion(); ?></td><td><a href=https://www.yiiframework.com>https://www.yiiframework.com</a></td></tr>
      <tr align="left">
		<td><span id="Brow">&nbsp;</span></td><td><span id="urlBrow">&nbsp;</span></td></tr>
	</table></p>
</div>
<script type="text/javascript">
/**
 * Определение версии WEB-сервера
 * @returns {String} Идентификация WEB-сервера
 **/
function getServerName() {
	return '<?php echo $_SERVER["SERVER_SOFTWARE"]; ?>';
}

var Url = {
	'MSIE':'http://www.microsoft.com',
    'Edg':'http://www.microsoft.com',
	'.NET':'http://www.microsoft.com',
	'Opera':'http://www.opera.com',
	'OPR':'http://www.opera.com',
    'YaBrowser':'https://browser.yandex.ru',
	'Firefox':'http://www.firefox.com',
	'Chrome':'http://www.chromium.org',
	'Safari':'http://www.apple.com'
};

var UrlSQL = {
	'MySQL':'http://www.mysql.com',
	'MariaDB':'https://mariadb.org/'
};

/**
 * Формирование URL СУБД, web-сервера, браузера
 **/		
function setData() {
	var d;
	d = document.getElementById("webSrvVers");
	if (d) {
		s = getServerName();
		d.innerHTML=s;
		if (s!=='WEB-server is not defined') {
			d = document.getElementById("urlSrv");
			if (s.indexOf("Apache") !== -1) {
				d.innerHTML="<a href=http://www.apache.org>http://www.apache.org</a>"; 
			}
		}
	}
	d = document.getElementById("Brow");
	s = window.navigator.userAgent;
	if (d) d.innerHTML=s;
	for (var key in Url ) {
		if( s.indexOf(key) !== -1) {
			d = document.getElementById("urlBrow");
			if (d) d.innerHTML="<a href="+Url[key]+">"+Url[key]+"</a>";
			break;
		}
	}
	d = document.getElementById("Sql");
	s = "<?=$MySQLVers?>";
	if (d) d.innerHTML=s;
	for (var key in UrlSQL ) {
		if( s.indexOf(key) !== -1) {
			d = document.getElementById("urlSql");
			if (d) d.innerHTML="<a href="+UrlSQL[key]+">"+UrlSQL[key]+"</a>";
			break;
		}
	}
}

setData();

</script>