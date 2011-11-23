#!/usr/bin/python
# -*- coding: utf-8 -*-
##################################################################
# VERSION 26/05/2011 pm 
# server version
##################################################################
import sys
import os
import string
import re
import subprocess
##################################################################
##################################################################
def makeJsonFromNgramTinaCsv(inFilePath):
	patt = re.compile(',"((\w| |\.|-)*)","\[.*]",(\d+)')
	outJson = []
	theFile = open(inFilePath,"r")
	for l in theFile.readlines():
		ff = re.search(patt,l)
		if ff!=None:
			ngram = ff.group(1)
			nocc = int(ff.group(3))
			if nocc>5:
				#print "FOUND NGRAM",ngram,nocc
				outJson.append({'ngram':ngram,'n':nocc})
	return outJson
##################################################################
##################################################################
def removeFilesInDir(folderPath):
	nFiles=0
	if len(folderPath)>7:
		if not folderPath.endswith("/"):
			folderPath+="/"
		contents = os.listdir(folderPath)
		for f in contents:
			nFiles+=1
			extcmd = subprocess.call(["rm", folderPath+f])
	return nFiles
##################################################################
##################################################################
def cleanBigStr(inStr):
	# REMOVE UNWANTED CHARS
	outstr1=inStr
	#remove linebreaks
	regex = re.compile('(\n{1,})')
	outstr1 = re.sub(regex, ' ', outstr1)
	#remove longs sequences of '7.3.6.7.43.22.3'
	regex = re.compile('[\d\. ]{3,}')
	outstr1 = re.sub(regex, ' ', outstr1)
	#remove longs sequences of 'I.V.VI.VI.II'
	regex = re.compile('[IVX\.]{3,}')
	outstr1 = re.sub(regex, ' ', outstr1)
	#remove quoting chars
	tr = string.maketrans("\""," ")
	outstr1 = string.translate(outstr1,tr)
	#just keep cool chars
	regex = re.compile('[^\w^\s^\(^\)^\/^\.^\:^,^-]')
	outstr1 = re.sub(regex, ' ', outstr1)
	#remove longs sequences of '... ... . . .'
	regex = re.compile('[…\.]{2,}')
	outstr1 = re.sub(regex, ' ', outstr1)
	#remove long seqs of whitespaces
	regex = re.compile('( {1,})')
	outstr1 = re.sub(regex, ' ', outstr1)
#	limit=79900
#	if len(outstr1)>limit:
#		outstr1 = outstr1[:limit]
	return outstr1
######################################################################
def cleanAndUpdateTxtFile(inPath):
	inFile = open(inPath,'r')
	newT=""
	for l in inFile.readlines():
		newT += l
	inFile.close()
	outFile = open(inPath,'w')
	outFile.write( cleanBigStr(newT) )
	outFile.close()
def cleanAndUpdateTxtFolder(inPath):
	contents = os.listdir(inPath)
	for a in contents:
		if a.endswith('.txt'):
			cleanAndUpdateTxtFile(inPath+a)	
######################################################################
def writeTinaCsvLineOnFile(theperiod,inId,inName,inStr,outFile):
	ligne = '\n"'+theperiod+'","'+str(inId)+'","'+inName+'","'+inName+'","'+inStr+'"'
	outFile.write(ligne)
#####################
def mergeFilesToTinaCsv(thePath,outFile):
	fCount=0
	contents = os.listdir(thePath)
	for a in contents:
		if a.endswith('.txt'):
			#print "DOING: ",thePath+a
			oneFile = open(thePath+a,"r")
			alltext=""
			for morc in oneFile.readlines():
				alltext+=morc
			oneFile.close()
			#print "ADDED FILE: ",a
			fCount+=1
			writeTinaCsvLineOnFile("PERIOD",a,a,cleanBigStr(alltext),outFile)
	return fCount
#####################
def mergeTxtFiles2TinaCsv(inFolderPath,outFilePath):
	fileOut = open(outFilePath,"w")
	fileOut.write('"corp_id","doc_id","title","acronym","abstract"')		
	fcount = mergeFilesToTinaCsv(inFolderPath, fileOut)
	#print "MLAB_USEFUL",fcount,"files were merged in",outFilePath
	fileOut.close()
	if fcount==0:
		return -1
	else:
		return 0
