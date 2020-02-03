Smart and simple php class for compress (minify) *.css, *.js and *.html files or data.
- removes whitespace or comments
- combines files to string, 
- fix some javascript syntax errors
- 3 level compress (0 - no compress, 1 - without whitespace, 2 - with whitespace)
- only remove comments from *.min.js or *.min.css files
- show execution time
- work as smarty modificator

## Usage

### PHP (work with files)
```php
<?php
require "jetPacker.php";
$files = array("/css/style.css", "/css/custom.css", "/css/settings.css");
$packer = new jetPacker();
$packer->files = $files;
$out = $packer->init();
echo $out;
```
### PHP (work with data)
```php
<?php
require "jetPacker.php";
$data = file_get_contents("index.html");
$packer = new jetPacker();
$packer->type = "html";
$out = $packer->compress($data);
echo $out;
```
 
### SMARTY
#### Connect as modifier
```php
<?php
require "jetPacker.php";
$smarty = new Smarty;
$smarty->registerPlugin('modifier', 'packer', 'jetPacker');

function jetPacker($files){
  $packer = new jetPacker();
  $packer->files = $files;
  $out = $packer->init();
  return $out;
}
```
#### In the template, use packer to compress files.
```smarty
...
<style>
{["/assets/css/style.css","/assets/css/custom.css","/assets/css/settings.css"]|packer}
</style>
...
```
