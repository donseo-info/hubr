<?php
/**
 * One-time script: create persona users in WordPress
 * Run: https://dollar.of-crimea.ru/wp-content/themes/hubr/create-personas.php?key=tg-parser-secret-key-change-me
 */

define('ABSPATH_CHECK', true);
if (($_GET['key'] ?? '') !== 'tg-parser-secret-key-change-me') {
    die('Forbidden');
}

require_once __DIR__ . '/../../../wp-load.php';

$personas = [
    ['login'=>'elena_brovkina',   'display'=>'Елена Бровкина',      'first'=>'Елена',    'last'=>'Бровкина',   'gender'=>'f'],
    ['login'=>'artur_id187643',   'display'=>'Артур',                'first'=>'Артур',    'last'=>'',           'gender'=>'m'],
    ['login'=>'songo',             'display'=>'Songo',                'first'=>'Андрей',   'last'=>'',           'gender'=>'m'],
    ['login'=>'vostok_dn',        'display'=>'Восток',               'first'=>'Восток',   'last'=>'',           'gender'=>'m'],
    ['login'=>'imalwex',          'display'=>'Imalwex',              'first'=>'',         'last'=>'',           'gender'=>'m'],
    ['login'=>'nastya_id1640584', 'display'=>'Настя',                'first'=>'Настя',    'last'=>'',           'gender'=>'f'],
    ['login'=>'yuliya_id2190612', 'display'=>'Юлия',                 'first'=>'Юлия',     'last'=>'',           'gender'=>'f'],
    ['login'=>'kai_alvrcan',      'display'=>'Kai Alvrcan',          'first'=>'Kai',      'last'=>'Alvrcan',    'gender'=>'m'],
    ['login'=>'viktoriya_vv',     'display'=>'Виктория Викторовна',  'first'=>'Виктория', 'last'=>'Викторовна', 'gender'=>'f'],
    ['login'=>'kyura1966',        'display'=>'kyura1966',            'first'=>'',         'last'=>'',           'gender'=>'m'],
    ['login'=>'maks_kuznecov',    'display'=>'Макс Кузнецов',        'first'=>'Макс',     'last'=>'Кузнецов',   'gender'=>'m'],
    ['login'=>'lex_vxw',          'display'=>'Lex Vxw',              'first'=>'Lex',      'last'=>'Vxw',        'gender'=>'m'],
    ['login'=>'sergey_seriy',     'display'=>'Sergey Seriy',         'first'=>'Сергей',   'last'=>'Серый',      'gender'=>'m'],
    ['login'=>'olya_id4319284',   'display'=>'Оля',                  'first'=>'Оля',      'last'=>'',           'gender'=>'f'],
    ['login'=>'tretiakov',        'display'=>'Tretiakov',            'first'=>'',         'last'=>'Третьяков',  'gender'=>'m'],
    ['login'=>'lida_dolgan',      'display'=>'LidaD',                'first'=>'Лида',     'last'=>'Долган',     'gender'=>'f'],
    ['login'=>'evlyukhin_alex',   'display'=>'Александр',            'first'=>'Александр','last'=>'Евлюхин',   'gender'=>'m'],
    ['login'=>'nutella93',        'display'=>'Nutella',              'first'=>'',         'last'=>'',           'gender'=>'f'],
    ['login'=>'denissalabuta',    'display'=>'denissalabuta',        'first'=>'Денис',    'last'=>'Салабута',   'gender'=>'m'],
    ['login'=>'sogreev',          'display'=>'Согреев',              'first'=>'',         'last'=>'Согреев',    'gender'=>'m'],
    ['login'=>'katunya',          'display'=>'Katunya',              'first'=>'Катя',     'last'=>'',           'gender'=>'f'],
    ['login'=>'agarkov_timofey',  'display'=>'Тимофей Агарков',      'first'=>'Тимофей',  'last'=>'Агарков',    'gender'=>'m'],
    ['login'=>'allesya_alesya',   'display'=>'allesya_alesya',       'first'=>'Алеся',    'last'=>'',           'gender'=>'f'],
    ['login'=>'oleg_tebloev',     'display'=>'Олег Теблоев',         'first'=>'Олег',     'last'=>'Теблоев',    'gender'=>'m'],
    ['login'=>'super_coach',      'display'=>'super_coach',          'first'=>'',         'last'=>'',           'gender'=>'m'],
    ['login'=>'albina_koryakina', 'display'=>'Albina Koryakina',     'first'=>'Альбина',  'last'=>'Корякина',   'gender'=>'f'],
    ['login'=>'dasha_darichi',    'display'=>'Dasha',                'first'=>'Даша',     'last'=>'',           'gender'=>'f'],
    ['login'=>'sdelano_pod',      'display'=>'Sdelano_pod',          'first'=>'',         'last'=>'',           'gender'=>'m'],
    ['login'=>'anna_sav',         'display'=>'Anna',                 'first'=>'Анна',     'last'=>'',           'gender'=>'f'],
    ['login'=>'varhotskyi',       'display'=>'VARHOTSKYI',           'first'=>'',         'last'=>'Варховский', 'gender'=>'m'],
];

