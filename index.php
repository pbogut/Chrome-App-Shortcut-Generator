<?php
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        $icon = isset($_FILES['icon']['tmp_name']) && $_FILES['icon']['tmp_name'] ? $_FILES['icon']['tmp_name'] : dirname(__FILE__) . '/icon.png';
        $name = isset($_POST['name']) && $_POST['name'] ? $_POST['name'] : 'Chrome App Shortcut Generator';
        $url = isset($_POST['url']) && $_POST['url'] ? $_POST['url'] : 'http://www.smeagol.pl/apps/google-app-shortcut-generator';

        if (strpos($url, 'http') !== 0) {
            $url = "http://$url";
        }

        $json = [
            'manifest_version' => 2,
            'name' => $name,
            'description' => 'Generated at http://www.smeagol.pl/apps/google-app-shortcut-generator',
            'version' => '1.0',
            'icons' => [
                '128' => 'icon.png',
            ],
            'app' => [
                'urls' => [
                    $url,
                ],
                'launch' => [
                    'web_url' => $url,
                    'container' => 'panel',
                ],
            ],
            'permissions' => [
                'unlimitedStorage',
                'notifications'
            ]
        ];
        $dirName = uniqid(time(), true);
        $dirPath = dirname(__FILE__) . '/tmp/' . $dirName;
        mkdir($dirPath, 0777, true);
        $key = openssl_pkey_new();
        openssl_pkey_export($key, $privateKey);
        file_put_contents("$dirPath.pem", $privateKey);
        file_put_contents("$dirPath/manifest.json", json_encode($json), FILE_TEXT);
        copy($icon, "$dirPath/icon.png");
        chdir(dirname(__FILE__) . '/tmp');
        exec(dirname(__FILE__) . "/crxmake.sh {$dirPath} {$dirPath}.pem");

        header('Content-type: application/octet-stream');
        header('Content-disposition: attachment; filename=' . strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $name)) .'.crx');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Length: ' . filesize("$dirPath.crx"));
        readfile("$dirPath.crx");
        unlink("$dirPath.crx");
        unlink("$dirPath.pem");
        unlink("$dirPath/icon.png");
        unlink("$dirPath/manifest.json");
        rmdir($dirPath);
        exit();
    }
?>
<!doctype html>
<html>
<head>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <h1 style="text-align: center">Chrome App Shortcut Generator</h1>
    </div>
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-xs-12 col-md-6">
            <div class="jumbotron">
                <form enctype="multipart/form-data" method="post">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input class="form-control" type="text" id="name" name="name" value="Chrome App Shortcut Generator"/>
                    </div>
                    <div class="form-group">
                        <label for="url">Url</label>
                        <input class="form-control" type="text" id="url" name="url" value="http://www.smeagol.pl/apps/google-app-shortcut-generator"/>
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon file</label>
                        <input type="file" id="icon" name="icon"/>
                        <span class="help-block">Application icon 128x128 png.</span>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
<footer style="text-align: center">
<a href="http://www.smeagol.pl">Smeagol.pl</a>
</footer>
</html>
