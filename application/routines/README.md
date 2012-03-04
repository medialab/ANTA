# Query to set documents stqtus indexed based upon stored entities (with a threshold), a lot of time
UPDATE documents SET status="indexed" WHERE id_document IN ( 
SELECT `id_document` FROM `rws_entities_documents` GROUP BY (id_document ) HAVING count( `id_rws_entity` ) > 30 ) LIMIT 100

# select entities distribution
SELECT `id_rws_entity`, count( `id_document` ) FROM `rws_entities_documents` GROUP BY ( `id_rws_entity` ) HAVING count( `id_document` ) > 1

# filter by count
UPDATE rws_entities SET `ignore`=1 WHERE `id_rws_entity` EXISTS (  SELECT `id_rws_entity` FROM `rws_entities_documents` GROUP BY ( `id_rws_entity` ) HAVING count( `id_document` ) < 2 ) LIMIT 10

# or use
SELECT * FROM `rws_entities_distribution` WHERE distribution < 2