$avatarDir = __DIR__ . '/../../../wp-content/uploads/personas/';
if (!is_dir($avatarDir)) wp_mkdir_p($avatarDir);

$results = [];

foreach ($personas as $p) {
    echo "Processing: {$p['login']}...<br>\n";
    flush();

    // Check if user exists
    $existing = get_user_by('login', $p['login']);
    if ($existing) {
        $results[] = "SKIP: {$p['login']} (already exists, ID={$existing->ID})";
        continue;
    }

    // Random registration date: 1-5 years ago
    $daysAgo = rand(180, 1800);
    $regDate = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days") + rand(0, 86400));

    // Random karma: 10-2000
    $karma = rand(10, 2000);

    // Generate email
    $email = $p['login'] . '@' . ['mail.ru','gmail.com','yandex.ru','inbox.ru','bk.ru'][rand(0,4)];

    // Create user
    $userId = wp_insert_user([
        'user_login'      => $p['login'],
        'user_pass'       => wp_generate_password(16),
        'user_email'      => $email,
        'display_name'    => $p['display'],
        'first_name'      => $p['first'],
        'last_name'       => $p['last'],
        'user_registered' => $regDate,
        'role'            => 'subscriber',
        'nickname'        => $p['login'],
    ]);

    if (is_wp_error($userId)) {
        $results[] = "ERROR: {$p['login']}: " . $userId->get_error_message();
        continue;
    }

    // Set karma
    update_user_meta($userId, 'karma', $karma);
    update_user_meta($userId, 'karma_history', []);

    // Download avatar from thispersondoesnotexist.com
    $avatarFile = $avatarDir . $p['login'] . '.jpg';
    if (!file_exists($avatarFile)) {
        $ch = curl_init('https://thispersondoesnotexist.com/');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        ]);
        $imgData = curl_exec($ch);
        curl_close($ch);

        if ($imgData && strlen($imgData) > 1000) {
            file_put_contents($avatarFile, $imgData);
            sleep(1); // be polite
        }
    }

    // Upload avatar to WP media and set as user avatar
    if (file_exists($avatarFile)) {
        $upload = wp_upload_bits($p['login'] . '.jpg', null, file_get_contents($avatarFile));
        if (empty($upload['error'])) {
            $attachId = wp_insert_attachment([
                'post_mime_type' => 'image/jpeg',
                'post_title'     => $p['display'],
                'post_status'    => 'inherit',
            ], $upload['file']);
            require_once ABSPATH . 'wp-admin/includes/image.php';
            wp_update_attachment_metadata($attachId, wp_generate_attachment_metadata($attachId, $upload['file']));
            // Store avatar attachment ID in usermeta (used by some themes)
            update_user_meta($userId, 'wp_user_avatar', $attachId);
            update_user_meta($userId, '_local_avatar', ['full' => $upload['url'], 'thumb' => $upload['url']]);
        }
    }

    $results[] = "OK: {$p['login']} (ID={$userId}, karma={$karma}, reg={$regDate})";
    echo end($results) . "<br>\n";
    flush();
}

echo "<hr><pre>";
foreach ($results as $r) echo $r . "\n";
echo "</pre>";
echo "<strong>Done!</strong>";
