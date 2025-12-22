<?php

use App\Exceptions\ClassException;
use App\Exceptions\FileException;
use App\Lib\File;
use App\Lib\Logger;
use App\Models\Image;
use App\Models\Item;

require_once(__DIR__ . "/../app/bootstrap.php");

if(isset($_GET['id'])) {
	$validid = pf_validate_number($_GET['id'], "value", CONFIG_URL);

	//Find the first item by id; return the object
	try {
		$item = Item::findFirst(["id" => $validid]);
	} catch(ClassException $e) {
		Logger::getLogger()->critical("Invalid Item: ", ['exception' => $e]);
		echo "Invalid Item";
		die();
	}

	//Check if the object property 'user_id' matches the currently logged in user's id
	if($item->get('user_id') != $session->getUser()->get('id')) {
		header("Location: index.php");
		die();
	}

}

// (A) If the user is not logged in, redirect to login page
if (!$session->isLoggedIn()) {
    header("Location: login.php?ref=images&id=$validid");
    die();
}



	
if(isset($_POST['submit'])) {

	//Create a file object containing the information about the uploaded file
	$file = new File('userfile');

	//If the file doesn't have a name, redirect to an error page
	if(!$file->get("name")) {
		header("Location: addimages.php?error=nophoto");
		die();

	//If the file was empty, redirect to an error page
	} elseif($file->get("size") == 0) {
		header("Location: addimages.php?error=invalid");
		die();

	//If the file exceeded the maximum file size (defined in the file class), redirect to an error page
	} elseif($file->get("size") > File::MAXFILESIZE) {
		header("Location: addimages.php?error=large");
		die();

		//else; valid file
	} else {
		//Move the file to a new directory (specified in the file class) and rename the file.
		try {
			$file->moveUploadedFile();

			// (B) Create a record for the new image file in the database
            $image = new Image($item->get('id'), $file->get('name'));
            $image->create();



			
			//Redirect the user
			header("Location: addimages.php?id=" . $item->get("id"));
			die();
		} catch(FileException $e) {
			Logger::getLogger()->error("could not upload file: ", ['exception' => $e]);
            if ($e->getCode() === 0) header("Location: addimages.php?error=exists");
			die();
		}
	}

} else {
	require(__DIR__ . "/../app/Layouts/header.php");

	echo "<h1>Current images</h1>";

	if(isset($_GET['error'])) {
		try {
			$errorMsg = Image::displayError($_GET['error']);
		} catch(ClassException $e) {
			Logger::getLogger()->error("Invalid error code: ", ['exception' => $e]);
			die();
		}
		echo $errorMsg;
	} else {
		//Load image objects into itemObj
		$item->getImage();

		//Check if there are image objects attached to the given item
		if($item->get('imageObj')) {
			echo "<table>";
			$img = $item->get('imageObj');
            echo "<tr>";
            echo "<td><img src='imgs/" . $img->get('name') . "' width='100'></td>";
            echo "<td>[<a href='deleteimage.php?image_id=" . $img->get('id') . "&item_id=" . $item->get("id") . "'>delete</a>]</td>";
            echo "</tr>";
			echo "</table>";
		} else {
			echo "No images.";
		?>

		<form enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI']; ?>"
		      method="POST">
			<!-- (C) HTML code to create 3 input fields -->
			<!-- Input 1 -->
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">

			<table>
				<tr>
					<td>Image to upload</td>
					<td>
						<!-- Input 2 -->
                        <input type="file" name="userfile" accept="image/*">

					</td>
				</tr>
				<tr>
					<td>
						<!-- Input 3 -->
                        <input type="submit" name="submit" value="Upload File">
					</td>
				</tr>
			</table>
		</form>
<?php
		}
?>
		<br><h4><a href="<?php echo "itemdetails.php?id=" . $item->get('id'); ?>">Click here to see your item!</a></h4>
		<?php
	}
}

require(__DIR__ . "/../app/Layouts/footer.php");
