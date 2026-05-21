<?php

/**
 * @version 1.2
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array{'content':string} $args
 */

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body style="color:#333; font-family: -apple-system,BlinkMacSystemFont,Roboto,Helvetica Neue,sans-serif; font-size: 16px; line-height: 1.5; margin:0">
<?php echo $args['content'] ?>
</body>
</html>
