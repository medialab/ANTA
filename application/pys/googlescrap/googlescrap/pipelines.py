# Specific pipeline for anta system.
#
# Don't forget to add your pipeline to the ITEM_PIPELINES setting
# See: http://doc.scrapy.org/topics/item-pipeline.html
import MySQLdb
from scrapy import log
import re
from database_config import * 
from urlparse import urlparse

class GooglescrapPipeline(object):
	
	def __init__(self) :
		self.connection=MySQLdb.connect(host=MYSQL_HOST, user=MYSQL_USER, passwd=MYSQL_PASSWORD)
		self.cursor = self.connection.cursor()
		
	def attach_tag( self, spider, doc_id, tag, category ):
	# attach a tag to a given document, using the info collected inside the spider (like crawl_database)
    	# get or create !todo
    	
		self.cursor.execute( "SELECT id_tag FROM `" + spider.crawl_database + "`.`" + spider.tags_storage + "` WHERE content = %s LIMIT 1", ( tag ) )
		tag_row = self.cursor.fetchone()
		print "@pipeline: selected tag '",tag,"': ", tag_row
        
        # tags does not exist
		if tag_row == None :
        	# insert domain and query as tag
			self.cursor.execute( "INSERT IGNORE INTO `" + spider.crawl_database + "`.`" + spider.tags_storage + "`( content, id_category ) SELECT %s, id_category FROM `" + spider.crawl_database + "`.`"+ spider.category_storage + "` c WHERE c.content = %s", ( tag, category ) ) 
			self.connection.commit()
			tag_id = self.cursor.lastrowid
			
		else :
			tag_id = tag_row[ 0 ]
    	
    	# attach tag to the document
		query = "INSERT INTO %s.%s( id_tag, id_document ) VALUES ('%s', '%s')" % ( spider.crawl_database, spider.tags_relation_table, tag_id, doc_id ) 
		log.msg(query)
		self.cursor.execute(query)
    	
    
	def process_item(self, item, spider):
		crawl_database = spider.crawl_database
		crawl_storage  = spider.crawl_storage
		relation_table = spider.relation_table
        
		query = "INSERT INTO %s.%s(title, description, remote_url, mimetype, language) VALUES ('%s', '%s', '%s', 'text/plain', '%s')" % (crawl_database, crawl_storage, re.escape(item['title']), re.escape(item['text']), re.escape(item['url']), re.escape( spider.language ) ) 
		log.msg(query)
		self.cursor.execute(query)
		doc_id = self.cursor.lastrowid
        
		query = "INSERT INTO %s.%s(id_crawl, id_document) VALUES ('%s', '%s')" % (crawl_database, relation_table, spider.crawl_id, doc_id) 
		log.msg(query)
		self.cursor.execute(query)
        
		self.attach_tag( spider, doc_id, urlparse( item['url'] )[1], "domain" )
		self.attach_tag( spider, doc_id, item['referer'], "query" )
        
		self.connection.commit()
		
	