#####################
def mergeTxtFile2TinaCsv(inFilePath,outFilePath):
	fileOut = open(outFilePath,"w")
	fileOut.write('"corp_id","doc_id","title","acronym","abstract"')		
	
	inFile = open(inFilePath,"r")
	alltext=""
	for morc in inFile.readlines():
		alltext+=morc
	inFile.close()
	
	fileName = inFilePath.split('/')[-1]
	writeTinaCsvLineOnFile("PERIOD",fileName,fileName,cleanBigStr(alltext),fileOut)
	#print "MLAB_USEFUL file merged in",outFilePath
	fileOut.close()
##################################################################
##################################################################
# MODIFY TINA WHITELIST FROM REGULAR EXPRESSION
# (detect th lines in the whitelist and puts "w" on entities we want to keep
# USAGE : 
#patternsToWhiten.append( re.compile('("",)(?="((\w| )*( )+)*(THEPATTERNWEWANTTOFIND)(\w| )*","\[)') )
def modifyWhiteListReverseOnly2(filePath,newPath,maxNgram):
	# Keeping the original
	savedPath = filePath
	try:
		savedPath = filePath[:-4]+'_old.csv'
		extcmd = subprocess.call(["mv", filePath, savedPath])
	except:
		print "MLAB_USEFUL - note that file wasn't renamed (subprocess problem)",savedPath
		
	patt = re.compile('("",)(?="((\w| |\.|-)*)","\[.*]",(\d+))')
	
	fileIn = open(savedPath,"r")
	
	newText=""
	inLine=""
	totalWhites=0
	totalLines=0
	nocc=0
	
	newLines=[]
	storedAllLines=[]
	
	# STORE NGRAM_NOCCS_LINE DISTRIBUTION
	for i in fileIn.readlines():
		# WRITE HEADER
		if totalLines==0:
			newLines.append(i)
		totalLines+=1
		inLine=i
		ff = re.search(patt,inLine)
		if ff!=None:
			ngram = ff.group(2)
			nocc = int(ff.group(4))
			if(isCoolNgram(ngram)) and nocc<3:
				storedAllLines.append([nocc,inLine])
	
	# SORT LINES BASED ON NOCC
	storedAllLines = sorted(storedAllLines)
	
	# PRODUCE OUTPUT
	for l in storedAllLines:
		if totalWhites < maxNgram:
			totalWhites+=1
			newLines.append( re.sub(patt,'"w",',l[1]) )			
	
	fileIn.close()
	
	# WRITE IT DOWN
	#fileOut = open(filePath[:-4]+"_w_updated.csv","w")
	fileOut = open(newPath,"w")
	for l in newLines:
		fileOut.write(l)
	fileOut.close()
	
	print "MLAB_USEFUL WHITE LIST MODIFIED : "+str(totalWhites)+" lines marked in "+str(totalLines)+" in path:"+newPath
def modifyWhiteList(filePath,newPath,maxNgram):	
	# Keeping the original
	savedPath = filePath
	try:
		savedPath = filePath[:-4]+'_old.csv'
		extcmd = subprocess.call(["mv", filePath, savedPath])
	except:
		print "MLAB_USEFUL - note that file wasn't renamed (subprocess problem)",savedPath
		
	patt = re.compile('("",)(?="((\w| |\.|-)*)","\[.*]",(\d+))')
	
	fileIn = open(savedPath,"r")
	
	newText=""
	inLine=""
	totalWhites=0
	totalLines=0
	nocc=0
	
	newLines=[]
	storedAllLines=[]
	
	# STORE NGRAM_NOCCS_LINE DISTRIBUTION
	for i in fileIn.readlines():
		# WRITE HEADER
		if totalLines==0:
			newLines.append(i)
		totalLines+=1
		inLine=i
		ff = re.search(patt,inLine)
		if ff!=None:
			ngram = ff.group(2)
			nocc = int(ff.group(4))
			if(isCoolNgram(ngram)):
				storedAllLines.append([nocc,inLine])
	
	# SORT LINES BASED ON NOCC
	storedAllLines = sorted(storedAllLines,reverse=True)
	
	# PRODUCE OUTPUT
	for l in storedAllLines:
		if totalWhites < maxNgram:
			totalWhites+=1
			newLines.append( re.sub(patt,'"w",',l[1]) )			
	
	fileIn.close()
	
	# WRITE IT DOWN
	#fileOut = open(filePath[:-4]+"_w_updated.csv","w")
	fileOut = open(newPath,"w")
	for l in newLines:
		fileOut.write(l)
	fileOut.close()
	
	print "MLAB_USEFUL WHITE LIST MODIFIED : "+str(totalWhites)+" lines marked in "+str(totalLines)+" in path:"+newPath
