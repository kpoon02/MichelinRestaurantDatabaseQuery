<!-- Test Oracle file for UBC CPSC304
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  Modified by Jason Hall (23-09-20)
  This file shows the very basics of how to execute PHP commands on Oracle.
  Specifically, it will drop a table, create a table, insert values update
  values, and then query for values
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up All OCI commands are
  commands to the Oracle libraries. To get the file to work, you must place it
  somewhere where your Apache server can run it, and you must rename it to have
  a ".php" extension. You must also change the username and password on the
  oci_connect below to be your ORACLE username and password
-->

<?php
// The preceding tag tells the web server to parse the following text as PHP
// rather than HTML (the default)

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_kpoon02";			// change "cwl" to your own CWL
$config["dbpassword"] = "a90607425";	// change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()

$success = true;	// keep track of errors so page redirects only if there are no errors

$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<style>
<?php include 'main.css'; ?>
</style>

<html>

<head>
	<title>Group 24 - CPSC 304 Michelin Restaurant Project</title>
</head>

<div class="header">
	<h1 class="title">Michelin Restaurant Reviews in Vancouver</h1>
</div>

<br /><br />
<div class="header-bar">
	<?php
		$link = "restaurant.php";
		$text = "View Restaurants";

		echo "<a href='$link'>$text</a>";
	?>
</div>
<br /><br />


