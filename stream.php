<?php
include "VideoStream.php";
$video = $_GET["video"];
$stream = new VideoStream($video);
$stream->start();
exit;