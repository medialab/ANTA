# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#     * Rearrange models' order
#     * Make sure each model has one field with primary_key=True
# Feel free to rename the models, but don't rename db_table values or field names.
#
# Also note: You'll have to insert the output of 'django-admin.py sqlcustom [appname]'
# into your database.

from django.db import models

class Categories(models.Model):
    id_category = models.IntegerField(primary_key=True)
    content = models.CharField(unique=True, max_length=765)
    type = models.CharField(max_length=12)
    class Meta:
        app_label = ''
        db_table = u'categories'



class Documents(models.Model):
    id_document = models.IntegerField(primary_key=True)
    title = models.CharField(max_length=600)
    description = models.TextField()
    mimetype = models.CharField(max_length=150)
    size = models.IntegerField()
    language = models.CharField(max_length=6)
    date = models.DateTimeField()
    local_url = models.TextField()
    remote_url = models.TextField()
    status = models.CharField(max_length=24)
    ignore = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'documents'

class Sentences(models.Model):
    id_sentence = models.IntegerField(primary_key=True)
    id_document = models.ForeignKey(Documents, db_column='id_document')
    position = models.IntegerField()
    content = models.TextField()
    class Meta:
        app_label = ''
        db_table = u'sentences'

class CoOccurrences(models.Model):
    id_co_occurrence = models.IntegerField(primary_key=True)
    id_document = models.ForeignKey(Documents, db_column='id_document')
    id_sentence = models.ForeignKey(Sentences, db_column='id_sentence')
    stem_a = models.CharField(max_length=150, db_column='stem_A') # Field name made lowercase.
    stem_b = models.CharField(max_length=150, db_column='stem_B') # Field name made lowercase.
    word_a = models.CharField(max_length=150, db_column='word_A') # Field name made lowercase.
    word_b = models.CharField(max_length=150, db_column='word_B') # Field name made lowercase.
    distance = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'co_occurrences'

class Crawls(models.Model):
    id_crawl = models.IntegerField(primary_key=True)
    start_words = models.TextField()
    request_url = models.TextField()
    creation_date = models.DateTimeField()
    status = models.CharField(max_length=24)
    documents = models.ManyToManyField(Documents, through='documents_crawls')
    class Meta:
        app_label = ''
        db_table = u'crawls'

class Projects(models.Model):
    id_project = models.IntegerField(primary_key=True)
    title = models.IntegerField(unique=True)
    creation_date = models.DateTimeField()
    last_modified_date = models.DateTimeField()
    class Meta:
        app_label = ''
        db_table = u'projects'

class Tags(models.Model):
    id_tag = models.IntegerField(primary_key=True)
    content = models.CharField(unique=True, max_length=765)
    id_category = models.ForeignKey(Categories, db_column='id_category')
    parent_id_tag = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'tags'
        
#class DocumentsCrawls(models.Model):
#    id_document = models.ForeignKey(Documents, db_column='id_document')
#    id_crawl = models.ForeignKey(Crawls, db_column='id_crawl')
#    class Meta:
#        app_label = ''
#        db_table = u'documents_crawls'

class DocumentsMetrics(models.Model):
    number_of_documents = models.BigIntegerField()
    class Meta:
        app_label = ''
        db_table = u'documents_metrics'

class DocumentsProjects(models.Model):
    id_document = models.ForeignKey(Documents, db_column='id_document')
    id_project = models.ForeignKey(Projects, db_column='id_project')
    class Meta:
        app_label = ''
        db_table = u'documents_projects'

class DocumentsTags(models.Model):
    id_document = models.ForeignKey(Documents, db_column='id_document')
    id_tag = models.ForeignKey(Tags, db_column='id_tag')
    class Meta:
        app_label = ''
        db_table = u'documents_tags'

class Graphs(models.Model):
    id_graph = models.IntegerField(primary_key=True)
    engine = models.CharField(max_length=192)
    description = models.CharField(max_length=600)
    date = models.DateTimeField()
    localurl = models.TextField(db_column='localUrl') # Field name made lowercase.
    status = models.IntegerField()
    error = models.TextField()
    class Meta:
        app_label = ''
        db_table = u'graphs'

class NgrEntities(models.Model):
    id_ngr_entity = models.IntegerField(primary_key=True)
    sign = models.CharField(max_length=96)
    content = models.CharField(unique=True, max_length=600)
    pid = models.IntegerField()
    service = models.CharField(max_length=6)
    ignore = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'ngr_entities'

