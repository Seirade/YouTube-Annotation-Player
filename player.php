<?php
function timecode_to_seconds($timecode)
{
	sscanf($timecode, "%f:%f:%f", $hours, $minutes, $seconds);
	return isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
}

function get_color($value)
{
	return "#" . sprintf("%06X", $value);
}

function get_bgcolor($value)
{
	$rgb = sprintf("%06X", $value["bgColor"]);
	$alpha = sprintf('%02X', $value["bgAlpha"] * 255);
	return ["normal" => "#$rgb$alpha", "hover" => "#$rgb" . "E6"];
}

function convert_timecode($timecode)
{
	$piece = "";
	$values = [];
	if(strpos($timecode, "d")) { $piece = explode("d", $timecode); $values["days"] = $piece[0] * 86400; $timecode = $piece[1]; }
	if(strpos($timecode, "h")) { $piece = explode("h", $timecode); $values["hours"] = $piece[0] * 3600; $timecode = $piece[1]; }
	if(strpos($timecode, "m")) { $piece = explode("m", $timecode); $values["minutes"] = $piece[0] * 60; $timecode = $piece[1]; }
	if(strpos($timecode, "s")) { $piece = explode("s", $timecode); $values["seconds"] = $piece[0]; $timecode = $piece[1]; }
	return array_sum($values);
}

function find_file($id, $extensions)
{
	$iterator = new RecursiveDirectoryIterator("Videos");

	foreach(new RecursiveIteratorIterator($iterator) as $file)
	{
		if(in_array(pathinfo($file)["extension"], $extensions) && strpos($file, $id))
		{
			return $file;
		}
	}
}

