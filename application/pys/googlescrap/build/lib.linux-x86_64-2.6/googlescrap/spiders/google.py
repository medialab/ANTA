from scrapy.contrib.spiders import CrawlSpider, Rule
from scrapy.spider import BaseSpider

from scrapy.contrib.linkextractors.sgml import SgmlLinkExtractor
from scrapy.selector import HtmlXPathSelector
from googlescrap.items import GooglescrapItem
import re
import pprint
import subprocess
from lxml.html import fromstring
import urllib
from tempfile import TemporaryFile 
from urllib import quote, unquote
from googlescrap.pipelines import MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB
import MySQLdb
from scrapy import log

class GoogleSpider(CrawlSpider):
    name = 'google'
    
    crawl_id= 0
    
    start_words = ["brother grimm"]
    
    rules = (
        Rule(
            SgmlLinkExtractor(
                restrict_xpaths="//li[@class='g']//h3/a",
            ),  
            callback='parse_items', 
            follow=False),
    )

    def __init__(self, *a, **kw):
        lang = kw.get("lang") or "en"
        num = kw.get("num") or "100"
        self.base_url = "https://www.google.com/search?hl=en&source=hp&qscrl=1&num=%s&lr=%s&q=" % (num, "lang_" + lang)

        words = kw.get("words") or "brother grimm"

        self.start_words = words.split(";")
        self.start_urls = [ self.base_url + quote(start_word) for start_word in self.start_words]
        
        self.crawl_table = kw.get('crawl_table') or "crawls"
        self.crawl_storage = kw.get('crawl_storage') or "documents"
        self.relation_table = kw.get('relation_table') or "documents_crawls"
        
        self.tags_storage = kw.get('tags_storage') or "tags"
        self.tags_relation_table = kw.get('tags_relation_table') or "documents_tags"
        self.category_storage = kw.get('category_table') or "categories"
        self.language = lang
        
        if "crawl_database" in kw:
            self.crawl_database = kw['crawl_database']
        
        super(GoogleSpider, self).__init__(*a, **kw)

    def parse_items(self, response):
        hxs = HtmlXPathSelector(response)
        item = GooglescrapItem()
        item['title'] = hxs.select("//title/text()")[0].extract()
        f = TemporaryFile("w+")
        f.write(response.body)
        f.seek(0)
        process = subprocess.Popen(["lynx", "-stdin", "-dump", "-nolist", "-nonumbers"], shell=False, stdin=f, stdout=subprocess.PIPE)
        visible_text = process.communicate()[0].decode("utf-8")
        
        item['url'] = response.url
        item['text'] = visible_text
        item['referer'] = unquote(response.request.headers['Referer'].replace(self.base_url, ""))
        
        yield item
        
        
        
        
        
