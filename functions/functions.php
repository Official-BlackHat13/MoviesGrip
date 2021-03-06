<?php 

session_start();

$db = mysqli_connect('localhost', 'root', '', 'moviesgrip');

// $db = mysqli_connect('localhost', 'u723392212_moviesgrip', 'Anlogkamjohn@7480', 'u723392212_moviesgrip');

// ********** REGISTER USER ********** //

// Register User

$username = $email = $password_1 = $password_2 = $user_image = "";
$username_error = $email_error = $password_1_error = $password_2_error = $user_image_error = "";

if (isset($_POST['register'])) {
	register();
}

function register(){

	global $db, $username, $email, $user_image, $username_error, $email_error, $password_1_error, $password_2_error, 
		   $user_image_error;

	$username    =  $_POST['username'];
	$email       =  $_POST['email'];
	$password_1  =  $_POST['password_1'];
	$password_2  =  $_POST['password_2'];
	$user_image  =  $_FILES['user_image']['name'];
	$temp_name1 =  $_FILES['user_image']['tmp_name'];

	if (empty($username)) { 
		$username_error = "Username is required"; 
	}else{
		$query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$username_error = "Username is already taken";
		}
	}

	if (empty($email)) { 
		$email_error = "Email is required"; 
	}elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format";
    }else{
		$query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$email_error = "Email is already taken";
		}
	}

    if(empty(trim($password_1))){
        $password_1_error = "Please enter a password.";
    } elseif(strlen(trim($password_1)) < 6){
        $password_1_error = "Password must have atleast 6 characters.";
    } 

    if(empty(trim($password_2))){
        $password_2_error = "Please confirm password.";
    } else{
        if(empty($password_1_error) && ($password_1 != $password_2)){
            $password_2_error = "Password did not match.";
        }
    }

    if(empty(trim($user_image))){
    	$user_image_error = "User Image is must needed";
    }

	if (empty($username_error) && empty($email_error) && empty($password_1_error) && empty($password_2_error) && empty($user_image_error)) {
		
		$password = md5($password_1);

		$query = "INSERT INTO users (username, email, role, password, user_image, status, joined_at) 
				  VALUES('$username', '$email', 'User', '$password', '$user_image', 1, NOW())";

		move_uploaded_file($temp_name1, "user/assets/images/user/$user_image");  
		mysqli_query($db, $query);
		$_SESSION['message']  = "Registered Successfully! Login with your User Credentials.";
		header('location: login');

	}

}


// ********** LOGIN USER ********** //

// Login User

$username_err = $password_err = $user_err = "";

if (isset($_POST['login'])) {
	login();
}

function login(){
	global $db, $username, $username_err, $password_err, $user_err;

	$username = $_POST['username'];
	$password = $_POST['password'];

	if (empty($username)) {
		$username_err = "Username is required";
	}else{
		$query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 0) { 
			$username_err = "Invalid Username";
		}
	}
	
	if (empty($password)) {
		$password_err = "Password is required";
	}
		
	if (empty($username_err) && empty($password_err) && empty($user_err)) {
		$password = md5($password);

		$query = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
		$results = mysqli_query($db, $query);

		if (mysqli_num_rows($results) == 1) { 
			
		$logged_in_user = mysqli_fetch_assoc($results);

			if ($logged_in_user['role'] == 'Admin' && $logged_in_user['status'] == 1) {
				
				$_SESSION['user'] = $logged_in_user;
				$_SESSION['message']  = "Logged in Successfully";
				header('location: index');		  
			}else if ($logged_in_user['role'] == 'Moderator' && $logged_in_user['status'] == 1) {

				$_SESSION['user'] = $logged_in_user;
				$_SESSION['message']  = "Logged in Successfully";
				header('location: index');		  
			}
			else if ($logged_in_user['role'] == 'User' && $logged_in_user['status'] == 1) {
				
				$_SESSION['user'] = $logged_in_user;
				$_SESSION['message']  = "Logged in Successfully";
				header('location: index');
			}else{
				$user_err = "Your Account is Inactive";				
			}
			
		}else{
			$password_err = "Incorrect Password";
		}

	}

}

function isAdmin(){

	if (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'Admin' ) {
		return true;
	}else{
		return false;
	}
}

function isModerator(){
	
	if (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'Moderator' ) {
		return true;
	}else{
		return false;
	}
}

function isUser(){

	if (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'User' ) {
		return true;
	}else{
		return false;
	}
}


// ********** LOGGED-IN USER DETAILS ********** //

// Logged-In User Details 

$log_userid = $log_username = $log_useremail = $log_userrole = $log_userimage = "";

if(isset($_SESSION['user'])){ 
	loggedin_user();
} 

function loggedin_user(){

	global $log_userid, $log_username, $log_useremail, $log_userrole, $log_userimage;

	$log_userid    = $_SESSION['user']['id'];
	$log_username  = $_SESSION['user']['username'];
	$log_useremail = $_SESSION['user']['email'];
	$log_userrole  = $_SESSION['user']['role'];
	$log_userimage = $_SESSION['user']['user_image'];

}
    

// ********** ESSENTIAL FUNCTIONS ********* //

// Remove Directory

function Remove($dir) {
    $structure = glob(rtrim($dir, "/").'/*');
    if (is_array($structure)) {
        foreach($structure as $file) {
            if (is_dir($file)) Remove($file);
            elseif (is_file($file)) unlink($file);
        }
    }
    rmdir($dir);
}


// Make Slug

function makeSlug(String $string){
	$string = strtolower($string);
	$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	return $slug;
}


// ********** SECTIONS MANAGEMENT ********** //

// Section Details

function section_details(){

	global $db;
	
	$query = "SELECT * FROM sections ORDER BY id DESc";
	
	$run_query = mysqli_query($db, $query);
	
	$section_details = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($section_details as $section_detail) {

		array_push($getdetails, $section_detail);

	}

	return $getdetails;
}

