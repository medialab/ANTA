#!/usr/bin/python
# -*- coding: utf-8 -*-
import MySQLdb
import re
import string
import time

## simple tina interface class
from medialab_tinaWorker import *
from medialab_usefulScripts import *

## for logging
import traceback
####################################################################################### 
GLOBALAPPPATH='/home/daniele/public/anta/trunk/'
TINACONFIGFILEPATH='/home/daniele/public/anta/trunk/application/pys/tinaconf_anta.yaml'
GLOBALTINADIR='/home/daniele/public/anta/trunk/application/pys/tinafiles/'
GLOBALLOGPATH='/home/daniele/public/anta/trunk/logs/'
EXPORTGEXFPATH='/home/daniele/public/anta/trunk/gexf/'
#######################################################################################
class mysqlWorker:
	DB_HOST = ""
	DB_USER = ""
	DB_PASS = ""
	DB_NAME = ""
	connexion = None
	tinaw = None
	#######################################################################################
	def __init__(self,inUserId,idStatus):
		self.userId=int(inUserId)
		self.statusDBid = int(idStatus)
		self.logfilepath = GLOBALLOGPATH+"tinalog_"+inUserId+".txt"
		# we change cwd to write Tina log in it
		os.chdir(GLOBALLOGPATH)
	#######################################################################################
	def openUserB(self):
		self.connection = MySQLdb.connect( self.DB_HOST, self.DB_USER, self.DB_PASS, self.DB_NAME+'_'+self.userName )
	#######################################################################################
	def closeUserB(self):
		self.connection.commit()
		self.connection.close()
	#######################################################################################
	#######################################################################################
	#### produce ngrams from entire folder
	#### update ngr_entities in DB		
	def addAllDocumentsToDB(self):
		try:
			self.userName = self.getUserName( self.userId )
			self.initTina()
			self.logThis("=====================================================")
			self.logThis("FUNCTION ADD_ALL_DOCS: " + self.userName )
			self.logThis("USAGE: \n1) clear ngrams database\n2) search tina-ngrams on the entire corpus\n3) put them in DB\n")
		except:
			self.manageError("initTina error")
			return -1
			
		################################ MAKE SOURCE CSV
		fcount=-1
		try:
			self.statusToDB("working...","-",1,"making source csv")
			#inDocPath = GLOBALAPPPATH+'/uploads/'+self.userName+'/'+self.inDocumentName
			inDocsPath = GLOBALAPPPATH+'/uploads/'+self.userName+'/'
			csvFilePath = GLOBALTINADIR+"/tina_sourcefiles/"+self.tinaw.sourcecsv
			#mergeTxtFile2TinaCsv(inDocPath, csvFilePath)
			fcount = self.mergeTxtFolderToTina(inDocsPath, csvFilePath)
			self.logThis("tinacsv source made from all docs")
		except:
			self.manageError("merging text file(s) to tinasourcecsv (autorisation? file exists?)")
			if fcount==0:
				self.manageError("no txt files found in the user dir")
			return -1
		
		################################ TINA PRODUCE WHITELIST
		try:
			self.statusToDB("working...","-",1,"(1/2) working producing all ngrams")
			self.tinaw.processTinaSteps(1)
			self.logThis("ngrams file made")
		except:
			self.manageError("producing ngrams-whitelist")
			return -1
		
		############################### ADD NGRAMS TO DB
		try:
			self.statusToDB("working...","-",1,"(2/2) adding all ngrams in DB")
			self.clearAllNgrUserDB()
			self.openUserB()
			self.addNgramsToDB(False) # all docs
			self.closeUserB()
		except:
			self.closeUserB()
			self.manageError("adding ngrams in DB")
			return -1
		
		return 0
	#######################################################################################
	#######################################################################################
	#### take document file
	#### produce ngrams from this unique file
	#### update ngr_entities in DB
	def addDocumentToDB(self,inDocumentId): 
		try:
			self.inDocumentId = inDocumentId
			self.userName = self.getUserName( self.userId )
			self.inDocumentName = self.getDocumentName( self.inDocumentId )
			self.initTina()
			self.logThis("=====================================================")
			self.logThis("FUNCTION ADD_DOCUMENT: " + self.userName + " | "+self.inDocumentName)
			self.logThis("USAGE: \n1) extract tina ngrams of ONE document\n2) update ngram database with the ngrams found\n")
		except:
			self.manageError("initTina error")
			return -1
			
		################################ MAKE SOURCE CSV
		#fcount=-1
		try:
			self.statusToDB("working...","-",1,"making source csv")
			inDocPath = GLOBALAPPPATH+'/uploads/'+self.userName+'/'+self.inDocumentName
			#inDocsPath = GLOBALAPPPATH+'/uploads/'+self.userName+'/'
			csvFilePath = GLOBALTINADIR+"/tina_sourcefiles/"+self.tinaw.sourcecsv
			mergeTxtFile2TinaCsv(inDocPath, csvFilePath)
			#fcount = mergeTxtFiles2TinaCsv(inDocsPath, csvFilePath)
			self.logThis("tinacsv source made from unique text document: "+self.inDocumentName)
		except:
			self.manageError("merging text file(s) to tinasourcecsv (autorisation? file exists?)")
			#if fcount==0:
			#	self.manageError("no txt files found in the user dir")
			return -1
		
		################################ TINA PRODUCE WHITELIST
		try:
			self.statusToDB("working...","-",1,"(1/2) working producing ngrams")
			self.tinaw.processTinaSteps(1)
			self.logThis("ngrams file made")
		except:
			self.manageError("producing ngrams-whitelist")
			return -1
		
		############################### ADD NGRAMS TO DB
		try:
			self.statusToDB("working...","-",1,"(2/2) adding ngrams in DB")
			self.openUserB()
			self.addNgramsToDB(True) # only one doc
			self.closeUserB()
		except:
			self.closeUserB()
			self.manageError("adding ngrams in DB")
			return -1
		
		return 0
	#######################################################################################
	#######################################################################################
	#### make whitelist based on all documents
	#### update whitelist from DB (ngrams choosen in anta interface)
	#### produce graph
	def produceTinaGraph(self,maxNGram,doAll):
		try:
			self.userName = self.getUserName( self.userId )
			#self.statusToDB("tina launched","-",1,"-")
			self.initTina()
			self.logThis("=====================================================")
			self.logThis("FUNCTION PRODUCE_GRAPH for user: " + self.userName)
			self.logThis("USAGE: \n1) merging all .txt files found in the folder to a tina compliant csv file containing all the corpus\n2) dump the ngram database choices to update tina ngrams whitelist\n3) tina-index and tina-produce graph\n4) make sub-graphs : bipartie, only-docs, only-ngrams\n")
		except:
			self.manageError("initTina error")
			return -1
		
		if doAll:
			########################### MAKE SOURCE CSV
			fcount=-1
			try:
				self.statusToDB("working...","-",1,"working making source csv")
				#inDocPath = GLOBALAPPPATH+'/uploads/'+self.userName+'/'+self.inDocumentName
				inDocsPath = GLOBALAPPPATH+'/uploads/'+self.userName+'/'
				csvFilePath = GLOBALTINADIR+"/tina_sourcefiles/"+self.tinaw.sourcecsv
				#mergeTxtFile2TinaCsv(inDocPath, csvFilePath)
				fcount = self.mergeTxtFolderToTina(inDocsPath, csvFilePath)
				self.logThis("tinacsv source made from folder (all txt documents)")
			except:
				self.manageError("merging text file(s) to tinasourcecsv (autorisation? file exists?)")
				if fcount==0:
					self.manageError("no txt files found in the user dir")
				return -1
				
			########################## TINA PRODUCE WHITELIST
			try:
				self.statusToDB("working...","-",1,"(1/4) working producing all ngrams")
				self.tinaw.processTinaSteps(1)
				self.logThis("ngrams file made")
			except:
				self.manageError("producing ngrams-whitelist")
				return -1
		
		########################### MARK WANTED NGRAMS IN WHITELIST
		self.nbNGrams=0
