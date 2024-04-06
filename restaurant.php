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
<?php include 'restaurant.css'; ?>
</style>

<html>

<head>
	<title>Group 24 - CPSC 304 Michelin Restaurant Project</title>
</head>

<div class="header">
	<h1 class="title">Michelin Restaurants in Vancouver</h1>
</div>

<br /><br />
<div class="header-bar">
	<?php
		$link = "main.php";
		$text = "Back to Restaurant Reviews";

		echo "<a href='$link'>$text</a>";
	?>
</div>
<br /><br />


<body>
	<h2>Reset</h2>
	<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

	<form method="POST" action="restaurant.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
		<p><input type="submit" value="Reset" name="reset"></p>
	</form>

	<hr />
	
	<h2>Highest Restaurant Rating Grouped By Cuisine</h2>
	<form method="GET" action="restaurant.php">
		<input type="hidden" id="groupByRequest" name="groupByRequest">
		<input type="submit" name="groupBy"></p>
	</form>

	<hr />

	<h2>Award Categories with Minimum Number of Restaurants</h2>
	<form method="GET" action="restaurant.php">
		<input type="hidden" id="groupByHavingRequest" name="groupByHavingRequest">
		<label for="minimum">Enter minimum number of restaurants:</label>
		<input type="number" min=0 name="minimum" required> <br /><br />
		<input type="submit" name="groupByHaving"></p>
	</form>

	<hr />

	<h2>Restaurant with Highest Rating By Price Range</h2>
	<form method="GET" action="restaurant.php">
		<input type="hidden" id="nestedGroupByRequest" name="nestedGroupByRequest">
		<input type="submit" name="nestedGroupBy"></p>
	</form>

	<hr />

	<h2>Display All Restaurants</h2>
	<form method="GET" action="restaurant.php">
		<input type="hidden" id="displayTuplesRequest" name="displayTuplesRequest">
		<input type="submit" name="displayTuples"></p>
	</form>

	<hr />

	<h2>Display Signature Dishes by Price Range</h2>
	<form method="GET" action="restaurant.php">
		<input type="hidden" id="displayJoinRequest" name="displayJoinRequest">
		<label for="price">Select Price Range:</label>
		<select id="price" name="price">
			<option value="$">$</option>
			<option value="$$">$$</option>
			<option value="$$$">$$$</option>
			<option value="$$$$">$$$$</option>
		</select>
		<input type="submit" name="displayJoin"></p>
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

	function printResult($result)
	{ //prints results from a select statement
		echo "<br>Retrieved data from table Restaurant:<br>";
		echo "<table>";
		echo "<tr><th >RestaurantID</th><th>Name</th><th>Website</th><th>PriceRange</th><th>Address</th><th>PostalCode</th><th>AverageScore</th><th>CuisineID</th></tr>";

		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			echo "<tr><td>" . $row["RESTAURANTID"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["WEBSITE"];
            
            if (isset($row["PRICERANGE"])) {
                echo "</td><td>" . $row["PRICERANGE"];
            } else {
                echo "</td><td>N/A";
            }

            if (isset($row["ADDRESS"])) {
                echo "</td><td>" . $row["ADDRESS"];
            } else {
                echo "</td><td>N/A";
            }

            if (isset($row["POSTALCODE"])) {
                echo "</td><td>" . $row["POSTALCODE"];
            } else {
                echo "</td><td>N/A";
            }

            if (isset($row["AVERAGESCORE"])) {
                echo "</td><td>" . $row["AVERAGESCORE"];
            } else {
                echo "</td><td>N/A";
            }

            if (isset($row["CUISINEID"])) {
                echo "</td><td>" . $row["CUISINEID"];
            } else {
                echo "</td><td>N/A";
            }
            
            echo "</td><tr>";
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

	function handleResetRequest()
	{
		global $db_conn;
		echo "<br> Resetting Database <br>";
		executeFileSQL("rebuild_database.sql");

		oci_commit($db_conn);
	}

	function handleCountRequest()
	{
		global $db_conn;

		$result = executePlainSQL("SELECT Count(*) FROM demoTable");

		if (($row = oci_fetch_row($result)) != false) {
			echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
		}
	}

	function handleGroupByRequest()
	{
		global $db_conn;

		$result = executePlainSQL("SELECT CuisineID, MAX(AverageScore) FROM Restaurant GROUP BY CuisineID");

		$output = "<br>Retrieved data from Restaurant table:<br>";
		$output .= "<table>";
		$output .= "<tr><th>CuisineID</th><th>Max Rating	</th></tr>";

		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			$output .= "<tr><td>" . $row["CUISINEID"] . "</td><td>" . $row["MAX(AVERAGESCORE)"] . "</td></tr>"; //or just use "echo $row[0]"
		}

		$output .= "</table>";

		$result = executePlainSQL("SELECT * FROM Cuisine");

		$output .= "<br>Cuisine Table for Reference:<br>";
		$output .= "<table>";
		$output .= "<tr><th>CuisineID</th><th>Name</th><th>Description</th></tr>";

		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			$output .= "<tr><td>" . $row["CUISINEID"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["DESCRIPTION"] . "</td></tr>"; //or just use "echo $row[0]"
		}
		
		$output .= "</table>";
		echo $output;
	}

	function handleGroupByHavingRequest() {
		global $db_conn;
		
		$minimum = $_GET["minimum"];

		$result = oci_parse($db_conn, "SELECT A.Name, A.MichelinRating, COUNT(R.RestaurantID) FROM Award A INNER JOIN Restaurant R ON A.RestaurantID = R.RestaurantID GROUP BY A.Name, A.MichelinRating HAVING COUNT(R.RestaurantID) >= :minimum");
		oci_bind_by_name($result, ":minimum", $minimum);
		oci_execute($result);

		$output = "<br>Retrieved data from Restaurant and Award tables:<br>";
		$output .= "<table border='1'>";
		$output .= "<tr><th>Award Name</th><th>Michelin Stars</th><th># of Restaurants</th></tr>";

		while ($row = oci_fetch_assoc($result)) {
			$output .= "<tr><td>" . $row["NAME"] . "</td><td>" . $row["MICHELINRATING"] . "</td><td>" . $row["COUNT(R.RESTAURANTID)"] . "</td></tr>"; 
		}

		$output .= "</table>";
		echo $output;
	}

	function handleNestedGroupByRequest() {
		global $db_conn;

		$result = oci_parse($db_conn, "SELECT MAX(r.Name), r.PriceRange, MAX(r.AverageScore) FROM Restaurant r WHERE r.AverageScore = (SELECT MAX(AverageScore) FROM Restaurant WHERE PriceRange = r.PriceRange) GROUP BY r.PriceRange ORDER BY r.PriceRange ASC");

		oci_execute($result);

		$output = "<br>Retrieved data from Restaurant table:<br>";
		$output .= "<table border='1'>";
		$output .= "<tr><th>Restaurant</th><th>Price Range</th><th>Rating</th></tr>";

		while ($row = oci_fetch_assoc($result)) {
			$output .= "<tr><td>" . $row["MAX(R.NAME)"] . "</td><td>" . $row["PRICERANGE"] . "</td><td>" . $row["MAX(R.AVERAGESCORE)"] . "</td></tr>"; 
		}

		$output .= "</table>";
		echo $output;
	}

	function handleDisplayRequest()
	{
		global $db_conn;
		$result = executePlainSQL("SELECT * FROM Restaurant");
		printResult($result);
	}

	function handleJoinRequest()
	{
		global $db_conn;

		$price = $_GET["price"];

		$result = oci_parse($db_conn, "SELECT DISTINCT Name, DishName, PriceRange FROM Restaurant r INNER JOIN SignatureDish s ON r.RestaurantID = s.RestaurantID WHERE PriceRange = :price");
   		oci_bind_by_name($result, ":price", $price);
		oci_execute($result);
		
		$output = "<br>Retrieved data from Restaurant and SignatureDish Tables:<br>";
		$output .= "<table border='1'>";
		$output .= "<tr><th>Restaurant</th><th>Signature Dish</th><th>Price Range</th></tr>";

		while ($row = oci_fetch_assoc($result)) {
			$output .= "<tr><td>" . $row["NAME"] . "</td><td>" . $row["DISHNAME"] . "</td><td>" . $row["PRICERANGE"] . "</td></tr>"; 
		}

		$output .= "</table>";
		echo $output;
	}

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('resetTablesRequest', $_POST)) {
				handleResetRequest();
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
			} elseif (array_key_exists('groupBy', $_GET)) {
				handleGroupByRequest();
			} elseif (array_key_exists('displayTuples', $_GET)) {
				handleDisplayRequest();
			} elseif (array_key_exists('displayJoin', $_GET)) {
				handleJoinRequest();
			} elseif (array_key_exists('groupByHaving', $_GET)) {
				handleGroupByHavingRequest();
			} elseif (array_key_exists('nestedGroupBy', $_GET)) {
				handleNestedGroupByRequest();
			}

			disconnectFromDB();
		}
	}

	if (isset($_POST['reset'])) {
		handlePOSTRequest();
	} else if (isset($_GET['countTupleRequest']) || isset($_GET['displayTuplesRequest']) || isset($_GET['groupByRequest']) || isset($_GET['displayJoinRequest']) || isset($_GET['groupByHavingRequest']) || isset($_GET['nestedGroupByRequest'])) {
		handleGETRequest();
	}
	// End PHP parsing and send the rest of the HTML content
	?>
</body>

</html>
