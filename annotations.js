class Annotation
{
	constructor(data)
	{
		this.start = data.start;
		this.stop = data.stop;
		
		//Create the annotation (outer and inner portions)
		this.outer = document.createElement("div");
		this.inner = this.outer.appendChild(document.createElement("div"));
		
		//Set up the outer portion (background)
		this.outer.className = (data.type == "highlight") ? "outer-annotation highlight" : "outer-annotation";
		this.outer.id = data.id;
		this.outer.style.left = data.left;
		this.outer.style.top = data.top;
		this.outer.style.width = data.width;
		this.outer.style.height = data.height;
		this.outer.style.backgroundColor = (data.type == "highlight") ? "" : (typeof data.bgcolor !== "undefined") ? data.bgcolor : "";
		this.outer.style.borderColor = (data.type == "highlight") ? (typeof data.bordercolor !== "undefined") ? data.bordercolor : "" : "";
		this.outer.style.borderWidth = (data.type == "highlight") ? data.border_width + "px" : "0px";
		
		//Set up the inner portion (text element)
		this.inner.className = "inner-annotation";
		this.inner.innerHTML = (data.type == "highlight") ? data.highlight.text : data.text;
		this.inner.style.color = data.fontcolor;
		this.inner.style.textAlign = data.textalign;
		
		//Create the close button and add it to the inner annotation
		this.closebutton = this.inner.appendChild(document.createElement("span"));
		this.closebutton.className = "close-button";
		this.closebutton.setAttribute("data-annotation", this.outer.id);
		this.closebutton.addEventListener("click", function()
		{
			document.getElementById(this.getAttribute("data-annotation")).style.visibility = "hidden";
		});
		
		//Add any link icons and click actions
		if(data.action_type == "openUrl")
		{
			this.linkicon = this.inner.appendChild(document.createElement("span")).className = "link-icon";
			this.outer.addEventListener("click", function()
			{
				var video = document.getElementById("video");
				if(data.url_id == video_id)
				{
					//TODO: Check if there is an actual timecode
					video.currentTime = data.timecode;
				}
				
				else
				{
					if(data.target == "current") { location.href = "player.php?v=" + data.url_id; }
					if(data.target == "new") { window.open(data.url, "_blank"); }
				}
			});
		}
		
		//Add hover listeners
		var bgcolor = (typeof data.bgcolor !== "undefined") ? data.bgcolor : "";
		var hoverbgcolor = (typeof data.hoverbgcolor !== "undefined") ? data.hoverbgcolor : "";
		this.outer.addEventListener("mouseenter", function()
		{
			(data.type == "highlight") ? this.style.borderColor = data.hoverbordercolor : this.style.backgroundColor = hoverbgcolor;
			(data.type == "highlight") ? this.style.borderWidth = data.hoverborderwidth : "0px";
		});
		
		this.outer.addEventListener("mouseleave", function()
		{
			(data.type == "highlight") ? this.style.borderColor = data.bordercolor : this.style.backgroundColor = bgcolor;
			(data.type == "highlight") ? this.style.borderWidth = data.borderwidth : "0px";
		});
		
		//Set any dynamic properties
		this.update(data);
		
		//Add the annotation to the container
		document.getElementById("annotations").appendChild(this.outer);
	}
	
	update(data)
	{
		//Calculate video player size
		var video_width = parseFloat(window.getComputedStyle(document.querySelector("#container")).getPropertyValue("width"));
		var video_height = parseFloat(window.getComputedStyle(document.querySelector("#container")).getPropertyValue("height"));
		
		//Adjust annotation styles
		this.inner.style.fontSize = (data.textsize / 100) * video_height + "px";
		
		var padding_left = (video_width * 0.008) * ((data.style == "speech") ? 2 : 1);
		var padding_top = (video_height * 0.008) * ((data.style == "speech") ? 2 : 1);
		var padding_right = (padding_left * ((data.action_type == "openUrl") ? 2 : 1));
		
		this.inner.style.paddingLeft =  padding_left + "px";
		this.inner.style.paddingTop = padding_top + "px";
		this.inner.style.paddingRight = padding_right + "px";
		this.inner.style.paddingBottom = this.inner.style.paddingTop;
		
		//Calculate close button's position
		var annotation_width = (parseFloat(data.width) / 100) * video_width;
		var annotation_height = (parseFloat(data.height) / 100) * video_width;
		var annotation_right = ((parseFloat(data.left) / 100) * video_width) + annotation_width;
		var annotation_top = (parseFloat(data.top) / 100) * video_height;
		var annotation_bottom = annotation_top + annotation_height;
		
		var left = (annotation_right >= video_width) ? annotation_width - 16 : annotation_width - 8;
		var top = (annotation_top <= 8) ? 8 : -8;
		top = ((annotation_top >= (video_height - 56)) && (annotation_bottom >= (video_height - 56))) ? annotation_height - 64 : top;
		
		this.closebutton.style.left = left + "px";
		this.closebutton.style.top = top + "px";
	}
}

var annotations = [];

function load_annotations(input)
{
	for(var i=0; i<input.length; i+=1)
	{
		annotations.push(new Annotation(input[i]));
	}
}

function update_annotations()
{
	var video = document.getElementById("video");
	var time = document.getElementById("time");
	time.innerHTML = video.currentTime;
	
	for(var i=0; i<annotations.length; i+=1)
	{
		var annotation = annotations[i];
		if(video.currentTime <= annotation.start | video.currentTime >= annotation.stop)
		{
			annotation.outer.style.display = "none";
		}
		
		else
		{
			annotation.outer.style.display = "block";
		}
	}
}