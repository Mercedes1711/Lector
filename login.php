<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
</head>
<body>
  <?php

    if($_SERVER["REQUEST_METHOD"] == "POST"){
      if($_POST['usario']=== "usuario" and $_POST["contraseña"]=== "1234"){
        
      }
    }



  ?>
  <form action= <?php echo htmlspecialchars($_SERVER['PHP_SELF']);?> method= "POST">
    <label>usuario</label>
    <input name= "usuario" type="text">
    <label>contraseña</label>
    <input name= "contraseña" type="password">
    <label>correo</label>
    <input name= "correo" type="text">
    <label> 
    <input type="submit">
</form>
</body>
</html>