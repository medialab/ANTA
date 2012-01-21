# Scrapy settings for googlescrap project
#
# For simplicity, this file contains only the most important settings by
# default. All the other settings are documented here:
#
#     http://doc.scrapy.org/topics/settings.html
#

BOT_NAME = 'googlescrap'
BOT_VERSION = '1.0'

SPIDER_MODULES = ['googlescrap.spiders']
NEWSPIDER_MODULE = 'googlescrap.spiders'
USER_AGENT = '%s/%s' % (BOT_NAME, BOT_VERSION)

FEED_URI = "items.json"
FEED_FORMAT = "json"

ITEM_PIPELINES = ["googlescrap.pipelines.GooglescrapPipeline"]

EXTENSIONS = [
'googlescrap.extensions.spider_status.Spider_status',
]
