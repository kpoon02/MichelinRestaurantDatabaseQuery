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

<html>

<head>
	<title>CPSC 304 PHP/Oracle Demonstration</title>
</head>

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
	<form method="POST" action="main.php">
		<input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
		RestaurantID: <input type="text" name="restaurantID"> <br /><br />
		Your ReviewerID: <input type="text" name="reviewerID"> <br /><br />
		Date: <input type="date" name="date" min="2020-01-01" max="2040-12-31"> <br /><br />
		Score (out of 5): <input type="text" name="score"> <br /><br />
		Comment: <textarea id="comment" name="comment" rows="4" cols="50"></textarea> <br /><br />
		<input type="submit" value="Insert" name="insertSubmit"></p>
	</form>

	<hr />

	<h2>Update Review</h2>
	<p>* = mandatory</p>

	<form method="POST" action="main.php">
		<input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
		Your ReviewerID*: <input type="text" name="oldReviewerID"> <br /><br />
		Old ReviewID*: <input type="text" name="oldReviewID"> <br /><br />
		<p><b>Edit Review Content</b> (Leave values blank if you do not want to change them)</p>
		RestaurantID: <input type="text" name="newRestaurantID"> <br /><br />
		Date: <input type="date" name="newDate" min="2020-01-01" max="2040-12-31"> <br /><br />
		Score (out of 5): <input type="text" name="newScore"> <br /><br />
		Comment: <textarea id="comment" name="newComment" rows="4" cols="50"></textarea> <br /><br />
		<input type="submit" value="Update" name="updateSubmit"></p>
	</form>

	<hr />

	<h2>Count the Tuples in DemoTable</h2>
	<form method="GET" action="main.php">
		<input type="hidden" id="countTupleRequest" name="countTupleRequest">
		<input type="submit" name="countTuples"></p>
	</form>

	<hr />

	<h2>Display Tuples in DemoTable</h2>
	<form method="GET" action="main.php">
		<input type="hidden" id="displayTuplesRequest" name="displayTuplesRequest">
		<input type="submit" name="displayTuples"></p>
	</form>

	<hr />

	<h2>All Reviews</h2>
	<div id='review-container' style='display: flex; flex-wrap: wrap; justify-content: space-between;'>
	<?php
		function updateDisplayedReviews() {
			if (connectToDB()) {
				$allReviews = executePlainSQL("SELECT * FROM Review");
				
				while ($review = OCI_Fetch_Array($allReviews, OCI_ASSOC)) {
					// var_dump($review);
					echo "<div class='review' style='border: 1px solid; padding: 10px; word-wrap: break-word;'>";
					echo "<p><b>ReviewID:</b> {$review["REVIEWID"]}</p>";
					echo "<p><b>RestaurantID:</b> {$review["RESTAURANTID"]}</p>";
					echo "<p><b>ReviewerID:</b> {$review["REVIEWERID"]}</p>";
					echo "<p><b>Date:</b> {$review["REVIEWDATE"]}</p>";
					echo "<p><b>Score:</b> {$review["SCORE"]}</p>";
					$comment = $review["REVIEWCOMMENT"]->read(1000);
					echo "<p><b>Comment:</b> $comment</p>";
					echo "</div>";
				}
			}
		}
	?>
	</div>


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
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
			echo htmlentities($e['message']);
			$success = False;
		}

		$r = oci_execute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
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
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
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
				echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
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

	function printResult($result)
	{ //prints results from a select statement
		echo "<br>Retrieved data from table demoTable:<br>";
		echo "<table>";
		echo "<tr><th>ID</th><th>Name</th></tr>";

		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]"
		}

		echo "</table>";
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
		global $db_conn;

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
		ReviewDate= COALESCE(TO_DATE(:bind4, 'YYYY-MM-DD'), ReviewDate),
		Score= COALESCE(cast(:bind5 AS INT), Score),
		ReviewComment= COALESCE(TO_CLOB(:bind6), ReviewComment) 
		WHERE ReviewerID=:bind1 AND ReviewID=:bind2", $alltuples);

		oci_commit($db_conn);
		updateDisplayedReviews();
	}

	function handleResetRequest()
	{
		global $db_conn;
		// Drop old table
		echo "<br> Dropping Old Tables <br>";
		executeFileSQL("dropTables.sql");

		// Create new table
		echo "<br> Creating New Tables <br>";
		executeFileSQL("createTables.sql");

		oci_commit($db_conn);
		updateDisplayedReviews();
	}

	function handleInsertRequest()
	{
		global $db_conn;

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
		updateDisplayedReviews();
	}

	function handleCountRequest()
	{
		global $db_conn;

		$result = executePlainSQL("SELECT Count(*) FROM demoTable");

		if (($row = oci_fetch_row($result)) != false) {
			echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
		}
	}

	function handleDisplayRequest()
	{
		global $db_conn;
		$result = executePlainSQL("SELECT * FROM demoTable");
		printResult($result);
	}

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('resetTablesRequest', $_POST)) {
				handleResetRequest();
			} else if (array_key_exists('updateQueryRequest', $_POST)) {
				handleUpdateRequest();
			} else if (array_key_exists('insertQueryRequest', $_POST)) {
				handleInsertRequest();
			}

			disconnectFromDB();
		}
	}

	// HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handleGETRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('countTuples', $_GET)) {
				handleCountRequest();
			} elseif (array_key_exists('displayTuples', $_GET)) {
				handleDisplayRequest();
			}

			disconnectFromDB();
		}
	}

	if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
		handlePOSTRequest();
	} else if (isset($_GET['countTupleRequest']) || isset($_GET['displayTuplesRequest'])) {
		handleGETRequest();
	}

	// End PHP parsing and send the rest of the HTML content
	?>
</body>

</html>
