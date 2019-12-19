<?php
    function rmdir_recursive($dir) {
        foreach(scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir($dir.'/'.$file)) rmdir_recursive($dir.'/'.$file);
            elseif ($file !== 'clone.php') unlink($dir.'/'.$file);
        }

        rmdir($dir);
    }

    function copy_recursive($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    copy_recursive($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    file_put_contents("../../AutoIdeale-master.zip", fopen('https://github.com/bitfactory2019/AutoIdeale/archive/master.zip', 'r'));

    $zip = new ZipArchive;
    $zip->open("../../AutoIdeale-master.zip");

    copy_recursive("../../AutoIdeale-master/www/images", "../../AutoIdeale-temp");
    @mkdir("../../AutoIdeale-clone");
    $zip->extractTo("../../AutoIdeale-clone");
    $zip->close();
    copy_recursive("../../AutoIdeale-temp", "../../AutoIdeale-clone/www/images");
    rmdir_recursive("../../AutoIdeale-temp");
    rmdir_recursive("../../AutoIdeale-master");
    copy_recursive("../../AutoIdeale-clone/AutoIdeale-master", "../../AutoIdeale-master");
    ?>
    <html>
        <body>
            <div style="text-align: center;color: white; background: green; font: bold 32pt monospace; padding: 40px; margin: 100px;">
                AutoIdeale aggiornato correttamente
            </div>
            <p align="center"><a href="http://www.autoideale.it">Vai al sito</a></p>
        </body>
    </html>
