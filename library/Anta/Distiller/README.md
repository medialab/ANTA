distiller plugins
=================

The distiller application perform a list of analytical processes on each document of a corpus anta.
Of course, each process is fully customizable and this readme covers the editig and creation of a brand new analysis process, a.k.a "plugin".

## 1. Create and edit plugin script

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
<?php
class Anta_Distiller_Example extends Anta_Distiller_ThreadHandler{
	public function init(){
	
	}
}
?>
```
## 2. Activate and test the plugin

The distiller script anta/application/routines/type-distiller.php loads a separate zend application to perform the analysis onto 
various document.
Add your script class along with its name just before the start() method at the end of the file:

```php
<?php
	# ... anta/application/routines/type-distiller.php coontent 
	$distiller->addThreadHandler( 'ng', 'Anta_Distiller_Ngram' );
	$distiller->addThreadHandler( 'ex', 'Anta_Distiller_Example' );
	# ...

	# 3. start loaded distiller
	$distiller->start();
?>
```
The first argument of the addThreadHandler function is a type identifier of your choice, the second argument is the class name of your plugin.
The distiller application will load subsequently the threads added inside the threads table of the anta database and related to your user current routine.
A more detailed documentation will be provided asap.

Normally, the apache config file for your anta installation should have an explicit alias towards anta routines directory:

```apache
   Alias /anta_distiller /your/path/to/anta/application/routines
   <Directory "/your/path/to/anta/application/routines">
       Order allow,deny
       Allow from all
   </Directory>
```
The script is located at anta/application/routines/type-distiller.php. point your browser at this page to test it.

Notes on threads:
Actually, Threads are not concurrent process but subsequent ones (of course, their names do not reflect their activities...and I'm sorry about that), and the list of threads compose a pseudo-pipeline.
The list of threads activated for the current user are retrieved by the static method:
 
```php
$threads = Application_Model_ThreadsMapper::getThreads( $this->user);
```

## 3. Access to the user, to the routine information and to the document
all the information avbailable are stored inside the instance as protected variables. No method are provided so far.
```php
		$document =& $this->_target;
		$user     =& $this->_distiller->user;
```

### 3.1 some useful methods
```php	
	# write a message inside the log file
	$this->_log( $message, $breakLine=false )
	
	# to retrieve the unique url for the document
	$localUrl = Anta_Core::getDocumentUrl( $user, $doc );
		
	# a valid language for the document (match against the existent stemmers, default is english)		
	$language = Anta_Core::getlanguage( $doc->language );
```

Please refer to the documentation inside threadHandler.php script