#		if maxNGram==0: ##### BASED ON DB CHOICES
		try:
			self.statusToDB("working...","-",1,"(2/4) update whitelist file based on DB")
			self.openUserB()
			self.nbNGrams = self.updateWhitelistFromDB()
			self.closeUserB()
		except:
			self.closeUserB()
			self.manageError("(2/4) updating whitelist based on DB")
			return -1
#		else: ### BASED ON QUANTITY OF NGRAMS
#			try:
#				self.nbNGrams = maxNGram
#				modifyWhiteList(self.tinaw.ngramcsv,self.tinaw.ngramcsv,maxNgrams)
#				print "TINA whitelist updated"
#			except:
#				self.manageError("updating ngrams-whitelist")
#				return -1

		############################ TINA INDEXING
		try:
			self.statusToDB("working...","-",1,"(3/4) working indexing")
			self.tinaw.processTinaSteps(2)
			self.logThis("indexing made")
		except:
			self.manageError("(3/4) indexing")
			return -1
		
		############################ TINA PRODUCE GRAPH
		graphPath="None"
		try:
			self.statusToDB("working...","-",1,"(4/4) working building graph")
			graphPath = self.tinaw.processTinaSteps(3)
			if graphPath==666:
				self.manageError("(4/4) 666 Tina error making graph")
				return -1
			self.logThis("graph made : "+graphPath)
		except:
			self.manageError("(4/4) producing graph")
			return -1
		
		############################ RENAME GEXF FILE AND UPDATE STATUS WITH GRAPH PATH
		try:
			# rename and move it
			newName = self.userName+"_TinaCooccJaccard_"+str(self.nbNGrams)+"grams_graph.gexf"
			#newGraphPath = '/'.join(graphPath.split('/')[:-1]) +'/'+ newName
			newGraphPath = EXPORTGEXFPATH + newName
			extcmd = subprocess.call(["mv",graphPath,newGraphPath])
			self.graphPath = newGraphPath
			self.logThis("new graph location : "+self.graphPath)
			self.statusToDB("success",newName,"0","Tina coocurrences ("+str(self.nbNGrams)+" ngrams)")
		except:
			self.manageError("(4/4) renaming graph file & adding status in DB")
			return -1
		
		############################ MAKE GEXF GRAPHS : bipartite, only docs, only ngrams
		try:
			removeGexfEdgesTinaGraph(self.graphPath,self.graphPath[:-5]+'_docs.gexf','documents')
			removeGexfEdgesTinaGraph(self.graphPath,self.graphPath[:-5]+'_ngrams.gexf','ngrams')
			removeGexfEdgesTinaGraph(self.graphPath,self.graphPath[:-5]+'_bipart.gexf','bipart')
			#self.logThis("new graph name : "+newGraphPath)
			#self.statusToDB("Tina coocurrences ("+str(self.nbNGrams)+" ngrams)",newName,"0","graph made")
		except:
			self.manageError("(4/4) making alternates graphs (bipartite/docs/ngrams)")
			return -1
			
		return 0
	###############################################################################################################
	### INIT TINA OBJECT
	def initTina(self):
		try:
			################ TINA INIT
			projectName = 'tinaProj_'+self.userName#+'_'+str(time.time())
			self.tinaw = tinaWorker()
			self.tinaw.setTinaDir(GLOBALTINADIR)
			self.tinaw.setProjectName(projectName)
			self.tinaw.setProjectConfig(TINACONFIGFILEPATH)		
		except:
			self.manageError("creating tina object named (config file?)")
			return -1
	#######################################################################################
	def getUserName(self,inUserId):
		try:
			rootConnex = MySQLdb.connect( self.DB_HOST, self.DB_USER, self.DB_PASS, self.DB_NAME )
			cursor = rootConnex.cursor()
			queru='SELECT name FROM users WHERE id_user=%i;'%(inUserId)
			cursor.execute(queru)
			r = cursor.fetchone()
			userName = r[0]
			cursor.close()
			
			rootConnex.close()
			return userName
		except:
			self.logThis("ERROR FATAL WITH USER ID")
			return -1
	#######################################################################################
	def getDocumentName(self,inDocumentId):
		try:	
			self.openUserB()
			cursor = self.connection.cursor()
			queru='SELECT local_url,mimetype FROM documents WHERE id_document=%i;'%(inDocumentId)
			cursor.execute(queru)
			r = cursor.fetchone()
			docUrl = r[0]
			mimetype = r[1]
			
			docName = docUrl
			if mimetype != "text/plain":
				docName += '.txt'
				
			cursor.close()
			self.closeUserB()
			return docName
		except:
			self.closeUserB()
			self.manageError("fetching document name/url from ID")
			return -1
	#######################################################################################		