################################
def isCoolNgram(inGram):
	# removes
	cool = True
	if len(inGram)<3:
		cool = False
	patt = re.compile('(\w\s)+\w')
	ff = re.match(patt,inGram)
	if ff!=None:
		cool = False
	return cool
##################################################################
##################################################################
def removeGexfEdgesTinaGraph(inFilePath,outFilePath,what):
	#<node id="Document::93" label="p102.txt">
	#    <attvalues>
	#        <attvalue for="0" value="1.00" />
	#        <attvalue for="1" value="Document" />
	#        <attvalue for="2" value="POA no content (toobig)" />
	#        <attvalue for="3" value="" />
	#    </attvalues>
	#</node>
	#
	#<node id="NGram::ffda893313b15e7a52ddd027f4cca6028ca5bfdbe6404ed259def21a7fd1ba16" label="induces">
	#    <attvalues>
	#        <attvalue for="0" value="22.00" />
	#        <attvalue for="1" value="NGram" />
	#    </attvalues>
	#</node>
	#
	#<edge id="1243" source="NGram::9e3a5313b0ac1bca48902d4fda4ce48adb9f59b8aa95904b4cb7f371d743de4c" target="NGram::12ea12eace7d655f471ce55e34f89b1b77a3d9d05a445ca82877dd2235beaa51" type="directed" weight="3.000000"></edge>
	eNgNg = re.compile( '<edge id="\d+" source="NGram.*target="NGram.* weight="((\d)*)\..*"></edge>' )
	eDocDoc = re.compile( '<edge id="\d+" source="Document.*target="Document.* weight="((\d)*)\..*"></edge>' )
	eDocNg = re.compile( '<edge id="\d+" source="Document.*target="NGram.* weight="((\d)*)\..*"></edge>' )
	eNgDoc = re.compile( '<edge id="\d+" source="NGram.*target="Document.* weight="((\d)*)\..*"></edge>' )
	#nDoc = re.compile( '<node id="Document.* label=".*">' )
	#nNg = re.compile( '<node id="NGram.* label=".*">' )
	if what=='ngrams':
		modifyGexfTinaGraphWithRE(inFilePath,outFilePath,eDocDoc)
		modifyGexfTinaGraphWithRE(outFilePath,outFilePath,eDocNg)
		modifyGexfTinaGraphWithRE(outFilePath,outFilePath,eNgDoc)
	if what=='documents':
		modifyGexfTinaGraphWithRE(inFilePath,outFilePath,eNgNg)
		modifyGexfTinaGraphWithRE(outFilePath,outFilePath,eDocNg)
		modifyGexfTinaGraphWithRE(outFilePath,outFilePath,eNgDoc)
	if what=='bipart':
		modifyGexfTinaGraphWithRE(inFilePath,outFilePath,eNgNg)
		modifyGexfTinaGraphWithRE(outFilePath,outFilePath,eDocDoc)
		modifyGexfTinaGraphWithRE(outFilePath,outFilePath,eNgDoc) # cause appears 2 times
	print "MLAB_USEFUL GEXF ["+what+"] made"
############################
def modifyGexfTinaGraphWithRE(inFilePath,outFilePath,inRegExpr):
	fileIn = open(inFilePath,"r")
	newText=""
	inLine=""
	totalWhites=0
	totalLines=0
	for i in fileIn.readlines():
		inLine=i
		totalLines+=1
		founded = re.search(inRegExpr,inLine)
		if founded!=None: # do not copy it
			totalWhites+=1
		else: # copy it
			newText += inLine
	fileIn.close()
	
	fileOut = open(outFilePath,"w")
	fileOut.write(newText)
	fileOut.close()
	print "MLAB_USEFUL GEXF FILE MODIFIED : "+str(totalWhites)+" lines removed in "+str(totalLines)+" (left: "+str(totalLines-totalWhites)+")"
##################################################################
##################################################################
if __name__ == '__main__':
	#print "ARGUMENTS",sys.argv
	comd=sys.argv[1]
	arg=sys.argv[2]
	if comd=="convertpdf2txt":
		print "CONVERTING ",arg
		convertPdfsInAllSubFoldersDir(arg)
	if comd=="makecsvfromtxt":
		print "MERGING TXT FILES FROM ", arg
		mergeTxtFiles2TinaCsv(arg,sys.argv[3])
	if comd=='mw':
		maxNG=sys.argv[3]
		print "MODIFY WHITELIST ",arg,maxNG
		modifyWhiteList(arg,arg,maxNG)
##################################################################
##################################################################
