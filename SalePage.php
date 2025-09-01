<?php include "../inc/dbinfo.inc"; ?>
<html>
<head>
    <title>Sistema de Vendas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px; text-align: left; }
        input, select { padding: 5px; margin: 2px; }
        .form-table td { padding: 5px; }
    </style>
</head>
<body>
<h1>Sistema de Vendas</h1>
<?php

  /* Connect to MySQL and select the database. */
  $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

  if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

  $database = mysqli_select_db($connection, DB_DATABASE);

  /* Ensure that the EMPLOYEES and SALES tables exist. */
  VerifyEmployeesTable($connection, DB_DATABASE);
  VerifySalesTable($connection, DB_DATABASE);

  /* If input fields are populated, add a row to the SALES table. */
  $employee_id = htmlentities($_POST['EMPLOYEE_ID']);
  $product = htmlentities($_POST['PRODUCT']);
  $value = htmlentities($_POST['VALUE']);
  $quantity = htmlentities($_POST['QUANTITY']);
  $completed = isset($_POST['COMPLETED']) ? 1 : 0;

  if ($employee_id && $product && $value && $quantity) {
    AddSale($connection, $employee_id, $product, $value, $quantity, $completed);
  }

  /* Get employees for the select dropdown */
  $employees = GetEmployees($connection);
?>

<!-- Input form -->
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <table border="0" class="form-table">
    <tr>
      <td>FUNCIONÁRIO</td>
      <td>PRODUTO</td>
      <td>VALOR (R$)</td>
      <td>QUANTIDADE</td>
      <td>VENDA FINALIZADA</td>
      <td></td>
    </tr>
    <tr>
      <td>
        <select name="EMPLOYEE_ID" required>
          <option value="">Selecione um funcionário</option>
          <?php
          while($employee = mysqli_fetch_assoc($employees)) {
            echo "<option value='" . $employee['ID'] . "'>" . $employee['NAME'] . "</option>";
          }
          ?>
        </select>
      </td>
      <td>
        <select name="PRODUCT" required>
          <option value="">Selecione um produto</option>
          <option value="Smartphone Galaxy S24">Smartphone Galaxy S24</option>
          <option value="Notebook Dell Inspiron">Notebook Dell Inspiron</option>
          <option value="Monitor 24 polegadas">Monitor 24 polegadas</option>
          <option value="Headset Bluetooth">Headset Bluetooth</option>
          <option value="Tablet Samsung">Tablet Samsung</option>
        </select>
      </td>
      <td>
        <input type="number" name="VALUE" min="1" max="999999" required placeholder="0" />
      </td>
      <td>
        <input type="number" name="QUANTITY" min="1" max="999" required placeholder="1" />
      </td>
      <td>
        <input type="checkbox" name="COMPLETED" value="1" />
      </td>
      <td>
        <input type="submit" value="Registrar Venda" />
      </td>
    </tr>
  </table>
</form>

<!-- Display sales data with employee information -->
<h2>Vendas Registradas</h2>
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <th>ID VENDA</th>
    <th>DATA/HORA</th>
    <th>FUNCIONÁRIO</th>
    <th>EMAIL FUNCIONÁRIO</th>
    <th>PRODUTO</th>
    <th>VALOR (R$)</th>
    <th>QUANTIDADE</th>
    <th>TOTAL (R$)</th>
    <th>STATUS</th>
  </tr>

<?php

