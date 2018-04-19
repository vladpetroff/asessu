<?php
##[k] запрет запуска модуля вне контекста системы
if (!isset($db)){
	header("HTTP/1.0 404 Not Found");
	header("Location: /404.html");
}

##[k] модуль содержит часто используемые вещи типа заполнения контентного поля, метаописания, ключевых слов и тайтла.
if ($page->link != 'catalog') {
	wr('TITLE', $page->title);
	wr('KEYWORDS', $page->keywords);
	wr('DESCRIPTION', $page->description);
	wr('H1', $page->h1);
}
// blia - вывод стачического кода страницы	
if ($page->html != '') {
	wr('PAGE_NAME', $page->name);
	wr('PAGE_CONTENT', $page->html);
}


wc($output);



?>
