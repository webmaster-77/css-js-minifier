Smart and simple php class for compress (minify) *.css, *.js and *.html files or data.
- removes whitespace or comments
- combines files to string, 
- fix some javascript syntax errors
- 3 level compress (0 - no compress, 1 - without whitespace, 2 - with whitespace)
- only remove comments from *.min.js or *.min.css files
- show execution time
- work as smarty modificator

How to use:
1. include php class
2. create files-list
3. call the class
4. set settings
5. get minified files to row

Example as PHP:
    require "jetPacker.php";
    $files = array("/css/style.css", "/css/custom.css", "/css/settings.css");
		$packer = new jetPacker();
		$packer->files = $files;
		$packer->level = 2;     //compress level
		$out = $packer->add_files();
    echo $out;
    
Example as SMARTY:    
