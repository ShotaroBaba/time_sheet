<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete</title>
</head>
<body>
    <?php if($_POST['registrationSuccess']) { ?>

    Your registration has completed. Please go back to the <a href="/">top page</a>.

    <?php } else {?>
    Please go back to the <a href="/">top page</a>.
    <?php } ?>
     
</body>
</html>