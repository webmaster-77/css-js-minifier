<?php
/**
 * jetPacker v.1.0
 *
 * @copyright	2019 Alexey Evteev
 * @link		https://webtask.pro
 * @author		Alexey Evteev
 *
 */

class jetPacker {
	public $files;
	public $type;
	public $level;
	public $minified;

	public function __construct(){
		//массив с файлами
		$this->files = array();
		//тип по умолчанию
		$this->type = "html";
		//по умолчанию файл не сжат
		$this->minified = false;
		//сжатие по умолчанию
		$this->level = 1;
		//разрешенные форматы
		$this->allow_mimes = ["js", "css"];
	}
	/*
	 * убираем 96% ненужного кода. остальные 4% это пробелы перед операторами, циклами, разрывами строк. оставляем т.к. бессмысленно экономить 4%
	 * если надо сжать с пробелами, то следует установить в настройках уровень МАКСИМУМ
	 */
	public function compress($buffer){
		//правила только для js.
		if($this->type == "js"){
			/*
			регулярка КОРРЕКТНО удаляет комментарии, пример:
			// Kludges for bugs and behavior differences that can't be feature
			// detected are enabled based on userAgent etc sniffing.
			var userAgent = navigator.userAgent;
			var webkit =!edge && /WebKit\//.test(userAgent);
			1. комментарии с новой строки включая пробелы
			2. начинающиеся на // и заканчивающиеся разрывом строки или концом строки.
			3. начинающиеся на /* и заканчивающиеся на *\/
			4. комментарии ПОСЛЕ кода: var t = $(this);//some comment
			*/
			$buffer = preg_replace("~(^|\s{1,})((//(.*?)+(\n|$))|(/\*(.*?)\*/))~ms", " ", $buffer);
			//если у нас файл минимизирован, то ТОЛЬКО удаляем из него комментарии
			if($this->minified){
				//и возвращаем
				return trim($buffer);
			}
			/*
				регулярка изменяет криво написанный код, пример:
				var inline = parserConfig.inline
					if (!parserConfig.propertyKeywords)
				в данном коде не проставлена ; в конце обозначения переменной.
				после "склейки в строку" будет ошибка.
				регулярка смотрит чтобы в окончании строки была граница слова, потом разрыв строки, потом опять граница слова
			*/
			$buffer = preg_replace("~(\b)(\n(\s{1,}\b))~m", ";", $buffer);
			/*
			 * удаляем табуляторы, переводы строк и множественные пробелы. эту строку нельзя использовать для всех типов
			 * данных(js,css,html) т.к. её место вызова для каждого типа важно!
			*/
			$buffer = preg_replace("#\s+#", " ", $buffer);
			/*
			 * заменяет криво написанные функциональные выражения в js где в конце не ставят ;
			 * пример: var f = function(param, param) { var mass = ''; return true; }
			 * В конце, после } должна стоять ;
			*/
			//не будет работать если в функции есть объявление объекта с фигурными скобками
			//$buffer = preg_replace("~(var \w\s?=\s?function\(.*?\)\s?{.*?})(\s|$)~", "$1;", $buffer);
			
			//при макс. сжатии убираем пробелы
			if($this->level == 2){
				$buffer = preg_replace("#\s?(:|}|{|,|;|!=|!==|===|==|=|\|\||&&)\s?#", "$1", $buffer);
			}
		}
		//правила только для css
		if($this->type == "css"){
			//многострочные комментарии вида /* comment */
			$buffer = preg_replace("~(^|\s{1,})(/\*(.*?)\*/)~ms", " ", $buffer);
			//если файл минимизирован
			if($this->minified){
				//и возвращаем
				return trim($buffer);
			}
			/* удаляем табуляторы, переводы строк и множественные пробелы */
			$buffer = preg_replace("#\s+#", " ", $buffer);
			//при макс. сжатии добаваляем ещё правила
			if($this->level == 2){
				//при перечислении правил пробел не обязателен
				$buffer = str_replace(", .", ",.", $buffer);
				//только с внутренней стороны заменять т.к. есть @media запросы
				//и там нельзя убрать пробел с внешней стороны
				$buffer = str_replace("( ", "(", $buffer);
				$buffer = str_replace(" )", ")", $buffer);
				$buffer = preg_replace("#\s?(:|;|\+|}|{)\s?#", "$1", $buffer);
				//у последнего правила убираем ; она не обязательна
				$buffer = str_replace(";}", "}", $buffer);
			}
		}
		//правила только для html
		if($this->type == "html"){
			$buffer = preg_replace("/<!--(.*?)-->/sm", "", $buffer);
			/* удаляем табуляторы, переводы строк и множественные пробелы */
			$buffer = preg_replace("#\s+#", " ", $buffer);
			//при макс. сжатии добаваляем ещё правила
			if($this->level == 2){
				//если закоментировать 1-ую строку то появяися "визуальные" пробелы
				$buffer = str_replace("> <", "><", $buffer);
				$buffer = str_replace(" >", ">", $buffer);
				$buffer = str_replace("< ", "<", $buffer);
			}
		}
		return trim($buffer);
	}

	public function add_files(){
		//время вызова метода
		$time_start = microtime(true);
		//по умолчанию данных нет
		$data = "";
		//рабочая директория
		$root = getcwd();
		foreach($this->files AS $file){
			//по умолчанию файл не минимизирован
			$this->minified = false;
			//если файл есть физически
			if(is_readable($root.$file)){
				//узнаём тип файла
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				//работаем только с этими типами файлов
				if(in_array($ext, $this->allow_mimes)){
					//если в названии файла есть .min, то это минимизированный файл и надо только комментарии удалить
					if(strstr($file, ".min") !== false){
						//для минимизированных файлов включаем удаление комментариев
						$this->minified = true;
					}
					//данные из файла
					$content = file_get_contents($root.$file);
					//если сжатие включено
					if($this->level > 0){
						//объявляем тип данных
						$this->type = $ext;
						//сжимаем
						$content = $this->compress($content);
					}
					//если сжатия нет то выводим как есть с комментарием о имени файла
					$data .= ($this->level == 0 ? "/*File: ".$file."*/" : "").$content."\n";
				}
				else{
					//с таким типом данных мы не работаем
					$data .= "/*Filetype is invalid (".$ext.")*/\n";
				}
			}
			else{
				//файл не найден
				$data .= "/*File ".$file." not found*/\n";
			}
		}
		//время окончания работы скрипта
		$time_end = microtime(true);
		//добавляем в вывод время обработки
		$data = "/*".($time_end - $time_start)."*/\n".trim($data);
		//возвращаем все данные по всем файлам
		return $data;
	}
}