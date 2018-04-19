/*
@description script contains common functions
@author kodji
@version 1
*/

/*
@description safe logger. Prevent from errors if firebug is not installed
@param string msg
@return no return
*/
var log = function (msg) {
	if (window.console && console.log) console.log(msg) else alert(msg);
}