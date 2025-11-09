<?php
require '../app/Helpers/jwe_helper.php';

$pub = file_get_contents(WRITEPATH . 'keys/public.pem');
$priv = file_get_contents(WRITEPATH . 'keys/private.pem');

$test = jwe_encrypt(['alg'=>'RSA-OAEP','enc'=>'XC20P'], 'hello', $pub);
$result = jwe_decrypt($test, $priv);

var_dump($result);