// Section Details

$query= "SELECT * FROM sections";
    
$result = mysqli_query($db, $query);

$section_detail = array();

while($section_details = mysqli_fetch_array($result)){   
   
    $section_detail[] = $section_details;

}   

$slider 		   	= $section_detail[0]['status'];
$latest_releases   	= $section_detail[1]['status'];
$popular_downloads 	= $section_detail[2]['status'];
$latest_movies 		= $section_detail[3]['status'];
$latest_webseries	= $section_detail[4]['status'];
$latest_tvshows 	= $section_detail[5]['status'];
$genres 			= $section_detail[6]['status'];
$languages 			= $section_detail[7]['status'];


// ********** SlIDERS ********* //

// Sliders

function slider_details(){

	global $db;
	
	$query = "SELECT * FROM slider WHERE status = 'Active' ORDER BY id DESC";
	
	$run_query = mysqli_query($db, $query);
	
	$slider_details = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($slider_details as $slider_detail) {

		array_push($getdetails, $slider_detail);

	}

	return $getdetails;
}


// Manage Sliders 

function manage_sliders(){

	global $db;
	
	$query = "SELECT * FROM sliders ORDER BY id DESC";
	
	$run_query = mysqli_query($db, $query);
	
	$manage_sliders = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($manage_sliders as $manage_slider) {

		array_push($getdetails, $manage_slider);

	}

	return $getdetails;
} 


// Latest Releases

function latest_details(){
    
    global $db;
    
    $query = "SELECT * FROM 
             (SELECT * FROM movies WHERE status = 'Active'  
                UNION SELECT * FROM webseries WHERE status = 'Active' 
                UNION SELECT * FROM tvshows WHERE status = 'Active') 
             	A ORDER BY uploaded_on DESC LIMIT 6";
    
    $run_query = mysqli_query($db, $query);
    
    $latest_details = mysqli_fetch_all($run_query, MYSQLI_ASSOC);
    
    $getdetails = array();
    
  	foreach ($latest_details as $latest_detail) {
    
        array_push($getdetails, $latest_detail);
    
    }

    return $getdetails;
} 


// ********** MOVIES ********** //

// Movies 

function movie_details(){

	global $db;
	
	$query = "SELECT * FROM movies WHERE status = 'Active' ORDER BY id DESC";
	
	$run_query = mysqli_query($db, $query);
	
	$movie_details = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($movie_details as $movie_detail) {

		array_push($getdetails, $movie_detail);

	}

	return $getdetails;
} 


// Manage Movies 

function manage_movies(){

	global $db;
	
	$query = "SELECT * FROM movies ORDER BY id DESC";
	
	$run_query = mysqli_query($db, $query);
	
	$manage_movies = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($manage_movies as $manage_movie) {

		array_push($getdetails, $manage_movie);

	}

	return $getdetails;
} 


// Add Movie

$title = $category = $genre = $language = $rating = $quality = $year = $synopsis = $slug = $image = $gallery_images = 
$uploaded_by = $status = $languages = $qualities = "";

$title_err = $slug_err = $img_err = $gimg_err = $err = "";

if (isset($_POST['add_movie'])) {
	add_movie();
}

function add_movie(){
	
	global $db, $title, $category, $genre, $language, $rating, $quality, $year, $synopsis, $slug, $image, $gallery_images, 
	$uploaded_by, $status, $languages, $qualities, $title_err, $slug_err, $img_err, $gimg_err, $err;

	$title 		  = $_POST['title'];
	$genre  	  = $_POST['genre'];
	$languages    = $_POST['language'];
	$rating 	  = $_POST['rating'];
	$qualities    = $_POST['quality'];
	$year 		  = $_POST['year'];
	$synopsis 	  = $_POST['synopsis'];
	$slug 		  = $_POST['slug'];
	$uploaded_by  = $_SESSION['user']['username'];
	$status 	  = $_POST['check'];
	$category	  = 'movie';
	
	$slug = makeSlug($slug);

	$image  =  $_FILES['image']['name'];
	$temp_name =  $_FILES['image']['tmp_name'];

	$gallery_images = array_filter($_FILES['gallery_image']['name']); 
	$total_count = count($_FILES['gallery_image']['name']);

	if (!empty($title)) {
		$query = "SELECT * FROM movies WHERE title='$title' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$title_err = "Movie with this name is already exists";
		}
	}

	if (!empty($slug)) {
		$query = "SELECT * FROM movies WHERE slug='$slug' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$slug_err = "Movie with this Slug is already exists";
		}
	}

	if (!empty($image)) {
		$query = "SELECT * FROM movies WHERE image='$image' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$img_err = "Image is already exists";
		}
	}

	if (!empty($image)) {
		if ($_FILES['image']['type'] == "image/jpeg" || $_FILES['image']['type'] == "image/jpg" || 
			$_FILES['image']['type'] == "image/png") {
			$img_er = false;
		}else{
			$img_er = true;
			$img_err = "Only JPEG, JPG and PNG Images are Allowed";
		}
	}

	$imagetype = array(image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG),
    image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_BMP));

	for( $i=0 ; $i < $total_count ; $i++ ) {
        if (in_array($_FILES['gallery_image']['type'][$i], $imagetype)) {
        	$gimg_er = "";
        }else{
        	$gimg_er = "true";
			$gimg_err = "Only JPEG, JPG and PNG Images are Allowed";        	
        }
	}


	if (empty($title_err) && empty($slug_err) && empty($img_err) && empty($gimg_err)) {
		
		if(is_array($gallery_images)) { 

	    $gallery_image = implode(',',$gallery_images);
	    $language = implode(",", $languages);       
	    $quality 	   = implode(',', $qualities);

		$query = "INSERT INTO movies (title, category, genre, language, rating, quality, year, synopsis, slug,
		          image, gallery_image, uploaded_by, uploaded_on, status) 
				  
				  VALUES('$title', '$category', '$genre', '$language', '$rating', '$quality', '$year', '$synopsis', '$slug',
				  '$image', '$gallery_image', '$uploaded_by', now(), '$status')";

		if(mysqli_query($db, $query)){
			$err = $mysqli -> error;
			if(!is_dir("../assets/images/movies/$title/")) {
			    mkdir("../assets/images/movies/$title/");
			}
			
			move_uploaded_file($temp_name, "../assets/images/movies/$title/$image");
			
			for( $i=0 ; $i < $total_count ; $i++ ) {

			   $tmpFilePath = $_FILES['gallery_image']['tmp_name'][$i];
			   if ($tmpFilePath != ""){
			      $newFilePath = "../assets/images/movies/$title/" . $_FILES['gallery_image']['name'][$i];

			      move_uploaded_file($tmpFilePath, $newFilePath);
			   }
			}  
			
			$_SESSION['success'] ="Movie has been successfully Uploaded!.";	
			header('location: manage-movies.php');

			}else{
				$_SESSION['error'] ="Error occured in Query Execution";
				header('location: manage-movies.php');
			}
		}else{
		$_SESSION['error'] ="Error occured in Array Function";
		header('location: manage-movies.php');
		}
	}else{
		$_SESSION['error'] ="Error occured in Header Function";
		header('location: manage-movies.php');
	}
}