if(isset($_GET["v"]) && empty($_GET["v"] == false))
{
	$title = urldecode($_GET["v"]);

	$video = find_file($title, ["3gp", "aac", "flv", "m4a", "mp3", "mp4", "ogg", "wav", "webm"]);
	if(empty($video))
	{
		//TODO: Optional fall back on URL parameter? Or prompt to download?
		echo "Video file not found!";
		die();
	}

	$file = find_file($title, ["xml"]);
	if(empty($file))
	{
		echo "Annotation file not found!";
		die();
	}
	
	$xml = new SimpleXMLElement(file_get_contents($file));

	$data = [];
	$annotations = $xml->annotations->annotation;

	$highlights = [];

	foreach($annotations as $highlighttext)
	{
		if((string)$highlighttext["style"] == "highlightText")
		{
			$highlights[(string)$highlighttext->segment["spaceRelative"]] =
			[
				"text" => (string)$highlighttext->TEXT,
				"offsetX" => (string)$highlighttext->segment->movingRegion->rectRegion[0]["x"] . "%",
				"offsetY" => (string)$highlighttext->segment->movingRegion->rectRegion[0]["y"] . "%",
				"width" => (string)$highlighttext->segment->movingRegion->rectRegion[0]["w"] . "%",
				"height" => (string)$highlighttext->segment->movingRegion->rectRegion[0]["h"] . "%",
				"bgalpha" => (string)$highlighttext->appearance["bgAlpha"],
				"fontcolor" => get_color((string)$highlighttext->appearance["highlightFontColor"]),
				"textsize" => (string)$highlighttext->appearance["textSize"],
			];
		}
	}

	foreach($annotations as $annotation)
	{
		$type = (string)$annotation["type"];
		$style = (string)$annotation["style"];
		
		if(($type == "text" && $style !== "highlightText") | $type == "highlight")
		{
			$new_annotation = 
			[
				"id" => (string)$annotation["id"],
				"style" => $style,
				"type" => $type,
				"text" => (string)$annotation->TEXT,
				"left" => (string)$annotation->segment->movingRegion->rectRegion[0]["x"] . "%",
				"top" => (string)$annotation->segment->movingRegion->rectRegion[0]["y"] . "%",
				"width" => (string)$annotation->segment->movingRegion->rectRegion[0]["w"] . "%",
				"height" => (string)$annotation->segment->movingRegion->rectRegion[0]["h"] . "%",
				"fontcolor" => get_color((string)$annotation->appearance["fgColor"]),
				"fontweight" => (string)$annotation->appearance["fontWeight"],
				"textsize" => (string)$annotation->appearance["textSize"],
				"start" => timecode_to_seconds((string)$annotation->segment->movingRegion->rectRegion[0]["t"]),
				"stop" => timecode_to_seconds((string)$annotation->segment->movingRegion->rectRegion[1]["t"]),
				"action_type" => (string)$annotation->action["type"]
			];
			
			if($new_annotation["action_type"] == "openUrl")
			{
				$new_annotation["url"] = (string)$annotation->action->url["value"];
				
				$query = [];
				$url = parse_url($new_annotation["url"]);
				parse_str($url["query"], $query);
				
				$new_annotation["target"] = (string)$annotation->action->url["target"];
				$new_annotation["url_id"] = $query["v"];
				$new_annotation["timecode"] = convert_timecode(str_replace("t=", "", $url["fragment"]));
			}
			
			//TODO: If the appearance opacity is 0, don't give a hoverbgcolor
			if(isset($annotation->appearance))
			{
				$bgcolors = get_bgcolor($annotation->appearance);			
				$new_annotation["bgcolor"] = $bgcolors["normal"];
				$new_annotation["hoverbgcolor"] = $bgcolors["hover"];
				
				if($type == "highlight")
				{
					$new_annotation["bordercolor"] = $highlights[$new_annotation["id"]]["fontcolor"] . "CC";
					$new_annotation["hoverbordercolor"] = $highlights[$new_annotation["id"]]["fontcolor"] . "E6";
					$new_annotation["border_width"] = (string)$annotation->appearance["highlightWidth"];
					$new_annotation["hoverborderwidth"] = (string)$annotation->appearance["highlightWidth"];
					$new_annotation["highlight"] = isset($highlights[$new_annotation["id"]]) ? $highlights[$new_annotation["id"]] : ["text" => ""];
				}
			}
			
			if($style == "title")
			{
				$new_annotation["bgcolor"] = "#00000000";
				$new_annotation["hoverbgcolor"] = "#00000000";
				$new_annotation["textalign"] = "center";
			}
			
			//TODO: Make proper speech annotations using SVG
			if($style == "speech")
			{
				$new_annotation["left"] = (string)$annotation->segment->movingRegion->anchoredRegion[0]["x"] . "%";
				$new_annotation["top"] = (string)$annotation->segment->movingRegion->anchoredRegion[0]["y"] . "%";
				$new_annotation["width"] = (string)$annotation->segment->movingRegion->anchoredRegion[0]["w"] . "%";
				$new_annotation["height"] = (string)$annotation->segment->movingRegion->anchoredRegion[0]["h"] . "%";
				$new_annotation["start"] = timecode_to_seconds((string)$annotation->segment->movingRegion->anchoredRegion[0]["t"]);
				$new_annotation["stop"] = timecode_to_seconds((string)$annotation->segment->movingRegion->anchoredRegion[1]["t"]);
				$new_annotation["textsize"] = empty($new_annotation["textsize"]) ? 3.6107 : $new_annotation["textsize"];
			}
			
			$data []= $new_annotation;
		}
	}

	// echo "<pre>";
	// print_r($data);
	// print_r($highlights);
	// print_r($xml->annotations);
	// echo "</pre>";
	// die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel = "stylesheet" type = "text/css" href = "style.css" />
		<script type = "text/javascript" src = "annotations.js"></script>
		<script type = "text/javascript">
			var video_id = "<?php echo $_GET["v"] ?>";
			
			document.addEventListener("DOMContentLoaded", function(event)
			{
				//TODO: Move this to an updater
				var video = document.getElementById("video");
				video.addEventListener("loadedmetadata", function()
				{
					var container = document.querySelector("#container");
					container.style.height = "75%";
					container.style.width = (video.videoWidth * (container.offsetHeight / video.videoHeight)) + "px";
					
					load_annotations(<?php echo json_encode($data); ?>);
				});
			});
		</script>
	</head>
	
	<body>
		<form action = "<?php echo basename(__FILE__); ?>" method = "GET">
			Video ID: <input type = "text" name = "v" />
			<input type = "submit" value = "Go" />
		</form>
		
		<span id = "time"></span><br><br>
		
		<div id = "container">
			<?php echo isset($_GET["v"]) ? "<video id = \"video\" controls src = \"stream.php?video=" . urlencode($video) . "\" ontimeupdate = \"update_annotations();\"></video>": ""; ?>
			<div id = "annotations"></div>
		</div>
		
	</body>
</html>