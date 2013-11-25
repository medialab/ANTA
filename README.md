ANTA, actor-network text analyzer
=================================

ANTA or Actor Network Text Analyzer is a piece of software developed by the Sciences Po médialab to analyses medium-size text corpora, by extracting the expressions they contained in a set of texts and drawing a network of the occurrence of such expressions in the texts.

## goals
Anta is a web platform based on Zend Framework which serves two main goals:

* *simplify* as much as possible the researchers' workflow in text analysis
* build a *graph* based onto the set of documents dealing with co-word analysis

Using ANTA is very easy. We will introduce its usage by reading the image displayed on the login page of the software. 
  
![Alt text](http://jiminy.medialab.sciences-po.fr/anta_dev/images/anta-02.png "the path of anta")

## the 5 steps workflow
Using ANTA requires going through 5 different steps: the researchers' workflow has been subdivided into 5 main steps, from the creation of the corpus to the words extraction (the so-called entities)
As soon as users have uploaded their texts and while their are tagging them, the system works in background analyzing the texts to extract the expressions occurring in them. To do so, Anta draws on Alchemy (Orchestr8 text API). Thanks to Alchemy, Anta identifies the n-grams (expressions of n-words) recurring in the text and is even capable to recognize 'named entities' as such. It knows, for example, that Alice is a person name, that France is a country and Paris a city. The expressions identified by Anta are called entities.

* Step one. *Include documents*, via upload or import.  
  First of all users are asked to upload the texts composing their corpus in the system. ANTA can read txt and pdf documents, and it has a doc support via catdoc.
  - manual upload
  - import google results (up to 100 documents per query)
  - import via json api
  
* Step two. *Tag documents* and selection of subsets.                         
  After having uploaded all their texts, users are asked to categorize them according to the classification that best suits their research interests. Documents, for example, can be tagged by author, by type, by subject and any other taxonomy used by the researcher.
  - tag based selection to focus on small subsets
  - google spreadheet import / export of the list of documents (limited features)


* Step three. *Include entities*.  
  Once the system has concluded its extraction, users are asked to chose which entities they want to include in their analysis and which one the one to exclude. Even for relatively small corpora, the number of the extracted entities is often surprisingly large, to large for a manual filtering. ANTA offers an semi-automatic filtering system that help users reduce the number of the entities they will analyze.
  - entities selection through visual TF/IDF measures
  
* Step four. *Tag entities*, and merging as well.  
  In order to facilitate the analysis the included entities can be tagged by the users according to the classification best suits their research interests. It is also possible to merge entities that are synonymous for the scope of the research.

* Step five. *Export* the graph.  
  The last step consists in exporting the graph of tagged documents and tagged entities. ANTA exporting system delivers a gefx file containing a bipartite network of documents and entities. Two nodes of the network are connected if the corresponding document is connected to the corresponding entity.

## Features
Anta executes a script - the "distiller" -  for each document of the corpus which performs a *sequence* of processes decided by the user.

* chainable *plugin* structure
* one click analysis start and *smart logging*
* JSON api interfaces
  - standard api, providing basic methods to upload, tag
  - a "frog" api, relationships between documents and entities, lite api driven search engine
  - a "squid" api, that implements the entities tf/idf visualization

## External dependencies
Anta makes use of external text analysis services, like Alchemy Api from Orchestr8 http://www.alchemyapi.com/

* Php libraries:
  - zend
* Python libraries:
  - nltk for stemming features and sentences tokenizer, cfr. http://www.nltk.org/
  - beautifulsoup, cfr. http://www.crummy.com/software/BeautifulSoup/
  - networkx, cfr. http://networkx.lanl.gov/


## Installing ANTA on your own server
Below follows a tutorial of getting ANTA up and running on your own server. Be aware that while using ANTA is quite easy, installing it onto your own server is more of a challenges.

### Requirement
- PHP, MySql, Python (tested with 2.7)
- Linux server with pdftotext and catdoc
- Exec enabled in PHP
- Privileges to create mysql users

### Installing ANTA:
1. Download the latest version of ANTA from Github and upload into folder on your server
2. Create a database named 'anta' and a user also named 'anta' and assign all privileges for the 'anta' database to this user.
3. Import the anta.sql file on this database creating the necessary table structure.
4. Rename application.sample.ini in the folder /application/configs/ to application.ini
5. Edit the file:
	- Fill out the database section. Notice: The mysql user has to be the root user and not the newly created ANTA user.
	- Go to http://www.alchemyapi.com/ and register for a key. Copy that key into alchemy.api.key.
	- Go to http://www.opencalais.com/ and register for a key. Copy that key into opencalais.api.key
6. Download Zend Framework version 1.11.0 from http://framework.zend.com/downloads/archives 
Notice: ANTA currently doesn't support newer versions, so stick with this one, which has been tested to work.
7. Unzip the downloaded file and copy the entire /library/Zend folder to your ANTA library/ folder. 
8. In your anta root folder create the folder /uploads and make it writable for all (777).
9. Add the following virtual host:
	Alias /anta_dev /path/to/anta/public

	<Directory "/path/to/anta/public">
   	Options Indexes MultiViews FollowSymLinks
   	AllowOverride All
   	Order allow,deny
   	Allow from all
	</Directory>

10. In your browser visit www.yourdomain.xx/anta_dev/install/

You should now be able to log into ANTA with the user dummy / admini at www.yourdomain.xx/anta_dev/. Start by changing the password of your dummy admin under 'Your account' 

### Installing Python modules (used for the text analysis): 
- Install networkx from terminal using this command:
sudo pip install networkx
- Install beautifulsoup4 from terminal using this command:
sudo pip install beautifulsoup4 
- Install NLTK following the instructions here:
http://nltk.org/install.html
- Install NLTK-data following the instructions here:
http://nltk.org/data.html

### Troubleshooting:
_No upload button under the include document site_
- Make sure the folder /public/js is writable

_Continually getting the error "bad encoding or file type", when uploading PDFs_
- You might be be missing pdf2text/pdftotext on your server or the server might be unable to locate the utility. A simple way to tell php the path to pdftotext is by e.g. adding the following line in the middle of your public/index.php file:

	putenv("PATH=" .$_ENV["PATH"]. ':/usr/local/bin'); 

_Problems with upload_
- Make sure that uploads and all it's subfolders are writable.

_Stem functions_
- If you are getting errors from the system not being able to find to find anything starting with stem (e.g. the function stem_english) your php installation are missing the __stem__ extension. If you have pecl working this can be solved by running the command:

  		sudo pecl install stem

  If this doesn't work, you can then make a manual installation with the following steps:

  1) Download the package (e.g. version 1.5.1) from http://pecl.php.net/package/stem.
  2) Upload and unpack
  3) Go to the folder containing the files of stem
  4) Install by running

		phpize
		./configure
		make
		make install

Other problems
- Make sure that the logs folder is writable. Wrong log folder permissions  might result in errors when analyzing text.
