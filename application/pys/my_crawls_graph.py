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



#print "hi"

def main( mysql_db, crawl_id, output) :
    #print crawl_id
    query = """
    SELECT tags.content AS tag_content, 
           d.content    AS entity_content, 
           start_words, 
           frequency 
    FROM   (SELECT content, 
                   start_words, 
                   frequency, 
                   id_tag 
            FROM   (SELECT re.id_rws_entity, 
                           content, 
                           start_words, 
                           frequency 
                    FROM   (SELECT id_rws_entity, 
                                   frequency, 
                                   start_words 
                            FROM   (SELECT id_document, 
                                           start_words 
                                    FROM   documents_crawls AS dc 
                                           JOIN crawls AS c 
                                             ON dc.id_crawl = c.id_crawl 
                                    WHERE  c.id_crawl = %s) AS a 
                                   JOIN rws_entities_documents_unignored AS redu 
                                     ON redu.id_document = a.id_document) AS b 
                           JOIN rws_entities AS re 
                             ON re.id_rws_entity = b.id_rws_entity) AS c 
                   JOIN rws_entities_tags AS ret 
                     ON ret.id_rws_entity = c.id_rws_entity) AS d 
    LEFT OUTER JOIN tags 
    ON tags.id_tag = d.id_tag  
    ORDER BY start_words
    LIMIT 20
             """ % crawl_id
             
    connection=MySQLdb.connect(host=MYSQL_HOST, user=MYSQL_USER, passwd=MYSQL_PASSWORD, db=mysql_db)
    cursor = connection.cursor()
    cursor.execute(query)

    res = cursor.fetchall()

    g = nx.Graph()

    for key, group in groupby(res, lambda x: x[2]):
        g.add_node(key, type = "start_word")
        #print "added", key, "type : actor"
        for thing in group:
            #print thing
            if thing[1] != key :
                g.add_node(thing[1], frequency = float(thing[3]), type = thing[0] )
            #print "added", thing[1], "type : entity"
            g.add_edge(key, thing[1], weight = float(thing[3]))
        #print " "
    #filename = EXPORT_DIR + "anta-export-graph-" + str(getrandbits(128)) + ".gexf"
    nx.write_gexf( g, output )
    return
    
if __name__ == "__main__" :
    exit(main(sys.argv[1],sys.argv[2],sys.argv[3]))
