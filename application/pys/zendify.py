# zend python configuration script
# ini location '../configs/application.ini'

import ConfigParser
import MySQLdb
import sys
from medialab_mysqlWorker import *

# absolute application_PATH
APPLICATION_PATH = '/home/daniele/public/anta/trunk/application'

# relative Zend configuration file location uri
APPLICATION_INI_URI = APPLICATION_PATH + '/configs/application.ini'

# read Zend configuration file
config = ConfigParser.RawConfigParser()
config.read(  APPLICATION_INI_URI );

######################################################


########## USAGE

# ./zendify.py  add_doc 								[idUser] [idStatus] [idDocument]
# ./zendify.py  do_all_docs 							[idUser] [idStatus]
# ./zendify.py  make_graph 								[idUser] [idStatus]

# ./zendify.py  make_graph_with_existing_white			[idUser] [idStatus]



########## NOT USED YET

# auto selection of ngrams based on how much you want
# ./zendify.py  make_graph_auto 	[idUser] [idStatus] [maxNGrams]


######################################################
def doJob():
	#print "ARGS FOR TINA",sys.argv
	cmd=sys.argv[1]
	inUserId=sys.argv[2]
	idStatus=sys.argv[3]
	
	mysqlw = mysqlWorker(inUserId,idStatus)
	mysqlw.DB_HOST = config.get('database : production',  'mysql.host')
	mysqlw.DB_USER = config.get('database : production',  'mysql.user')
	mysqlw.DB_PASS = config.get('database : production',  'mysql.pass')
	mysqlw.DB_NAME = config.get('database : production',  'mysql.dbnm')
	
	if cmd=="add_doc":
		inDocumentId=sys.argv[4]
		return mysqlw.addDocumentToDB( int(inDocumentId) )
	
	if cmd=="do_all_docs":
		return mysqlw.addAllDocumentsToDB()
	
	if cmd=="make_graph":
		return mysqlw.produceTinaGraph( 0, True )
	
	if cmd=="make_graph_with_existing_white":
		return mysqlw.produceTinaGraph( 0, False )
		
#	else if cmd=="make_graph_auto":
#		maxNgrams=sys.argv[4]
#		return mysqlw.produceTinaGraph( int(maxNgrams) )
######################################################
if __name__ == '__main__':
	doJob()
######################################################
