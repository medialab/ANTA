#!/usr/bin/env python
#-*- coding:utf-8 -*-

import MySQLdb
from itertools import groupby
import networkx as nx
import sys
from random import getrandbits

sys.stderr = sys.stdout

MYSQL_HOST = "localhost"
MYSQL_USER = "anta"
MYSQL_PASSWORD = "diaballein"

def main( mysql_db, crawl_id, output) :
    query = """
SELECT c.id_crawl, c.start_words, d1.title, d1.remote_url, r1.content as entity, r.frequency, r.relevance, tags.content as tag 
FROM crawls c, documents_crawls d, documents d1, rws_entities_documents r, rws_entities r1 
LEFT OUTER JOIN rws_entities_tags rt ON rt.id_rws_entity=r1.id_rws_entity
LEFT OUTER JOIN tags ON rt.id_tag=tags.id_tag
WHERE c.id_crawl = %s AND d.id_crawl=c.id_crawl AND d.id_document=d1.id_document AND r.id_document=d1.id_document AND r.id_rws_entity=r1.id_rws_entity ORDER BY r.relevance 
             """ % crawl_id
             
    connection=MySQLdb.connect(host=MYSQL_HOST, user=MYSQL_USER, passwd=MYSQL_PASSWORD, db=mysql_db)
    cursor = connection.cursor()
    cursor.execute(query)

    res = cursor.fetchall()

    g = nx.DiGraph()

    for keyword, group in groupby(res, lambda x: x[1]):
            g.add_node(keyword, type = "keyword")
            #print "added", key, "type : actor"
            for thing in group:
                entity    = thing[4]
                if entity == keyword : entity += " "
                url       = thing[3]
                subdomain = url.replace("http://","").replace("www","").replace("https://", "").split("/")[0]

                frequency = thing[5]
                tag       = thing[7]
                #print keyword, subdomain, entity, frequency, tag
                g.add_node(entity, frequency = int(frequency), tag = tag or "", type="ngram" ) # add entity
                g.add_node(subdomain, type="subdomain") # add subdomain
                g.add_edge(keyword, entity, weight = frequency)
                g.add_edge(keyword, subdomain, weight = frequency)
                g.add_edge(subdomain, entity, weight = frequency)
    nx.write_gexf( g, output )
    return
    
if __name__ == "__main__" :
    exit(main(sys.argv[1],sys.argv[2],sys.argv[3]))
