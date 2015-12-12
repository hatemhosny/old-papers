<?php

//debug vars
$refresh_hide = false;

//uri vars
$host = strip_tags($_SERVER['HTTP_HOST']);
$request = explode('?', strip_tags($_SERVER['REQUEST_URI']));
$md5 = md5($host . $request[0]);

//class vars
$class = 'i' . substr($md5, 6, 8);
$obj = 'i' . substr($md5, 16, 8);

//is lazy loaded?
if(isset($_GET['lazy']) && $_GET['lazy'] === 'true') {
	//generate suffix
	$rand = mt_rand(100000, 999999);
	//add suffix
	$class .= $rand;
	$obj .= $rand;
}

//file vars
$file = basename(__FILE__);
$file_http = 'js_http.php';

//cache time (24 hours)
$cache_time = 60 * 60 * 24;

//format uri
$uri = explode('?', $_SERVER['REQUEST_URI']);
$uri = $_SERVER['HTTP_HOST'] . $uri[0];

//js header
header('Cache-Control: public, max-age=' . $cache_time);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');
header('Content-type: text/javascript');

?>
if(typeof(window['<?php echo $class; ?>']) == 'undefined') {

	var <?php echo $class; ?> = function() {

		var scripts = document.getElementsByTagName('script');
		var uri = scripts[scripts.length - 1].src;

		if(!uri || uri.indexOf('<?php echo $file; ?>') == -1) {
			uri = '<?php echo $uri; ?>';
		} else {
			uri = uri.split('//')[1].split('?')[0].split('#')[0];
		}

		this.script_host = uri.split('/')[0];
		this.script_req = uri.replace(this.script_host, "");

		this.script_host_http = '<?php echo $host; ?>';
		this.script_req_http = '<?php echo str_replace($file, $file_http, $request[0]); ?>';

		this.splice_count = 0;
		this.script_count = 0;

		this.node = null;
		this.node_queue = new Array();
		this.node_queue_parent = new Array();
	
		this.dw = document.write;
		this.dw_data = '';
		this.dw_node = null;

		this.response = null;

		//initialise
		this.init = function() {
			var obj = this;
			this.ready(function() {
				obj.find_scripts(obj);
			});
		}

		/*
		 * Author: Diego Perini (diego.perini at gmail.com)
		 * License: MIT
		 * Link: http://javascript.nwbox.com/ContentLoaded/
		 */
		this.ready = function(fn, win) {
			//set default window?
			if(typeof win === 'undefined') {
				win = window;
			}
			//set vars
			var done = false, top = true,
				doc = win.document, root = doc.documentElement,
				add = doc.addEventListener ? 'addEventListener' : 'attachEvent',
				rem = doc.addEventListener ? 'removeEventListener' : 'detachEvent',
				pre = doc.addEventListener ? '' : 'on';
			//check listener
			var check = function(e) {
				//already loaded?
				if(e.type === 'readystatechange' && doc.readyState !== 'complete') {
					return;
				}
				//remove listener
				(e.type === 'load' ? win : doc)[rem](pre + e.type, check, false);
				//call now?
				if(!done && (done = true)) {
					fn.call(win, e.type || e);
				}
			};
			//poll listener
			var poll = function() {
				try {
					root.doScroll('left');
				} catch(e) {
					setTimeout(poll, 50);
					return;
				}
				check('poll');
			};
			//lets begin
			if(doc.readyState === 'complete') {
				fn.call(win, 'lazy');
			} else {
				if(doc.createEventObject && root.doScroll) {
					try {
						top = !win.frameElement;
					} catch(e) {
						//do nothing
					}
					if(top) {
						poll();
					}
				}
				doc[add](pre + 'DOMContentLoaded', check, false);
				doc[add](pre + 'readystatechange', check, false);
				win[add](pre + 'load', check, false);
			}
		}

		//find scripts
		this.find_scripts = function(obj) {
			document.write = function(data) {
				return <?php echo $obj; ?>.write_dom(data);
			}
			document.writeln = function(data) {
				return <?php echo $obj; ?>.write_dom(data + "\n");
			}
			if(document.getElementsByTagName('body')[0].innerHTML.replace(/^\s+|\s+$/g, "") == '') {
				var body = false;
			} else {
				var body = true;
			}
			var query = new Array();
			var doc = document.documentElement || document.body;
			var scripts = doc.getElementsByTagName('script');
			for(var a=0; a < scripts.length; a++) {
				if(scripts[a].src.indexOf(this.script_host + this.script_req) == -1 && scripts[a].src.indexOf(this.script_req) != 0) {
					continue;
				}
				if(!body) {
					var temp = scripts[a].cloneNode(true);
					document.getElementsByTagName('head')[0].removeChild(scripts[a]);
					document.body.appendChild(temp);
					scripts[a] = temp;
				}
				if(scripts[a].src.indexOf('#') > 0) {
					var parts = scripts[a].src.split('#');
				} else {
					var parts = scripts[a].src.split('?');
				}
				if(parts[1]) {
					var type, zone, ref;
					var pairs = parts[1].split('&');
					for(var b=0; b < pairs.length; b++) {
						var split = pairs[b].split('=');
						if(split[0] == 'type') {
							type = split[1];
						} else if(split[0] == 'zone') {
							zone = split[1];
						} else if(split[0] == 'ref') {
							ref = split[1];
						}
					}
					if(type && zone) {
						obj.script_count++;
						var p = document.createElement('div');
						p.id = "<?php echo $obj; ?>_" + obj.script_count;
						query.push(parts[1] + "&id=" + p.id);
						scripts[a].parentNode.replaceChild(p, scripts[a]);
						try {
							var clone = p.parentNode.cloneNode(true);
							clone.innerHTML = '<div>test</div>';
						} catch(e) {
							var o = p.parentNode;
							var n = document.createElement('div');
							for(var i=0; i < o.attributes.length; i++) {
								var attr = o.attributes[i];
								if(attr.specified) {
									n.setAttribute(attr.name, attr.value);
								}
							}
							n.innerHTML = o.innerHTML;
							o.parentNode.replaceChild(n, o);
						}
						a--;
					}
				}
			}
			obj.insert_script(query, ref);
		}

		//insert script
		this.insert_script = function(query, ref) {
			ref = ref || 0;
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.async = true;
			s.src = '//' + this.script_host_http + this.script_req_http + '?cls=<?php echo $obj; ?>&rand=' + Math.floor(Math.random()*99999999) + (ref > 0 ? '&ref=' + ref : '');
			for(var a=0; a < query.length; a++) {
				s.src += '&queries[]=' + encodeURIComponent(query[a]);
			}
			s.onload = s.onreadystatechange = function() {
				if(!s.readyState || s.readyState === "loaded" || s.readyState === "complete") {
					s.onload = s.onreadystatechange = null;
					if(head && s.parentNode) {
						head.removeChild(s);
					}
				}
			}
			var head = document.getElementsByTagName('head')[0] || document.documentElement;
			head.insertBefore(s, head.firstChild);
		}

		//execute scripts
		this.exec_scripts = function() {
			if(this.node_queue.length == 0) {
				this.json_callback();
				return;
			}
			var obj = this;
			var script = this.node_queue[0];
			var head = document.getElementsByTagName('head')[0] || document.documentElement;
			var src = (script.src || "").replace(/^\s+|\s+$/g, "");
			var text = (script.text || script.textContent || script.innerHTML || "").replace(/^\s+|\s+$/g, "");
			var s = document.createElement('script');
			this.node = this.node_queue_parent[0];
			this.splice_count = 0;
			if(src != '') {
				s.src = src;
				s.type = 'text/javascript';
				s.src = src;
				s.onload = s.onreadystatechange = function() {
					if(!s.readyState || s.readyState === "loaded" || s.readyState === "complete") {
						s.onload = s.onreadystatechange = null;
						if(head && s.parentNode) {
							head.removeChild(s);
						}
						obj.node_queue.shift();
						obj.node_queue_parent.shift();
						obj.exec_scripts();
					}
				}
				head.insertBefore(s, head.firstChild);
			} else if(text != '') {
				s.type = 'text/javascript';
				s.text = text;
				head.insertBefore(s, head.firstChild);
				head.removeChild(s);
				obj.node_queue.shift();
				obj.node_queue_parent.shift();
				obj.exec_scripts();
			}
		}

		//json response
		this.json = function(data) {
			if(data && data[0]) {
				this.response = data;
				this.json_callback();
			}
		}

		//json callback
		this.json_callback = function(data) {
			if(this.node) {
				this.node = null;
			}
			if(data) {
				this.response.push(data);
			}
			if(this.response.length == 0) {
				return;
			} else {
				var data = this.response[0];
				var elem = document.getElementById(data.id);
				this.response.shift();
			}
			if(!elem) {
				return;
			}
			if(data.css) {
				var head = document.getElementsByTagName("head")[0];         
				var css = document.createElement('link');
				css.type = 'text/css';
				css.rel = 'stylesheet';
				css.href = data.css;
				head.appendChild(css);
			}
			var fragment = document.createElement('div');
			elem.parentNode.replaceChild(fragment, elem);
			fragment = this.inner_html(fragment, data.content);
			var scripts = fragment.getElementsByTagName('script');
			for(var i=0; i < scripts.length; i++) {
				if(scripts[i].src || scripts[i].text) {
					this.node_queue.push(scripts[i]);
					this.node_queue_parent.push(scripts[i].parentNode);
				}
			}
			if(data.query && data.refresh > 0) {
				var obj = this;
				setTimeout(function() {
					fragment.id = data.id;
					<?php if($refresh_hide) { ?>
                    fragment.style.visibility = "hidden";
					<?php } ?>
					data.query += "&refreshed=1";
					obj.insert_script(data.query.split(','));
				}, (data.refresh * 1000));
			}
			this.exec_scripts();
		}

		//write to dom
		this.write_dom = function(data) {
			var temp = data.replace(/<\\\//g, "</").replace(/^\s+|\s+$/g, "");
			if(this.dw_data.length > 0) {
				this.dw_data += data;
				var count = 0;
				var total = 0;
				var prev = '';
				for(var i=0; i < this.dw_data.length; i++) {
					if(prev == '<' && this.dw_data[i].match(/[a-z\/]/gi)) {
						count++;
						total++;
					} else if(this.dw_data[i] == '>' && prev.match(/[a-z\/"']/gi)) {
						count--;
						total++;
					}
					prev = this.dw_data[i];
				}
				if(count == 0 && total > 0 && total % 4 == 0) {
					data = this.dw_data;
					this.node = this.dw_node;
					this.dw_data = '';
					this.dw_node = null;
				} else {
					return;
				}
			} else if(temp.indexOf('<') == 0 && temp.indexOf('</') == -1 && temp.indexOf('/>') == -1) {
				this.dw_node = this.node || document.body;
				this.dw_data = data;
				return;
			}
			if(!this.node) {
				var fragment = this.html_dom(data);
				for(var i=0; i < fragment.childNodes.length; i++) {
					document.body.appendChild(fragment.childNodes[i].cloneNode(true));
				}
			} else {
				var fragment = this.html_dom(data);
				var scripts = fragment.getElementsByTagName('script');
				this.inner_html(this.node, this.node.innerHTML + data);
				for(var i=0; i < scripts.length; i++) {
					if(scripts[i].src || scripts[i].text) {
						this.splice_count++;
						this.node_queue.splice(this.splice_count, 0, scripts[i]);
						this.node_queue_parent.splice(this.splice_count, 0, this.node);
					}
				}
			}
		}

		//set inner html
		this.inner_html = function(node, html) {
			try {
				var ie = /*@cc_on!@*/false;
				html = html.replace(/^\s+|\s+$/g, "");
				if(ie && html.indexOf('<input') != 0) {
					node.innerHTML = '<input type="hidden" />' + html;
				} else {
					node.innerHTML = html;
				}
			} catch(e) {
				//failed
			}
			return node;
		}

		//html to dom
		this.html_dom = function(html) {
			var fragment = document.createElement('div');
			fragment = this.inner_html(fragment, html);
			return fragment;
		}

	}

	<?php echo $obj; ?> = new <?php echo $class; ?>;
	<?php echo $obj; ?>.init();

}