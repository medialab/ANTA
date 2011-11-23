#!/usr/bin/python
# -*- coding: utf-8 -*-
from tinasoft import PytextminerApi
#########################################################  
class tinaWorker:
	def __init__(self):
		self.period = "PERIOD"
	##################################
	def setTinaDir(self,inDir):
		self.tinadir = inDir
		self.tinasourcefiles = self.tinadir+"tina_sourcefiles/"
	def setProjectName(self,inStr):
		self.projectName = inStr
		self.sourcecsv = self.projectName+"_source.csv"
		self.ngramcsv = self.tinadir+"tina_whitelists/"+self.projectName+"_ngrams.csv"
	def setProjectConfig(self,inStr):
		self.tinasoft = PytextminerApi(inStr)
	##################################	
	def processTinaSteps(self,step):
		##############
		if step==1:
			#print "TINA INTERFACE PRODUCING WHITELIST.CSV…"
			extract_res = self.tinasoft.extract_file(
					self.sourcecsv,
					self.projectName,
					outpath=self.ngramcsv,
					format="tinacsv",
					minoccs=1,
			)
			#print "TINA INTERFACE RES: ",extract_res
		##############
		if step==2:
			#print "TINA INTERFACE INDEXING..."
			index_res = self.tinasoft.index_file(
					self.sourcecsv,
					self.projectName,
					whitelistpath=self.ngramcsv,
					format="tinacsv",
			)
			#print "TINA INTERFACE RES: ",index_res
		##############
		# Exporting to current.
		if step==3:
			#print "TINA INTERFACE PRODUCING GRAPH..."
			generg_res = self.tinasoft.generate_graph(
					self.projectName,
					self.period,
					#whitelistpath = self.ngramcsv,
					outpath = 'test_graph',
					ngramgraphconfig={
					#	'edgethreshold': [1.0,'inf'],
					#	'nodethreshold': [1,'inf'],
					#	'alpha': 0.1,
						'proximity': "Cooccurrences"
					#	'proximity': "EquivalenceIndeX"
					#	'proximity': "PseudoInclusion"
					},
					documentgraphconfig={
					#	'edgethreshold': [1.0,'inf'],
					#	'nodethreshold': [1,'inf'],
					#	'proximity': "sharedNGrams"
						'proximity': "logJaccard"
					},
					exportedges=True
			)
			return generg_res
#########################################################