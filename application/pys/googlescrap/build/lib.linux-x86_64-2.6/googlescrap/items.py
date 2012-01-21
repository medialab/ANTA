# Define here the models for your scraped items
#
# See documentation in:
# http://doc.scrapy.org/topics/items.html

from scrapy.item import Item, Field

class GooglescrapItem(Item):
    # define the fields for your item here like:
    url = Field()
    title = Field()
    text = Field()
    referer = Field()
    