<body>
	<h2>Reset</h2>
	<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

	<form method="POST" action="main.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
		<p><input type="submit" value="Reset" name="reset"></p>
	</form>

	<hr />

	<h2>Insert Restaurant Review</h2>
	<p>* = mandatory</p>
	<form method="POST" action="main.php">
		<input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
		RestaurantID*: <input type="number" min=0 name="restaurantID" required> <br /><br />
		Your ReviewerID*: <input type="number" min=0 name="reviewerID" required> <br /><br />
		Date*: <input type="date" name="date" min="2020-01-01" max="2040-12-31" required> <br /><br />
		Score (out of 5)*: <input type="number" min=0 max=5 name="score" required> <br /><br />
		Comment: <textarea id="comment" name="comment" rows="4" cols="50"></textarea> <br /><br />
		<input type="submit" value="Insert" name="insertSubmit"></p>
	</form>
	<hr />


	<!-- Insertion, insert a new reviewer into the table -->
	<h2>Insert New Reviewer</h2>
	<form method="POST" action="main.php">
		<label for="reviewerID">ReviewerID:</label>
    		<input type="text" id="reviewerID" name="reviewerID"> <br /><br />
    
    		<label for="name">Name:</label>
    		<input type="text" id="name" name="name"> <br /><br />
    
    		<label for="reviewerType">Type:</label>
    		<select id="reviewerType" name="reviewerType" onchange="toggleFields()">
        		<option value="">Select Type</option>
        		<option value="ProfessionalCritic">Professional Critic</option>
        		<option value="FoodBlogger">Food Blogger</option>
    		</select><br><br>
    
    		<div id="titleField" style="display:none;">
        		<label for="title">Title:</label>
        		<input type="text" id="title" name="title"><br><br>
    		</div>
    
    		<div id="websiteField" style="display:none;">
        		<label for="website">Website:</label>
        		<input type="text" id="website" name="website"><br><br>
    		</div>
    
    		<input type="submit" value="Insert" name="insertReviewer">
	</form>
	<script>
	function toggleFields() {
    		var type = document.getElementById("reviewerType").value;
    		document.getElementById("titleField").style.display = (type == "ProfessionalCritic") ? "block" : "none";
    		document.getElementById("websiteField").style.display = (type == "FoodBlogger") ? "block" : "none";
	}
	</script>
	<hr />
	

   	 <!-- Selection, select all reviews fiven a RiviewerID -->
   	 <h2>Search Reviews by Reviewer</h2>
   	 <form method="POST" action="main.php">
       	 Reviewer ID: <input type="text" name="searchReviewerID" required>
       	 <input type="submit" name="searchReviewsByReviewer" value="Search">
   	 </form>
   	 <hr />

	 <!-- projection, project restaurants with user chosen attributes -->
	 <h2>Display Restaurants with Selected Attributes</h2>
	<form method="POST" action="main.php">
    <input type="hidden" name="displayProjectionRequest">
    <p>Select attributes to display:</p>
    <input type="checkbox" id="name" name="attributes[]" value="Name" checked disabled>
    <label for="name">Name (always displayed)</label><br>
    <input type="checkbox" id="website" name="attributes[]" value="Website">
    <label for="website">Website</label><br>
    <input type="checkbox" id="priceRange" name="attributes[]" value="PriceRange">
    <label for="priceRange">Price Range</label><br>
    <input type="checkbox" id="address" name="attributes[]" value="Address">
    <label for="address">Address</label><br>
    <input type="checkbox" id="averageScore" name="attributes[]" value="AverageScore">
    <label for="averageScore">Average Score</label><br>
    <input type="submit" value="Display" name="displayProjectionSubmit">
	</form>
	<hr />

   	 <!-- Deletion, Delete a reviewer and respective reviews given reviewerID-->
   	 <h2>Delete a Reviewer Account</h2>
	 <h3>All reviews by this account will also be deleted</h3>
   	 <form method="POST" action="main.php" onsubmit="return confirmDelete();">
   	 Reviewer ID: <input type="number" name="deleteReviewerID" required><br><br>
   	 <input type="submit" name="deleteReviewer" value="Delete">
   	 </form>
   	 <script>
   	 function confirmDelete() {
        	return confirm('Are you sure you want to delete this account?');
   	 }
   	 </script>
   	 <hr />

	<h2>Update Review</h2>
	<p>* = mandatory</p>

	<form method="POST" action="main.php">
		<input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
		Your ReviewerID*: <input type="number" min=0 name="oldReviewerID" required> <br /><br />
		Old ReviewID*: <input type="number" min=0 name="oldReviewID" required> <br /><br />
		<p><b>Edit Review Content</b> (Leave values blank if you do not want to change them)</p>
		RestaurantID: <input type="number" min=0 name="newRestaurantID"> <br /><br />
		Date: <input type="date" name="newDate" min="2020-01-01" max="2040-12-31"> <br /><br />
		Score (out of 5): <input type="number" min=0 max=5 name="newScore"> <br /><br />
		Comment: <textarea id="comment" name="newComment" rows="4" cols="50"></textarea> <br /><br />
		<input type="submit" value="Update" name="updateSubmit"></p>
	</form>

	<hr />

	<h2>Display All Reviews</h2>
	<form method="GET" action="main.php">
		<input type="hidden" id="displayTuplesRequest" name="displayTuplesRequest">
		<input type="submit" name="displayTuples"></p>
	</form>

	<hr />

	<!-- Division, all reviewers that have reviewed all restaurants-->
	<h2>Display All Reviewers that have reviewed all restaurants</h2>
	<form method="GET" action="main.php">
		<input type="hidden" id="displayDivisionRequest" name="displayDivisionRequest">
		<input type="submit" name="displayDivision"></p>
	</form>

	<hr />

	<?php
	// The following code will be parsed as PHP

	function debugAlertMessage($message)
	{
		global $show_debug_alert_messages;

		if ($show_debug_alert_messages) {
			echo "<script type='text/javascript'>alert('" . $message . "');</script>";
		}
	}

	function executePlainSQL($cmdstr)
	{ //takes a plain (no bound variables) SQL command and executes it
		//echo "<br>running ".$cmdstr."<br>";
		global $db_conn, $success;

		$statement = oci_parse($db_conn, $cmdstr);
		//There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

		if (!$statement) {
			// echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
			echo htmlentities($e['message']);
			$success = False;
		}

		$r = oci_execute($statement, OCI_DEFAULT);
		if (!$r) {
			// echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = oci_error($statement); // For oci_execute errors pass the statementhandle
			echo htmlentities($e['message']);
			$success = False;
		}

		return $statement;
	}

	function executeBoundSQL($cmdstr, $list)
	{
		/* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

		global $db_conn, $success;
		$statement = oci_parse($db_conn, $cmdstr);

		if (!$statement) {
			// echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn);
			echo htmlentities($e['message']);
			$success = False;
		}

		foreach ($list as $tuple) {
			foreach ($tuple as $bind => $val) {
				//echo $val;
				//echo "<br>".$bind."<br>";
				oci_bind_by_name($statement, $bind, $val);
				unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
			}

			$r = oci_execute($statement, OCI_DEFAULT);
			if (!$r) {
				// echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
				$e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
				echo htmlentities($e['message']);
				echo "<br>";
				$success = False;
			}
		}
	}

	function executeFileSQL($filepath)
	{
		$filecontents = file_get_contents($filepath);
		$sqlStatements = explode(";", $filecontents);
		foreach ($sqlStatements as $sqlStatement) {
			executePlainSQL($sqlStatement);
		}	
	}

	//function for displaying selection query
	function printResult($result)
	{ //prints results from a select statement
		echo "<br>Retrieved data from table Review:<br>";
		
		while ($review = OCI_Fetch_Array($result, OCI_ASSOC)) {
			echo "<div class='review' style='border: 1px solid; padding: 10px; word-wrap: break-word;'>";
			echo "<p><b>ReviewID:</b> {$review["REVIEWID"]}</p>";
			echo "<p><b>RestaurantID:</b> {$review["RESTAURANTID"]}</p>";
			echo "<p><b>ReviewerID:</b> {$review["REVIEWERID"]}</p>";
			echo "<p><b>Date:</b> {$review["Date"]}</p>";
			echo "<p><b>Score:</b> {$review["SCORE"]}</p>";
			if (array_key_exists("Comment", $review)) {
				$comment = $review["Comment"]->read(1000);
			} else {
				$comment = NULL;
			}
			echo "<p><b>Comment:</b> $comment</p>";
			echo "</div>";
		}
	}

	//function for displaying projection query
	function printRestaurantAttributesResult($result, $attributes) {
		echo "<br>Retrieved Restaurant Data:<br>";
		echo "<table border='1'>";
	
		// Header
		echo "<tr>";
		foreach ($attributes as $attribute) {
			echo "<th>" . htmlspecialchars($attribute) . "</th>";
		}
		echo "</tr>";
	
		// Rows
		while ($restaurant = OCI_Fetch_Array($result, OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS)) {
			echo "<tr>";
			foreach ($attributes as $attribute) {
				$attributeUpper = strtoupper($attribute);
				if (array_key_exists($attributeUpper, $restaurant)) {
					$data = $restaurant[$attributeUpper] !== null ? htmlspecialchars($restaurant[$attributeUpper]) : "(No data)";
					echo "<td>" . $data . "</td>";
				} else {
					echo "<td>(Attribute not found)</td>";
				}
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	
	

	//special function for printing division -> problem with existing printResult
	function printDivisionResult($result) {
		echo "<br>Reviewers who have reviewed all restaurants:<br>";
		
		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			echo "<div class='reviewer' style='border: 1px solid; padding: 10px; margin-bottom: 10px;'>";
			echo "<p><b>ReviewerID:</b> " . htmlspecialchars($row["REVIEWERID"]) . "</p>";
			echo "<p><b>Name:</b> " . htmlspecialchars($row["NAME"]) . "</p>";
			echo "</div>";
		}
	}

	function connectToDB()
	{
		global $db_conn;
		global $config;

		// Your username is ora_(CWL_ID) and the password is a(student number). For example,
		// ora_platypus is the username and a12345678 is the password.
		// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
		$db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

		if ($db_conn) {
			debugAlertMessage("Database is Connected");
			return true;
		} else {
			debugAlertMessage("Cannot connect to Database");
			$e = OCI_Error(); // For oci_connect errors pass no handle
			echo htmlentities($e['message']);
			return false;
		}
	}

	function disconnectFromDB()
	{
		global $db_conn;

		debugAlertMessage("Disconnect from Database");
		oci_close($db_conn);
	}

	function handleUpdateRequest()
	{
		global $db_conn, $success;

		// check if a review exists
		$reviewerID = $_POST['oldReviewerID'];
		$reviewID = $_POST['oldReviewID'];

		$result = executePlainSQL("SELECT * FROM Review WHERE ReviewerID=$reviewerID AND ReviewID=$reviewID");

		if (($row = oci_fetch_row($result)) == false) {
			echo "Review not found.";
			return;
		}


		$tuple = array(
			":bind1" => $_POST['oldReviewerID'],
			":bind2" => $_POST['oldReviewID'],
			":bind3" => $_POST['newRestaurantID'],
			":bind4" => $_POST['newDate'],
			":bind5" => $_POST['newScore'],
			":bind6" => $_POST['newComment']
		);

		$alltuples = array(
			$tuple
		);

		// if bind is NULL, then don't update that attribute
		executeBoundSQL("UPDATE Review SET 
		RestaurantID= COALESCE(cast(:bind3 AS INT), RestaurantID),		
		\"Date\"= COALESCE(TO_DATE(:bind4, 'YYYY-MM-DD'), \"Date\"),
		Score= COALESCE(cast(:bind5 AS INT), Score),
		\"Comment\"= COALESCE(TO_CLOB(:bind6), \"Comment\") 
		WHERE ReviewerID=:bind1 AND ReviewID=:bind2", $alltuples);

		oci_commit($db_conn);

		if ($success) {
			echo "Successfully updated review.";
		} else {
			echo "Error updating review.";
		};
	}

	function handleResetRequest()
	{
		global $db_conn;
		echo "<br> Resetting Database <br>";
		executeFileSQL("rebuild_database.sql");

		oci_commit($db_conn);
	}

	function handleInsertReviewRequest()
	{
		global $db_conn, $success;

		//Getting the values from user and insert data into the table
		$tuple = array(
			":bind1" => $_POST['restaurantID'],
			":bind2" => $_POST['reviewerID'],
			":bind3" => $_POST['date'],
			":bind4" => $_POST['score'],
			":bind5" => $_POST['comment']
		);

		$alltuples = array(
			$tuple
		);

		executeBoundSQL("INSERT INTO Review VALUES (NULL, :bind1, :bind2, TO_DATE(:bind3, 'YYYY-MM-DD'), :bind4, :bind5)", $alltuples);
		oci_commit($db_conn);
		
		if ($success) {
			echo "Successfully added review.";
		} else {
			echo "Error adding review.";
		};
	}
	

	// backend implementation of inserting a new reviewer
   	function handleInsertReviewerRequest() {
   		 global $db_conn;

   		 $reviewerID = $_POST['reviewerID'];
   		 $name = $_POST['name'];
   		 $reviewerType = $_POST['reviewerType'];

   		 $reviewerQuery = oci_parse($db_conn, "INSERT INTO Reviewer (ReviewerID, Name) VALUES (:reviewerID, :name)");
   		 oci_bind_by_name($reviewerQuery, ":reviewerID", $reviewerID);
   		 oci_bind_by_name($reviewerQuery, ":name", $name);

   		 if (!oci_execute($reviewerQuery)) {
       			 $e = oci_error($reviewerQuery);
       			 echo "Reviewer insert error: " . $e['message'];
       			 return; // Stop the function if there's an error
   		 }

   		 if ($reviewerType == "ProfessionalCritic") { // if reviewer is a professional critic
       			 $title = $_POST['title'];
       			 $criticQuery = oci_parse($db_conn, "INSERT INTO ProfessionalCritic (ReviewerID, Title) VALUES (:reviewerID, :title)");
       			 oci_bind_by_name($criticQuery, ":reviewerID", $reviewerID);
       			 oci_bind_by_name($criticQuery, ":title", $title);
       			 if (!oci_execute($criticQuery)) {
           			 $e = oci_error($criticQuery);
           			 echo "Critic insert error: " . $e['message'];
           			 return;
       			 }
   		 } elseif ($reviewerType == "FoodBlogger") { //if reviewer is a food blogger
       			 $website = $_POST['website'];
       			 $bloggerQuery = oci_parse($db_conn, "INSERT INTO FoodBlogger (ReviewerID, Website) VALUES (:reviewerID, :website)");
       			 oci_bind_by_name($bloggerQuery, ":reviewerID", $reviewerID);
       			 oci_bind_by_name($bloggerQuery, ":website", $website);
       			 if (!oci_execute($bloggerQuery)) {
           			 $e = oci_error($bloggerQuery);
           			 echo "Blogger insert error: " . $e['message'];
           			 return;
       			 }
    		}

   		 oci_commit($db_conn);
   		 echo "Reviewer added successfully";
		}

	function handleDisplayRequest()
	{
		global $db_conn;
		$result = executePlainSQL("SELECT * FROM Review");
		printResult($result);
	}

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest() {
		if (connectToDB()) {
			if (array_key_exists('resetTablesRequest', $_POST)) {
				handleResetRequest();
			} else if (array_key_exists('updateQueryRequest', $_POST)) {
				handleUpdateRequest();
			} else if (array_key_exists('insertQueryRequest', $_POST)) {
				handleInsertReviewRequest();
			} else if (array_key_exists('searchReviewsByReviewer', $_POST)) { //included selection query
				handleSelectReviewsByReviewer();
			} else if (array_key_exists('deleteReviewer', $_POST)) { //included delete query
				handleDeleteReviewerRequest();
			} else if (array_key_exists('insertReviewer', $_POST)) { //included insert query
				handleInsertReviewerRequest();
			}  if (array_key_exists('displayProjectionSubmit', $_POST)) { //included projection query
				handleDisplayRestaurantsProjection();
			} 
	
			disconnectFromDB();
		}
	}

	// HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handleGETRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('displayTuples', $_GET)) {
				handleDisplayRequest();
			} elseif (array_key_exists('displayDivision', $_GET)) { //included division querty 
				handleDisplayDivisionRequest();
			} 

			disconnectFromDB();
		}
	}

	if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['insertReviewer'])
	|| isset($_POST['searchReviewsByReviewer']) || isset($_POST['deleteReviewer']) || isset($_POST['displayProjectionSubmit'])) {
		handlePOSTRequest();
	} else if (isset($_GET['displayTuplesRequest']) || isset($_GET['displayDivisionRequest'])
	) {
		handleGETRequest();
	}

	// backend implementation of selection and display using printResult() - FIXED
	function handleSelectReviewsByReviewer() {
		global $db_conn;
	
		$reviewerID = $_POST['searchReviewerID']; // Fetch user input
	
		$query = oci_parse($db_conn, "SELECT * FROM Review WHERE ReviewerID = :reviewerID ORDER BY \"Date\"");
		oci_bind_by_name($query, ":reviewerID", $reviewerID);
	
		if (!oci_execute($query)) {
			$e = oci_error($query);
			echo "Query execution error: " . $e['message'];
			return;
		}
	
		// Call printResult 
		printResult($query);
	}
	

	//backend implemetation of projection
	function handleDisplayRestaurantsProjection() {
		global $db_conn;
	
		$attributes = ['Name'];  // Default attribute
		if (isset($_POST['attributes'])) {
			$attributes = array_merge($attributes, $_POST['attributes']);
		}
		$attributesList = array_map('strtoupper', $attributes);
		$attributesListString = implode(", ", $attributesList);
		$query = oci_parse($db_conn, "SELECT $attributesListString FROM Restaurant");
	
		if (!oci_execute($query)) {
			$e = oci_error($query);
			echo "Query execution error: " . $e['message'];
			return;
		}
	
		// Print the results
		printRestaurantAttributesResult($query, $attributes);
	}
	
   	// backend implementation of deleting a review
   	function handleDeleteReviewerRequest() {
    	global $db_conn;

    	$reviewerID = $_POST['deleteReviewerID']; // Fetch the ReviewerID 

    	//DELETE query for ProfessionalCritic and FoodBlogger
    	$queries = [
        	"DELETE FROM ProfessionalCritic WHERE ReviewerID = :reviewerID",
        	"DELETE FROM FoodBlogger WHERE ReviewerID = :reviewerID",
        	"DELETE FROM Review WHERE ReviewerID = :reviewerID", // Cascade not working(?), explicitly delete reviews
        	"DELETE FROM Reviewer WHERE ReviewerID = :reviewerID"
    	];

    	foreach ($queries as $sql) {
      		$query = oci_parse($db_conn, $sql);
     		oci_bind_by_name($query, ":reviewerID", $reviewerID);

        	
        	if (!oci_execute($query, OCI_NO_AUTO_COMMIT)) {
            	$e = oci_error($query);
            	echo "Error executing query: " . $e['message'];
            	oci_rollback($db_conn); 
            	return; 
        	}
    	}

    	oci_commit($db_conn);
    	echo "Reviewer and all related data successfully deleted.";
	}


	//backend implementation of division query
	function handleDisplayDivisionRequest() {
		global $db_conn;
		
		// total number of restaurants
		$totalRestaurantsQuery = oci_parse($db_conn, "SELECT COUNT(*) AS TOTAL FROM Restaurant");
		if (!oci_execute($totalRestaurantsQuery)) {
			$e = oci_error($totalRestaurantsQuery);
			echo "Error getting total number of restaurants: " . $e['message'];
			return;
		}
		$row = oci_fetch_assoc($totalRestaurantsQuery);
		$totalRestaurants = $row['TOTAL'];

		$query = oci_parse($db_conn, "
			SELECT Reviewer.ReviewerID, Reviewer.Name
			FROM Reviewer
			JOIN Review ON Reviewer.ReviewerID = Review.ReviewerID
			GROUP BY Reviewer.ReviewerID, Reviewer.Name
			HAVING COUNT(DISTINCT Review.RestaurantID) = :totalRestaurants
		");
		oci_bind_by_name($query, ":totalRestaurants", $totalRestaurants);
	
		if (!oci_execute($query)) {
			$e = oci_error($query);
			echo "Query execution error: " . $e['message'];
			return;
		}
	
		// Call printDivisionResult to output the results, names after join for division not working I think
		printDivisionResult($query);
	}

	// End PHP parsing and send the rest of the HTML content
	?>
</body>

</html>
