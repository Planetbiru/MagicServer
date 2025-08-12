<?php
$notifyUrl = 'http://127.0.0.1:8080/notify';
$token = 'change_me_to_a_random_secret';

$data = [
    'message' => 'Halo semua, ini notifikasi penting',
    'type' => 'system',
    'meta' => ['priority'=>'high']
];

$ch = curl_init($notifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) echo "CURL error: $err\n";
else echo "Response: $res\n";
