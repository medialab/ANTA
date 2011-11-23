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
#http://jiminy-dev.medialab.sciences-po.fr/anta_dev/api/item-upload/user/n6r?debug=true&item=blablabla

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
	print 'pqm: '+auth, "token",token
	return auth, token
################################################
def antapost_upload(antakey,token,data):	
	# encode data
	data = base64.b64encode(dumps(data))
	print "64ENCODED_EXCERPT:"+data[:200]
	
	params = { 'debug':'true','item':data, 'verbose':'true', 'token':token } #'item':data,
	
	conn = httplib.HTTPConnection(BASE_ANTA_URL)
	conn.request("POST", ANTA_UPLOAD + antakey +"?token="+token, urlencode(params))
	print "params", params;
	
	response = conn.getresponse()
	print "status:"+str(response.status),response.reason
	print "SERVER RESPONSE", response.read()
	print json.loads(response.read())
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
		elif lcount<2:
			print "================================== PARSING ONE CSV LINE"
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
			antapost_upload(anta_key, anta_token, data)
			time.sleep(1)
		else:
			a=1
		lcount+=1
	print "================================== PARSING DONE"
	inFile.close()
	#data={'moi':'pierre','lui':'anta'}
	#antapost_upload(key,data)
################################################



parseCsvAndSend('./SolaireMediaRefined.csv')




#################################
#REFINE save macro
#PYTHON save script
#
#A
#
#
#B
#POST
#pqm = id dans la réponse
#
#
#sample = [undoc=uneligne]
#blabla = base64_encode( serialize ( sample ) );
#
#
#http://jiminy-dev.medialab.sciences-po.fr/anta_dev/api/item-upload/user/pqm?debug=true&item=blabla
#
#
#SEE
#http://docs.python.org/library/httplib.html
#
#serialize
#http://pypi.python.org/pypi/phpserialize