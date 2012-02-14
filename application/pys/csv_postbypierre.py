# -*- coding: utf-8 -*-
###########################################################################
import httplib
import base64
from urllib import urlencode
import json
import time
# http://pypi.python.org/pypi/phpserialize
from phpserialize import *
###########################################################################

#http://jiminy-dev.medialab.sciences-po.fr/anta_dev/api/authenticate?username=solairemed&password=solaire
#http://jiminy-dev.medialab.sciences-po.fr/anta_dev/api/item-upload/user/25a?debug=true&item=blablabla&token=97db82ad00b8c867ba32cc84a44d3ec9

ANTA_USER = 'solairemed'
ANTA_PASS = 'solaire'

BASE_ANTA_URL = 'jiminy-dev.medialab.sciences-po.fr'
ANTA_AUTH = '/anta_dev/api/authenticate?'
ANTA_UPLOAD = '/anta_dev/api/item-upload/user/'

################################################
def antapost_authenticate(username,passw):
	# Authenticate
	params = {'username':username,'password':passw}
	conn = httplib.HTTPConnection(BASE_ANTA_URL)
	conn.request("GET", ANTA_AUTH + urlencode(params))
	response = conn.getresponse()
	print "status: "+str(response.status),response.reason
	data = json.loads(response.read())
	print "SERVER RESPONSE"
	print data
	auth = data['authenticated_user']['id']
	token = data['token']
	print 'pqm:',auth
	print 'token:',token
	return auth, token
################################################
def antapost_upload(antakey,token,data):	
	# encode data
	data = base64.b64encode(dumps(data))
	print "64ENCODED_EXCERPT:"+data[:50]
	
	params = { 'debug':'true','item':data, 'verbose':'true', 'token':token } #'item':data,
	
	conn = httplib.HTTPConnection(BASE_ANTA_URL)
	headers = {"Content-type": "application/x-www-form-urlencoded", "Accept": "text/plain"}
	conn.request("POST", ANTA_UPLOAD + antakey, urlencode(params), headers)
	#print "params", params;
	
	response = conn.getresponse()
	print "status:"+str(response.status),response.reason
	try:
		d = json.loads(response.read())
		print "SERVER RESPONSE", d['status']
	except:
		print "ERROR decoding response…"
	return response.status==200
################################################
def parseCsvAndSend(inFilePath):
	print "================================== AUTHENTICATE"
	anta_key, anta_token = antapost_authenticate(ANTA_USER,ANTA_PASS)
	
	inFile = open(inFilePath,'r')
	lcount=0
	header=[]
	for u in inFile.readlines():
		if u.endswith('\n'):
			u=u[:-1]
		if lcount==0: # parse header
			print "================================== PARSING CSV HEADER"
			header = u.split('\t')
			print header
		elif lcount>177:
			print "================================== PARSING ONE CSV LINE (",lcount,")"
			data={}
			metadata={}
			for i,v in enumerate(u.split('\t')):
				if header[i].startswith('meta_'):
					# then push values in the metadata dict as array !
					metadata[ header[i][5:] ] = [v]
				else:
					data[header[i]]=v
			# put metadata
			data['metadata'] = metadata
			for k,v in data.items():
				if type(v)==str:
					print "KEY: "+ k + " = "+ v[:100]
				elif type(v)==dict:
					for q,u in v.items():
						print "META: "+ k + " : " + q + " = "+ u[0][:100]
			
			print "================================== POST"
			sleeptime=2
			success=False
			while not success:
				success = antapost_upload(anta_key, anta_token, data)
				sleeptime +=1
				time.sleep(1)
			print "================================== POST MADE SUCCESFULLY"
		else:
			donothing=1
		lcount+=1
	print "================================== PARSING DONE"
	inFile.close()
################################################
parseCsvAndSend('./SolaireMediaRefined.csv')

