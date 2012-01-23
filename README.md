ANTA, actor-network text analyzer
=================================


## goals
Anta is a web platform based on Zend Framework which serves two main goals:

* *simplify* as much as possible the researchers' workflow in text analysis
* build a *graph* based onto the set of documents dealing with co-word analysis

## the 5 steps workflow
THe workflow has been subdivided into 5 main steps, from the creation of the corpus to the words extraction (the so-called entities)

* documents upload or import, documents tagging
  - manual upload
  - import google results (up to 100 documents per query)
  - import via json api
* documents selection
  - tag based selection to focus on small subsets
  - google spreadheet import / export of the list of documents (limited features)
* entities selection through visual TF/IDF measures
* entities tagging and merging
* export of the relationships

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
