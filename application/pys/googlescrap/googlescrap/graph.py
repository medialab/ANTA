import MySQLdb

MYSQL_HOST = "jiminy.medialab.sciences-po.fr"
MYSQL_USER = "anta"
MYSQL_PASSWORD = "diaballein"
MYSQL_DB = "anta_patrick"

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
                                WHERE  c.id_crawl = 13) AS a 
                               JOIN rws_entities_documents_unignored AS redu 
                                 ON redu.id_document = a.id_document) AS b 
                       JOIN rws_entities AS re 
                         ON re.id_rws_entity = b.id_rws_entity) AS c 
               JOIN rws_entities_tags AS ret 
                 ON ret.id_rws_entity = c.id_rws_entity) AS d 
       LEFT OUTER JOIN tags 
         ON tags.id_tag = d.id_tag  """
         
connection=MySQLdb.connect(host=MYSQL_HOST, user=MYSQL_USER, passwd=MYSQL_PASSWORD)
cursor = connection.cursor()
cursor.execute(query)

r=connection.store_result()