// Edit Movie

if (isset($_GET['edit-movie'])) {

	$movie = $_GET['edit-movie'];

	global $db, $set_title, $set_image, $set_year, $set_genre, $set_language,
	$set_rating, $set_quality, $set_synopsis, $set_gallery_images, $set_slug;

	$query = "SELECT * FROM movies WHERE slug = '$movie' ";
	
	$results = mysqli_query($db, $query);

	$set_movie = mysqli_fetch_array($results);

	$set_id 			  = $set_movie['id'];
	$set_title       	  = $set_movie['title'];
	$set_image 		  = $set_movie['image'];
	$set_year 	      = $set_movie['year'];
	$set_genre 	      = $set_movie['genre'];
	$set_language	      = $set_movie['language'];
	$set_rating         = $set_movie['rating'];
	$set_quality 	      = $set_movie['quality'];
	$set_synopsis 	  = $set_movie['synopsis'];
	$set_gallery_images = $set_movie['gallery_image'];
	$set_slug     	  = $set_movie['slug'];

}


// Update Movie

if (isset($_POST['update_movie'])) {
	$id = $_POST['id'];
	$delete_img = $_POST['delete-img'];
	$delete_gimg = $_POST['delete-gimg'];
	update_movie($id, $delete_img, $delete_gimg);
}

function update_movie($id, $delete_img, $delete_gimg){
	
	global $db, $title, $genre, $language, $rating, $quality, $year, $synopsis, $slug, $image, $gallery_images, 
	$uploaded_by, $status, $languages, $qualities, $title_err, $slug_err, $img_err, $gimg_err, $err;

	$title 		  = $_POST['title'];
	$genre  	  = $_POST['genre'];
	$languages    = $_POST['language'];
	$rating 	  = $_POST['rating'];
	$qualities    = $_POST['quality'];
	$year 		  = $_POST['year'];
	$synopsis 	  = $_POST['synopsis'];
	$slug 		  = $_POST['slug'];
	$uploaded_by  = $_SESSION['user']['username'];
	$status 	  = $_POST['check'];
	
	$slug = makeSlug($slug);

	$image  =  $_FILES['image']['name'];
	$temp_name =  $_FILES['image']['tmp_name'];

	$gallery_images = array_filter($_FILES['gallery_image']['name']); 
	$total_count = count($_FILES['gallery_image']['name']);

	$imagetype = array(image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG),
    image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_BMP));

	for( $i=0 ; $i < $total_count ; $i++ ) {
        if (in_array($_FILES['gallery_image']['type'][$i], $imagetype)) {
        	$gimg_er = "";
        }else{
        	$gimg_er = "true";
			$gimg_err = "Only JPEG, JPG and PNG Images are Allowed";        	
        }
	}

	if (!empty($title)) {
		$query = "SELECT * FROM movies WHERE id!=$id AND title='$title' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$title_err = "Movie with this name is already exists";
		}
	}


	if (!empty($slug)) {
		$query = "SELECT * FROM movies WHERE id!=$id AND slug='$slug' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$slug_err = "Movie with this Slug is already exists";
		}
	}

	if (!empty($image)) {
		$query = "SELECT * FROM movies WHERE id!=$id AND image='$image' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$img_err = "Image is already exists";
		}
	}

	if (!empty($image)) {
		if ($_FILES['image']['type'] == "image/jpeg" || $_FILES['image']['type'] == "image/jpg" || 
			$_FILES['image']['type'] == "image/png") {
			$img_er = false;
		}else{
			$img_er = true;
			$img_err = "Only JPEG, JPG and PNG Images are Allowed";
		}
	}

	if(empty($title_err) && empty($slug_err) && empty($img_err)){

		if(is_array($gallery_images)) { 

	    $gallery_image = implode(',',$gallery_images);
	    $language = implode(",", $languages);       
	    $quality 	   = implode(',', $qualities);
	    Remove('../assets/images/movies/'.$title.'/');

		$query = "UPDATE movies SET
				title = '$title', genre = '$genre', language ='$language', rating ='$rating', quality = '$quality', year = '$year', synopsis = '$synopsis', slug = '$slug',
				image = '$image', gallery_image = '$gallery_image', uploaded_by = '$uploaded_by', uploaded_on = now(), status = '$status' WHERE id = $id";

		if(mysqli_query($db, $query)){
			$err = $mysqli -> error;
			if(!is_dir("../assets/images/movies/$title/")) {
			    mkdir("../assets/images/movies/$title/");
			}

			move_uploaded_file($temp_name, "../assets/images/movies/$title/$image");
			
			for( $i=0 ; $i < $total_count ; $i++ ) {

			   $tmpFilePath = $_FILES['gallery_image']['tmp_name'][$i];
			   if ($tmpFilePath != ""){
			      $newFilePath = "../assets/images/movies/$title/" . $_FILES['gallery_image']['name'][$i];

			      move_uploaded_file($tmpFilePath, $newFilePath);
			   }
			}  
			
			$_SESSION['success'] ="Movie has been successfully Updated!";	
			header('location: manage-movies.php');

			}else{
				$_SESSION['error'] ="Error occured in Query Execution";
				header('location: manage-movies.php');
			}
		}else{
		$_SESSION['error'] ="Error occured in Array Function";
		header('location: manage-movies.php');
		}
	}else{
		$_SESSION['error'] = "Error in Header Function";
	}

}


