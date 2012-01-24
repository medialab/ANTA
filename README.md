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

1. Step one. *Include documents*, via upload or import.  
  First of all users are asked to upload the texts composing their corpus in the system. ANTA can read txt and pdf documents, and it has a doc support via catdoc.
  - manual upload
  - import google results (up to 100 documents per query)
  - import via json api
  
2. Step two. *Tag documents* and selection of subsets.                         
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

## features
Anta executes a script - the "distiller" -  for each document of the corpus which performs a *sequence* of processes decided by the user.

* chainable *plugin* structure
* one click analysis start and *smart logging*
* JSON api interfaces
  - standard api, providing basic methods to upload, tag
  - a "frog" api, relationships between documents and entities, lite api driven search engine
  - a "squid" api, that implements the entities tf/idf visualization

## external dependencies
Anta makes use of external text analysis services, like Alchemy Api from Orchestr8 http://www.alchemyapi.com/

* Php libraries:
  - zend
* Python libraries:
  - nltk for stemming features and sentences tokenizer, cfr. http://www.nltk.org/
  - beautifulsoup, cfr. http://www.crummy.com/software/BeautifulSoup/
  - networkx, cfr. http://networkx.lanl.gov/