class NgrEntitiesDocuments(models.Model):
    id_ngr_entity = models.ForeignKey(NgrEntities, db_column='id_ngr_entity')
    id_document = models.ForeignKey(Documents, db_column='id_document')
    relevance = models.FloatField()
    frequency = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'ngr_entities_documents'

class NgrEntitiesTags(models.Model):
    id_ngr_entity = models.ForeignKey(NgrEntities, db_column='id_ngr_entity')
    id_tag = models.ForeignKey(Tags, db_column='id_tag')
    class Meta:
        app_label = ''
        db_table = u'ngr_entities_tags'

class Occurrences(models.Model):
    id_occurrence = models.IntegerField(primary_key=True)
    id_document = models.ForeignKey(Documents, db_column='id_document')
    id_sentence = models.ForeignKey(Sentences, db_column='id_sentence')
    stem = models.CharField(max_length=150)
    word = models.CharField(max_length=150)
    class Meta:
        app_label = ''
        db_table = u'occurrences'



class ProjectsTags(models.Model):
    id_project = models.ForeignKey(Projects, db_column='id_project')
    id_tag = models.ForeignKey(Tags, db_column='id_tag')
    class Meta:
        app_label = ''
        db_table = u'projects_tags'

class RwsEntities(models.Model):
    id_rws_entity = models.IntegerField(primary_key=True)
    sign = models.CharField(max_length=96)
    content = models.CharField(unique=True, max_length=600)
    pid = models.IntegerField()
    service = models.CharField(max_length=6)
    ignore = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'rws_entities'

class RwsEntitiesDistribution(models.Model):
    id_rws_entity = models.IntegerField()
    distribution = models.BigIntegerField()
    class Meta:
        app_label = ''
        db_table = u'rws_entities_distribution'

class RwsEntitiesDocuments(models.Model):
    id_rws_entity = models.ForeignKey(RwsEntities, db_column='id_rws_entity')
    id_document = models.ForeignKey(Documents, db_column='id_document')
    relevance = models.FloatField()
    frequency = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'rws_entities_documents'

class RwsEntitiesDocumentsUnignored(models.Model):
    content = models.CharField(max_length=600)
    id_rws_entity = models.IntegerField()
    id_document = models.IntegerField()
    frequency = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'rws_entities_documents_unignored'

class RwsEntitiesMetrics(models.Model):
    number_of_entities = models.BigIntegerField()
    class Meta:
        app_label = ''
        db_table = u'rws_entities_metrics'

class RwsEntitiesPerDocuments(models.Model):
    id_document = models.IntegerField()
    entitites_per_document = models.BigIntegerField()
    class Meta:
        app_label = ''
        db_table = u'rws_entities_per_documents'

class RwsEntitiesTags(models.Model):
    id_rws_entity = models.ForeignKey(RwsEntities, db_column='id_rws_entity')
    id_tag = models.ForeignKey(Tags, db_column='id_tag')
    class Meta:
        app_label = ''
        db_table = u'rws_entities_tags'

class RwsMetricsTf(models.Model):
    id_rws_entity = models.IntegerField()
    frequency = models.IntegerField()
    id_document = models.IntegerField()
    entity_frequency = models.DecimalField(null=True, max_digits=16, decimal_places=4, blank=True)
    class Meta:
        app_label = ''
        db_table = u'rws_metrics_tf'

class RwsMetricsTfIdf(models.Model):
    id_rws_entity = models.IntegerField()
    df = models.DecimalField(null=True, max_digits=25, decimal_places=4, blank=True)
    tf = models.DecimalField(null=True, max_digits=16, decimal_places=4, blank=True)
    idf = models.FloatField(null=True, blank=True)
    tf_idf = models.FloatField(null=True, blank=True)
    class Meta:
        app_label = ''
        db_table = u'rws_metrics_tf_idf'



class SuperEntities(models.Model):
    id_super_entity = models.IntegerField(primary_key=True)
    pid = models.IntegerField()
    content = models.TextField(blank=True)
    sign = models.CharField(unique=True, max_length=600, blank=True)
    ignore = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'super_entities'

class SuperEntitiesTags(models.Model):
    id_super_entity = models.IntegerField(primary_key=True)
    id_tag = models.IntegerField()
    class Meta:
        app_label = ''
        db_table = u'super_entities_tags'



