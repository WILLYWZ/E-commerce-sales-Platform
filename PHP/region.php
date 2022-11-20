<?php 
include("includes/init.php");
$title = "region";
$db = open_sqlite_db("data/project.sqlite");
$messages = array();

function loop($values)
{
  foreach ($values as $value) {
    echo "<option value=\"" . htmlspecialchars($value) . "\">" . htmlspecialchars($value) . "</option>";
  }
}
function print_record($record)
{
?>
  <tr>
    <td><?php echo htmlspecialchars($record["regionID"]); ?></td>
    <td><?php echo htmlspecialchars($record["regionName"]); ?></td>
    <td><?php echo htmlspecialchars($record["regionManager"]); ?></td>
  </tr>
<?php
}

const SEARCH_FIELDS = [
  "all" => "Select Search Category",
  "RegionID" => "By ID",
  "RegionName" => "By Name",
  "RegionManager" => "By Manager",
];

if (isset($_GET['search'])) {
  $do_search = TRUE;

  // check if the category exists
  $category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);
  if (in_array($category, array_keys(SEARCH_FIELDS))) {
    $search_field = $category;
  } else {
    array_push($messages, "Invalid Category");
    $do_search = FALSE;
  }

  // Get search terms
  $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
  $search = trim($search);
} else {
  $do_search = FALSE;
  $category = NULL;
  $search = NULL;
}

// get list of products
$regionID = exec_sql_query($db, "SELECT regionID FROM Region", NULL)->fetchAll(PDO::FETCH_COLUMN);
$regionName = exec_sql_query($db, "SELECT regionName FROM Region", NULL)->fetchAll(PDO::FETCH_COLUMN);
$regionManager = exec_sql_query($db, "SELECT regionManager FROM Region", NULL)->fetchAll(PDO::FETCH_COLUMN);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $regionID = $_POST['regionID'];
  $regionName = $_POST['regionName'];
  $regionManager = $_POST['regionManager'];

  $valid_review = TRUE;

  if (!in_array($regionID, $regionID)) {
    $valid_review = TRUE;
  } else {
    $valid_review = FALSE;
    array_push($messages, "Region ID already exists!");
  }

  if ($regionID == NULL) {
    $valid_review = FALSE;
    array_push($messages, "Region ID could not be empty!");
  }

  if ($regionName == NULL) {
    $valid_review = FALSE;
    array_push($messages, "Region Name could not be empty!");
  }

  if ($regionManager == NULL) {
    $valid_review = FALSE;
    array_push($messages, "Region Manager could not be empty!");
  }


  if ($valid_review) {
    $sql = "INSERT INTO Region (regionID, regionName, regionManager) VALUES (:regionID, :regionName, :regionManager)";
    $params = array(
      ':regionID' => $regionID,
      ':regionName' => $regionName,
      ':regionManager' => $regionManager,
    );
    // Insert valid product info into database
    $result = exec_sql_query($db, $sql, $params);
    if ($result) {
      unset($messages);
      $messages = array();
      array_push($messages, "Entry Successfully Added");
    }
    else {
      unset($messages);
      $messages = array();
      array_push($messages, "Could Not Add Entry");
    }
  }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>E-Commerce Database</title>
  <link rel="stylesheet" href="styles/all.css">
</head>

<body>
  <?php include("includes/header.php"); ?>
  <div class="sidebar">
    <a href="home.php">Home</a>
    <a href="products.php">Products</a>
    <a href="customers.php">Customers</a>
    <a href="transactions.php">Transactions</a>
    <a class="active" href="region.php">Region</a>
    <a href="store.php">Store</a>
    <a href="salespersons.php">Salespersons</a>
  </div>

  <div id="main">
    <?php
    // Write out any messages to the user.
    foreach ($messages as $message) {
      echo "<p><strong>" . htmlspecialchars($message) . "</strong></p>\n";
    }
    ?>

    <form id="searchForm" action="region.php" method="get" novalidate>
      <select name="category">
        <?php foreach (SEARCH_FIELDS as $field_name => $label) { ?>
          <option value="<?php echo htmlspecialchars($field_name); ?>"><?php echo htmlspecialchars($label); ?></option>
        <?php } ?>
      </select>
      <input type="text" name="search" required />
      <button type="submit">Search</button>
    </form>


    <?php
    if ($do_search) {
    ?>
      <h2>Search Results</h2>

      <?php
      if ($search_field == "all") {
        // Search across all fields
        $sql = "SELECT * FROM Region WHERE (regionID LIKE '%' || :search || '%') 
                                          OR (regionName LIKE '%' || :search || '%') 
                                          OR (regionManager LIKE '%' || :search || '%') ";
        $params = array(
          ':search' => $search
        );
      } else {
        // Search across the specified field
        $sql = "SELECT * FROM Region WHERE ($search_field LIKE '%' || :search || '%')";
        $params = array(
          ':search' => $search
        );
      }
    } else {
      ?>
      <h2>Products List</h2>
      <?php
      $sql = "SELECT * FROM Region";
      $params = array();
    }

    $result = exec_sql_query($db, $sql, $params);
    if ($result) {
      $records = $result->fetchAll();

      if (count($records) > 0) {
      ?>
        <table id = "region">
          <tr>
            <th>Region ID</th>
            <th>Region Name</th>
            <th>Region Manager</th>
          </tr>

          <?php
          foreach ($records as $record) {
            print_record($record);
          }
          ?>
        </table>
    <?php
      } else {
        // No results found
        echo "<p> No Match Found. </p>";
      }
    }
    ?>
  </div>
  <div id="submit">
    <h2>Add New Region</h2>

    <form action="region.php" method="post" novalidate>

      <div>
        <label>Region ID</label>
        <input type="text" name="regionID" />
      </div>

      <div>
        <label>Region Name</label>
        <input type="text" name="regionName" />
      </div>

      <div>
        <label>RegionManager </label>
        <input type="text" name="regionManager" />
      </div>


      <div>
        <button id="add" type="submit" value="submit">Add Region</button>
      </div>
    </form>
  </div>

  <?php include("includes/footer.php"); ?>

</body>

</html>