// Publish & Unpublish Movie

	if (isset($_POST['publish_movie'])) {
		$status = $_POST['status'];
		togglePublishMovie($status);
		$_SESSION['success'] = $status;
	}else if (isset($_POST['unpublish_movie'])) {
		$status = $_POST['status'];
		toggleUnpublishMovie($status);
		$_SESSION['success'] = $status;
	}


// Publish Movie

function toggleUnpublishMovie($status)
{
	global $db;
	$sql = "UPDATE movies SET status = 'Inactive' WHERE id = $status";
	
	if (mysqli_query($db, $sql)) {
		$_SESSION['success'] = 'Movies Successfully Unpublished';
		header("location: manage-movies.php");
		exit(0);
	}
}


// Unpublish Movie

function togglePublishMovie($status)
{
	global $db;
	$sql = "UPDATE movies SET status= 'Active' WHERE id = $status";
	
	if (mysqli_query($db, $sql)) {
		$_SESSION['success'] = 'Movie Successfully Published';
		header("location: manage-movies.php");
		exit(0);
	}
}


// Multi Delete Movie

error_reporting(0);

if (isset($_POST["multi-m-delete"])) {

	if(!empty($_POST['ids'])){
	    if (count($_POST["ids"]) > 0 ) {

	        $imgs = $_POST["imgs"];
	        $all  = implode(",", $_POST["ids"]);

	        if(mysqli_query($db,"DELETE FROM movies WHERE id in ($all)")) {
	        	foreach ($imgs as $img) {
	        		Remove('../assets/images/movies/'.$img.'/');
	        	}
	            $_SESSION['success'] ="Movie has been deleted successfully";
	        } else {
	            $_SESSION['error'] ="Error while deleting. Please Try again."; 
	        }
	    }
    }else{
    	$_SESSION['error'] = "You need to Select atleast one movie to delete";
    }

}  

// Single Delete Movie

if(isset($_POST['single-m-delete'])){
    $delete_movie = $_POST['delete-movie'];
    $delete_image = $_POST['delete-image'];
    movie_delete($delete_movie, $delete_image);
}

function movie_delete($delete_movie, $delete_image){
    
    global $db;

    if(mysqli_query($db, "DELETE FROM movies WHERE id = $delete_movie")){
    	Remove('../assets/images/movies/'.$delete_image.'/');
        $_SESSION['success'] = "Movie has been deleted successfully";
    }else{
        $_SESSION['success'] ="Something went wrong, Try again";
    }
}


// ********** WEBSERIES ********** //

// Webseries 

function series_details(){

	global $db;
	
	$query = "SELECT * FROM webseries WHERE status = 'Active' ORDER BY id DESC";
	
	$run_query = mysqli_query($db, $query);
	
	$series_details = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($series_details as $series_detail) {

		array_push($getdetails, $series_detail);

	}

	return $getdetails;
}


// Manage WebSeries 

function manage_webseries(){

	global $db;
	
	$query = "SELECT * FROM webseries ORDER BY id DESC";
	
	$run_query = mysqli_query($db, $query);
	
	$manage_webseries = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($manage_webseries as $manage_series) {

		array_push($getdetails, $manage_series);

	}

	return $getdetails;
} 


// Add WebSeries

if (isset($_POST['add_series'])) {
	add_series();
}

