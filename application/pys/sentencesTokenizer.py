#!/usr/bin/python
# -*- coding: utf-8 -*-
########################################
import sys
import nltk.data
import json
import codecs

########################################
def giveJson(dict):
	
	return json.dumps(dict, indent=4)
########################################
def tokenize(inPathFile, lang):
	"""
	get a file (absolute path)
	get a language (as in nltk_data/tokenizers/punkt/...) : 'french','english',..
	retun a json containing all sentences
	{
		status:'ok'/'ko'
		error:
		sentences:[]
	}
	"""
	
	res=dict()
	res['status']="ok"
	res['error']="everything fine"
	res['sentences']=[]
	
	try:
		fileIn = codecs.open(inPathFile, "r", "utf-8")
		#u = fileIn.read()
		
	except:
		res['status']="ko"
		res['error']="file does not exist"
		return giveJson(res)
	
	
	alltxt=''.join(fileIn.readlines())
	fileIn.close()
		
	try:
		tokenizer = nltk.data.load('nltk:tokenizers/punkt/'+lang+'.pickle')	
	except:
		res['status']="ko"
		res['error']= "nltk/langage " + lang +"  not found"
		print "Unexpected error:", sys.exc_info()[0]
		return giveJson(res)
	
	try:
		sent=tokenizer.tokenize(alltxt.strip())
		res['status']="ok"
		res['error']="ok"
		res['sentences']=sent
	except:
		res['status']="ko"
		res['error']="problem tokenizing"
		return giveJson(res)
	return giveJson(res)



########################################
def main():
	path=sys.argv[1]
	lang=sys.argv[2]
	print tokenize(path,lang)
########################################
if __name__ == '__main__':
	main()
########################################