#		self.statusDBid = -1
#		try:
#			############### CREATES STATUS LINE IN DB
#			self.openUserB()
#			cursor = self.connection.cursor()
#			queru="INSERT INTO graphs (engine,description,localUrl,status,error) VALUES ('%s','%s','%s','%s','%s');" %("TINA","working...","-","1","working")
#			cursor.execute(queru)
#			self.statusDBid = cursor.lastrowid
#			self.closeUserB()
#			you can try it:
#			self.statusToDB("descripttion","emptyPath",-6,"statous")
#		
#		except:
#			self.closeUserB()
#			print "ERROR FATAL WRITTING INITIAL STATUS"
#			return -1
	#######################################################################################
	def logThis(self,instr):
		logStr = "TINA (" + self.userName + ") LOG :    " + instr
		# write local logfile
		logFile=open(self.logfilepath,'a+')
		logFile.write(logStr+"\n")
		logFile.close()
		# write stdout
		print logStr
	###############################
	def manageError(self,error):
		# write status in DB
		self.statusToDB("error","-1","-1",error)
		# write local logfile
		logFile=open(self.logfilepath,'a+')
		logFile.write(traceback.format_exc())
		logFile.close()
		# write stdout
		print "TINA ERROR",error
	#######################################################################################
	def statusToDB(self,description,graphpath,status,error):
		self.openUserB()
		cursor = self.connection.cursor()
		queru="UPDATE graphs SET `engine`='%s',`description`='%s',localUrl='%s',status=%i,`error`='%s' WHERE id_graph=%i ;" %("TINA",description,graphpath,int(status),error,self.statusDBid)
		#print "QU:",queru
		cursor.execute(queru)
		self.closeUserB()
	#######################################################################################
	def clearAllNgrUserDB(self):
		self.openUserB()
		cursor = self.connection.cursor()
		cursor.execute("DELETE FROM ngr_entities WHERE `service`='TI';")
		#cursor.execute("DELETE FROM ngr_entities_documents ;")
		#cursor.execute("DELETE FROM ngr_entities_tags ;")
		cursor.close()
		self.closeUserB()
	#######################################################################################
	# Be careful to only take ngramq from real documents (not deleted ones) !
	def updateWhitelistFromDB(self):
		ngramCsvFilePath = self.tinaw.ngramcsv
		cursor = self.connection.cursor()
		
		csvFile = open(ngramCsvFilePath,"r")
		newLines = []
		reg = re.compile('"","((\w| |\.)+)","(\[.*])",(\d+),".+",\d+,\d+,.+,".+",".+","(.+)",".+"' )
		nKeepedNgrams=0
		nLookedNgrams=0
		nLines=0
		
		for line in csvFile.readlines():
			# keeping header:
			if nLines==0:
				newLines.append(line)
			nLines+=1
			# find ngram
			res = re.findall(reg,line)
			if len(res)>0:
				ngram = res[0][0]
				nLookedNgrams+=1
				
				keepthegram = False
				
				#### Check if ngram is in DB
				queru = 'SELECT `ignore` FROM ngr_entities WHERE content="%s";' %(ngram)
				cursor.execute(queru)
				r = cursor.fetchone()
				if r!=None:
					keepthegram = (r[0]==0)
				if keepthegram:
					#print "========= FOUND NGRAM in DB, adding in whitelist file",ngram
					# marking it and add it
					keepedline = '"w' + line[1:]
					nKeepedNgrams+=1
					newLines.append(keepedline)
		
		csvFile.close()
		
		# replace inFile
		outCsvFile = open(ngramCsvFilePath,"w")
		for l in newLines:
			outCsvFile.write(l)
		outCsvFile.close()
		
		self.logThis( "looking at db, "+str(nKeepedNgrams)+"/"+str(nLookedNgrams)+" ngrams were keeped by user" )
		return nKeepedNgrams
	#######################################################################################
	def addNgramsToDB(self,onlyOneDoc):
		ngramCsvFilePath = self.tinaw.ngramcsv
		self.logThis("EXTRACTING: "+ngramCsvFilePath)
		
		theService="TI"
		cursor = self.connection.cursor()
		csvFile = open(ngramCsvFilePath,"r")
		# EXAMPLE:
		# "","centre for policy research","['NN', 'IN', 'NNP', 'NNP']",1,"centre for policy research",4,1,1.00,"PERIOD",1.00,"PERIO
		
		#,"protocol","['?']",8,"Protocol *** protocol",1,8,"1.00","PERIOD","1.00","PERIOD","PERIOD","1 *** 3 *** 2 *** 5 *** 4 *** 7 *** 6 *** 8","2ea88c7jytdj45bf"
		reg = re.compile('"","((\w| |\.)+)","(\[.*])",(\d+),".+",\d+,\d+,.+,".+",".+","(.+)",".+"' )
		
		nTotalNgrams=0
		nAddedNgrams=0
		
		for line in csvFile.readlines():
			# ie, for each ngram
			res = re.findall(reg,line)
			if len(res)>0:
				ngram = res[0][0]
				# get grammar forms like ['NN', 'IN', 'NNP', 'NNP']
				allgramforms = res[0][2]
				regex = re.compile('[\[\'\] ]+')
				gramforms = re.sub(regex, '', allgramforms)
				thegramforms = gramforms.split(',')
				ngram_nocc = int(res[0][3])
				alldocssources = res[0][4]
				regex = re.compile(' \*\*\* ')
				thedocssources = re.split(regex,alldocssources)
				
				ngram_sign = ngram.replace(" ","")
				nTotalNgrams+=1
				
				#if ngram_nocc>1:
				#	print "========= FOUND NGRAM WITH OCC.>1, great"
				#print "========= FOUND", ngram_nocc, ngram, thegramforms, thedocssources
				
				if len(ngram)>199:
					self.logThis(".. note that one ngram was not added (too long): "+ngram)
				else:
					########## ADDING THE NGRAM (checking if the sign is already in the DB)
					queru='SELECT * FROM ngr_entities WHERE content = "%s";' %(ngram)
					cursor.execute(queru)
					r = cursor.fetchone()
					ngram_id = 0
					if r!=None:
						ngram_id = r[0]
						#print "NGRAM - already there, with id:",ngram_id,
					else:
						try:
							queru="INSERT INTO ngr_entities (sign,content,pid,service) VALUES ('%s','%s',%i,'%s');" %(ngram_sign,ngram,0,theService)
							cursor.execute(queru)
							ngram_id = cursor.lastrowid
							nAddedNgrams+=1
						except:
							self.logThis("PB with query: "+queru)
						#print "NGRAM - not yet inthere, so was added with",ngram_id
	
					########## ADD/GET GRAMMAR TAG CATEGORY
					queru='SELECT id_category FROM categories WHERE content = "grammar";'
					cursor.execute(queru)
					r = cursor.fetchone()
					id_gram_cat = 0
					if r!=None:
						id_gram_cat = r[0]
					else:
						queru="INSERT INTO categories (content) VALUES ('grammar');"
						cursor.execute(queru)
						id_gram_cat = cursor.lastrowid
					
					########## ADDING GRAMMAR FORMS AS TAGS
					for gramar in thegramforms:
						# check if grammar-tag is already there
						queru='SELECT * FROM tags WHERE content = "%s";' %(gramar)
						cursor.execute(queru)
						r = cursor.fetchone()
						if r==None:
							queru="INSERT IGNORE INTO tags (content,id_category) VALUES ('%s',%i);" %(gramar,id_gram_cat)
							cursor.execute(queru)
							tag_id = cursor.lastrowid
							#print "GrammarForm was not there added as id:", tag_id
						else:
							tag_id = r[0]
							#print "GrammarForm was already there with id:", tag_id
						
						# creates link betweel ngram @ his tags
						#print "ADDING NGRAM-TAG link"
						queru="INSERT IGNORE INTO ngr_entities_tags (id_ngr_entity,id_tag) VALUES (%i,%i);" %(ngram_id,tag_id)
						cursor.execute(queru)
					########################################################### ADDING DOCUMENT SOURCES
					###################### IF TINA ON ONLY ONE DOCUMENT
					if onlyOneDoc: 
						#print "ADDING NGRAM-DOCUMENT link"
						ngram_relevance = 0.9
						#queru="INSERT IGNORE INTO ngr_entities_documents (id_ngr_entity,id_document,relevance,frequency) VALUES (%i,%i,%f,%i) ON DUPLICATE KEY UPDATE frequency=frequency+%i" %(ngram_id, self.inDocumentId,ngram_relevance,ngram_nocc,ngram_nocc)
						queru="INSERT IGNORE INTO ngr_entities_documents (id_ngr_entity,id_document,relevance,frequency) VALUES (%i,%i,%f,%i)" %(ngram_id, self.inDocumentId, ngram_relevance, 1)
						cursor.execute(queru)
						queru="UPDATE documents SET `status`='indexed' WHERE id_document=%i" %(self.inDocumentId)
						cursor.execute(queru)
						self.logThis("Document "+str(self.inDocumentId)+" set as indexed")
					##################### IF ALL DOCUMENTS
					else: 
						ngram_relevance = 0.9
						for doc in thedocssources:
							docum_id = int( doc.split('_')[1] )
							#self.logThis("ADDING NG-DOC LINK "+str(ngram_id)+" with "+str(docum_id) )
							queru="INSERT IGNORE INTO ngr_entities_documents (id_ngr_entity,id_document,relevance,frequency) VALUES (%i,%i,%f,%i);" %(ngram_id, docum_id, ngram_relevance, 1)
							cursor.execute(queru)
						
		self.logThis("DB Updated: "+str(nTotalNgrams)+" ngrams processed ("+str(nAddedNgrams)+" were actually added in the DB - others were already there)")
		
		if not onlyOneDoc:
			# setting (status=indexed) for all documents
			queru="UPDATE documents SET `status`='indexed'"
			cursor.execute(queru)
			self.logThis("Status set to 'indexed' for all documents")
		
		csvFile.close()
		cursor.close()
	#######################################################################################
	def mergeTxtFolderToTina(self,inFolderPath,outFilePath):
		self.openUserB()
		
		fileOut = open(outFilePath,"w")
		fileOut.write('"corp_id","doc_id","title","acronym","abstract"')		
		fCount=0
		contents = os.listdir(inFolderPath)
		for a in contents:
			if a.endswith('.txt'):
				# get id from database
				doc_id = self.getDocumentId(a)
				if doc_id!=-1:
					#print "DOING: ",thePath+a
					oneFile = open(inFolderPath+a,"r")
					alltext=""
					for morc in oneFile.readlines():
						alltext+=morc
					oneFile.close()
					fCount+=1
					doc_name = "doc_"+str(doc_id)
					writeOneTextonFile("PERIOD",doc_name,doc_name,cleanBigStr(alltext),fileOut)
		#print "MLAB_USEFUL",fcount,"files were merged in",outFilePath
		fileOut.close()
		self.closeUserB()
		return fCount
	#######################################################################################
	def getDocumentId(self,documentName):
		outId = -1
		cursor = self.connection.cursor()
		txtDocumentName = documentName
		try:
			if txtDocumentName.endswith('pdf.txt'):
				txtDocumentName = documentName[:-4]
			else:
				if txtDocumentName.endswith('.txt'):
					txtDocumentName = documentName
				
			queru='SELECT id_document FROM documents WHERE local_url="%s";'%(txtDocumentName)
			#self.logThis("B.QUERY: " + queru)
			cursor.execute(queru)
			r = cursor.fetchone()
			outId = int(r[0])
			#self.logThis("C.id found "+ str(outId))
			cursor.close()
		except:
			self.logThis(".. note that a doc was not found: " + txtDocumentName)
			cursor.close()
		return outId
################################################################################################