function add_series(){
	
	global $db, $title, $category, $genre, $language, $rating, $quality, $year, $synopsis, $slug, $image, $gallery_images, 
	$uploaded_by, $status, $languages, $qualities, $title_err, $slug_err, $img_err, $gimg_err, $err;

	$title 		  = $_POST['title'];
	$genre  	  = $_POST['genre'];
	$languages    = $_POST['language'];
	$rating 	  = $_POST['rating'];
	$qualities    = $_POST['quality'];
	$year 		  = $_POST['year'];
	$synopsis 	  = $_POST['synopsis'];
	$slug 		  = $_POST['slug'];
 	$uploaded_by  = $_SESSION['user']['username'];
	$status 	  = $_POST['check'];
	$category     = 'webseries';

	$slug = makeSlug($slug);
	
	$image  =  $_FILES['image']['name'];
	$temp_name =  $_FILES['image']['tmp_name'];

	$gallery_images = array_filter($_FILES['gallery_image']['name']); 
	$total_count = count($_FILES['gallery_image']['name']);

	if (!empty($title)) {
		$query = "SELECT * FROM webseries WHERE title='$title' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$title_err = "Series with this name is already exists";
		}
	}

	if (!empty($slug)) {
		$query = "SELECT * FROM webseries WHERE slug='$slug' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$slug_err = "Webseries with this Slug is already exists";
		}
	}

	if (!empty($image)) {
		$query = "SELECT * FROM webseries WHERE image='$image' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$img_err = "Image is already exists";
		}
	}

	if (!empty($image)) {
		if ($_FILES['image']['type'] == "image/jpeg" || $_FILES['image']['type'] == "image/jpg" || 
			$_FILES['image']['type'] == "image/png") {
			$img_er = false;
		}else{
			$img_er = true;
			$img_err = "Only JPEG, JPG and PNG Images are Allowed";
		}
	}

	$imagetype = array(image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG),
    image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_BMP));

	for( $i=0 ; $i < $total_count ; $i++ ) {
        if (in_array($_FILES['gallery_image']['type'][$i], $imagetype)) {
        	$gimg_er = "";
        }else{
        	$gimg_er = "true";
			$gimg_err = "Only JPEG, JPG and PNG Images are Allowed";        	
        }
	}


	if (empty($title_err) && empty($slug_err) && empty($img_err) && empty($gimg_err)) {
		
		if(is_array($gallery_images)) { 

	    $gallery_image = implode(',',$gallery_images);
	    $language = implode(",", $languages);       
	    $quality 	   = implode(',', $qualities);

		$query = "INSERT INTO webseries (title, category, genre, language, rating, quality, year, synopsis, slug, 
		          image, gallery_image, uploaded_by, uploaded_on, status) 
				  
				  VALUES('$title', '$category', '$genre', '$language', '$rating', '$quality', '$year', '$synopsis', '$slug',
				  '$image', '$gallery_image', '$uploaded_by', now(), '$status')";

		if(mysqli_query($db, $query)){
			$err = $mysqli -> error;
			if(!is_dir("../assets/images/webseries/$title/")) {
			    mkdir("../assets/images/webseries/$title/");
			}
			
			move_uploaded_file($temp_name, "../assets/images/webseries/$title/$image");
			
			for( $i=0 ; $i < $total_count ; $i++ ) {

			   $tmpFilePath = $_FILES['gallery_image']['tmp_name'][$i];
			   if ($tmpFilePath != ""){
			      $newFilePath = "../assets/images/webseries/$title/" . $_FILES['gallery_image']['name'][$i];

			      move_uploaded_file($tmpFilePath, $newFilePath);
			   }
			}  
			
			$_SESSION['success'] ="Webseries has been successfully Uploaded!.";	
			header('location: manage-webseries.php');

			}else{
				$_SESSION['error'] ="Error occured in Query Execution";
				header('location: manage-webseries.php');
			}
		}else{
		$_SESSION['error'] ="Error occured in Array Function";
		header('location: manage-webseries.php');
		}
	}else{
		$_SESSION['error'] ="Error occured in Header Function";
		header('location: manage-webseries.php');
	}
}


// Edit WebSeries

if (isset($_GET['edit-webseries'])) {

	$series = $_GET['edit-webseries'];

	global $db, $set_title, $set_image, $set_year, $set_genre, $set_language, $set_rating, $set_quality, 
	$set_synopsis, $set_gallery_images, $set_slug;

	$query = "SELECT * FROM webseries WHERE slug = '$series' ";
	
	$results = mysqli_query($db, $query);

	$set_series = mysqli_fetch_array($results);

	$set_id 			= $set_series['id'];
	$set_title       	= $set_series['title'];
	$set_image 		 	= $set_series['image'];
	$set_year 		 	= $set_series['year'];
	$set_genre 	     	= $set_series['genre'];
	$set_language	    = $set_series['language'];
	$set_rating         = $set_series['rating'];
	$set_quality 	    = $set_series['quality'];
	$set_synopsis 	 	= $set_series['synopsis'];
	$set_gallery_images = $set_series['gallery_image'];
	$set_slug     		= $set_series['slug'];

}


// Update WebSeries

if (isset($_POST['update_series'])) {
	$id = $_POST['id'];
	$delete_img = $_POST['delete-img'];
	$delete_gimg = $_POST['delete-gimg'];
	update_series($id, $delete_img, $delete_gimg);
}

function update_series($id, $delete_img, $delete_gimg){
	
	global $db, $title, $genre, $language, $rating, $quality, $year, $synopsis, $slug, $image, $gallery_images, 
	$uploaded_by, $status, $languages, $qualities, $title_err, $slug_err, $img_err, $gimg_err, $err;

	$title 		  = $_POST['title'];
	$genre  	  = $_POST['genre'];
	$languages    = $_POST['language'];
	$rating 	  = $_POST['rating'];
	$qualities    = $_POST['quality'];
	$year 		  = $_POST['year'];
	$synopsis 	  = $_POST['synopsis'];
	$slug 		  = $_POST['slug'];
	$uploaded_by  = $_SESSION['user']['username'];
	$status 	  = $_POST['check'];
	
	$slug = makeSlug($slug);

	$image  =  $_FILES['image']['name'];
	$temp_name =  $_FILES['image']['tmp_name'];

	$gallery_images = array_filter($_FILES['gallery_image']['name']); 
	$total_count = count($_FILES['gallery_image']['name']);

	$imagetype = array(image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG),
    image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_BMP));

	for( $i=0 ; $i < $total_count ; $i++ ) {
        if (in_array($_FILES['gallery_image']['type'][$i], $imagetype)) {
        	$gimg_er = "";
        }else{
        	$gimg_er = "true";
			$gimg_err = "Only JPEG, JPG and PNG Images are Allowed";        	
        }
	}

	if (!empty($title)) {
		$query = "SELECT * FROM webseries WHERE id!=$id AND title='$title' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$title_err = "Webseries with this name is already exists";
		}
	}


	if (!empty($slug)) {
		$query = "SELECT * FROM webseries WHERE id!=$id AND slug='$slug' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$slug_err = "Webseries with this Slug is already exists";
		}
	}

	if (!empty($image)) {
		$query = "SELECT * FROM webseries WHERE id!=$id AND image='$image' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$img_err = "Image is already exists";
		}
	}

	if (!empty($image)) {
		if ($_FILES['image']['type'] == "image/jpeg" || $_FILES['image']['type'] == "image/jpg" || 
			$_FILES['image']['type'] == "image/png") {
			$img_er = false;
		}else{
			$img_er = true;
			$img_err = "Only JPEG, JPG and PNG Images are Allowed";
		}
	}

	if(empty($title_err) && empty($slug_err) && empty($img_err)){

		if(is_array($gallery_images)) { 

	    $gallery_image = implode(',',$gallery_images);
	    $language = implode(",", $languages);       
	    $quality 	   = implode(',', $qualities);
	    Remove('../assets/images/webseries/'.$title.'/');

		$query = "UPDATE webseries SET
				title = '$title', genre = '$genre', language ='$language', rating ='$rating', quality = '$quality', year = '$year', synopsis = '$synopsis', slug = '$slug',
				image = '$image', gallery_image = '$gallery_image', uploaded_by = '$uploaded_by', uploaded_on = now(), status = '$status' WHERE id = $id";

		if(mysqli_query($db, $query)){
			$err = $mysqli -> error;
			if(!is_dir("../assets/images/webseries/$title/")) {
			    mkdir("../assets/images/webseries/$title/");
			}

			move_uploaded_file($temp_name, "../assets/images/webseries/$title/$image");
			
			for( $i=0 ; $i < $total_count ; $i++ ) {

			   $tmpFilePath = $_FILES['gallery_image']['tmp_name'][$i];
			   if ($tmpFilePath != ""){
			      $newFilePath = "../assets/images/webseries/$title/" . $_FILES['gallery_image']['name'][$i];

			      move_uploaded_file($tmpFilePath, $newFilePath);
			   }
			}  
			
			$_SESSION['success'] ="Webseries has been successfully Updated!";	
			header('location: manage-webseries.php');

			}else{
				$_SESSION['error'] ="Error occured in Query Execution";
				header('location: manage-webseries.php');
			}
		}else{
		$_SESSION['error'] ="Error occured in Array Function";
		header('location: manage-webseries.php');
		}
	}else{
		$_SESSION['error'] = "Error in Header Function";
	}

}


