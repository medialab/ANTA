#!/usr/bin/python
# -*- coding: utf-8 -*-
import sys, os, MySQLdb, re, string, time, getopt

#
# ANTA structured document csv extractor
# 
# user		- the desired username
# csv		- the csv file to analyse
# separator - column separator
# content	- text content
# title	- document title
# language - document language
# date     - document date
#


print "init\n  --\n  received n. arguments:",len( sys.argv )

# dummy test arguments length...
if len( sys.argv ) < 4 :
	print "  not enough arguments!"
	sys.exit(0)

# params via argv
username    = sys.argv[1]
fileToParse = sys.argv[2]
separator   = sys.argv[3]

# some other param
notagpattern = "<.*?>"

print "  args: ", username, fileToParse, separator

# mysql -csv map for document table
documents  = { 'title':'ItemTitle', 'language':'langue', 'date':'ItemPubDate_t' }
categories = [ 'ItemSource', 'ItemCategory', 'ItemCategoryDomain', 'itemID', 'feedID', 'CreatedUniqueID']
content    = 'ItemDescription'


# test connection and force exit
DB_HOST = "localhost"
DB_USER = "anta"
DB_PASS = "diaballein"
DB_NAME = "anta_" + username
userpath  = '/home/daniele/public/anta/trunk/uploads/' + username

try:
	connection = MySQLdb.connect( DB_HOST, DB_USER, DB_PASS, DB_NAME)
except OperationalError:
	print "mysql connection troubles: ", sys.exc_info()
	sys.exit()

print "  connection ok\n  --"
	
# check file existance
f = open( fileToParse )
print "checking csv file:", f

# array of column headers
fields = []

# return a dd/mm/YYYY string
def parseDate( date, pattern ):
	dates = re.findall( "(\d{4})-(\d{2})-(\d{2})", date)
	if len( dates ) != 1:
		return ""
	date = dates[0]
	return date[2] + "/" + date[1] + "/" + date[0]

def fput( file, content ):
	# try to create a file and save scraped content there
	try:		
		outfile = open( file, "w" )
		outfile.write(  content );	
		return True
	except:
		print( "problem in writing file " + file )
		return False

# save desired categories
cursor = connection.cursor()

for c in categories:
	query = """INSERT IGNORE INTO `categories`(
		content, type
	) VALUES ( %s, 'txt' )"""

	# execute query		
	cursor.execute( query, ( c,) )		
# close und commit
cursor.close()
connection.commit() 
	
# line number aka uniqueId
nline = 0;	
#split lines
for line in f:
	line = line[:-1]
	if len( fields ) == 0:
		fields = re.split( separator, line )
		continue
	
	nline+=1
	
	
	values = re.split( separator, line )
	dict = {}

	# create dictionary
	i = 0
	for f in fields:
		dict[ f ] = values[ i ]
		i+=1
	
	# some values
	date     =  parseDate( dict[ documents[ 'date' ] ] ,"(\d{4})-(\d{2})-(\d{2})" )
	title    =  dict[ documents['title'] ]
	language =  dict[ documents['language'] ]
	text     =  re.sub( notagpattern, ' ', dict[ content ][1:-1] )
	
	# create a file
	localUrl = "doc_" + str( nline ) +  ".txt";
	filename = userpath + "/" + localUrl ;
	
	if fput( filename, title + "\n" + text  ) == False:
		print "  error on file " + filename
		break
		
	#get filesize
	filesize = os.path.getsize( filename )
	
	print "  saved:", title, date
	print "    local url:", localUrl, filesize
	
	
	# add an entry
	query ="""INSERT IGNORE INTO `documents` (
			`id_document`, `title`,`description`,
			`mimetype`, `size`,
			`language`, `date`, `local_url` ,
			`status`
		) VALUES (
			NULL, %s,'',
			%s, %s, 
			%s, STR_TO_DATE( %s, '%%d/%%m/%%Y'), %s,
				'ready'
			)"""
			
	# execute query		
	cursor = connection.cursor()
	cursor.execute( query, (title, "text/plain", str( filesize ), language, date, localUrl ) )
	
	# get document id
	documentId = connection.insert_id()
	print "  stored id:", documentId
	
	# store tags
	if documentId == 0:
		# close und commit
		cursor.close()
		connection.commit()
		print "  documentId = 0, skipping"
		continue
	
	# do not handle multiple values in a single cell
	for c in categories:
		print "  storing [", c,":", dict[ c ],"]"
		# insert new tag
		query = """INSERT IGNORE INTO `tags` (`content`, `id_category`) SELECT 
				%s, id_category FROM categories WHERE categories.content = %s LIMIT 1
			"""
		cursor.execute( query, ( dict[ c ], c))
		tagId = connection.insert_id();
		
		# tag existing
		if tagId == 0 :
			cursor.execute( """SELECT id_tag FROM tags WHERE content LIKE %s""", ( dict[ c ],) )
			row = cursor.fetchone()
			if row != None :
				tagId = row[ 0 ]
			else:
				print "  tagId = 0, skipping"
				continue
				
		# create link tag-documents
		cursor.execute(	"""INSERT IGNORE INTO `documents_tags` (
					`id_document`, `id_tag` 
				) VALUES (	%s, %s )""", ( documentId, tagId ) )
			
		
		
	cursor.close()
	connection.commit()
	
