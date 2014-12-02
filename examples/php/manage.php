<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="./codebase/webix.css" type="text/css" media="screen" charset="utf-8">
		<script src="./codebase/webix.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" src="./common/testdata.js"></script>
		<link rel="stylesheet" type="text/css" href="./common/samples.css">
		<style>
			#areaA, #areaB {
				margin: 50px 10px;
				width:320px;
				height:400px;
				float: left;
			}
			#areaC{
				margin: 50px 10px;
				width:800px;
				height:400px;
				float: left;			
			}
		</style>
		<title>Tabview</title>
	</head>
	<body>
		<script type="text/javascript">
		</script>	
		<div id="areaA"></div>
		<table>
		<tr><input id = "Start" type="button" value="Start" /><input id = "Stop" type="button" value="Stop" /><input id = "Kill" type="button" value="Kill" /></tr>
		<tr><td>
			<div id = "areaC" style="background:black">
			<p><font color="white"><span id="processInfo"></span></font></p>
			</div>
		</td></tr>
		</table>		
		<script type="text/javascript" charset="utf-8">
			var filter = null;
			var xmlHttp = null;
			var treedata = null;
			var selected_id = null;
			var PhysicalView = null;
			var LogicalView = null;			
			function createXMLHttpRequest(){
				if(window.ActiveXObject){
					xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				else if(window.XMLHttpRequest){
					xmlHttp = new XMLHttpRequest();
				}
			}
			function fetchdata(){
				createXMLHttpRequest();
				var url="info.php";
				xmlHttp.open("GET",url,true);
				xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");		
				xmlHttp.onreadystatechange = callback;
				xmlHttp.send(null);
			}
			function buildPyhView(){
				var plyTree = {};
				for(var i = 0,len1 = treedata.length; i < len1; i++){
					var tmp1 = treedata[i].service;
					for(var j = 0,len2 = tmp1.length; j < len2; j++){
						var tmp2 = plyTree[tmp1[j].ip];
						if(!tmp2){
							tmp2 = [];
							plyTree[tmp1[j].ip] = tmp2;
						}
						tmp2.push(tmp1[j]);	
					}		
				}
				for (var key in plyTree){
					var services = plyTree[key];
					var rootitem = {value:key,type:"ip"};
					var rootid = PhysicalView.add(rootitem, null, 0);					
					for(var i = 0,len = services.length; i < len; i++){
						var item =  {value:services[i].type,type:"process"};
						var id = PhysicalView.add(item, null, rootid);
					}
				}
			}
			function buildLogView(){
				for(var i = 0,len1 = treedata.length; i < len1; i++){
					var tmp1 = treedata[i].service;
					var rootitem = {value:treedata[i].groupname,type:"group"};
					var rootid = LogicalView.add(rootitem, null, 0);					
					for(var j = 0,len2 = tmp1.length; j < len2; j++){
						var item =  {value:tmp1[j].type,type:"process"};
						var id = LogicalView.add(item, null, rootid);
					}		
				}
			}			
			function callback(){
				if(xmlHttp.readyState == 4){
					if(xmlHttp.status == 200){
						var info = JSON.parse(xmlHttp.responseText);
						var str = '';
						var find = false;
						var gotmatch = false;
						if(filter == null){
							document.getElementById("Start").disabled = false;
							document.getElementById("Stop").disabled = false;
							document.getElementById("Kill").disabled = false;
							for(var i = 0,len = info.length; i < len; i++){
								str = str + info[i] + '</br>'
							}
						}else{
							for(var i = 0,len = info.length; i < len; i++){
								if(find == false){ 
									str = str + info[i] + '</br>'
									if(info[i] == 'process_info'){
										find = true;
									}
								}else{
									var s = String(info[i]);
									if(s.search(filter) > 0){
										str = str + info[i] + '</br>';
										gotmatch = true;										
									}
								}
							}
							if(gotmatch){
								document.getElementById("Start").disabled = true;
								document.getElementById("Stop").disabled = false;
								document.getElementById("Kill").disabled = false;
							}else{
								document.getElementById("Start").disabled = false;
								document.getElementById("Stop").disabled = true;
								document.getElementById("Kill").disabled = true;
							}														
						}						
						document.getElementById("processInfo").innerHTML = str;
						setTimeout("fetchdata()",1000);
					}
				}
			}				
			fetchdata();					
			<?php
				$redis = new Redis();
				$redis->connect('127.0.0.1', 6379);
				$deployment = $redis->get('deployment');
				echo "treedata = $deployment";
			?>			
			webix.ui({
				container: "areaA",
				borderless:true, 
				view:"tabview",
				cells:[
					{
						header:"Physical View",
						body:{
							select:true,
							view:"tree",
							ready:function(){ 
								PhysicalView = this;
								buildPyhView();
							},							
							on:{
								"onAfterSelect":function(id){
									var item = this.getItem(id);
									//webix.message(id);
									if(item.type == "ip"){
										filter = null;
									}else if(item.type == "process"){
										filter = item.value;
									}
									fetchdata();
								}
							},
							data:[]
						},
					},
					{
						header:"Logical View",
						body:{
							select:true,
							view:"tree",
							ready:function(){ 
								LogicalView = this;
								buildLogView();
							},							
							on:{
								"onAfterSelect":function(id){
									var item = this.getItem(id);
									//webix.message(id);
									if(item.type == "ip"){
										filter = null;
									}else if(item.type == "process"){
										filter = item.value;
									}
									fetchdata();
								}
							},
							data:[]
						},
					},							
				]
			});
		</script>
	</body>
</html>