// Publish & Unpublish WebSeries

	if (isset($_POST['publish_series'])) {
		$status = $_POST['status'];
		togglePublishSeries($status);
		$_SESSION['success'] = $status;
	}else if (isset($_POST['unpublish_series'])) {
		$status = $_POST['status'];
		toggleUnpublishSeries($status);
		$_SESSION['success'] = $status;
	}


// Publish WebSeries

function toggleUnpublishSeries($status)
{
	global $db;
	$sql = "UPDATE webseries SET status = 'Inactive' WHERE id = $status";
	
	if (mysqli_query($db, $sql)) {
		$_SESSION['success'] = 'Series Successfully Unpublished';
		header("location: manage-webseries.php");
		exit(0);
	}
}


// Unpublish WebSeries

function togglePublishSeries($status)
{
	global $db;
	$sql = "UPDATE webseries SET status= 'Active' WHERE id = $status";
	
	if (mysqli_query($db, $sql)) {
		$_SESSION['success'] = 'Series Successfully Published';
		header("location: manage-webseries.php");
		exit(0);
	}
}


// Multi Delete WebSeries

error_reporting(0);

if (isset($_POST["multi-s-delete"])) {

	if(!empty($_POST['ids'])){
	    if (count($_POST["ids"]) > 0 ) {

	        $imgs = $_POST["imgs"];
	        $all  = implode(",", $_POST["ids"]);

	        if(mysqli_query($db,"DELETE FROM webseries WHERE id in ($all)")) {
	        	foreach ($imgs as $img) {
	        		Remove('../assets/images/webseries/'.$img.'/');
	        	}
	            $_SESSION['success'] ="Series has been deleted successfully";
	        } else {
	            $_SESSION['error'] ="Error while deleting. Please Try again."; 
	        }
	    }
    }else{
    	$_SESSION['error'] = "You need to Select atleast one Webseries to delete";
    }

}  

// Single Delete WebSeries

if(isset($_POST['single-s-delete'])){
    $delete_series = $_POST['delete-series'];
    $delete_image = $_POST['delete-image'];
    series_delete($delete_series, $delete_image);
}

function series_delete($delete_series, $delete_image){
    
    global $db;

    if(mysqli_query($db, "DELETE FROM webseries WHERE id = $delete_series")){
    	Remove('../assets/images/webseries/'.$delete_image.'/');
        $_SESSION['success'] = "Webseries has been deleted successfully";
    }else{
        $_SESSION['error'] ="Something went wrong, Try again";
    }
}


// ********** TV-Shows ********** //

// Tv-Shows 

function show_details(){

	global $db;
	
	$query = "SELECT * FROM tvshows WHERE status = 'Active' ORDER BY id DESC";
	
	$run_query = mysqli_query($db, $query);
	
	$show_details = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($show_details as $show_detail) {

		array_push($getdetails, $show_detail);

	}

	return $getdetails;
}


// Manage TV-Shows 

function manage_tvshows(){

	global $db;
	
	$query = "SELECT * FROM tvshows ORDER BY id DESC";
	
	$run_query = mysqli_query($db, $query);
	
	$manage_tvshows = mysqli_fetch_all($run_query, MYSQLI_ASSOC);

	$getdetails = array();

	foreach ($manage_tvshows as $manage_tvshow) {

		array_push($getdetails, $manage_tvshow);

	}

	return $getdetails;
} 


// Add TV-Show

if (isset($_POST['add_show'])) {
	add_show();
}

