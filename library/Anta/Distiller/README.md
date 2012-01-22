plugins.

1. Create and edit plugin script.
As described by Zend Frameworks, classes are loaded according their names and the distiller plugin structure respect this format.
To allow the autoload feature for your brand new plugin class:
 - create a file as you like inside this folder: Example.php
 - edit the file by creating the plugin class (it must extend ThreadHandler class)

```php
class Anta_Distiller_Example extends Anta_Distiller_ThreadHandler{

}
```

So, when the zend app find the class Anta_Distiller_Example, it will load the class Anta_Distiller_Example located at anta/distiller/example.php script. This class extends the functionality of ThreadHandler class, defined of course in the php script anta/distiller/ThreadHandler.php.
See below how to activate the plugin for your analysis chain.

The distiller will call the "init" class method. So the class file will become:

```php
class Anta_Distiller_Example extends Anta_Distiller_ThreadHandler{
	public function init(){
	
	}
}
```
2. Activate and test the plugin
The distiller script anta/application/routines/type-distiller.php loads a separate zend application to perform the analysis onto 
various document.
Add your script class along with its name just before the start() method at the end of the file:

```php
	# ... anta/application/routines/type-distiller.php coontent 
	$distiller->addThreadHandler( 'ng', 'Anta_Distiller_Ngram' );
	$distiller->addThreadHandler( 'ex', 'Anta_Distiller_Example' );
	# ...

	# 3. start loaded distiller
	$distiller->start();
?>
```
The first argument of the addThreadHandler function is the type identifier specified 

Threads are not concurrent process but subsequent ones (of course, their names do not reflect their activities...), and the list of threads compose a pseudo-pipeline.
The list of threads activated by the current user are retrieved by the static method.
 
```php
$threads = Application_Model_ThreadsMapper::getThreads( $this->user);
```

3. Access to the user, to the routine information and to the document

	$document =& $this->_target;
	$user     =& $this->_distiller->user;


3. useful methods

_log( $message, $breakLine=false ){

activate the plugin


Anta_Distiller_Exsample 

create a file named as you like. THe class name will reflect



