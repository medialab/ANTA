#!/usr/bin/python
# -*- coding: utf-8 -*-
########################################
import sys, os.path, json, networkx as nx, itertools
from networkx.algorithms import bipartite
from os.path import basename, dirname, splitext

sys.stderr = sys.stdout

jr=dict()
jr["status"]="ok"
jr["action"]="split-gexf"

# def project_bipartite_graph(graph,node_set,weight_label=None):
# # your edge attribute named as weight_label has to be integer/float or string in a form of an integer/float
#         
#     
#     projection = nx.DiGraph()
#     
#     
#     projection.add_nodes_from( (n,graph.node[n]) for n in set(graph.nodes()).difference(node_set) )
#      
#     for node in node_set :
#         otherparty_nodes = set(graph.neighbors(node) or graph.predecessors(node)) 
#         #print node+" ",otherparty_nodes
#         for link in itertools.combinations(otherparty_nodes,2) :
#             if not link in projection.edges() :
#                 projection.add_edge(link[0],link[1])
#                 
#                 try :
#                     projection[link[0]][link[1]][weight_label or "weight" ]= float(graph[node][link[0]][weight_label])+float(graph[node][link[1]][weight_label]) if weight_label else 1
#                 except :
#                     projection[link[0]][link[1]][weight_label or "weight" ]= float(graph[link[0]][node][weight_label])+float(graph[link[1]][node][weight_label]) if weight_label else 1
# 
#             else :
#                 try :
#                     projection[link[0]][link[1]][weight_label or "weight" ]+= float(graph[node][link[0]][weight_label])+float(graph[node][link[1]][weight_label]) if weight_label else 1
#                 except :
#                     projection[link[0]][link[1]][weight_label or "weight" ]+= float(graph[link[0]][node][weight_label])+float(graph[link[1]][node][weight_label]) if weight_label else 1
#     return projection

def collaboration_weighted_projected_graph(B, nodes):
    """equivalence measure taken from Callon-leximappe"""  
    if B.is_multigraph():
        raise nx.NetworkXError("not defined for multigraphs")
    if B.is_directed():
        pred=B.pred
        G=nx.DiGraph()
    else:
        pred=B.adj
        G=nx.Graph()
    G.graph.update(B.graph)
    G.add_nodes_from((n,B.node[n]) for n in nodes)
    for u in nodes:
        unbrs = set(B[u])
        nbrs2 = set((n for nbr in unbrs for n in B[nbr])) - set([u])
        for v in nbrs2:
            vnbrs = set(pred[v])
            common = unbrs & vnbrs
            # modify this line….
            weight = sum([1.0/(len(B[n]) - 1) for n in common if len(B[n])>1])
            G.add_edge(u,v,weight=weight)
    return G
    

# split a gexf into two monopartites grap
def splitBipartiteGexf( inputGexf, outputGexfPath ):
    outputGexfPath = outputGexfPath + os.sep
    jr["input_gexf"] = inputGexf 
    jr["outputGexfPath"] = outputGexfPath
    # otuput files
    xgexf = os.path.join( dirname( outputGexfPath ), basename( splitext( inputGexf )[0] )  )+".x.gexf"
    ygexf = os.path.join( dirname( outputGexfPath ), basename( splitext( inputGexf )[0] )  )+".y.gexf"
        
    try:
        graph = nx.readwrite.gexf.read_gexf( inputGexf );
    except:
        throwError( "unable to read gexf file" )
        return
    
    # bug in networkx, we need to make the directed graph as undirected
    graph=graph.to_undirected()
    
    jr["numOfNodes"] = len( graph.nodes() )
    jr["numOfEdges"] = len( graph.edges() )
    
    X,Y=bipartite.sets(graph)
    print "biparte.sets..."
    print X
    print Y
    
    #xgr=project_bipartite_graph(graph,X,"weight")
    xgr=bipartite.generic_weighted_projected_graph(graph,X)
    print "biparte.xgr..."
    print len(xgr.nodes())
    print len(xgr.edges())
    try:
        nx.readwrite.gexf.write_gexf(xgr, xgexf )
    except:
        throwError( "unable to write file, path:'" + xgexf + "'" )
        return
    
    #ygr=project_bipartite_graph(graph,Y,"weight")
    ygr=bipartite.generic_weighted_projected_graph(graph,Y)
    print "biparte.ygr..."
    print len(ygr.nodes())
    print len(ygr.edges())
    try:
        nx.readwrite.gexf.write_gexf(ygr, ygexf )
    except:
        throwError( "unable to write file, path:'" + ygexf + "'" )
        #print sys.exc_info()
    jr['output_gexf'] = [ xgexf, ygexf ]
    
    print "nodes in X", xgr.nodes()
    print "edges in X", list( xgr.edges() )
    print "nodes in Y", ygr.nodes()
    
    
    #print "edges in Y", list( ygr.edges() )
    # write file using path and given filename
    
    
    


# print the default error json response     
def throwError( error ):    
    jr["status"]="ko"
    jr["error"]=error
    jr["exception"] = { "type":sys.exc_info()[0], "value":sys.exc_info()[1], "traceback":sys.exc_info()[2]}
    print jr
    exit()
    
########################################
def main():
    try:
        inputGexfFile=sys.argv[1]
    except:
        throwError( "sys.argv: list index out of range" )
    if len(sys.argv) > 2 :
        outputGexfFilePath = sys.argv[2]
    else:
        outputGexfFilePath = dirname( sys.argv[1] )
        
    splitBipartiteGexf(inputGexfFile, outputGexfFilePath)
    print jr
#    except:
#        throwError( "params not found" ) 
#        print sys.exc_info()
    
########################################
if __name__ == '__main__':
    main()
########################################