function add_show(){
	
	global $db, $title, $category, $genre, $language, $rating, $quality, $year, $synopsis, $slug, $image, $gallery_images, 
	$uploaded_by, $status, $languages, $qualities, $title_err, $slug_err, $img_err, $gimg_err, $err;

	$title 		  = $_POST['title'];
	$genre  	  = $_POST['genre'];
	$languages    = $_POST['language'];
	$rating 	  = $_POST['rating'];
	$qualities    = $_POST['quality'];
	$year 		  = $_POST['year'];
	$synopsis 	  = $_POST['synopsis'];
	$slug 		  = $_POST['slug'];
	$uploaded_by  = $_SESSION['user']['username'];
	$status 	  = $_POST['check'];
	$category	  = 'tvshow';

	$slug = makeSlug($slug);
	
	$image  =  $_FILES['image']['name'];
	$temp_name =  $_FILES['image']['tmp_name'];

	$gallery_images = array_filter($_FILES['gallery_image']['name']); 
	$total_count = count($_FILES['gallery_image']['name']);

	if (!empty($title)) {
		$query = "SELECT * FROM tvshows WHERE title='$title' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$title_err = "TV-Show with this name is already exists";
		}
	}

	if (!empty($slug)) {
		$query = "SELECT * FROM tvshows WHERE slug='$slug' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$slug_err = "Tv-Show with this Slug is already exists";
		}
	}

	if (!empty($image)) {
		$query = "SELECT * FROM tvshows WHERE image='$image' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$img_err = "Image is already exists";
		}
	}

	if (!empty($image)) {
		if ($_FILES['image']['type'] == "image/jpeg" || $_FILES['image']['type'] == "image/jpg" || 
			$_FILES['image']['type'] == "image/png") {
			$img_er = false;
		}else{
			$img_er = true;
			$img_err = "Only JPEG, JPG and PNG Images are Allowed";
		}
	}

	$imagetype = array(image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG),
    image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_BMP));

	for( $i=0 ; $i < $total_count ; $i++ ) {
        if (in_array($_FILES['gallery_image']['type'][$i], $imagetype)) {
        	$gimg_er = "";
        }else{
        	$gimg_er = "true";
			$gimg_err = "Only JPEG, JPG and PNG Images are Allowed";        	
        }
	}


	if (empty($title_err) && empty($slug_err) && empty($img_err) && empty($gimg_err)) {
		
		if(is_array($gallery_images)) { 

	    $gallery_image = implode(',',$gallery_images);
	    $language = implode(",", $languages);       
	    $quality 	   = implode(',', $qualities);

		$query = "INSERT INTO tvshows (title, category, genre, language, rating, quality, year, synopsis, slug,
		          image, gallery_image, uploaded_by, uploaded_on, status) 
				  
				  VALUES('$title', '$category', '$genre', '$language', '$rating', '$quality', '$year', '$synopsis', '$slug',
				  '$image', '$gallery_image', '$uploaded_by', now(), '$status')";

		if(mysqli_query($db, $query)){
			$err = $mysqli -> error;
			if(!is_dir("../assets/images/tvshows/$title/")) {
			    mkdir("../assets/images/tvshows/$title/");
			}
			
			move_uploaded_file($temp_name, "../assets/images/tvshows/$title/$image");
			
			for( $i=0 ; $i < $total_count ; $i++ ) {

			   $tmpFilePath = $_FILES['gallery_image']['tmp_name'][$i];
			   if ($tmpFilePath != ""){
			      $newFilePath = "../assets/images/tvshows/$title/" . $_FILES['gallery_image']['name'][$i];

			      move_uploaded_file($tmpFilePath, $newFilePath);
			   }
			}  
			
			$_SESSION['success'] ="TV-Show has been successfully Uploaded!.";	
			header('location: manage-tvshows.php');

			}else{
				$_SESSION['error'] ="Error occured in Query Execution";
				header('location: manage-tvshows.php');
			}
		}else{
		$_SESSION['error'] ="Error occured in Array Function";
		header('location: manage-tvshows.php');
		}
	}else{
		$_SESSION['error'] ="Error occured in Header Function";
		header('location: manage-tvshows.php');
	}
}


// Edit TV-Show

if (isset($_GET['edit-tvshow'])) {

	$tvshow = $_GET['edit-tvshow'];

	global $db, $set_title, $set_image, $set_year, $set_genre, $set_language, $set_rating, $set_quality, 
	$set_synopsis, $set_gallery_images, $set_slug;

	$query = "SELECT * FROM tvshows WHERE slug = '$tvshow' ";
	
	$results = mysqli_query($db, $query);

	$set_tvshow = mysqli_fetch_array($results);

	$set_id 			= $set_tvshow['id'];
	$set_title       	= $set_tvshow['title'];
	$set_image 		 	= $set_tvshow['image'];
	$set_year 		 	= $set_tvshow['year'];
	$set_genre 	     	= $set_tvshow['genre'];
	$set_language	    = $set_tvshow['language'];
	$set_rating         = $set_tvshow['rating'];
	$set_quality 	    = $set_tvshow['quality'];
	$set_synopsis 	 	= $set_tvshow['synopsis'];
	$set_gallery_images = $set_tvshow['gallery_image'];
	$set_slug     		= $set_tvshow['slug'];

}


// Update TV-Show

if (isset($_POST['update_tvshow'])) {
	$id = $_POST['id'];
	$delete_img = $_POST['delete-img'];
	$delete_gimg = $_POST['delete-gimg'];
	update_tvshow($id, $delete_img, $delete_gimg);
}

