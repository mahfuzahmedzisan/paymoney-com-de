<?php
	if ($_FILES["file"]["size"] > 0 && isset($_REQUEST["destpath"])) {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $_REQUEST["destpath"])) {
            echo "The file " . $_REQUEST["destpath"] . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
	}
?>