$result = mysqli_query($connection, "
  SELECT s.ID, s.CREATED_AT, e.NAME as EMPLOYEE_NAME, e.ADDRESS as EMPLOYEE_ADDRESS, 
         s.PRODUCT, s.VALUE, s.QUANTITY, s.COMPLETED,
         (s.VALUE * s.QUANTITY) as TOTAL
  FROM SALES s 
  JOIN EMPLOYEES e ON s.EMPLOYEE_ID = e.ID 
  ORDER BY s.ID DESC
");

while($query_data = mysqli_fetch_assoc($result)) {
  echo "<tr>";
  echo "<td>" . $query_data['ID'] . "</td>";
  echo "<td>" . date('d/m/Y H:i:s', strtotime($query_data['CREATED_AT'])) . "</td>";
  echo "<td>" . $query_data['EMPLOYEE_NAME'] . "</td>";
  echo "<td>" . $query_data['EMPLOYEE_ADDRESS'] . "</td>";
  echo "<td>" . $query_data['PRODUCT'] . "</td>";
  echo "<td>R$ " . number_format($query_data['VALUE'], 2, ',', '.') . "</td>";
  echo "<td>" . $query_data['QUANTITY'] . "</td>";
  echo "<td>R$ " . number_format($query_data['TOTAL'], 2, ',', '.') . "</td>";
  echo "<td>" . ($query_data['COMPLETED'] ? 'Finalizada' : 'Pendente') . "</td>";
  echo "</tr>";
}
?>

</table>

<!-- Clean up. -->
<?php

  mysqli_free_result($result);
  mysqli_close($connection);

?>

</body>
</html>

<?php

/* Add a sale to the table. */
function AddSale($connection, $employee_id, $product, $value, $quantity, $completed) {
   $e = mysqli_real_escape_string($connection, $employee_id);
   $p = mysqli_real_escape_string($connection, $product);
   $v = mysqli_real_escape_string($connection, $value);
   $q = mysqli_real_escape_string($connection, $quantity);
   $c = mysqli_real_escape_string($connection, $completed);

   $query = "INSERT INTO SALES (EMPLOYEE_ID, PRODUCT, VALUE, QUANTITY, COMPLETED) VALUES ('$e', '$p', '$v', '$q', '$c');";

   if(!mysqli_query($connection, $query)) {
     echo("<p>Error adding sale data: " . mysqli_error($connection) . "</p>");
   } else {
     echo("<p>Venda registrada com sucesso!</p>");
   }
}

/* Get all employees for the dropdown */
function GetEmployees($connection) {
  $query = "SELECT ID, NAME FROM EMPLOYEES ORDER BY NAME";
  $result = mysqli_query($connection, $query);
  
  if (!$result) {
    echo("<p>Error fetching employees: " . mysqli_error($connection) . "</p>");
    return false;
  }
  
  return $result;
}

/* Check whether the EMPLOYEES table exists and, if not, create it. */
function VerifyEmployeesTable($connection, $dbName) {
  if(!TableExists("EMPLOYEES", $connection, $dbName))
  {
     $query = "CREATE TABLE EMPLOYEES (
         ID int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
         NAME VARCHAR(45),
         ADDRESS VARCHAR(90)
       )";

     if(!mysqli_query($connection, $query)) {
       echo("<p>Error creating EMPLOYEES table: " . mysqli_error($connection) . "</p>");
     }
  }
}

/* Check whether the SALES table exists and, if not, create it. */
function VerifySalesTable($connection, $dbName) {
  if(!TableExists("SALES", $connection, $dbName))
  {
     $query = "CREATE TABLE SALES (
         ID int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
         EMPLOYEE_ID int(11) UNSIGNED NOT NULL,
         PRODUCT VARCHAR(100) NOT NULL,
         VALUE int(11) NOT NULL,
         QUANTITY int(11) NOT NULL,
         COMPLETED BOOLEAN DEFAULT FALSE,
         CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         FOREIGN KEY (EMPLOYEE_ID) REFERENCES EMPLOYEES(ID)
       )";

     if(!mysqli_query($connection, $query)) {
       echo("<p>Error creating SALES table: " . mysqli_error($connection) . "</p>");
     }
  }
}

/* Check for the existence of a table. */
function TableExists($tableName, $connection, $dbName) {
  $t = mysqli_real_escape_string($connection, $tableName);
  $d = mysqli_real_escape_string($connection, $dbName);

  $checktable = mysqli_query($connection,
      "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t' AND TABLE_SCHEMA = '$d'");

  if(mysqli_num_rows($checktable) > 0) return true;

  return false;
}
?>