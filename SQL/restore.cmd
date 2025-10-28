@Echo off
Rem  ***************************************************************************
Rem  *		Оценка удовлетворённости условий труда
Rem  *	   =============================================
Rem  *
Rem  * Пакетный файл: restore.cmd
Rem  * Функции:       Восстановление backup базы данных
Rem  * Параметры:     Дата backup
Rem  * ------------------------------------------------------------------------
Rem  * Авторство:   ИП "HOME LAB", Пенза (с), 2025
Rem  * Разработчик: Александр Васильков
Rem  * E-Mail:	    bac@sura.ru
Rem  * ------------------------------------------------------------------------
Rem  * Версия: 0.0.1
Rem  * Дата:   06.10.2025
Rem  ***************************************************************************

Rem  Здесь нужно установить тербуемые значения
Rem  ==========================================
SET  MyPath=C:\Program Files\MySQL\MySQL Server 8.2
ECHO %MyPath%

SET MyUser=root
SET MyPassword=123456
SET DbName=memorial
Rem  ==========================================

IF [%1] == [] GOTO shell
"%MyPath%\bin\mysql" --password=%MyPassword% --user=%MyUser%  --execute="source memDump%1.sql;" %DbName%
EXIT
:shell
Echo '*** Error *** No specify backup date!'

