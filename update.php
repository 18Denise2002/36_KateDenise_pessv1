<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<link href="katestyle.css" rel="stylesheet" type="text/css">
<?php 
if (isset($_POST["btnUpdate"])) {
    
    require_once 'db_config.php';
    
    $mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
    
    if ($mysqli->connect_errno) {
        die("Failed to connect to MySQL: ".$mysqli->connect_errno);
    }
    
    $sql = "UPDATE patrolcar SET patrolcarStatusId = ? WHERE patrolcarId = ?";
    
    if (!($stmt = $mysqli->prepare($sql))) {
        die("Prepare failed1: ".$mysqli->errno);
    }
    if (!$stmt->bind_param('ss', $_POST['patrolcarStatus'], $_POST['patrolcarId'])) {
        die("Binding parameters failed: ".$stmt->errno);
    }
    if(!$stmt->execute()) {
        die("Update patrolcar table failed: ".$stmt->errno);
    }
    
    if ($_POST["patrolcarStatus"] == '4') {
        
        $sql = "UPDATE dispatch SET timeArrived = NOW() 
        WHERE timeArrived is NULL AND patrolcarld = ?";
        
        if (!($stmt = $mysqli->prepare($sql))) {
            die("Prepare failed2: ".$mysqli->errno);
        }
        
        if (!$stmt->bind_param('s', $_POST['patrolcarId'])) {
            die("Binding parmeters failed: ".$stmt->errno);
        }
        
        if (!$stmt->execute()) {
            die("Update dispatch table failed: ".$stmt->errno);
        }
    } else if ($_POST["patrolcarStatus"] == '3') {
        
        $sql = "SELECT incidentld  FROM dispatch WHERE     timeCompleted IS NULL AND patrolcarld = ?";
        
        if (!($stmt = $mysqli->prepare($sql))) {
            die("Prepare failed3: ".$mysqli->errno);
        }
        if (!$stmt->bind_param('s', $_POST['patrolcarId'])) {
            die("Binding parameters failed: ".$stmt->errno);
        }
        if (!$stmt->execute()) {
            die("Execute failed failed: ".$stmt->errno);
        }
        if (!($resultset = $stmt->get_result())) {
            die("Getting result set failed: ".$stmt->errno);
        }
        
        $incidentId;
        
        while ($row = $resultset->fetch_assoc()) {
            $incidentId = $row['incidentId'];
        }
        
        $sql = "UPDATE dispatch SET timeCompleted = NOW() WHERE timeCompleted is NULL AND patrolcarld = ?";
        
        if (!($stmt = $mysqli->prepare($sql))) {
            die("Prepare failed4: ".$mysqli->errno);
        }
        if (!$stmt->bind_param('s', $_POST['patrolcarId'])) {
            die("Binding parameters failed: ".$stmt->errno);
        }
        if (!$stmt->execute()) {
            die("Update dispatch table failed: ".$stmt->errno);
        }
        
        $sql = "UPDATE incident SET incidentStatusld = '3' WHERE incidentld = '$incidentId' AND NOT EXISTS (SELECT * FROM dispatch WHERE 	timeCompleted IS NULL AND incidentld = '$incidentId')";
        
        if (!($stmt = $mysqli->prepare($sql))) {
            die("Prepare Failed 11: ".$mysqli->errno);
        }
        if (!$stmt->execute()) {
            die("Update dispatch table failed: ".$stmt->errno);
        }
        $resultset->close();
    }
    $stmt->close();
    $mysqli->close();
    ?>
    <script>window.location="logcall1.php";</script>
    
<?php } ?>
</head>

 

<body>
<?php require_once 'nav.php'; ?>
<br><br>
<?php 
if (!isset($_POST["btnSearch"])) {
?> 
<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
<table width="50%" border="0" align="center" cellpadding="4" cellspacing="4">
    <tr></tr>
    <tr>
        <td>Patrol Car ID:</td>
        <td><input type="text" name="patrolcarId" id="patrolcarId"></td>
        <td><input type="submit" name="btnSearch" id="btnSearch" value="Search"></td>
    </tr>
</table>
</form>
<?php } 
else
{
    require_once 'db_config.php';
    
    $mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
    
    if ($mysqli->connect_errno) {
        die("Failed to connect to MySQL: ".$mysqli->connect_errno);
    }
    
    $sql = "SELECT * FROM patrolcar WHERE patrolcarId = ?";
    
    if (!($stmt = $mysqli->prepare($sql))){
        die("Prepare failed5: ".$mysqli->errno);
    }
    
    if (!$stmt->bind_param('s', $_POST['patrolcarId'])){
        die("Binding parameters failed: ".$stmt->errno);
    }
    
    if (!$stmt->execute()){
        die("Execute failed failed: ".$stmt->errno);
    }
    
    if (!($resultset = $stmt->get_result())){
        die("Getting result set failed: ".$stmt->errno);
    }
    
    if ($resultset->num_rows == 0) {
        ?>
    <script>window.location="update.php";</script>
    <?php }
    
    $patrolcarId;
    $patrolcarStatusId;
    
    while ($row = $resultset->fetch_assoc()){
        $patrolcarId = $row['patrolcarId'];
        $patrolcarStatusId = $row['patrolcarStatusId'];
    }
    
    $sql = "SELECT * FROM patrolcar_status";
    if (!($stmt = $mysqli->prepare($sql))){
        die("Prepare failed6: ".$mysqli->errno);
    }
    
    if (!$stmt->execute()){
        die("Execute failed: ".$stmt->errno);
    }
    
    if (!($resultset = $stmt->get_result())){
        die("Getting result set failed: ".$stmt->errno);
    }
    
    $patrolCarStatusArray;;
    
    while ($row = $resultset->fetch_assoc()){
        $patrolCarStatusArray[$row['statusId']] = $row['statusDesc'];
    }
    
    $stmt->close();
    $resultset->close();
    $mysqli->close();
?>
<form name="form2" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> "> 
    <table width="50%" border="0" align="center" cellpadding="4" cellspacing="4">
    <tr></tr>
    <tr>
        <td>ID :</td>
        <td><?php echo $patrolcarId ?>
        <input type="hidden" name="patrolcarId" id="patrolcarId" value="<?php echo $patrolcarId ?>">
        </td>
    </tr>
    <tr>
        <td>Status :</td>
        <td><select name="patrolcarStatus" id="patrolcarStatus">
            <?php foreach ($patrolCarStatusArray as $key => $value) { ?>
            <option value="<?php echo $key ?>"
            <?php if ($key==$patrolcarStatusId) { ?> selected="selected"
            <?php } ?> >
            <?php echo $value ?>
            </option>
            <?php } ?>
            </select></td>
    </tr>
    <tr>
        <td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnUpdate" id="btnUpdate" value="Update"></td>
    </tr>
    </table>
</form>
    
<?php } ?>
</body>
</html>