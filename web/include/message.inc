<?php
/**
 * Формирование окна вывода сообщения
 */
function prMsg() {
  echo "<div id='dialogoverlay'></div>\n".
  "<div id='dialogbox'>\n".
  "<div>\n".
  "    <div id='dialogboxhead'>МЕМОРИАЛ</div>\n".
  "    <table width='100%' bgcolor='white'><tr><td><img src='images/question.png'></td><td>\n".
  "    <div id='dialogboxbody' align='center'></div><br /><br />\n".
  "    <div id='dialogboxfoot' align='right'>\n".
  "        <div class='btn-group'>\n".
  "            <button class='button' type='button' style='width:90px' onclick='reqMsg.Accept(false)'>Нет</button>\n".
  "            <button class='button' type='button' style='width:90px' onclick='reqMsg.Accept(true)'>Да</button>\n".
  "        </div>\n".
  "    </div>\n".
  "  </td></tr></table>\n".
  "  </div>\n".
  "</div>\n".
  "<script language=JavaScript>\n";
  echo  "\tvar reqMsg = new Msg();\n".
		"</script>\n";
}

