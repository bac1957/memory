@Echo off
Rem  ***************************************************************************
Rem  *			               М Е М О Р И А Л
Rem  *         Мемориал участников во Второй мировой войне 
Rem  *	   =============================================
Rem  *
Rem  * Пакетный файл: db.cmd
Rem  * Функции:       Выполнение SQL- запросов
Rem  * Параметры:     Имя файла 
Rem  * ------------------------------------------------------------------------
Rem  * Авторство:   ИП "HOME LAB", Пенза (с), 2025
Rem  * Разработчик: Александр Васильков
Rem  * E-Mail:	    bac@sura.ru
Rem  * ------------------------------------------------------------------------
Rem  * Версия: 0.0.1
Rem  * Дата:   06.09.2025
Rem  ***************************************************************************

Rem  Здесь нужно установить тербуемые значения
Rem  ==========================================
Rem SET MyPath="D:\MySQL"
rem SET MyPath=C:\Program Files\MySQL\MySQL Server 5.6
SET  MyPath=C:\Program Files\MySQL\MySQL Server 8.2
ECHO %MyPath%

SET MyUser=root
SET MyPassword=123456
SET DbName=MySQL
Rem  ==========================================

IF [%1] == [] GOTO shell
"%MyPath%\bin\mysql" --password=%MyPassword% --user=%MyUser%  --execute="source %1;" %DbName%
EXIT
:shell
"%MyPath%\bin\mysql" --password=%MyPassword% --user=%MyUser% %DbName%