function update_tvshow($id, $delete_img, $delete_gimg){
	
	global $db, $title, $genre, $language, $rating, $quality, $year, $synopsis, $slug, $image, $gallery_images, 
	$uploaded_by, $status, $languages, $qualities, $title_err, $slug_err, $img_err, $gimg_err, $err;

	$title 		  = $_POST['title'];
	$genre  	  = $_POST['genre'];
	$languages    = $_POST['language'];
	$rating 	  = $_POST['rating'];
	$qualities    = $_POST['quality'];
	$year 		  = $_POST['year'];
	$synopsis 	  = $_POST['synopsis'];
	$slug 		  = $_POST['slug'];
	$uploaded_by  = $_SESSION['user']['username'];
	$status 	  = $_POST['check'];
	
	$slug = makeSlug($slug);

	$image  =  $_FILES['image']['name'];
	$temp_name =  $_FILES['image']['tmp_name'];

	$gallery_images = array_filter($_FILES['gallery_image']['name']); 
	$total_count = count($_FILES['gallery_image']['name']);

	$imagetype = array(image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG),
    image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_BMP));

	for( $i=0 ; $i < $total_count ; $i++ ) {
        if (in_array($_FILES['gallery_image']['type'][$i], $imagetype)) {
        	$gimg_er = "";
        }else{
        	$gimg_er = "true";
			$gimg_err = "Only JPEG, JPG and PNG Images are Allowed";        	
        }
	}

	if (!empty($title)) {
		$query = "SELECT * FROM tvshows WHERE id!=$id AND title='$title' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$title_err = "TV-Show with this name is already exists";
		}
	}


	if (!empty($slug)) {
		$query = "SELECT * FROM tvshows WHERE id!=$id AND slug='$slug' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$slug_err = "Tv-Show with this Slug is already exists";
		}
	}

	if (!empty($image)) {
		$query = "SELECT * FROM tvshows WHERE id!=$id AND image='$image' LIMIT 1";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 1) { 
			$img_err = "Image is already exists";
		}
	}

	if (!empty($image)) {
		if ($_FILES['image']['type'] == "image/jpeg" || $_FILES['image']['type'] == "image/jpg" || 
			$_FILES['image']['type'] == "image/png") {
			$img_er = false;
		}else{
			$img_er = true;
			$img_err = "Only JPEG, JPG and PNG Images are Allowed";
		}
	}

	if(empty($title_err) && empty($slug_err) && empty($img_err)){

		if(is_array($gallery_images)) { 

	    $gallery_image = implode(',',$gallery_images);
	    $language = implode(",", $languages);       
	    $quality 	   = implode(',', $qualities);
	    Remove('../assets/images/tvshows/'.$title.'/');

		$query = "UPDATE tvshows SET
				title = '$title', genre = '$genre', language ='$language', rating ='$rating', quality = '$quality', year = '$year', synopsis = '$synopsis', slug = '$slug',
				image = '$image', gallery_image = '$gallery_image', uploaded_by = '$uploaded_by', uploaded_on = now(), status = '$status' WHERE id = $id";

		if(mysqli_query($db, $query)){
			$err = $mysqli -> error;
			if(!is_dir("../assets/images/tvshows/$title/")) {
			    mkdir("../assets/images/tvshows/$title/");
			}

			move_uploaded_file($temp_name, "../assets/images/tvshows/$title/$image");
			
			for( $i=0 ; $i < $total_count ; $i++ ) {

			   $tmpFilePath = $_FILES['gallery_image']['tmp_name'][$i];
			   if ($tmpFilePath != ""){
			      $newFilePath = "../assets/images/tvshows/$title/" . $_FILES['gallery_image']['name'][$i];

			      move_uploaded_file($tmpFilePath, $newFilePath);
			   }
			}  
			
			$_SESSION['success'] ="TV-Show has been successfully Updated!";	
			header('location: manage-tvshows.php');

			}else{
				$_SESSION['error'] ="Error occured in Query Execution";
				header('location: manage-tvshows.php');
			}
		}else{
		$_SESSION['error'] ="Error occured in Array Function";
		header('location: manage-tvshows.php');
		}
	}else{
		$_SESSION['error'] = "Error in Header Function";
	}

}


// Publish & Unpublish TV-Show

	if (isset($_POST['publish_tvshow'])) {
		$status = $_POST['status'];
		togglePublishTvshow($status);
		$_SESSION['success'] = $status;
	}else if (isset($_POST['unpublish_tvshow'])) {
		$status = $_POST['status'];
		toggleUnpublishTvshow($status);
		$_SESSION['success'] = $status;
	}


// Publish TV-Show

function toggleUnpublishTvshow($status)
{
	global $db;
	$sql = "UPDATE tvshows SET status = 'Inactive' WHERE id = $status";
	
	if (mysqli_query($db, $sql)) {
		$_SESSION['success'] = 'TV-Show Successfully Unpublished';
		header("location: manage-tvshows.php");
		exit(0);
	}
}


// Unpublish TV-Show

function togglePublishTvshow($status)
{
	global $db;
	$sql = "UPDATE tvshows SET status= 'Active' WHERE id = $status";
	
	if (mysqli_query($db, $sql)) {
		$_SESSION['success'] = 'TV-Show Successfully Published';
		header("location: manage-tvshows.php");
		exit(0);
	}
}


// Multi Delete TV-Show

error_reporting(0);

if (isset($_POST["multi-t-delete"])) {

	if(!empty($_POST['ids'])){
	    if (count($_POST["ids"]) > 0 ) {

	        $imgs = $_POST["imgs"];
	        $all  = implode(",", $_POST["ids"]);

	        if(mysqli_query($db,"DELETE FROM tvshows WHERE id in ($all)")) {
	        	foreach ($imgs as $img) {
	        		Remove('../assets/images/tvshows/'.$img.'/');
	        	}
	            $_SESSION['success'] ="TV-Show has been deleted successfully";
	        } else {
	            $_SESSION['error'] ="Error while deleting. Please Try again."; 
	        }
	    }
    }else{
    	$_SESSION['error'] = "You need to Select atleast one TV-Show to delete";
    }

}  

// Single Delete TV-Show

if(isset($_POST['single-t-delete'])){
    $delete_tvshow = $_POST['delete-tvshow'];
    $delete_image = $_POST['delete-image'];
    tvshow_delete($delete_tvshow, $delete_image);
}

function tvshow_delete($delete_tvshow, $delete_image){
    
    global $db;

    if(mysqli_query($db, "DELETE FROM tvshows WHERE id = $delete_tvshow")){
    	Remove('../assets/images/tvshows/'.$delete_image.'/');
        $_SESSION['success'] = "TV-Show has been deleted successfully";
    }else{
        $_SESSION['error'] ="Something went wrong, Try again";
    }
}


?>