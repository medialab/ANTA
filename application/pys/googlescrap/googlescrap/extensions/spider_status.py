from scrapy.xlib.pydispatch import dispatcher
from scrapy import signals, log
import time, MySQLdb, re

from googlescrap.pipelines import MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB

class Spider_status(object):

    def __init__(self):
        dispatcher.connect(self.engine_started, signal=signals.engine_started)
        dispatcher.connect(self.spider_opened, signal=signals.spider_opened)
        dispatcher.connect(self.spider_closed, signal=signals.spider_closed)

    def engine_started(self):
        log.msg("engine started")

    def spider_opened(self, spider):
        connection=MySQLdb.connect(host=MYSQL_HOST, user=MYSQL_USER, passwd=MYSQL_PASSWORD)
        cursor = connection.cursor()
        query = 'INSERT INTO %s.%s(start_words, status) VALUES ("%s", "alive")' % (spider.crawl_database, spider.crawl_table, re.escape(", ".join(spider.start_words)) )
        log.msg(query)
        cursor.execute(query)
        spider.crawl_id = cursor.lastrowid
        connection.commit()
        
    def spider_closed(self, spider, reason):
        log.msg("the spider has finished")
        log.msg("crawl id : %i" % spider.crawl_id)
        
        connection=MySQLdb.connect(host=MYSQL_HOST, user=MYSQL_USER, passwd=MYSQL_PASSWORD)
        cursor = connection.cursor()
        query = 'UPDATE %s.%s SET status="finished" WHERE id_crawl=%i' % (spider.crawl_database, spider.crawl_table, spider.crawl_id )

        cursor.execute(query)
        connection.commit()
        
        
