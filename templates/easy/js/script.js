$(document).ready(function(){
	//Определяем меню 
	var menu = [
		{
			name: "Управление контентом",
			items : [
				{name:"Изображения",link:"images",clas:"menu-images"},
				{name:"Статьи",link:"articles",clas:"menu-articles"},
				{name:"Новости и акции",link:"news",clas:"menu-news"},
				{name:"Каталог",link:"catalog",clas:"menu-catalog"},
				{name:"Структура сайта",link:"tree",clas:"menu-tree"}				
			]
		}
	]

	//Создаем окно панели управления
	$('body').dashboard();
});