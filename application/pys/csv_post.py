# -*- coding: utf-8 -*-
###########################################################################
import httplib
import base64
from urllib import urlencode
import json
import time
from pprint import pprint
import sys

import os
# to parse csv
import csv

sys.stderr = sys.stdout

# http://pypi.python.org/pypi/phpserialize
from phpserialize import *

BASE_ANTA_URL = 'jiminy.medialab.sciences-po.fr'
ANTA_AUTH = '/anta_dev/api/authenticate?'
ANTA_UPLOAD = '/anta_dev/api/item-upload/user/'
#http://jiminy-dev.medialab.sciences-po.fr/anta_dev/api/authenticate?username=solairemed&password=solaire
#http://jiminy-dev.medialab.sciences-po.fr/anta_dev/api/item-upload/user/25a?debug=true&item=blablabla&token=97db82ad00b8c867ba32cc84a44d3ec9

################################################
def antapost_authenticate(username,passw):
	# Authenticate
	params = {'username':username,'password':passw}
	conn = httplib.HTTPConnection(BASE_ANTA_URL)
	conn.request("GET", ANTA_AUTH + urlencode(params))
	response = conn.getresponse()
	#print "status: "+str(response.status),response.reason
	data = json.loads(response.read())
	#print "SERVER RESPONSE"
	#print data
	auth = data['authenticated_user']['id']
	token = data['token']
	print 'pqm:',auth
	print 'token:',token
	return auth, token
################################################
def antapost_upload(antakey,token,data):	
	# encode data
	data = base64.b64encode(dumps(data))
	#print "64ENCODED_EXCERPT:"+data[:50]
	
	params = { 'debug':'false','item':data, 'verbose':'false', 'token':token }
	conn = httplib.HTTPConnection(BASE_ANTA_URL)
	headers = {"Content-type": "application/x-www-form-urlencoded", "Accept": "text/plain"}
	conn.request("POST", ANTA_UPLOAD + antakey, urlencode(params), headers)
	
	response = conn.getresponse()
	json_response = response.read()
	print "status:"+str(response.status),response.reason
	try:
		d = json.loads( json_response, ensure_ascii=False)
		print "SERVER RESPONSE", d['status'], d['error']
	except:
		print "error decoding "
		return {"status":"ko","error":"error decoding http response"}
	print response.read()
	return {"status":response.status }
################################################
def parseCsvAndSend(inFilePath,username,password):
	try:
		anta_key, anta_token = antapost_authenticate(username,password)
	except:
		return { 'status':"ko", "error":"authentication failed" }
	try:
		f = open(inFilePath)
		reader = csv.DictReader(open(inFilePath), delimiter='\t',quotechar='"')
		f.close()
	except:
		return {'status':"ko", "error":'file not found'}
	print "parsing file"
	
	mandatory = ['title','date','ref_url','mimetype','language','content']
	for row in reader:
		# for each row
		data={}
		metadata={}
		for k in row.keys():
			if k in mandatory:
				data[k]=row[k]
			else:
				metadata[k]=[ row[k] ]
		data['metadata']=metadata
		#pprint(data)
		sleeptime=2
		success=False
		while not success:
			statusdic = antapost_upload(anta_key,anta_token,data)
			success = statusdic['status']==200
			
			sleeptime +=1
			time.sleep(1)
	#os.remove(inFilePath)
	return {'status':"ok"}
################################################


################################################
def main():
	path= sys.argv[1]
	user= sys.argv[2]
	pasw= sys.argv[3]
	print "parsing csv started"
	res = parseCsvAndSend(path,user,pasw)
	
	print json.dumps(res,indent=4,ensure_ascii=False)
################################################
if __name__ == '__main__':
	main()
################################################
